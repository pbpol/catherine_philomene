<?php
/**
*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
*
* @author    Línea Gráfica E.C.E. S.L.
* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class LGTagManager extends Module
{
    public function __construct()
    {
        $this->name        = 'lgtagmanager';
        $this->tab         = 'analytics_stats';
        $this->version     = '1.0.5';
        $this->author      = 'Línea Gráfica';
        $this->displayName = $this->l('Google Tag Manager');
        $this->module_key  = 'e3b1114e44a067f8637853ff20ade838';

        parent::__construct();
        if (version_compare(_PS_VERSION_, '1.6.0', '>')) {
            $this->bootstrap = true;
        } else {
            $this->bootstrap = false;
        }

        if ($this->id && !Configuration::get('TAG_MANAGER_ID')) {
            $this->warning = $this->l('You have not set your Tag Manager ID yet');
        }
        $this->description = $this->l('Integrate Google Tag Manager script into your shop.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        /** Backward compatibility */
        require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
    }

    public function install()
    {
        return (
            parent::install()
            && $this->registerHook('header')
            && $this->registerHook('top')
            && $this->registerHook('displayAfterBody')
        );
    }

    private function getP()
    {
        $default_lang = $this->context->language->id;
        $lang         = Language::getIsoById($default_lang);
        $pl           = array('es','fr');
        if (!in_array($lang, $pl)) {
            $lang = 'en';
        }
        $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/publi/style.css');
        $base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ?
            'https://'.$this->context->shop->domain_ssl :
            'http://'.$this->context->shop->domain);
        if (version_compare(_PS_VERSION_, '1.5.0', '>')) {
            $uri = $base.$this->context->shop->getBaseURI();
        } else {
            $uri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')  ?
                    'https://'._PS_SHOP_DOMAIN_SSL_DOMAIN_:
                    'http://'._PS_SHOP_DOMAIN_).__PS_BASE_URI__;
        }
        $path = _PS_MODULE_DIR_.$this->name
            .DIRECTORY_SEPARATOR.'views'
            .DIRECTORY_SEPARATOR.'publi'
            .DIRECTORY_SEPARATOR.$lang
            .DIRECTORY_SEPARATOR.'index.php';
        $object = Tools::file_get_contents($path);
        $object = str_replace('src="/modules/', 'src="'.$uri.'modules/', $object);

        return $object;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitTagManager')) {
            Configuration::updateValue('TAG_MANAGER_ID', Tools::getValue('TAG_MANAGER_ID'));
            if (version_compare(_PS_VERSION_, '1.6.0', '>')) {
                $output = '
                <div class="conf confirm alert alert-success">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    '.$this->l('Settings updated').'
                <br></div>';
            } else {
                $output = '
                <div class="conf confirm alert">
                    '.$this->l('Settings updated').'
                <br></div>';
            }
        }
        return $output.$this->getP().$this->displayForm();
    }

    public function displayForm()
    {
        $this->fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->displayName,
                    'icon' => 'icon-user'
                ),
                'input' => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Your tag manager ID'),
                        'name'     => 'TAG_MANAGER_ID',
                        'required' => true,
                        'col'      => '1',
                        'hint'     => $this->l('Example:').' GTM-XXXXXX',
                    ),
                    array(
                        'type'         => 'html',
                        'label'        => $this->l('How to install Google Tag Manager:'),
                        'name'         => 'LGTAGMANAGER_INTRUCTIONS',
                        'html_content' => '<p>'
                            .$this->l('1.Add your Tag Manager ID in the field above and click on "Update ID"').'</br>'
                            .$this->l('2.On your FTP, edit the file "/themes/your_current_theme/header.tpl"').'</br>'
                            .$this->l('3.Add the following code immediately after the opening "body" tag:')
                            .'&nbsp;<span style="font-weight:bold;">{hook h =\'displayAfterBody\'}</span></br>'
                            .$this->l('4.Save the changes and clear out your cache to take them into account').'</p>',
                    ),
                    array(
                        'type'         => 'html',
                        'label'        => $this->l('How to implement Google Analytics:'),
                        'name'         => 'LGTAGMANAGER_INTRUCTIONS2',
                        'html_content' => '<p>'
                            .$this->l('1.On your Tag Manager account, add a new tag').'</br>'
                            .$this->l('2.In the section "Tag Configuration", choose "Universal Analytics" tag type,').
                            '&nbsp;'
                            .$this->l('add your Analytics tracking ID and choose "Page View" tracking type').'</br>'
                            .$this->l('3.In the section "Triggering", create a new trigger').'&nbsp;'
                            .$this->l('with “Page View” type and “All Page Views” firing').'</br>'
                            .$this->l('4.Click on "Create Tag" and then on the red button "Publish"').'</p>',
                    ),
                    array(
                        'type'         => 'html',
                        'label'        => $this->l('How to see the transactions in Analytics:'),
                        'name'         => 'LGTAGMANAGER_INTRUCTIONS3',
                        'html_content' => '<p>'
                            .$this->l('1.On your Tag Manager account, add a new tag').'&nbsp;'
                            .$this->l('(create a second tag, do not modify the tag created above)').'</br>'
                            .$this->l('2.In the section "Tag Configuration", choose "Universal Analytics" tag type,').
                            '&nbsp;'
                            .$this->l('add your Analytics tracking ID and choose "Transaction" tracking type').'</br>'
                            .$this->l('3.In the section "Triggering", create a new trigger').'&nbsp;'
                            .$this->l('with “Page View” type, “Some Page Views” firing').'&nbsp;'
                            .$this->l('and select the condition "Page URL" - "contains" - "order-confirmation"').'</br>'
                            .$this->l('4.Click on "Create Tag" and then on the red button "Publish"').'</br>'
                            .$this->l('5.If you use PayPal, you should create a third tag').'&nbsp;'
                            .$this->l('with "Universal Analytics" tag type, "Transaction" tracking type,').
                            '&nbsp;'
                            .$this->l('create a new trigger with “Page View” type, “Some Page Views” firing,').
                            '&nbsp;'
                            .$this->l('select the first condition "Page URL" - "contains" - "paypal"').'&nbsp;'
                            .$this->l('and add the second condition "Page URL" - "contains" - "submit"').'</br>'
                            .$this->l('6.On your Analytics account,').'&nbsp;'
                            .$this->l('go to "Admin" and click on "Ecommerce Settings"').'&nbsp;'
                            .$this->l('choose the option "Enable Ecommerce: ON" and save').'</br>'
                            .$this->l('7.Then make a purchase on your website or wait until a customer does it').'</br>'
                            .$this->l('8.On your Analytics account,').'&nbsp;'
                            .$this->l('go to "Reporting > Conversions > Ecommerce > Overview"').'&nbsp;'
                            .$this->l('(make sure that the date interval is correctly set)').'</p>',
                    ),
                ),
                'submit' => array(
                    'name'  => 'submitTagManager',
                    'title' => $this->l('Update ID'),
                )
            )
        );

        $this->fields_value['TAG_MANAGER_ID'] = Tools::safeOutput(
            Tools::getValue('TAG_MANAGER_ID', Configuration::get('TAG_MANAGER_ID'))
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this;
        $helper->fields_value    = $this->fields_value;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $default_lang                     = $this->context->language->id;
        $helper->default_form_language    = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title          = $this->displayName;
        $helper->show_toolbar   = false; // false -> remove toolbar
        $helper->toolbar_scroll = false; // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action  = 'submitTagManager';
        return $helper->generateForm(array($this->fields_form));
    }

    public function hookHeader($params)
    {
        $myController =$this->context->controller->php_self;
        if (
            $myController=='order-confirmation'
            or (
                stripos($_SERVER['REQUEST_URI'], 'paypal') != false
                and stripos($_SERVER['REQUEST_URI'], 'submit') != false
            )
        ) {
            if ($myController=='order-confirmation') {
                $id_order = $this->context->controller->id_order;
            } elseif (
                stripos($_SERVER['REQUEST_URI'], 'paypal') != false
                and stripos($_SERVER['REQUEST_URI'], 'submit') != false
            ) {
                $id_order = Tools::getValue('id_order');
            }
            $order = new Order($id_order);
            $parameters = Configuration::getMultiple(array('PS_LANG_DEFAULT'));

            if (Validate::isLoadedObject($order)) {
                $deliveryAddress = new Address((int)$order->id_address_delivery);

                $conversion_rate = 1;
                if ($order->id_currency != Configuration::get('PS_CURRENCY_DEFAULT')) {
                        $currency = new Currency((int)$order->id_currency);
                        $conversion_rate = (float)$currency->conversion_rate;
                }

                // Order general information
                $trans = array(
                        'id'       => (int)$order->id,                                  // order ID - required
                        'store'    => htmlentities(Configuration::get('PS_SHOP_NAME')), // affiliation or store name
                        'total'    => Tools::ps_round(
                            (float)$order->total_paid / (float)$conversion_rate,
                            2
                        ),                                                              // total - required
                        'tax'      => '0',                                              // tax
                        'shipping' => Tools::ps_round(
                            (float)$order->total_shipping / (float)$conversion_rate,
                            2
                        ),                                                              // shipping
                        'city'     => addslashes($deliveryAddress->city),               // city
                        'state'    => '',                                               // state or province
                        'country'  => addslashes($deliveryAddress->country)             // country
                );

                // Product information
                $products = $order->getProducts();
                $items = array();
                foreach ($products as $product) {
                    $category = Db::getInstance()->getRow(
                        'SELECT name '.
                        'FROM `'._DB_PREFIX_.'category_lang` , '._DB_PREFIX_.'product '.
                        'WHERE `id_product` = '.(int)$product['product_id'].
                        '  AND `id_category_default` = `id_category`'.
                        '  AND `id_lang` = '.(int)$parameters['PS_LANG_DEFAULT']
                    );

                    $items[] = array(
                            'OrderId'  => (int)$order->id,                                // order ID - required
                            'SKU'      => addslashes($product['product_id']),             // SKU/code - required
                            'Product'  => addslashes($product['product_name']),           // product name
                            'Category' => addslashes($category['name']),                  // category or variation
                            'Price'    => Tools::ps_round(
                                (float)$product['product_price_wt'] / (float)$conversion_rate,
                                2
                            ),                                                            // unit price - required
                            'Quantity' => addslashes((float)$product['product_quantity']) //quantity - required
                    );
                }

                $TAG_MANAGER_ID = Configuration::get('TAG_MANAGER_ID');

                $this->context->smarty->assign('items', $items);
                $this->context->smarty->assign('trans', $trans);
                $this->context->smarty->assign('TAG_MANAGER_ID', $TAG_MANAGER_ID);
                $this->context->smarty->assign('isOrder', true);

                return $this->display(__FILE__, '/views/templates/front/top-page.tpl');
            }

        } else {

            if (
                (method_exists('Language', 'isMultiLanguageActivated') && Language::isMultiLanguageActivated())
                || Language::countActiveLanguages() > 1
            ) {
                $multilang = (string)Tools::getValue('isolang').'/';
            } else {
                $multilang = '';
            }
            $defaultMetaOrder = Meta::getMetaByPage('order', $this->context->language->id);
            if (
                strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.'order.php') === 0
                || strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.$multilang.$defaultMetaOrder['url_rewrite']) === 0
            ) {
                $this->context->smarty->assign('pageTrack', '/order/step'.(int)Tools::getValue('step').'.html');
            }
            $this->context->smarty->assign('TAG_MANAGER_ID', Configuration::get('TAG_MANAGER_ID'));
            $this->context->smarty->assign('isOrder', false);
                    

            return $this->display(__FILE__, 'views/templates/front/top-page.tpl');
        }
    }

    public function hookFooter($params)
    {
        // for retrocompatibility
        if (!$this->isRegisteredInHook('header')) {
            $this->registerHook('header');
        }
        return $this->hookHeader($params);
    }

    public function hookDisplayAfterBody($params)
    {
        $TAG_MANAGER_ID = Configuration::get('TAG_MANAGER_ID');
        $this->context->smarty->assign('TAG_MANAGER_ID', $TAG_MANAGER_ID);

        return $this->display(__FILE__, '/views/templates/front/after-body.tpl');
    }
}
