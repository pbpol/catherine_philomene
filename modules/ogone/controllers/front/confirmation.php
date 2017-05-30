<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class OgoneConfirmationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $this->context = Context::getContext();

        $ogone = new Ogone();

        $ogone->log('controllers/front/confirmation.php called');

        $ogone_params = array();
        $ignore_key_list = $ogone->getIgnoreKeyList();
        foreach ($_GET as $key => $value) {
            if (Tools::strtoupper($key) != 'SHASIGN' && $value != '' && !in_array($key, $ignore_key_list)) {
                $ogone_params[Tools::strtoupper($key)] = $value;
            }
        }

        $sha_sign_received = Tools::getIsset('SHASIGN') ? Tools::getValue('SHASIGN') : '';
        $sha1 = $ogone->calculateShaSign($ogone_params, Configuration::get('OGONE_SHA_OUT'));
        $ogone->log('SHA CALCULATED : ' . $sha1);
        $ogone->log('SHA RECEIVED : ' . $sha_sign_received);

        $id_module = $ogone->id;
        $id_cart = $ogone->extractCartId($ogone_params['ORDERID']);

        if ($sha_sign_received && $sha1 != $sha_sign_received) {
            die('Invalid SHA SIGN');
        }

        $ogone_return_code = (int) $ogone_params['STATUS'];
        $ogone->log('ogone_return_code : ' . $ogone_return_code);
        $existing_id_order = (int) Order::getOrderByCartId($id_cart);
        $ogone->log('existing_id_order : ' . $existing_id_order);
        $ogone_state = $ogone->getCodePaymentStatus($ogone_return_code);
        $ogone->log('ogone_state : ' . $ogone_state);
        $payment_state_id = $ogone->getPaymentStatusId($ogone_state);
        $ogone->log('payment_state_id : ' . $payment_state_id);

        if (!$existing_id_order && in_array($payment_state_id, array(Configuration::get(Ogone::PAYMENT_ERROR), Configuration::get(Ogone::PAYMENT_CANCELLED)))) {
            $ogone->log('No existing order id, ogone status %s mapped to %s, leaving validation script', $ogone_return_code, $payment_state_id);
            $redirect = 'index.php?controller=order&step=3';
            Tools::redirect($redirect);
            exit;
        }

        $query =  'SELECT secure_key FROM ' . _DB_PREFIX_ . 'customer WHERE id_customer = ' .
            (int) $this->context->customer->id;
        $key = Db::getInstance()->getValue($query);
        $ogone_link = $this->context->link->getPageLink('order-confirmation');
        $tpl_vars = array(
            'id_module' => $id_module,
            'id_cart' => $id_cart,
            'key' => $key,
            'ogone_link' => $ogone_link,
            'operation' => Configuration::get('OGONE_OPERATION') ?
                Configuration::get('OGONE_OPERATION') :
                Ogone::OPERATION_SALE,
            'order_id' => Tools::getValue('orderID'),
            'support_link' => $this->context->link->getPageLink(version_compare(_PS_VERSION_, '1.5', 'lt') ? 'contact-form' : 'contact', null, null, array('message' => sprintf($this->module->l('Processing of order %s'), Tools::getValue('orderID'))))
        );

        $ogone->log($tpl_vars);

        $this->context->smarty->assign($tpl_vars);

        $this->setTemplate('waiting.tpl');
    }
}
