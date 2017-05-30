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

class Payment14Controller extends FrontController
{

    /** Nom du fichier php front office */
    public $php_self = 'modules/ogone/payment.php';

    public $template = '';

    public function setTemplate($template)
    {
        $this->template = dirname(__FILE__) . '/../views/templates/front/'.$template;
    }

    public function displayContent()
    {
        if (Context::getContext()->customer->id ==  Context::getContext()->cookie->id_customer) {
            Context::getContext()->customer->logged = Context::getContext()->cookie->logged;
        }

        $this->module = new Ogone();

        $cart = Context::getContext()->cart;

        $alias = new OgoneAlias(Tools::getValue('id_alias'));

        if (!Validate::isLoadedObject($alias) || $alias->id_customer != $cart->id_customer) {
            Tools::redirect('index.php?controller=order');
        }

        $alias_data = $alias->toArray();
        $alias_data['logo'] = $this->module->getAliasLogoUrl($alias_data, 'cc_medium.png');

        $validate_url = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
        htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
        __PS_BASE_URI__.'modules/ogone/validate_alias.php';

        $aliases_url = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
        htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
        __PS_BASE_URI__.'modules/ogone/aliases.php';

        $return_order_link = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
        htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
        __PS_BASE_URI__.'order.php?step=3';

        Context::getContext()->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'alias_data' => $alias_data,
            'expiry_date' => date('m/Y', strtotime($alias_data['expiry_date'])),
            'return_order_link' => $return_order_link,
            'validate_link' => $validate_url,
            'alias_link' => $aliases_url,
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            '3ds_active' => $this->module->use3DSecureForDL(),
        ));

        $this->setTemplate('payment_execution.tpl');

        parent::displayContent();

        echo Context::getContext()->smarty->fetch($this->template);
    }
}
