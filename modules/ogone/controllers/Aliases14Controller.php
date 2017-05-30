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

class Aliases14Controller extends FrontController
{

    /** Nom du fichier php front office */
    public $php_self = 'modules/ogone/aliases.php';

    public $template = '';

    public function setTemplate($template)
    {
        $this->template = dirname(__FILE__) . '/../views/templates/front/'.$template;
    }

    public function run()
    {

        $this->init();
        $this->module = Module::getInstanceByName('ogone');

        $this->preProcess();
        if (!Tools::getValue('result')) {
            $this->displayHeader();
        }
        $this->process();
        $this->displayContent();
        if (!Tools::getValue('result')) {
            $this->displayFooter();
        }
    }


    public function displayContent()
    {
        if (Context::getContext()->customer->id ==  Context::getContext()->cookie->id_customer) {
            Context::getContext()->customer->logged = Context::getContext()->cookie->logged;
        }

        $this->dispatch();

        parent::displayContent();
        echo Context::getContext()->smarty->fetch($this->template);
    }

    protected function dispatch()
    {
        $customer = Context::getContext()->customer;
        if (!$customer || !$customer->isLogged()) {
            $url = 'index.php?controller=authentication&back=' .
                urlencode(
                    (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
                    __PS_BASE_URI__.'modules/ogone/aliases.php'
                );
                Tools::redirect($url);
        } elseif (Tools::getValue('result')) {
            return $this->processAliasCreationReturn();
        } elseif (Tools::getValue('action') === 'delete' && Tools::getValue('id_alias')) {
            $this->processDelete();
        }

        $this->assignList();
    }

    protected function processAliasCreationReturn()
    {
        $tpl_vars = array();
        $this->display_header = false;
        $this->display_footer = false;
        if (Tools::getValue('result') == 'ok' && Tools::getValue('alias_full') && $this->module->verifyShaSignatureFromGet()) {
            $data = $this->module->getAliasReturnVariables();
            list($result, $message) = $this->module->createAlias(Context::getContext()->customer->id, $data, true);
            if ($result) {
                if ($this->module->makeImmediateAliasPayment() &&  Tools::getValue('aip')) {
                    $tpl_vars['payment_url'] = $this->module->getLocalAliasPaymentLink(array('id_alias' => (int)$message));
                } else {
                    $tpl_vars['payment_url'] = null;

                }
                Context::getContext()->smarty->assign($tpl_vars);
                $this->setTemplate('parent-reload.tpl');
                return true;
            } else {
                $tpl_vars['error'] = $message;
            }

        } else {
            $tpl_vars['error'] = Tools::getValue('NCError') ?
            Tools::getValue('NCError') :
            $this->module->l('Alias creation error');
        }

        Context::getContext()->smarty->assign($tpl_vars);
        $this->setTemplate('aliases-error.tpl');

    }

    protected function getAliasReturnVariables()
    {
        $raw_data = $_GET; // cannot use Tools::getValue
        $raw_data = array_change_key_case($raw_data, CASE_UPPER);
        $data = array();
        $data['ALIAS'] = isset($raw_data['ALIAS_ALIASID']) ? $raw_data['ALIAS_ALIASID'] : null;
        $data['CARDNO'] = isset($raw_data['CARD_CARDNUMBER']) ? $raw_data['CARD_CARDNUMBER'] : null;
        $data['CN'] = isset($raw_data['CARD_CARDHOLDERNAME']) ? $raw_data['CARD_CARDHOLDERNAME'] : null;
        $data['ED'] = isset($raw_data['CARD_EXPIRYDATE']) ? $raw_data['CARD_EXPIRYDATE'] : null;
        $data['BRAND'] = isset($raw_data['CARD_BRAND']) ? $raw_data['CARD_BRAND'] : null;
        $data['NCERROR'] = isset($raw_data['ALIAS_NCERROR']) ? $raw_data['ALIAS_NCERROR'] : null;
        $data['STATUS'] = isset($raw_data['ALIAS_STATUS']) ? $raw_data['ALIAS_STATUS'] : null;
        if (isset($raw_data['ALIAS_STOREPERMANENTLY'])) {
            $data['STOREPERMANENTLY'] = $raw_data['ALIAS_STOREPERMANENTLY'];
        }
        $data['SHASIGN'] = isset($raw_data['SHASIGN']) ? $raw_data['SHASIGN'] : null;
        return $data;
    }

    protected function assignList()
    {
        $customer = Context::getContext()->customer;
        $module_url = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
            __PS_BASE_URI__.'modules/ogone/aliases.php';

        if (!$customer || !$customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication&back=' . urlencode($module_url));
        }

        if ($this->module->canUseAliases()) {
            $aliases = array();

            foreach (OgoneAlias::getCustomerActiveAliases($customer->id) as $alias) {
                $alias['delete_link'] = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
                htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.
                'modules/ogone/aliases.php?action=delete&id_alias='.$alias['id_ogone_alias'];
                $alias['logo'] = $this->module->getAliasLogoUrl($alias, 'cc_small.png');
                $aliases[] = $alias;
            }

            $tpl_vars = array(
                'url' => $module_url,
                'aliases' => $aliases,
                'htp_urls' => $this->module->getHostedTokenizationPageRegistrationUrls($customer->id),
            );
            Context::getContext()->smarty->assign($tpl_vars);
            $this->setTemplate('aliases.tpl');
        } else {
            $this->setTemplate('aliases-disabled.tpl');
        }

    }

    public function processDelete()
    {
        $id_alias = (int) Tools::getValue('id_alias');
        $alias = new OgoneAlias((int) $id_alias);
        if (!Validate::isLoadedObject($alias) ||
            (int) $alias->id_customer !== (int) Context::getContext()->customer->id) {
            Context::getContext()->smarty->assign('errors', array($this->module->l('Invalid customer')));
            return false;
        } else {
            if ($alias->delete()) {
                Context::getContext()->smarty->assign('messages', array($this->module->l('Alias deleted')));
                return true;
            }
        }

        Context::getContext()->smarty->assign('errors', array($this->module->l('Unable delete alias')));
        return false;

    }
}
