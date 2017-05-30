<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class OgoneTransactionLog extends ObjectModel
{

    /**
     * Cart id. Is used as ORDERID in transaction processing
     * @var int
     */
    public $id_cart;

    /**
     * Order id
     * @var int Id order
     */
    public $id_order;

    /**
     * Customer
     * @var int
     */
    public $id_customer;

    /**
     * @var string Ogone PAYID
     */
    public $payid;

    /**
     * Ogone response status code (numeric)
     * @var int
     */
    public $status;

    /**
     * Json-encoded raw Ogone response
     * @var string
     */
    public $response;

    public $date_add;

    public $date_upd;

    protected $fieldsRequired = array();
    protected $fieldsSize = array();
    protected $fieldsValidate = array( 'id_cart' => 'isUnsignedInt', 'id_order' => 'isUnsignedInt',
        'id_customer' => 'isUnsignedInt', 'status' => 'isUnsignedInt', 'date_add' => 'isDate', 'date_upd' => 'isDate');
    protected $table = 'ogone_tl';
    protected $identifier = 'id_ogone_tl';

    public function getFields()
    {
        $fields = array();
        parent::validateFields();
        if (isset($this->id)) {
            $fields['id_ogone_tl'] = (int)($this->id);
        }
        $fields['id_cart'] =  (int)($this->id_cart);
        $fields['id_order'] =  (int)($this->id_order);
        $fields['id_customer'] =  (int)($this->id_customer);
        $fields['status'] =  (int)($this->status);
        $fields['response'] = pSQL($this->response);
        $fields['payid'] = pSQL($this->payid);

        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);
        return $fields;
    }

    /**
     * Returns decoded response as array or empty array on error
     */
    public function getResponseDecoded()
    {
        $decoded = self::decodeResponse($this->response);
        return (is_array($decoded)) ? $decoded : array();
    }

    /**
     * Encodes response to format which can be stocked in database
     * @param mixed $response
     * @return string Encoded response
     */
    public static function encodeResponse($response)
    {
        return Tools::jsonEncode($response);
    }

    /**
     * Decodes response
     * @param string $response
     * @return array Decoded response
     */
    public static function decodeResponse($response)
    {
        return Tools::jsonDecode($response, true);
    }

    public static function getAllByCartId($id_cart)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_tl' .
        ' WHERE id_cart=' . (int) $id_cart . ' ORDER BY date_add DESC, id_ogone_tl DESC';
        return Db::getInstance()->executeS($query);
    }

    public static function getAllByOrderId($id_order)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_tl' .
        ' WHERE id_order=' . (int) $id_order . ' ORDER BY date_add DESC, id_ogone_tl DESC';
        return Db::getInstance()->executeS($query);
    }

    public static function getAllByPayId($payid)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_tl' .
        ' WHERE paid=' . pSql($payid) . ' ORDER BY date_add DESC, id_ogone_tl DESC';
        return Db::getInstance()->executeS($query);
    }

    public static function getLastByCartId($id_cart)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_tl' .
        ' WHERE id_cart=' . (int) $id_cart . ' ORDER BY date_add DESC, id_ogone_tl DESC';
        return Db::getInstance()->getRow($query);
    }

    public static function getLastByOrderId($id_order)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_tl' .
        ' WHERE id_order=' . (int) $id_order . ' ORDER BY date_add DESC, id_ogone_tl DESC';
        return Db::getInstance()->getRow($query);
    }

    public static function getLastByPayId($payid)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_tl' .
        ' WHERE paid=' . pSql($payid) . ' ORDER BY date_add DESC, id_ogone_tl DESC';
        return Db::getInstance()->getRow($query);
    }

    public static function getOgoneOrderIdFromTransaction($transaction)
    {
        if ($transaction && is_array($transaction) && isset($transaction['response'])) {
            $response = Tools::jsonDecode($transaction['response'], true);
            if (is_array($response) && isset($response['ORDERID'])) {
                return $response['ORDERID'];
            }
        }
        return null;
    }

    public static function getOgoneOrderIdByCartId($id_cart)
    {
        return self::getOgoneOrderIdFromTransaction(self::getLastByCartId($id_cart));
    }

    public static function getOgoneOrderIdByOrderId($id_order)
    {
        return self::getOgoneOrderIdFromTransaction(self::getLastByOrderId($id_order));
    }

    public static function getTransactionsByOrderIdAndStatus($id_order, $statuses = array())
    {
        $result = array();
        $query  = 'SELECT * FROM `'._DB_PREFIX_.'ogone_tl` WHERE id_order = ' . (int)$id_order ;
        if ($statuses && is_array($statuses)) {
            $query .= ' AND status IN ('.implode(array_map('intval', $statuses)).')';
        }
        foreach (Db::getInstance()->executeS($query) as $transaction) {
            $response = Tools::jsonDecode($transaction['response'], true);
            $transaction['response'] = $response;
            $result[] = $transaction;
        }
        return $result;
    }

    public static function getTransactionsByCartIdAndStatus($id_cart, $statuses = array())
    {
        $result = array();
        $query  = 'SELECT * FROM `'._DB_PREFIX_.'ogone_tl` WHERE id_cart = ' . (int)$id_cart ;
        if ($statuses && is_array($statuses)) {
            $query .= ' AND status IN ('.implode(array_map('intval', $statuses)).')';
        }
        foreach (Db::getInstance()->executeS($query) as $transaction) {
            $response = Tools::jsonDecode($transaction['response'], true);
            $transaction['response'] = $response;
            $result[] = $transaction;
        }
        return $result;
    }
}
