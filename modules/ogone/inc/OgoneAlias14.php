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

/**
 * Alias model for Prestashop 1.5+
 */
class OgoneAlias extends ObjectModel
{

    public $id_customer;

    public $alias;

    public $active;

    public $cardno;

    public $cn;

    public $brand;

    public $expiry_date;

    public $date_add;

    public $date_upd;

    public $is_temporary;

    protected $fieldsRequired = array();
    protected $fieldsSize = array();
    protected $fieldsValidate = array('id_customer' => 'isUnsignedId', 'date_add' => 'isDate',
        'date_upd' => 'isDate', 'expiry_date' => 'isDate');
    protected $table = 'ogone_alias';
    protected $identifier = 'id_ogone_alias';

    public function getFields()
    {

        parent::validateFields();
        $fields = array();
        if (isset($this->id)) {
            $fields['id_ogone_alias'] = (int)($this->id);
        }
        $fields['id_customer'] = (int)($this->id_customer);
        $fields['alias'] = pSql($this->alias);
        $fields['cardno'] = pSql($this->cardno);
        $fields['cn'] = pSql($this->cn);
        $fields['brand'] = pSql($this->brand);
        $fields['active'] = (int)($this->active);
        $fields['expiry_date'] = pSQL($this->expiry_date);
        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);
        $fields['is_temporary'] = (int)$this->is_temporary;
        return $fields;
    }

    public static function getCustomerActiveAliases($id_customer)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ .
        'ogone_alias WHERE id_customer = ' . (int) $id_customer . ' AND active = 1 AND expiry_date > DATE(NOW())';
        return Db::getInstance()->executeS($query);
    }

    public static function getByAlias($alias)
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'ogone_alias WHERE alias = "' . pSql($alias) . '"';
        return Db::getInstance()->getRow($query);
    }

    public function toArray()
    {
        return $this->getFields();
    }
}
