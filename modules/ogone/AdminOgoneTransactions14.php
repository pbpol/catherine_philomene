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

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ogone.php';

class AdminOgoneTransactions14 extends AdminTab
{

    public $module = null;

    public $table = 'ogone_tl';

    public $className = 'AdminOgoneTransactions14';

    protected $return_code_list = null;

    public $view = 1;


    public function __construct()
    {
        $this->module = new Ogone();

        parent::__construct();
        $this->context = Context::getContext();

        $this->fieldsDisplay = array(
            'id_order' => array('title' => $this->l('Reference'), 'align' => 'center', 'width' => 25,
                'filter_key' => 'o!id_order'),
            'id_cart' => array('title' => $this->l('Cart'), 'width' => 25, 'align' => 'center', 'type' => 'integer'),
            'date_add' => array('title' => $this->l('Order date'), 'width' => 35, 'align' => 'right',
                'type' => 'datetime', 'filter_key' => 'a!date_add'),
            'payid' => array('title' => $this->l('payid'), 'width' => 80),
            'status' => array('title' => $this->l('status'), 'width' => 60, 'callback' => 'printPaymentStatus'),
        );
        $this->_select .= 'o.id_order AS reference';
        $this->_join .= ' LEFT OUTER JOIN ' . _DB_PREFIX_ . 'cart c ON c.id_cart = a.id_cart';
        $this->_join .= ' LEFT OUTER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_cart = a.id_cart';

    }

    public function renderView()
    {
        if (Tools::getValue('id_ogone_tl')) {
            $transaction = new OgoneTransactionLog(Tools::getValue('id_ogone_tl'));
            $this->tpl_view_vars['transaction']= $transaction;
        }
        return parent::renderView();
    }


    public function printPaymentStatus($status, $row = null)
    {
        if ($this->return_code_list === null) {
            $this->return_code_list = $this->module->getReturnCodesList();
        }
        $name = isset($this->return_code_list[$status]) ? $this->return_code_list[$status] : $status;
        list($background_color, $color) = $this->module->getPaymentStatusColor($status);
        $pattern = '<span class="label color_field" style="background-color:%s;color:%s">%s</status>';
        return sprintf($pattern, $background_color, $color, $name);
    }

    /* 1.4 wants method name w/o camel caps */
    public function viewogone_tl()
    {
        $id_order = null;
        if (Tools::getValue('id_order')) {
            $id_order = Tools::getValue('id_order');
        } else if (Tools::getValue('id_ogone_tl')) {
            $order = new OgoneTransactionLog(Tools::getValue('id_ogone_tl'));
            if ($order->id_order) {
                $id_order = $order->id_order;
            } else if ($order->id_cart) {
                $id_order = Order::getOrderByCartId($order->id_cart);
            }
        }
        if ($id_order) {
            Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders'));
        } else {
            $this->displayList();

        }
    }
}
