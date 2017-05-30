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

class Validation14Controller extends FrontController
{

    /** Nom du fichier php front office */
    public $php_self = 'modules/ogone/alias_validation.php';

    public $template = '';

    public function setTemplate($template)
    {
        $this->template = dirname(__FILE__) . '/../views/templates/front/'.$template;
    }

    public function displayContent()
    {

        $cart = Context::getContext()->cart;
        $this->module = new Ogone();

        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0 ||
            !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'ogone') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (Tools::getValue('id_alias') && (int)Tools::getValue('id_alias') > 0) {
            $alias = new OgoneAlias((int)Tools::getValue('id_alias'));
        } else {
            die($this->module->l('This alias is not available.', 'validation'));
        }

        list($result, $message) = $this->module ->doDirectLinkAliasPayment($cart, $alias);

        switch ($result) {
            /* Payment done without 3dsecure */
            case Ogone::DL_ALIAS_RET_PAYMENT_DONE:
                $redirect = 'order-confirmation.php?id_cart='.$cart->id.'&id_module='.
                    $this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key;
                Tools::redirect($redirect);
                break;
            /* Payment via 3-D Secure, need to inject 3-D secure HTML */
            case Ogone::DL_ALIAS_RET_INJECT_HTML:
                $tpl_vars = array(
                    'result' => true,
                    'message'  => $message
                );
                Context::getContext()->smarty->assign($tpl_vars);
                $this->setTemplate('validation-message.tpl');
                break;
            /* Error has occured */
            case Ogone::DL_ALIAS_RET_ERROR:
                $tpl_vars = array(
                    'result' => false,
                    'error'  => $message
                );
                Context::getContext()->smarty->assign($tpl_vars);
                $this->setTemplate('validation-error.tpl');
                break;
            /* Any other return */
            default:
                Tools::redirect('order.php?step=3');
                break;
        }

        parent::displayContent();

        echo Context::getContext()->smarty->fetch($this->template);
    }
}
