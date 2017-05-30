<?php

// Security
if (!defined('_PS_VERSION_'))
    exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');

require (dirname(__FILE__).'/megatoplinks.class.php');
// Loading Models
class Labmegamenus extends Module {
    private $_html = '';
    private $_postErrors = array();
    private $_show_level = 1;
    private $pattern = '/^([A-Z_]*)[0-9]+/';
    private $_menuLink = '';
    private $_menuLinkMobile = '';
    private $spacer_size = '5';

    public function __construct() {
        $this->name = 'labmegamenus';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->bootstrap =true;
        $this->author = 'Labersthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->_show_level = Configuration::get($this->name . '_show_depth');
        parent::__construct();
        $this->displayName = $this->l('Megamenu Customer');
        $this->description = $this->l('block config');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->admin_tpl_path = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/';
    }

    public function install() {
        if(!(int)Tab::getIdFromClassName('AdminLabMenu')) {
            $parent_tab = new Tab();
            // Need a foreach for the language
            $parent_tab->name[$this->context->language->id] = $this->l('Lab Module');
            $parent_tab->class_name = 'AdminLabMenu';
            $parent_tab->id_parent = 0; // Home tab
            $parent_tab->module = $this->name;
            $parent_tab->add();
        }

        $tab = new Tab();
        // Need a foreach for the language
        foreach (Language::getLanguages() as $language)
            $tab->name[$language['id_lang']] = $this->l('Mega menu');
        $tab->class_name = 'Adminlabmegamenus';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminLabMenu');
        $tab->module = $this->name;
        $tab->add();

        Configuration::updateGlobalValue('LABMEGAMENU_ITEMS', 'CAT3,CAT4,CAT5');
        Configuration::updateValue($this->name . '_show_homepage', 1);
        Configuration::updateValue($this->name . '_show_icon', 0);
        Configuration::updateValue($this->name . '_cate_hot', 'CAT4');
        Configuration::updateValue($this->name . '_cate_new', 'CAT3');
        Configuration::updateValue($this->name . '_menu_depth', 4);
        Configuration::updateValue($this->name . '_merge_cate', 1);
        Configuration::updateValue($this->name . '_show_depth', 4);
        Configuration::updateValue($this->name . '_top_offset', 77);
        Configuration::updateValue($this->name . '_effect',2);
        Configuration::updateValue($this->name . '_number_product',5);
        $this->installDb();
        return parent::install() &&
                $this->registerHook('displayHeader')
                &&
                $this->registerHook('megamenu')
                &&
                $this->registerHook('home')
                &&
                 $this->registerHook('displayBackOfficeHeader')
                &&
                $this->registerHook('leftColumn');
    }

    public function uninstall() {
        $tab = new Tab((int)Tab::getIdFromClassName('Adminlabmegamenus'));
        $tab->delete();
        Configuration::deleteByName('LABMEGAMENU_ITEMS');
        Configuration::deleteByName($this->name . '_show_homepage');
        Configuration::deleteByName($this->name . '_show_icon');
        Configuration::deleteByName($this->name . '_cate_hot');
        Configuration::deleteByName($this->name . '_cate_new');
        Configuration::deleteByName($this->name . '_menu_depth');
        Configuration::deleteByName($this->name . '_merge_cate');
        Configuration::deleteByName($this->name . '_show_depth');
        Configuration::deleteByName($this->name . '_top_offset');
        Configuration::deleteByName($this->name . '_effect');
        Configuration::deleteByName($this->name . '_number_product');
        $this->uninstallDb();
        // Uninstall Module
        if (!parent::uninstall())
            return false;
        return true;
    }

    public function getContent() {
        $this->_html .= '<h2>' . $this->displayName . '</h2>';
        $id_lang = (int)Context::getContext()->language->id;
        $languages = $this->context->controller->getLanguages();
        $default_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $labels = Tools::getValue('label') ? array_filter(Tools::getValue('label'), 'strlen') : array();
        $links_label = Tools::getValue('link') ? array_filter(Tools::getValue('link'), 'strlen') : array();
        $divLangName = 'link_label';
        if (Tools::isSubmit('submitlabmegamenu')) {
            //$this->_postValidation();

            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        } else if (Tools::isSubmit('submitLabItemmenu')) {
            $items = Tools::getValue('items');
        //    var_dump((string)implode(',', $items),1);die;
            if (is_array($items) && count($items))
                $updated = Configuration::updateValue('LABMEGAMENU_ITEMS', (string)implode(',', $items));
            else
                $updated = Configuration::updateValue('LABMEGAMENU_ITEMS', '');
            if ($updated)
                $this->_html .= $this->displayConfirmation($this->l('The settings have been updated.'));
            else
                $this->_html .= $this->displayError($this->l('Unable to update settings.'));
            $update_cache = true;

        }else if (Tools::isSubmit('submitBlocktopmegaLinks')){


            $id_shop = (int) Context::getContext()->shop->id;
            foreach ($languages as $key => $val)
            {
                $links_label[$val['id_lang']] = Tools::getValue('link_'.(int)$val['id_lang']);
                $labels[$val['id_lang']] = Tools::getValue('label_'.(int)$val['id_lang']);
            }

            $count_links_label = count($links_label);
            $count_label = count($labels);
            if ($count_links_label || $count_label)
            {
                if (!$count_links_label)
                    $this->_html .= $this->displayError($this->l('Please complete the "Link" field.'));
                elseif (!$count_label)
                    $this->_html .= $this->displayError($this->l('Please add a label.'));
                elseif (!isset($labels[$default_language]))
                    $this->_html .= $this->displayError($this->l('Please add a label for your default language.'));
                else
                {

                    $shops = Shop::getContextListShopID();
                    foreach ($shops as $shop_id)
                    {
                        $added = MegaTopLinks::add($links_label, $labels,  Tools::getValue('new_window', 0), (int)$shop_id);

                        if (!$added)
                        {
                            $shop = new Shop($shop_id);
                            $errors_add_link[] =  $shop->name;
                        }

                    }

                    if (!count($errors_add_link))
                        $this->_html .= $this->displayConfirmation($this->l('The link has been added.'));
                    else
                        $this->_html .= $this->displayError(sprintf($this->l('Unable to add link for the following shop(s): %s'), implode(', ', $errors_add_link)));


                    /*MegaTopLinks::add($links_label, $labels,  Tools::getValue('new_window', 0), $id_shop);
                    $this->_html .= $this->displayConfirmation($this->l('The link has been added.'));*/
                }
            }

        }
        else if (Tools::isSubmit('deletelinksmegatop'))
        {
            $errors_delete_link = array();
            $id_linksmegatop = Tools::getValue('id_linksmegatop', 0);
            $shops =  Shop::getContextListShopID();
            foreach ($shops as $shop_id)
            {
                $deleted = MegaTopLinks::remove($id_linksmegatop, (int)$shop_id);
                Configuration::updateValue('LABMEGAMENU_ITEMS', str_replace(array('LNK'.$id_linksmegatop.',', 'LNK'.$id_linksmegatop), '', Configuration::get('LABMEGAMENU_ITEMS')));

                if (!$deleted)
                {
                    $shop = new Shop($shop_id);
                    $errors_delete_link[] =  $shop->name;
                }

            }
            if (!count($errors_delete_link))
                $this->_html .= $this->displayConfirmation($this->l('The link has been removed.'));
            else
                $this->_html .= $this->displayError(sprintf($this->l('Unable to remove link for the following shop(s): %s'), implode(', ', $errors_delete_link)));

        }elseif (Tools::isSubmit('updatelinksmegatop'))
        {
            $id_linksmegatop = (int)Tools::getValue('id_linksmegatop', 0);
            $id_shop = (int)Shop::getContextShopID();

            if (Tools::isSubmit('updatelink'))
            {
                $link = array();
                $label = array();
                $new_window = (int)Tools::getValue('new_window', 0);

                foreach (Language::getLanguages(false) as $lang)
                {
                    $link[$lang['id_lang']] = Tools::getValue('link_'.(int)$lang['id_lang']);
                    $label[$lang['id_lang']] = Tools::getValue('label_'.(int)$lang['id_lang']);
                }

                MegaTopLinks::update($link, $label, $new_window, (int)$id_shop, (int)$id_linksmegatop);
                $this->_html .= $this->displayConfirmation($this->l('The link has been edited.'));
            }
            $update_cache = true;
        }


        return $this->_html.$this->_displayForm().$this->renderMenuForm().$this->renderAddLinkForm().$this->renderList();


    }

    public function renderList()
    {
        $links = MegaTopLinks::gets((int)$this->context->language->id, null, (int)Shop::getContextShopID());
        $fields_list = array(
            'id_linksmegatop' => array(
                'title' => $this->l('Link ID'),
                'type' => 'text',
            ),
            'label' => array(
                'title' => $this->l('Label'),
                'type' => 'text',
            ),
            'link' => array(
                'title' => $this->l('Link'),
                'type' => 'link',
            ),
            'new_window' => array(
                'title' => $this->l('New window'),
                'type' => 'bool',
                'align' => 'center',
                'active' => 'status',
            )
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_linksmegatop';
        $helper->table = 'linksmegatop';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Link list');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($links, $fields_list);
    }
    public function getSelectOptionsHtml($options = NULL, $name = NULL, $selected = NULL) {
        $html = "";
        $html .='<select name =' . $name . ' style="width:130px">';
        if (count($options) > 0) {
            foreach ($options as $key => $val) {
                if (trim($key) == trim($selected)) {
                    $html .='<option value=' . $key . ' selected="selected">' . $val . '</option>';
                } else {
                    $html .='<option value=' . $key . '>' . $val . '</option>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }

    private function _postProcess() {
     //   var_dump(Tools::getValue('effect'));die;
        Configuration::updateValue($this->name . '_show_homepage', Tools::getValue('show_homepage'));
        Configuration::updateValue($this->name . '_show_icon', Tools::getValue('show_icon'));
        Configuration::updateValue($this->name . '_menu_depth', Tools::getValue('menu_depth'));
        Configuration::updateValue($this->name . '_cate_new', implode(',', Tools::getValue('cate_new')));
        Configuration::updateValue($this->name . '_cate_hot', implode(',', Tools::getValue('cate_hot')));
        Configuration::updateValue($this->name . '_merge_cate', Tools::getValue('merge_cate'));
        Configuration::updateValue($this->name . '_show_depth', Tools::getValue('show_depth'));
        Configuration::updateValue($this->name . '_top_offset', Tools::getValue('top_offset'));
        Configuration::updateValue($this->name . '_effect', Tools::getValue('effect'));
        Configuration::updateValue($this->name . '_number_product', Tools::getValue('number_product'));
        $this->_html .= $this->displayConfirmation($this->l('Configuration updated'));
    }

    public  function _displayForm() {
        $id_lang = (int)Context::getContext()->language->id;
        $languages = $this->context->controller->getLanguages();
        $default_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $spacer = str_repeat('&nbsp;', $this->spacer_size);
        $divLangName = 'link_label';

        if (Tools::isSubmit('submitBlocktopmegaEdit'))
        {
            $id_linksmegatop = (int)Tools::getValue('id_linksmegatop', 0);

            $id_shop = (int)Shop::getContextShopID();

            if (!Tools::isSubmit('link'))
            {
                $tmp = MegaTopLinks::getLinkLang($id_linksmegatop, $id_shop);
                $links_label_edit = $tmp['link'];
                $labels_edit = $tmp['label'];
                $new_window_edit = $tmp['new_window'];
            }
            else
            {
                MegaTopLinks::update(Tools::getValue('link'), Tools::getValue('label'), Tools::getValue('new_window', 0), (int)$id_shop, (int)$id_linksmegatop, (int)$id_linksmegatop);
                $this->_html .= $this->displayConfirmation($this->l('The link has been edited'));
            }

        }
        $tabEffect = array();
      //  $this->_html .= $this->getSelectOptionsHtml(array(0 => 'SlideDown', 1 => 'FadeIn', 2 => 'Show'), 'effect', (Tools::getValue('effect') ? Tools::getValue('effect') : Configuration::get($this->name . '_effect')));
        $tabEffect = array(
            array( 'id'=>'0','mode'=>'SlideDown'),
            array('id'=>'1','mode'=>'FadeIn'),
            array('id'=>'2','mode'=>'Show'),

        );
        $cate_new =    $this->getCategoryOptionNew(1, (int)$id_lang, (int)Shop::getContextShopID());
   //     echo"<pre>".print_r($cate_new,1);die;
        $cate_hot =    $this->getCategoryOptionHot(1, (int)$id_lang, (int)Shop::getContextShopID());
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Show number columns:'),
                        'name' => 'menu_depth',
                        'class' => 'fixed-width-md',
                        'size'=>15,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Show Levels:'),
                        'name' => 'show_depth',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'listnew',
                        'label' => 'Select New Categories:',
                        'name' => 'cate_new[]',
                        'multiple'=>true,
                    ),
                    array(
                        'type' => 'listhost',
                        'label' => 'Select Hot Categories:',
                        'name' => 'cate_hot[]',
                        'multiple'=>true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Show Number Product New/Sale:',
                        'name' => 'number_product',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Top offset:',
                        'name' => 'top_offset',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'select',
                        'label' => 'Effect Tab: ',
                        'name' => 'effect',
                        'options' => array(                                  // This is only useful if type == select
                            'query' => $tabEffect,                           // $array_of_rows must contain an array of arrays, inner arrays (rows) being mode of many fields
                            'id' => 'id',                           // The key that will be used for each option "value" attribute
                            'name'=>'mode',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => 'Show Homepage :',
                        'name' => 'show_homepage',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => 'Merge small subcategories :',
                        'name' => 'merge_cate',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitlabmegamenu';
        $helper->module = $this;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id ,
            'cate_new' => $cate_new,
            'cate_hot' => $cate_hot,
            'choices' => $this->renderChoicesSelect(),
           'selected_links' => $this->makeMenuOption(),
        );
        $helper->override_folder = '/';
        return $helper->generateForm(array($fields_form));
    }


    public function renderMenuForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Menu Top Link'),
                    'icon' => 'icon-link'
                ),
                'input' => array(
                    array(
                        'type' => 'link_choice',
                        'label' => '',
                        'name' => 'link',
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'name' => 'submitLabItemmenu',
                    'title' => $this->l('Save')
                )
            ),
        );

        if (Shop::isFeatureActive())
            $fields_form['form']['description'] = $this->l('The modifications will be applied to').' '.(Shop::getContext() == Shop::CONTEXT_SHOP ? $this->l('shop').' '.$this->context->shop->name : $this->l('all shops'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'choices' => $this->renderChoicesSelect(),
            'selected_links' => $this->makeMenuOption(),
        );
        return $helper->generateForm(array($fields_form));
    }

    public function renderAddLinkForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('updatelinksmegatop') && !Tools::getValue('updatelinksmegatop')) ? $this->l('Update link') : $this->l('Add a new link'),
                    'icon' => 'icon-link'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Label'),
                        'name' => 'label',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Link'),
                        'name' => 'link',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('New window'),
                        'name' => 'new_window',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'name' => 'submitBlocktopmegaLinks',
                    'title' => $this->l('Add')
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->fields_value = $this->getAddLinkFieldsValues();

        if (Tools::getIsset('updatelinksmegatop') && !Tools::getValue('updatelinksmegatop'))
            $fields_form['form']['submit'] = array(
                'name' => 'updatelinksmegatop',
                'title' => $this->l('Update')
            );

        if (Tools::isSubmit('updatelinksmegatop'))
        {
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'updatelink');
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_linksmegatop');
            $helper->fields_value['updatelink'] = '';
        }

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages =$this->context->controller->getLanguages();
        $helper->default_form_language = (int)$this->context->language->id;

        return $helper->generateForm(array($fields_form));
    }


    public function getAddLinkFieldsValues()
    {
        $links_label_edit = '';
        $labels_edit = '';
        $new_window_edit = '';

        if (Tools::isSubmit('updatelinksmegatop'))
        {
            $link = MegaTopLinks::getLinkLang(Tools::getValue('id_linksmegatop'), (int)Shop::getContextShopID());
            $links_label_edit = $link['link'];
            $labels_edit = $link['label'];
            $new_window_edit = $link['new_window'];
        }

        $fields_values = array(
            'new_window' => Tools::getValue('new_window', $new_window_edit),
            'id_linksmegatop' => Tools::getValue('id_linksmegatop'),
        );

        if (Tools::getValue('submitAddmodule'))
        {
            foreach (Language::getLanguages(false) as $lang)
            {
                $fields_values['label'][$lang['id_lang']] = '';
                $fields_values['link'][$lang['id_lang']] = '';
            }
        }
        else
            foreach (Language::getLanguages(false) as $lang)
            {
                $fields_values['label'][$lang['id_lang']] = Tools::getValue('label_'.(int)$lang['id_lang'], isset($labels_edit[$lang['id_lang']]) ? $labels_edit[$lang['id_lang']] : '');
                $fields_values['link'][$lang['id_lang']] = Tools::getValue('link_'.(int)$lang['id_lang'], isset($links_label_edit[$lang['id_lang']]) ? $links_label_edit[$lang['id_lang']] : '');
            }

        return $fields_values;
    }

    public function getConfigFieldsValues()
    {
        return array(
            'menu_depth' => Tools::getValue('menu_depth', Configuration::get($this->name . '_menu_depth')),
            'show_homepage' => Tools::getValue('show_homepage', Configuration::get($this->name . '_show_homepage')),
            'show_depth' => Tools::getValue('show_depth', Configuration::get($this->name . '_show_depth')),
            'merge_cate' => Tools::getValue('merge_cate', Configuration::get($this->name . '_merge_cate')),
            'effect' => Tools::getValue('effect', Configuration::get($this->name . '_effect')),
            'top_offset' => Tools::getValue('top_offset', Configuration::get($this->name . '_top_offset')),
            'number_product' => Tools::getValue('number_product', Configuration::get($this->name . '_number_product')),
        );
    }

    public function renderChoicesSelect()
    {
        $spacer = str_repeat('&nbsp;', $this->spacer_size);
        $items = $this->getMenuItems();
      //  var_dump($items);die;
//var_dump( $this->getCMSOptions(0, 1, $this->context->language->id));die;
        $html = '<select multiple="multiple" id="availableItems" style="width: 300px; height: 160px;">';
        $html .= '<optgroup label="'.$this->l('CMS').'">';
        $html .= $this->getCMSOptions(0, 1, $this->context->language->id,$items);
        $html .= '</optgroup>';

        // BEGIN SUPPLIER
        $html .= '<optgroup label="'.$this->l('Supplier').'">';
        // Option to show all Suppliers
        $html .= '<option value="ALLSUP0">'.$this->l('All suppliers').'</option>';
        $suppliers = Supplier::getSuppliers(false, $this->context->language->id);
        foreach ($suppliers as $supplier)
            if (!in_array('SUP'.$supplier['id_supplier'], $items))
                $html .= '<option value="SUP'.$supplier['id_supplier'].'">'.$spacer.$supplier['name'].'</option>';
        $html .= '</optgroup>';

        // BEGIN Manufacturer
        $html .= '<optgroup label="'.$this->l('Manufacturer').'">';
        // Option to show all Manufacturers
        $html .= '<option value="ALLMAN0">'.$this->l('All manufacturers').'</option>';
        $manufacturers = Manufacturer::getManufacturers(false, $this->context->language->id);
        foreach ($manufacturers as $manufacturer)
            if (!in_array('MAN'.$manufacturer['id_manufacturer'], $items))
                $html .= '<option value="MAN'.$manufacturer['id_manufacturer'].'">'.$spacer.$manufacturer['name'].'</option>';
        $html .= '</optgroup>';

        // BEGIN Categories
        $shop = new Shop((int)Shop::getContextShopID());
        $html .= '<optgroup label="'.$this->l('Categories').'">';
        $html .= $this->generateCategoriesOption(
            Category::getNestedCategories(null, (int)$this->context->language->id, true), $items);
        $html .= '</optgroup>';

        // BEGIN Shops
        if (Shop::isFeatureActive())
        {
            $html .= '<optgroup label="'.$this->l('Shops').'">';
            $shops = Shop::getShopsCollection();
            foreach ($shops as $shop)
            {
                if (!$shop->setUrl() && !$shop->getBaseURL())
                    continue;

                if (!in_array('SHOP'.(int)$shop->id, $items))
                    $html .= '<option value="SHOP'.(int)$shop->id.'">'.$spacer.$shop->name.'</option>';
            }
            $html .= '</optgroup>';
        }

        // BEGIN Products
        $html .= '<optgroup label="'.$this->l('Products').'">';
        $html .= '<option value="PRODUCT" style="font-style:italic">'.$spacer.$this->l('Choose product ID').'</option>';
        $html .= '</optgroup>';

        // BEGIN  New Products
        $html .= '<optgroup label="'.$this->l('News Products').'">';
        $html .= '<option value="NEWPRODUCT1" style="font-style:italic">'.$spacer.$this->l('New product').'</option>';
        $html .= '</optgroup>';
        // BEGIN  sale Products
        $html .= '<optgroup label="'.$this->l('Sale Products').'">';
        $html .= '<option value="SALEPRODUCT1" style="font-style:italic">'.$spacer.$this->l('Sale Products').'</option>';
        $html .= '</optgroup>';

        // BEGIN Menu Top Links
        $html .= '<optgroup label="'.$this->l('Menu Top Links').'">';
        $links = MegaTopLinks::gets($this->context->language->id, null, (int)Shop::getContextShopID());
        foreach ($links as $link)
        {
            if ($link['label'] == '')
            {
                $default_language = Configuration::get('PS_LANG_DEFAULT');
                $link = MegaTopLinks::get($link['id_linksmegatop'], $default_language, (int)Shop::getContextShopID());
                if (!in_array('LNK'.(int)$link[0]['id_linksmegatop'], $items))
                    $html .= '<option value="LNK'.(int)$link[0]['id_linksmegatop'].'">'.$spacer.Tools::safeOutput($link[0]['label']).'</option>';
            }
            elseif (!in_array('LNK'.(int)$link['id_linksmegatop'], $items))
                $html .= '<option value="LNK'.(int)$link['id_linksmegatop'].'">'.$spacer.Tools::safeOutput($link['label']).'</option>';
        }
        $html .= '</optgroup>';
        $html .= '</select>';
        return $html;
    }

    private function generateCategoriesOption($categories, $items_to_skip = null)
    {
        $html = '';

        foreach ($categories as $key => $category)
        {
            if (isset($items_to_skip) && !in_array('CAT'.(int)$category['id_category'], $items_to_skip))
            {
                $shop = (object) Shop::getShop((int)$category['id_shop']);
                $html .= '<option value="CAT'.(int)$category['id_category'].'">'
                    .str_repeat('&nbsp;', $this->spacer_size * (int)$category['level_depth']).$category['name'].' ('.$shop->name.')</option>';
            }

            if (isset($category['children']) && !empty($category['children']))
                $html .= $this->generateCategoriesOption($category['children'], $items_to_skip);

        }
        return $html;
    }


    public  function getCategoryOptionNew($id_category = 1, $id_lang = false, $id_shop = false,$recursive = true) {
        $cate_new = Configuration::get($this->name . '_cate_new');
        $cateCurrent_new = explode(',', $cate_new);
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);
        if (is_null($category->id))
            return;
        if ($recursive)
        {
            $children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
            $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
        }
//var_dump($cateCurrent_new);die;
        $shop = (object) Shop::getShop((int)$category->getShopID());
                if(in_array('CAT'.(int)$category->id, $cateCurrent_new)){
                    $this->_html .= '<option value="CAT'.(int)$category->id.'" selected ="selected" >'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';
                }else{
                    $this->_html .= '<option value="CAT'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';
                }

        if (isset($children) && count($children))
            foreach ($children as $child)
                $this->getCategoryOptionNew((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
        return $this->_html ;
    }

    public  function getCategoryOptionHot($id_category = 1, $id_lang = false, $id_shop = false,$recursive = true) {
        $cate_hot = Configuration::get($this->name . '_cate_hot');
        $cateCurrent_hot = explode(',', $cate_hot);
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);
        if (is_null($category->id))
            return;
        if ($recursive)
        {
            $children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
            $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
        }

        $shop = (object) Shop::getShop((int)$category->getShopID());
                if(in_array('CAT'.(int)$category->id, $cateCurrent_hot)){
                    $this->html .= '<option value="CAT'.(int)$category->id.'" selected ="selected" >'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';
                }else{
                    $this->html .= '<option value="CAT'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';
                }

        if (isset($children) && count($children))
            foreach ($children as $child)
                $this->getCategoryOptionHot((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
        return $this->html;
    }

    private function getCategoryOptionLink($id_category = 1, $id_lang = false, $id_shop = false, $recursive = true)
    {

        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

        if (is_null($category->id))
            return;

        if ($recursive)
        {
            $children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
            $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
        }

        $shop = (object) Shop::getShop((int)$category->getShopID());
        $this->_html .= '<option value="CAT'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';

        if (isset($children) && count($children))
            foreach ($children as $child)
                $this->getCategoryOptionLink((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
    }

    public function getStaticblockLists($id_shop = NULL, $identify= NULL) {
        if (!Combination::isFeatureActive())
            return array();
        $id_lang = (int)$this->context->language->id;
        return Db::getInstance()->executeS('
                        SELECT * FROM ' . _DB_PREFIX_ . 'lab_managerblock AS psb
                            LEFT JOIN ' . _DB_PREFIX_ . 'lab_managerblock_lang AS psl ON psb.id_labmanagerblock = psl.id_labmanagerblock
                            LEFT JOIN ' . _DB_PREFIX_ . 'lab_managerblock_shop AS pss ON psb.id_labmanagerblock = pss.id_labmanagerblock
                        WHERE id_shop =' . $id_shop . '
                            AND id_lang =' . $id_lang .'
                            AND `identify` = "' . $identify . '"
                    ');
    }

    public function getStaticblockCustommerLists($id_shop = NULL) {
        if (!Combination::isFeatureActive())
            return array();
        $id_lang = (int)$this->context->language->id;
        return Db::getInstance()->executeS('
                        SELECT * FROM ' . _DB_PREFIX_ . 'lab_managerblock AS psb
                        LEFT JOIN ' . _DB_PREFIX_ . 'lab_managerblock_lang AS psl ON psb.id_labmanagerblock = psl.id_labmanagerblock
                        LEFT JOIN ' . _DB_PREFIX_ . 'lab_managerblock_shop AS pss ON psb.id_labmanagerblock = pss.id_labmanagerblock
                        where id_shop =' . $id_shop . '
                            AND id_lang =' . $id_lang .'
                            AND `identify` like "lab_item_menu' . '%"
                    ');
    }

    public function getStaticBlockContent($blockId = NULL, $task = NULL) {
        $id_shop = (int) Context::getContext()->shop->id;
        $staticBlock = $this->getStaticblockLists($id_shop, $blockId);
       // echo "<pre>"; print_r($blockId);die;
        $html = "";
        if (count($staticBlock) > 0) {
            $description= $staticBlock[0]['description'];
            $description = str_replace('/noraure/',__PS_BASE_URI__,$description);
            $html .= $description;
        }
        if ($task == 'item') {
            $staticBlock = $this->getStaticblockCustommerLists($id_shop);
            return $staticBlock;
        } else {
            return $html;
        }
    }

    public function getCurrentCategoriesId($lang_id = NULL) {
        if (isset($_GET['id_category'])) {
            $lastCateId = $_GET['id_category'];
        } else {
            $lastCateId = 0;
        }

        $lastCate = new Category((int) $lastCateId);
        //echo $lastCate->name[1]; echo '--------';
        $parentCate = $lastCate->getParentsCategories($lang_id);
        $arrayCateCurrent = array();
        foreach ($parentCate as $pcate) {
            $arrayCateCurrent[] = $pcate['id_category'];
        }
        return $arrayCateCurrent;
    }

    public function haveCateChildren($cate_id = NULL, $lang_id = NULL) {
        $cate = new Category();
        $childCates = $cate->getChildren($cate_id, $lang_id);
        if (count($childCates) > 0)
            return true;
        return false;
    }

    public function drawCustomMenuItem($category, $level = 0, $last = false, $item, $lang_id) {
        //if ($level > $this->_show_level)
            //continue;
        $cateCurrent = $this->getCurrentCategoriesId($lang_id);
        $categoryObject = new Category();
        $html = array();
        $blockHtml = '';
        $id_shop = (int) Context::getContext()->shop->id;
        $id = $category;
        $blockId = sprintf('lab_menu_idcat_%d', $id);
        $staticBlock = $this->getStaticBlockContent($blockId);
        $blockIdRight = sprintf('lab_menu_idcat_%d_right', $id);
      //  echo "<pre>".print_r($blockId);die;
        $staticBlockRight = $this->getStaticBlockContent($blockIdRight);

        // --- Static Block ---
        $blockHtml = $staticBlock;
        /* check block right */
        $blockHtmlRight = $staticBlockRight;
        if ($blockHtmlRight)
            $blockHtml = $blockHtmlRight;
        // --- Sub Categories ---
        $activeChildren = $categoryObject->getChildren($category, $lang_id);
        $activeChildren = $this->getCategoryByLevelMax($activeChildren);
        // --- class for active category ---
        $active = '';
        if (in_array($category, $cateCurrent))
            $active = ' active';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup) {
            $html[] = '<div id="lab_menu' . $id . '" class="lab_menu' . $active . ' nav-' . $item . ' labSub">';
        } else {
            $html[] = '<div id="lab_menu' . $id . '" class="lab_menu' . $active . ' nav-' . $item . ' ">';
        }
        //echo $category;
        //$cate = new Category((int) $category);
        $id_lang =  (int)Context::getContext()->language->id;
        $cate = new Category((int)$category,$id_lang,$id_shop);
        //$link = $categoryObject->getLinkRewrite($category, $lang_id);
        $parameters = "";
        $link = Context::getContext()->link->getCategoryLink((int) $category, null, null, ltrim($parameters, '/'));
        // --- Top Menu Item ---
        $html[] = '<div class="parentMenu">';
        $html[] = '<a href="' . $link . '">';
        //    echo "<pre>".print_r($cate,1);die;
        $name = strip_tags($cate->name);
        $name = str_replace('&nbsp;', '', $name);
        $name = $this->l($name);
        $cats_new_id = '';
        $cats_hot_id = '';
        $cats_new_id = Configuration::get($this->name . '_cate_new');
        $cats_hot_id = Configuration::get($this->name . '_cate_hot');

        // check cate gories hot -new
        $arr_catsid_new = array();
        $arr_catsid_hot = array();
        if ($cats_new_id) {
            if (stristr($cats_new_id, ',') === FALSE) {
                $arr_catsid_new = array(0 => $cats_new_id);
            } else {
                $arr_catsid_new = explode(",", $cats_new_id);
            }
        }
        if ($cats_hot_id) {
            if (stristr($cats_hot_id, ',') === FALSE) {
                $arr_catsid_hot = array(0 => $cats_hot_id);
            } else {
                $arr_catsid_hot = explode(",", $cats_hot_id);
            }
        }
        if (in_array('CAT'.$cate->id_category, $arr_catsid_hot))
            $html[] = '<span>'. $name . '</span><span class="icon-hot">' . $this->l('Hot') . '</span>';
        elseif (in_array('CAT'.$cate->id_category, $arr_catsid_new))
            $html[] = '<span>'. $name .'</span><span class="icon-new">' . $this->l('New') . '</span>';
        else
            $html[] = '<span>'. $name . '</span>';
        // end hot - new
        // ảnh thumb
        $files = scandir(_PS_CAT_IMG_DIR_);
        //var_dump(Configuration::get($this->name . '_show_icon'));die;
        if(Configuration::get($this->name . '_show_icon')==1){
            if (count($files) > 0)
            {
                $k=0;
                foreach ($files as $value=>$file){
                    if (preg_match('/'.$cate->id.'-([0-9])?_thumb.jpg/i',substr($file,0)) === 1){
                        if (preg_match('/'.$cate->id.'-([0-9])?_thumb.jpg/i',substr($file,1))!=1){
                            $k++;
                            $html[] .= '<span class ="thumb'.$k.'"><img src="'.$this->context->link->getMediaLink(_THEME_CAT_DIR_.$file).'"
                            class="imgm"/></span>';
                        }
                    }
                }
            }
        }
        // end
        $html[] = '</a>';
        $html[] = '</div>';

        // --- Add Popup block (hidden) ---
        if ($drawPopup) {

            if ($this->_show_level > 2) {
                // --- Popup function for hide ---
                $html[] = '<div id="popup' . $id . '" class="popup" style="display: none; width: 1228px;">';
                // --- draw Sub Categories ---
            //
                if (count($activeChildren) || $blockHtml) {
                    $html[] = '<div class="block1" id="block1' . $id . '">';
                    $html[] = $this->drawColumns($activeChildren, $id, $lang_id);
                    if ($blockHtml && $blockHtmlRight) {
                     //   echo "<pre>".print_r($blockHtmlRight);die;
                        $html[] = '<div class="column blockright">';
                            $html[] = $blockHtml;
                        $html[] = '</div>';
                    }
                    //$html[] = '<div class="clearBoth"></div>';
                    $html[] = '</div>';
                }
                // --- draw Custom User Block ---
                if ($blockHtml && !$blockHtmlRight) {
                    $html[] = '<div class="block2" id="block2' . $id . '">';
                        $html[] = $blockHtml;
                    $html[] = '</div>';
                }
                $html[] = '</div>';
            }
        }

        $html[] = '</div>';
        $html = implode("\n", $html);
        return $html;
    }
     public function drawCustomMenuItemMobile($category, $level = 0, $last = false, $item, $lang_id) {
        //if ($level > $this->_show_level)
            //continue;
        $cateCurrent = $this->getCurrentCategoriesId($lang_id);
        $categoryObject = new Category();
        $htmlmobile = array();
        $blockHtml = '';
        $id_shop = (int) Context::getContext()->shop->id;
        $id = $category;
        $blockId = sprintf('lab_menu_idcat_%d', $id);
        $staticBlock = $this->getStaticBlockContent($blockId);
        $blockIdRight = sprintf('lab_menu_idcat_%d_right', $id);
        $staticBlockRight = $this->getStaticBlockContent($blockIdRight);
        // --- Static Block ---
        $blockHtml = $staticBlock;
        /* check block right */
        $blockHtmlRight = $staticBlockRight;



        if ($blockHtmlRight)
            $blockHtml = $blockHtmlRight;
        // --- Sub Categories ---
        $activeChildren = $categoryObject->getChildren($category, $lang_id);
        $activeChildren = $this->getCategoryByLevelMax($activeChildren);
        // --- class for active category ---
        $active = '';
        if (in_array($category, $cateCurrent))
            $active = ' active';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup) {
            $htmlmobile[] = '<li class="lab_menu' . $active . ' nav-' . $item . '">';

        } else {
            $htmlmobile[] = '<li class="lab_menu' . $active . ' nav-' . $item . '">';
        }
        //echo $category;
        //$cate = new Category((int) $category);
        $id_lang =  (int)Context::getContext()->language->id;
        $cate = new Category((int)$category,$id_lang,$id_shop);
        //$link = $categoryObject->getLinkRewrite($category, $lang_id);
        $parameters = "";
        $link = Context::getContext()->link->getCategoryLink((int) $category, null, null, ltrim($parameters, '/'));
        // --- Top Menu Item ---
     //   $htmlmobile[] = '<div class="parentMenu">';
        $htmlmobile[] = '<a href="' . $link . '">';
    //    echo "<pre>".print_r($cate,1);die;
        $name = strip_tags($cate->name);
        $name = str_replace('&nbsp;', '', $name);
        $name = $this->l($name);



        // ảnh thumb
        $files = scandir(_PS_CAT_IMG_DIR_);
        if(Configuration::get($this->name . '_show_icon')==1){
            if (count($files) > 0)
            {
                $k=0;
                foreach ($files as $value=>$file){
                    if (preg_match('/'.$cate->id.'-([0-9])?_thumb.jpg/i',substr($file,0)) === 1){
                        if (preg_match('/'.$cate->id.'-([0-9])?_thumb.jpg/i',substr($file,1))!=1){
                            $k++;
                            $htmlmobile[] .= '<span class ="thumb'.$k.'"><img src="'.$this->context->link->getMediaLink(_THEME_CAT_DIR_.$file).'"
                            class="imgm"/></span>';
                        }
                    }
                }
            }
        }



         $id_lang =  (int)Context::getContext()->language->id;
         $cate = new Category((int)$category,$id_lang,$id_shop);
         //$link = $categoryObject->getLinkRewrite($category, $lang_id);
         $parameters = "";
         $link = Context::getContext()->link->getCategoryLink((int) $category, null, null, ltrim($parameters, '/'));
         // --- Top Menu Item ---
         //$html[] = '<div class="parentMenu">';
         $html[] = '<a href="' . $link . '">';
         $name = strip_tags($cate->name);
         $name = str_replace('&nbsp;', '', $name);
         $name = $this->l($name);
         $cats_new_id = '';
         $cats_hot_id = '';
         $cats_new_id = Configuration::get($this->name . '_cate_new');
         $cats_hot_id = Configuration::get($this->name . '_cate_hot');

         // check cate gories hot -new
         $arr_catsid_new = array();
         $arr_catsid_hot = array();
         if ($cats_new_id) {
             if (stristr($cats_new_id, ',') === FALSE) {
                 $arr_catsid_new = array(0 => $cats_new_id);
             } else {
                 $arr_catsid_new = explode(",", $cats_new_id);
             }
         }
         if ($cats_hot_id) {
             if (stristr($cats_hot_id, ',') === FALSE) {
                 $arr_catsid_hot = array(0 => $cats_hot_id);
             } else {
                 $arr_catsid_hot = explode(",", $cats_hot_id);
             }
         }
         if (in_array('CAT'.$cate->id_category, $arr_catsid_hot))
             $htmlmobile[] = '<span>'. $name . '</span><span class="icon-hot">' . $this->l('Hot') . '</span>';
         elseif (in_array('CAT'.$cate->id_category, $arr_catsid_new))
             $htmlmobile[] = '<span>'. $name .'</span><span class="icon-new">' . $this->l('New') . '</span>';
         else
             $htmlmobile[] = '<span>'. $name . '</span>';
        $htmlmobile[] = '</a>';
     //   $htmlmobile[] = '</div>';

        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            if ($this->_show_level > 2) {
                // --- Popup function for hide ---

                 if (count($activeChildren) || $blockHtml) {
                     $htmlmobile[] = $this->drawColumnsMobile($activeChildren, $id, $lang_id);
                   /* if ($blockHtml && $blockHtmlRight) {
                        $htmlmobile[] = '<div class="column blockright">';
                        $htmlmobile[] = $blockHtml;
                        $htmlmobile[] = '</div>';
                    }*/

                }
                // --- draw Custom User Block ---
               /*  if ($blockHtml && !$blockHtmlRight) {
                    $htmlmobile[] = '<div class="block2" id="block2' . $id . '">';
                    $htmlmobile[] = $blockHtml;
                    $htmlmobile[] = '</div>';
                } */

            }
        }

        $htmlmobile[] = '</li>';
        $htmlmobile = implode("\n", $htmlmobile);
        return $htmlmobile;
    }
    public function drawMenuItemMobile($children, $level = 1, $columChunk = 0, $lang_id = 1) {
        $html = '<li class="level' . $level . '">';
        $keyCurrent = NULL;
        if (isset($_GET['id_category'])) {
            $keyCurrent = $_GET['id_category'];
        }
        $countChildren = 0;
        $ClassNoChildren = '';
        $category = new Category();
        foreach ($children as $child) {
            $activeChildCat = $category->getChildren($child['id_category'], $lang_id);
            $activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0 && $columChunk == 1) {
            $ClassNoChildren = ' nochild';
        }
        $catsid = '';
        $catsid = Configuration::get($this->name . '_list_cate');
        $arr_catsid = array();
        if ($catsid) {
            if (stristr($catsid, ',') === FALSE) {
                $arr_catsid = array(0 => $catsid);
            } else {
                $arr_catsid = explode(",", $catsid);
            }
        }
            $id_shop = (int) Context::getContext()->shop->id;
        foreach ($children as $child) {
             $info = new Category((int) $child['id_category'], $lang_id,$id_shop);
            $level = $info->level_depth;
            $active = '';
            $currentCate = $this->getCurrentCategoriesId($lang_id);
            $cate_id = (int) $child['id_category'];
            if (in_array($cate_id, $currentCate)) {
                if ($this->haveCateChildren($cate_id, $lang_id)) {
                    $active = ' actParent';
                } else {
                    $active = ' active';
                }
            }
            // --- format category name ---
            $name = strip_tags($child['name']);
            $name = str_replace(' ', '&nbsp;', $name);

            if (count($child) > 0) {
                $parameters = null;
                $link = Context::getContext()->link->getCategoryLink((int) $child['id_category'], null, null, ltrim($parameters, '/'));
                if (in_array('CAT'.$child['id_category'], $arr_catsid)) {
                    $html.= '<h4 class="itemMenuName level' . $level . $active . $ClassNoChildren . '"><span>' . $name . '</span></h4>';
                } else {
                    $html.= '<a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="' . $link . '"><span>' . $name . '</span></a>';
                }

                $activeChildren = $category->getChildren($child['id_category'], $lang_id);
                $activeChildren = $this->getCategoryByLevelMax($activeChildren);
                if (count($activeChildren) > 0) {
                    $html.= '<ul class="level' . $level . '">';
                    //$html.= $this->drawMenuItem($activeChildren, $level + 1);
                    $html.= $this->drawMenuItemMobile($activeChildren, $level + 1,$columChunk, $lang_id);
                    $html.= '</ul>';
                }
            }
        }


        $html.= '</li>';
        return $html;
    }
    public function drawColumnsMobile($children, $id, $lang_id) {
        $html = '';
        $html.= '<ul>';
        // --- explode by columns ---
        $columns = Configuration::get($this->name . '_menu_depth');
        if ($columns < 1)
            $columns = 1;
        $chunks = $this->seperateColumns($children, $columns, $lang_id);
        $columChunk = count($chunks);
        // --- draw columns ---
        $classSpecial = '';
        $keyLast = 0;
        foreach ($chunks as $key => $value) {
            if (count($value))
                $keyLast++;
        }
        $blockHtml = '';
        $id_shop = (int) Context::getContext()->shop->id;
        $blockId = sprintf('lab_menu_idcat_%d', $id);
        $staticBlock = $this->getStaticBlockContent($blockId);
        $blockIdRight = sprintf('lab_menu_idcat_%d_right', $id);
        $staticBlockRight = $this->getStaticBlockContent($blockIdRight);
        // --- Static Block ---
        $blockHtml = $staticBlock;
        /* check block right */
        $blockHtmlRight = $staticBlockRight;

        foreach ($chunks as $key => $value) {
            if (!count($value))
                continue;
            if ($key == $keyLast - 1) {
                $classSpecial = ($blockHtmlRight && $blockHtml) ? '' : ' last';
            } elseif ($key == 0) {
                $classSpecial = ' first';
            } else {
                $classSpecial = '';
            }
                $html.= $this->drawMenuItemMobile($value, 1, $columChunk, $lang_id);
        }

        $html.= '</ul>';
        return $html;
    }
    public function getCategoryByLevelMax($cates = NULL) {
        if (count($cates) < 1)
            return array();
        $cateArray = array();
        foreach ($cates as $key => $cate) {
            $cate_id = $cate['id_category'];
            $cateObject = new Category((int) $cate_id);
            $cate_level = $cateObject->level_depth;
            if ($cate_level <= $this->_show_level) {
                $cateArray[$key] = $cate;
            }
        }

        if ($cateArray)
            return $cateArray;
        return array();
    }

    public function drawMenuItem($children, $level = 1, $columChunk = 0, $lang_id = 1) {
        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent = NULL;
        if (isset($_GET['id_category'])) {
            $keyCurrent = $_GET['id_category'];
        }
        $countChildren = 0;
        $ClassNoChildren = '';
        $category = new Category();
        foreach ($children as $child) {
            $activeChildCat = $category->getChildren($child['id_category'], $lang_id);
            $activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0 && $columChunk == 1) {
            $ClassNoChildren = ' nochild';
        }
        $catsid = '';
        $catsid = Configuration::get($this->name . '_list_cate');
        $arr_catsid = array();
        if ($catsid) {
            if (stristr($catsid, ',') === FALSE) {
                $arr_catsid = array(0 => $catsid);
            } else {
                $arr_catsid = explode(",", $catsid);
            }
        }
            $id_shop = (int) Context::getContext()->shop->id;
        foreach ($children as $child) {
             $info = new Category((int) $child['id_category'], $lang_id,$id_shop);
            $level = $info->level_depth;
            $active = '';
            $currentCate = $this->getCurrentCategoriesId($lang_id);
            $cate_id = (int) $child['id_category'];
            if (in_array($cate_id, $currentCate)) {
                if ($this->haveCateChildren($cate_id, $lang_id)) {
                    $active = ' actParent';
                } else {
                    $active = ' active';
                }
            }
            // --- format category name ---
            $name = strip_tags($child['name']);
            $name = str_replace(' ', '&nbsp;', $name);

            if (count($child) > 0) {
                $parameters = null;
                $link = Context::getContext()->link->getCategoryLink((int) $child['id_category'], null, null, ltrim($parameters, '/'));
                if (in_array('CAT'.$child['id_category'], $arr_catsid)) {
                    $html.= '<h4 class="itemMenuName level' . $level . $active . $ClassNoChildren . '"><span>' . $name . '</span></h4>';
                } else {
                    $html.= '<a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="' . $link . '"><span>' . $name . '</span></a>';
                }

                $activeChildren = $category->getChildren($child['id_category'], $lang_id);
                $activeChildren = $this->getCategoryByLevelMax($activeChildren);
                if (count($activeChildren) > 0) {
                    $html.= '<div class="itemSubMenu level' . $level . '">';
                    //$html.= $this->drawMenuItem($activeChildren, $level + 1);
                    $html.= $this->drawMenuItem($activeChildren, $level + 1,$columChunk, $lang_id);
                    $html.= '</div>';
                }
            }
        }
        $html.= '</div>';
        return $html;
    }

    public function drawColumns($children, $id, $lang_id) {
        $html = '';
        // --- explode by columns ---
        $columns = Configuration::get($this->name . '_menu_depth');
        if ($columns < 1)
            $columns = 1;
        $chunks = $this->seperateColumns($children, $columns, $lang_id);
        $columChunk = count($chunks);
        // --- draw columns ---
        $classSpecial = '';
        $keyLast = 0;
        foreach ($chunks as $key => $value) {
            if (count($value))
                $keyLast++;
        }
        $blockHtml = '';
        $id_shop = (int) Context::getContext()->shop->id;
        $blockId = sprintf('lab_menu_idcat_%d', $id);
        $staticBlock = $this->getStaticBlockContent($blockId);
        $blockIdRight = sprintf('lab_menu_idcat_%d_right', $id);
        $staticBlockRight = $this->getStaticBlockContent($blockIdRight);
        // --- Static Block ---
        $blockHtml = $staticBlock;
        /* check block right */
        $blockHtmlRight = $staticBlockRight;

        foreach ($chunks as $key => $value) {
            if (!count($value))
                continue;
            if ($key == $keyLast - 1) {
                $classSpecial = ($blockHtmlRight && $blockHtml) ? '' : ' last';
            } elseif ($key == 0) {
                $classSpecial = ' first';
            } else {
                $classSpecial = '';
            }
            $html.= '<div class="column' . $classSpecial . ' col' . ($key + 1) . '">';
            $html.= $this->drawMenuItem($value, 1, $columChunk, $lang_id);

            $html.= '</div>';
        }
        return $html;
    }

    public function drawCustomMenuBlock($blockId, $bc) {
        $html = array();
        $id = '_' . $blockId;


        $blockHtml = str_replace('/noraure/',__PS_BASE_URI__,$bc['description']);
        $drawPopup = $blockHtml;
        if ($drawPopup) {
            $html[] = '<div id="lab_menu' . $id . '" class="lab_menu">';
        } else {
            $html[] = '<div id="lab_menu' . $id . '" class="lab_menu">';
        }
        // --- Top Menu Item ---
        $html[] = '<div class="parentMenu">';
//        $html[] = '<a href="#">';
        $name = $this->l($bc['title']);
        $name = str_replace(' ', '&nbsp;', $name);
        $html[] = '<span class="block-title">' . $name . '</span>';
//        $html[] = '</a>';
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '" class="popup cmsblock" style="display: none; width: 904px;">';
            if ($blockHtml) {
                $html[] = '<div class="block2" id="block2' . $id . '">';
                $html[] = $blockHtml;
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html = implode("\n", $html);
        return $html;
    }
     public function drawCustomMenuMobile($blockId, $bc) {
        $html = array();
        $id =$blockId;


        $blockHtml = str_replace('/noraure/',__PS_BASE_URI__,$bc['description']);
        $drawPopup = $blockHtml;
        if ($drawPopup) {
            $html[] = '<li>';
        } else {
            $html[] = '<li>';
        }
        // --- Top Menu Item ---
        //$html[] = '<div class="parentMenu">';
//        $html[] = '<a href="#">';
        $name = $this->l($bc['title']);
        $name = str_replace(' ', '&nbsp;', $name);
        $html[] = '<a><span class="block-title">' . $name . '</span></a>';
//        $html[] = '</a>';
        //$html[] = '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            // --- Popup function for hide ---
            $html[] = '<ul id="popup' . $id . '" class="popup cmsblock">';
            if ($blockHtml) {
                $html[] = '<li class="block2 " id="block2' . $id . '">';
                $html[] = $blockHtml;
                $html[] = '</li>';
            }
            $html[] = '</ul>';
        }
        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
    }
    private function seperateColumns($parentCates, $num, $lang_id) {
        $category = new Category();
        $countChildren = 0;
        foreach ($parentCates as $cat => $childCat) {
            $activeChildCat = $category->getChildren($childCat['id_category'], $lang_id);
            $activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0) {
            $num = 1;
        }


        $count = count($parentCates);
        if ($count)
            $parentCates = array_chunk($parentCates, ceil($count / $num));

        $parentCates = array_pad($parentCates, $num, array());
        $is_merge = Configuration::get($this->name . '_merge_cate');
        if ($is_merge && count($parentCates)) {
            // --- combine consistently numerically small column ---
            // --- 1. calc length of each column ---
            $max = 0;
            $columnsLength = array();
            foreach ($parentCates as $key => $child) {
                $count = 0;
                $this->_countChild($child, 1, $count, $lang_id);

                if ($max < $count)
                    $max = $count;
                $columnsLength[$key] = $count;
            }
            // --- 2. merge small columns with next ---
            $xColumns = array();
            $column = array();
            $cnt = 0;
            $xColumnsLength = array();
            $k = 0;

            foreach ($columnsLength as $key => $count) {
                $cnt+= $count;
                if ($cnt > $max && count($column)) {
                    $xColumns[$k] = $column;
                    $xColumnsLength[$k] = $cnt - $count;
                    $k++;
                    $column = array();
                    $cnt = $count;
                }
                $column = array_merge($column, $parentCates[$key]);
            }
            $xColumns[$k] = $column;
            $xColumnsLength[$k] = $cnt - $count;
            // --- 3. integrate columns of one element ---
            $parentCates = $xColumns;
            $xColumns = array();
            $nextKey = -1;
            if ($max > 1 && count($parentCates) > 1) {
                foreach ($parentCates as $key => $column) {
                    if ($key == $nextKey)
                        continue;
                    if ($xColumnsLength[$key] == 1) {
                        // --- merge with next column ---
                        $nextKey = $key + 1;
                        if (isset($parentCates[$nextKey]) && count($parentCates[$nextKey])) {
                            $xColumns[] = array_merge($column, $parentCates[$nextKey]);
                            continue;
                        }
                    }
                    $xColumns[] = $column;
                }
                $parentCates = $xColumns;
            }
        }
        return $parentCates;
    }

    private function _countChild($children, $level, &$count, $lang_id) {
        $category = new Category();
        foreach ($children as $child) {
            $count++;
            $activeChildren = $category->getChildren($child['id_category'], $lang_id);
            $activeChildren = $this->getCategoryByLevelMax($activeChildren);
            if (count($activeChildren) > 0)
                $this->_countChild($activeChildren, $level + 1, $count, $lang_id);
        }
    }

    public function hookDisplayHeader() {

        $this->context->controller->addCSS($this->_path . 'css/custommenu.css');
        $this->context->controller->addJS($this->_path . 'js/custommenu.js');
        $this->context->controller->addJS($this->_path . 'js/mobile_menu.js');
    }
     public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'css/land_megamenu_back.css');
        $this->context->controller->addJS($this->_path . 'js/land_megamenu_back.js');
    }

    //mobile megamenu
    public function getTree($resultParents, $resultIds, $maxDepth, $id_category = null, $currentDepth = 0) {
        if (is_null($id_category))
            $id_category = $this->context->shop->getCategory();

        $children = array();
        if (isset($resultParents[$id_category]) && count($resultParents[$id_category]) && ($maxDepth == 0 || $currentDepth < $maxDepth))
            foreach ($resultParents[$id_category] as $subcat)
                $children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1);
        if (!isset($resultIds[$id_category]))
            return false;
        $return = array('id' => $id_category, 'link' => $this->context->link->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']),
            'name' => $resultIds[$id_category]['name'], 'desc' => $resultIds[$id_category]['description'],
            'children' => $children);
        return $return;
    }

    public function getblockCategTree() {

        // Get all groups for this customer and concatenate them as a string: "1,2,3..."
        $groups = implode(', ', Customer::getGroupsStatic((int) $this->context->customer->id));
        $maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
                FROM `' . _DB_PREFIX_ . 'category` c
                INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = ' . (int) $this->context->language->id . Shop::addSqlRestrictionOnLang('cl') . ')
                INNER JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = ' . (int) $this->context->shop->id . ')
                WHERE (c.`active` = 1 OR c.`id_category` = ' . (int) Configuration::get('PS_HOME_CATEGORY') . ')
                AND c.`id_category` != ' . (int) Configuration::get('PS_ROOT_CATEGORY') . '
                ' . ((int) $maxdepth != 0 ? ' AND `level_depth` <= ' . (int) $maxdepth : '') . '
                AND c.id_category IN (SELECT id_category FROM `' . _DB_PREFIX_ . 'category_group` WHERE `id_group` IN (' . pSQL($groups) . '))
                ORDER BY `level_depth` ASC, ' . (Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'cs.`position`') . ' ' . (Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC')))
            return;

        $resultParents = array();
        $resultIds = array();

        foreach ($result as &$row) {
            $resultParents[$row['id_parent']][] = &$row;
            $resultIds[$row['id_category']] = &$row;
        }

        $blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
        unset($resultParents, $resultIds);

        $id_category = (int) Tools::getValue('id_category');
        $id_product = (int) Tools::getValue('id_product');


        if (Tools::isSubmit('id_category')) {
            $this->context->cookie->last_visited_category = $id_category;
            $this->smarty->assign('currentCategoryId', $this->context->cookie->last_visited_category);
        }
        if (Tools::isSubmit('id_product')) {
            if (!isset($this->context->cookie->last_visited_category)
                    || !Product::idIsOnCategoryId($id_product, array('0' => array('id_category' => $this->context->cookie->last_visited_category)))
                    || !Category::inShopStatic($this->context->cookie->last_visited_category, $this->context->shop)) {
                $product = new Product($id_product);
                if (isset($product) && Validate::isLoadedObject($product))
                    $this->context->cookie->last_visited_category = (int) $product->id_category_default;
            }
            $this->smarty->assign('currentCategoryId', (int) $this->context->cookie->last_visited_category);
        }
        return $blockCategTree;
    }

    public function getMenuCustomerLink($lang_id = NULL) {
        $menu_items = $this->getMenuItems();
        $item1 =0;
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)Shop::getContextShopID();
        foreach ($menu_items as $item)
        {
            if (!$item)
                continue;

            preg_match($this->pattern, $item, $value);
            $id = (int)substr($item, strlen($value[1]), strlen($item));

            switch (substr($item, 0, strlen($value[1])))
            {
                case 'CAT':
                    $item1 = $item1+ 1;
                    $this->_menuLink .= $this->drawCustomMenuItem($id, 0, false, $item1, $lang_id);
                    $this->_menuLinkMobile .= $this->drawCustomMenuItemMobile($id, 0, false, $item1, $lang_id);
                    break;
                case 'PRD':
                    $selected = ($this->page_name == 'product' && (Tools::getValue('id_product') == $id)) ? ' class="sfHover"' : '';
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (!is_null($product->id))
                        $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($product->getLink()).'"><span>'.$product->name.'</span></a></div></div>'.PHP_EOL;
                        $this->_menuLinkMobile .= '<li  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($product->getLink()).'"><span>'.$product->name.'</span></a></div></li>'.PHP_EOL;
                    break;
                case 'NEWPRODUCT':
                    $Currency = $this->context->currency->sign;
                    $newProducts = Product::getNewProducts((int)$this->context->language->id, 0,(int)Configuration::get($this->name.'_number_product') );
                    $this->_menuLink .= '<div  class="lab_menu labmenuproducts"><div class="parentMenu" ><a href="'.__PS_BASE_URI__."new-products".'"><span>' . $this->l('New Product') . ' </span><span class="icon-new">' . $this->l('New') . '</span></a>';
                    $this->_menuLink .= '</div>'.PHP_EOL;
                    $this->_menuLink .= '<div class ="popup" style ="display:none">';
                    $this->_menuLink .= '<div class ="block1">';
                    foreach ($newProducts as $key=>$newProduct){
                        $count = $key + 1;
                        $imagePath = Link::getImageLink($newProduct['link_rewrite'], $newProduct['id_image'], 'home_default');
                        $this->_menuLink .= '<div class ="column item-product col'. $count .'"><div class="item" ><div class="img_produc"><a title="'.$newProduct['name'].'" class="product-img" href="'.$newProduct['link'].'"> <img src="'.$imagePath.'" alt="'.$newProduct['name'].'" /></a>
                            <p class="boxprice"><span class="price"> '. $Currency. ($newProduct['price']).'</span>'.PHP_EOL;
                            if($newProduct['reduction']!=0){
                                $this->_menuLink .= '<span class="old-price"> '.  $Currency.Tools::ps_round($newProduct['price_without_reduction'], 2).'</span>'.PHP_EOL;
                            }
                        $this->_menuLink .= '</p> </div>
                           <div class="right"><h2 class="produc-name"><a title="'.$newProduct['name'].'" href="'.$newProduct['link'].'"><span>'.$newProduct['name'].'</span></a></h2>
                            <a class="lnk_more" href="'.$newProduct['link'].'" title="' . $this->l('View') . '">' . $this->l('View') . '</a></div>
                           </div></div>'.PHP_EOL;
                    }
                    $this->_menuLink .= '</div></div></div>';

                    $this->_menuLinkMobile .= '<li><a href="'.__PS_BASE_URI__."new-products".'"><span>' . $this->l('New Product') . ' </span><span class="icon-new">' . $this->l('New') . '</span></a>';
                    $this->_menuLinkMobile .= '<ul>';
                    foreach ($newProducts as $newProduct){
                        $imagePath = Link::getImageLink($newProduct['link_rewrite'], $newProduct['id_image'],'home_default');
                        $this->_menuLinkMobile .= '<li><div class="img_produc"><a title="'.$newProduct['name'].'" class="product-img" href="'.$newProduct['link'].'"> <img src="'.$imagePath.'" alt="'.$newProduct['name'].'" /></a>
                           <p class="boxprice"><span class="price"> '. $Currency. ($newProduct['price']).'</span>'.PHP_EOL;
                            if($newProduct['reduction']!=0){
                                $this->_menuLinkMobile .= '<span class="old-price"> '.  $Currency.Tools::ps_round($newProduct['price_without_reduction'], 2).'</span>'.PHP_EOL;
                            }
                        $this->_menuLinkMobile .= '</p> </div>
                           <div class="right"><h2 class="produc-name"><a title="'.$newProduct['name'].'" href="'.$newProduct['link'].'"><span>'.$newProduct['name'].'</span></a></h2>
                            <a class="lnk_more" href="'.$newProduct['link'].'" title="' . $this->l('View') . '">' . $this->l('View') . '</a></div>
                           </li>'.PHP_EOL;
                    }
                    $this->_menuLinkMobile .= '</ul></li>';
                    break;
                case 'SALEPRODUCT':
                    $Currency = $this->context->currency->sign;
                    $SaleProducts = Product::getPricesDrop((int)$this->context->language->id, 0,(int)Configuration::get($this->name.'_number_product'));
                   // echo "<pre>".print_r($SaleProducts,1);die;
                    $this->_menuLink .= '<div  class="lab_menu labmenuproducts"><div class="parentMenu" ><a href="'.__PS_BASE_URI__."prices-drop".'"><span>' . $this->l(' Sale') . '</span><span class="icon-hot">' . $this->l('Hot') . '</span></a>';
                    $this->_menuLink .= '</div>'.PHP_EOL;
                    $this->_menuLink .= '<div class ="popup" style ="display:none">';
                    $this->_menuLink .= '<div class ="block1">';
                   // echo "<pre>".print_r($SaleProducts,1);die;
                    foreach ($SaleProducts as $key => $SaleProduct){
                        $count = $key + 1;
                        $imagePath = Link::getImageLink($SaleProduct['link_rewrite'], $SaleProduct['id_image'], 'home_default');
                        $this->_menuLink .= '<div class ="column item-product col'. $count .'"><div class="item" ><div class="img_produc"><a title="'.$SaleProduct['name'].'" class="product-img" href="'.$SaleProduct['link'].'"> <img src="'.$imagePath.'" alt=""/></a>
                                    <p class="boxprice"><span class="price"> '. $Currency. ($SaleProduct['price']).'</span>'.PHP_EOL;
                            if($SaleProduct['reduction']!=0){
                                $this->_menuLink .= '<span class="old-price"> '.  $Currency.Tools::ps_round($SaleProduct['price_without_reduction'], 2).'</span>'.PHP_EOL;
                            }
                            $this->_menuLink .='</p></div>
                               <div class="right"><h2 class="produc-name"><a title="'.$SaleProduct['name'].'" href="'.$SaleProduct['link'].'"><span>'.$SaleProduct['name'].'</span></a></h2>
                                <a class="lnk_more" href="'.$SaleProduct['link'].'" title="' . $this->l('View') . '">' . $this->l('View') . '</a>
                               </div></div></div>'.PHP_EOL;

                         if($count == (int)Configuration::get($this->name . '_menu_depth')){
                            $this->_menuLink .= '<div class="clearBoth">';
                            $this->_menuLink .= '</div>'.PHP_EOL;
                         }
                    }
                    $this->_menuLink .= '</div></div></div>';
                    $this->_menuLinkMobile .= '<li><a href="'.__PS_BASE_URI__."prices-drop".'"><span>' . $this->l(' Sale') . '</span><span class="icon-hot">' . $this->l('Hot') . '</span></a>';
                    $this->_menuLinkMobile .= '<ul><li>';
                    foreach ($SaleProducts as $SaleProduct){

                        $imagePath = Link::getImageLink($SaleProduct['link_rewrite'], $SaleProduct['id_image'], 'home_default');
                        $this->_menuLinkMobile .= '<div class="list_products"><div class="img_produc"><a title="'.$SaleProduct['name'].'" class="product-img" href="'.$SaleProduct['link'].'"> <img  src="'.$imagePath.'" alt="'.$SaleProduct['name'].'"/></a>
                            <p class="boxprice"><span class="price"> '. $Currency. ($SaleProduct['price']).'</span>'.PHP_EOL;
                            if($SaleProduct['reduction']!=0){
                                $this->_menuLinkMobile .= '<span class="old-price"> '.  $Currency.Tools::ps_round($SaleProduct['price_without_reduction'], 2).'</span>'.PHP_EOL;
                            }
                            $this->_menuLinkMobile .='</p></div>
                          <div class="right"><h2 class="produc-name"><a title="'.$SaleProduct['name'].'" class="product-img" href="'.$SaleProduct['link'].'"> <span>'.$SaleProduct['name'].'</span></a></h2>
                          <a class="lnk_more" href="'.$SaleProduct['link'].'" title="' . $this->l('View') . '">' . $this->l('View') . '</a></div>
                          </div>'.PHP_EOL;
                    }
                    $this->_menuLinkMobile .= '</li></ul></li>';
                    break;
                case 'CMS':
                    $selected = ($this->page_name == 'cms' && (Tools::getValue('id_cms') == $id)) ? ' class="sfHover"' : '';
                    $cms = CMS::getLinks((int)$id_lang, array($id));
                    if (count($cms))
                    $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($cms[0]['link']).'"><span>'.$cms[0]['meta_title'].'</span></a></div></div>'.PHP_EOL;
                    $this->_menuLinkMobile .= '<li ><a href="'.Tools::HtmlEntitiesUTF8($cms[0]['link']).'"><span>'.$cms[0]['meta_title'].'</span></a></li>'.PHP_EOL;
                break;

                case 'CMS_CAT':
                    $category = new CMSCategory((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category))
                    $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($category->getLink()).'"><span>'.$category->name.'</span></a>';
                    $this->_menuLink .= '</div>'.PHP_EOL;
                    $this->_menuLink .= $this->getCMSMenuItems($category->id);

                    $this->_menuLinkMobile .= '<li class="lab_menu_cms"><a href="'.Tools::HtmlEntitiesUTF8($category->getLink()).'"><span>'.$category->name.'</span></a>';
                    $this->_menuLinkMobile .= $this->getCMSMenuItemsMobile($category->id);
                    $this->_menuLinkMobile .= '</li>'.PHP_EOL;
                break;

                // Case to handle the option to show all Manufacturers
                case 'ALLMAN':

                    $link = new Link;
                    $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.$link->getPageLink('manufacturer').'" ><span>'.$this->l('Manufacturers').'</span></a></div>'.PHP_EOL;

                    $manufacturers = Manufacturer::getManufacturers();
                    $this->_menuLink .= '<div class ="popup" style ="display:none">';
                    foreach ($manufacturers as $key => $manufacturer)
                        $this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$link->getManufacturerLink((int)$manufacturer['id_manufacturer'], $manufacturer['link_rewrite']).'">'.$manufacturer['name'].'</a></div></div></div>'.PHP_EOL;
                    $this->_menuLink .= '</div></div>';

                    $this->_menuLinkMobile .= '<li ><a href="'.$link->getPageLink('manufacturer').'" ><span>'.$this->l('Manufacturers').'</span></a>'.PHP_EOL;
                    $this->_menuLinkMobile .= '<ul class ="popup">';
                    foreach ($manufacturers as $key => $manufacturer)

                        $this->_menuLinkMobile .= '<li><a href="'.$link->getManufacturerLink((int)$manufacturer['id_manufacturer'], $manufacturer['link_rewrite']).'">'.$manufacturer['name'].'</a></li>'.PHP_EOL;
                    $this->_menuLinkMobile .= '</ul></li>';
                    break;
                case 'MAN':
                    $selected = ($this->page_name == 'manufacturer' && (Tools::getValue('id_manufacturer') == $id)) ? ' class="sfHover"' : '';
                    $manufacturer = new Manufacturer((int)$id, (int)$id_lang);
                    if (!is_null($manufacturer->id))
                    {
                        if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
                            $manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
                        else
                            $manufacturer->link_rewrite = 0;
                        $link = new Link;
                        $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int)$id, $manufacturer->link_rewrite)).'"><span>'.$manufacturer->name.'</span></a></div></div>'.PHP_EOL;
                        $this->_menuLinkMobile .= '<li  class="lab_menu"><a href="'.Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int)$id, $manufacturer->link_rewrite)).'"><span>'.$manufacturer->name.'</span></a></li>'.PHP_EOL;

                    }
                    break;

                // Case to handle the option to show all Suppliers
                case 'ALLSUP':
                    $link = new Link;
                    $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.$link->getPageLink('supplier').'" ><span>'.$this->l('All suppliers').'</span></a></div>'.PHP_EOL;

                    $suppliers = Supplier::getSuppliers();
                    $this->_menuLink .= '<div class ="popup" style ="display:none">';
                    foreach ($suppliers as $key => $supplier)
                        $this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$link->getSupplierLink((int)$supplier['id_supplier'], $supplier['link_rewrite']).'">'.$supplier['name'].'</a></div></div></div>'.PHP_EOL;
                    $this->_menuLink .= '</div></div>';


                    $this->_menuLinkMobile .= '<li ><a href="'.$link->getPageLink('supplier').'" ><span>'.$this->l('All suppliers').'</span></a>'.PHP_EOL;
                    $this->_menuLinkMobile .= '<ul class ="popup">';
                    foreach ($suppliers as $key => $supplier)
                        $this->_menuLinkMobile .= '<li class ="block1"><a href="'.$link->getSupplierLink((int)$supplier['id_supplier'], $supplier['link_rewrite']).'">'.$supplier['name'].'</a></li>'.PHP_EOL;
                    $this->_menuLinkMobile .= '</ul></li>';
                    break;

                case 'SUP':
                    $selected = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == $id)) ? ' class="sfHover"' : '';
                    $supplier = new Supplier((int)$id, (int)$id_lang);
                    if (!is_null($supplier->id))
                    {
                        $link = new Link;

                        $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($link->getSupplierLink((int)$id, $supplier->link_rewrite)).'"><span>'.$supplier->name.'</span></a></div></div>'.PHP_EOL;
                        $this->_menuLinkMobile .= '<li  class="lab_menu"><a href="'.Tools::HtmlEntitiesUTF8($link->getSupplierLink((int)$id, $supplier->link_rewrite)).'"><span>'.$supplier->name.'</span></a></li>'.PHP_EOL;

                    }
                    break;

                case 'SHOP':
                    $selected = ($this->page_name == 'index' && ($this->context->shop->id == $id)) ? ' class="sfHover"' : '';
                    $shop = new Shop((int)$id);
                    if (Validate::isLoadedObject($shop))
                    {
                        $link = new Link;
                        $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="'.Tools::HtmlEntitiesUTF8($shop->getBaseURL()).'"><span>'.$supplier->name.'</span></a></div></div>'.PHP_EOL;
                        $this->_menuLinkMobile .= '<li id ="lab_menu_sub" class="lab_menu"><a href="'.Tools::HtmlEntitiesUTF8($shop->getBaseURL()).'"><span>'.$supplier->name.'</span></a></li>'.PHP_EOL;
                    }
                    break;
                case 'LNK':
                    $link = MegaTopLinks::get((int)$id, (int)$id_lang, (int)$id_shop);
                    if (count($link))
                    {
                        if (!isset($link[0]['label']) || ($link[0]['label'] == ''))
                        {
                            $default_language = Configuration::get('PS_LANG_DEFAULT');
                            $link = MegaTopLinks::get($link[0]['id_linksmegatop'], $default_language, (int)Shop::getContextShopID());
                        }
                        $this->_menuLink .= '<div  class="lab_menu"><div class="parentMenu" ><a href="' . __PS_BASE_URI__ . ''.($link[0]['link']).'"'.(($link[0]['new_window']) ? ' target="_blank"': '').'><span>'.$link[0]['label'].'</span></a></div></div>'.PHP_EOL;
                        $this->_menuLinkMobile .= '<li class="lab_menu"><a href="' . __PS_BASE_URI__ . ''.($link[0]['link']).'"'.(($link[0]['new_window']) ? ' target="_blank"': '').'><span>'.$link[0]['label'].'</span></a></li>'.PHP_EOL;
                    }
                    break;


            }

        }

    }

    public function hookmegamenu() {
        //$lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $lang_id = (int)Context::getContext()->language->id;
        $category = new Category();
        //$homeCates = $category->getHomeCategories($lang_id);
        $this->getMenuCustomerLink($lang_id);
        $item = 0;
        $html = "";
        $htmlmobile='';
        $showhome = Configuration::get($this->name . '_show_homepage');
        if ($showhome) {
            $page_name = Dispatcher::getInstance()->getController();
            $active = null;
            if ($page_name == 'index')
                $active = ' active';
            $id = "_home";
            $html .= '<div id="lab_menu' . $id . '" class="lab_menu' . $active . '">';
            $html .= '<div class="parentMenu">';
            $html .= '<a href="' . __PS_BASE_URI__ . '">';
            $html .= '<span>' . $this->l('Home') . '</span>';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</div>';

            $htmlmobile .= '<li class="parentMenu">';
            $htmlmobile .= '<a href="' . __PS_BASE_URI__ . '">';
            $htmlmobile .= '<span>' . $this->l('Home') . '</span>';
            $htmlmobile .= '</a>';
            $htmlmobile .= '</li>';
        }

        // foreach ($homeCates as $cate) {
            // $item++;
           // // $html .= $this->drawCustomMenuItem($cate['id_category'], 0, false, $item, $lang_id);
        // }
        $html .= $this->_menuLink;
        $htmlmobile.= $this->_menuLinkMobile;
        $blockCustomer = $this->getStaticBlockContent(null, 'item');
        foreach ($blockCustomer as $bc) {
            $html .= $this->drawCustomMenuBlock($bc['identify'], $bc);
            $htmlmobile .= $this->drawCustomMenuMobile($bc['identify'], $bc);
        }
        $isDhtml = (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false);
        $blockCategTree = $this->getblockCategTree();
        $this->smarty->assign('blockCategTree', $blockCategTree);
        if (file_exists(_PS_THEME_DIR_ . 'modules/blockcategories/blockcategories.tpl'))
            $this->smarty->assign('branche_tpl_path', _PS_THEME_DIR_ . 'modules/blockcategories/category-tree-branch.tpl');
        else
            $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_ . 'blockcategories/category-tree-branch.tpl');
        $this->smarty->assign('isDhtml', $isDhtml);
        $this->context->smarty->assign(
                array(
                    'megamenu' => $html,
                    'megamenumobile' => $htmlmobile,
                    'top_offset' => Configuration::get($this->name . '_top_offset'),
                    'effect' => Configuration::get($this->name . '_effect'),
                    'menu_link' =>  $this->_menuLink,
                )
        );
        return $this->display(__FILE__, 'megamenu.tpl');
    }

    private function getCMSMenuItems($parent, $depth = 1, $id_lang = false)
    {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        if ($depth > 3)
            return;
        $categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);
        $pages = $this->getCMSPages((int)$parent);
        if (count($categories) || count($pages))
        {
            $this->_menuLink .= '<div class ="popup" style ="display:none">';
            foreach ($pages as  $page) {
                $cms = new CMS($page['id_cms'], (int)$id_lang);
                $links = $cms->getLinks((int)$id_lang, array((int)$cms->id));
                $selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms') == $page['id_cms'])) ? ' class="sfHoverForce"' : '';
                $this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$links[0]['link'].'">'.$cms->meta_title.'</a></div></div></div>'.PHP_EOL;
            }
            $this->_menuLink .= '</div></div>';
        }
    }


    private function getCMSMenuItemsMobile($parent, $depth = 1, $id_lang = false)
    {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        if ($depth > 3)
            return;
        $categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);
        $pages = $this->getCMSPages((int)$parent);
        if (count($categories) || count($pages))
        {
            $this->_menuLinkMobile .= '<ul class ="popup">';
            foreach ($pages as  $page) {
                $cms = new CMS($page['id_cms'], (int)$id_lang);
                $links = $cms->getLinks((int)$id_lang, array((int)$cms->id));
                $selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms') == $page['id_cms'])) ? ' class="sfHoverForce"' : '';
                $this->_menuLinkMobile .= '<li class ="block1"><a href="'.$links[0]['link'].'">'.$cms->meta_title.'</a></li>'.PHP_EOL;
            }
            $this->_menuLinkMobile .= '</ul>';
        }
    }


    private function getCMSOptions($parent = 0, $depth = 1, $id_lang = false, $items_to_skip = null)
    {
        $html = '';
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);
        $pages = $this->getCMSPages((int)$parent, false, (int)$id_lang);

        $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$depth);

        foreach ($categories as $category)
        {
            if (isset($items_to_skip) && !in_array('CMS_CAT'.$category['id_cms_category'], $items_to_skip))
                $html .= '<option value="CMS_CAT'.$category['id_cms_category'].'" style="font-weight: bold;">'.$spacer.$category['name'].'</option>';
            $html .= $this->getCMSOptions($category['id_cms_category'], (int)$depth + 1, (int)$id_lang, $items_to_skip);
        }

        foreach ($pages as $page)
            if (isset($items_to_skip) && !in_array('CMS'.$page['id_cms'], $items_to_skip))
                $html .= '<option value="CMS'.$page['id_cms'].'">'.$spacer.$page['meta_title'].'</option>';

        return $html;
    }


    private function getCMSCategories($recursive = false, $parent = 1, $id_lang = false)
    {
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

        if ($recursive === false)
        {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
                FROM `'._DB_PREFIX_.'cms_category` bcp
                INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
                ON (bcp.`id_cms_category` = cl.`id_cms_category`)
                WHERE cl.`id_lang` = '.(int)$id_lang.'
                AND bcp.`id_parent` = '.(int)$parent;

            return Db::getInstance()->executeS($sql);
        }
        else
        {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
                FROM `'._DB_PREFIX_.'cms_category` bcp
                INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
                ON (bcp.`id_cms_category` = cl.`id_cms_category`)
                WHERE cl.`id_lang` = '.(int)$id_lang.'
                AND bcp.`id_parent` = '.(int)$parent;

            $results = Db::getInstance()->executeS($sql);
            foreach ($results as $result)
            {
                $sub_categories = $this->getCMSCategories(true, $result['id_cms_category'], (int)$id_lang);
                if ($sub_categories && count($sub_categories) > 0)
                    $result['sub_categories'] = $sub_categories;
                $categories[] = $result;
            }

            return isset($categories) ? $categories : false;
        }

    }

    private function getCMSPages($id_cms_category, $id_shop = false, $id_lang = false)
    {
        $id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

        $sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
            FROM `'._DB_PREFIX_.'cms` c
            INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
            ON (c.`id_cms` = cs.`id_cms`)
            INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
            ON (c.`id_cms` = cl.`id_cms`)
            WHERE c.`id_cms_category` = '.(int)$id_cms_category.'
            AND cs.`id_shop` = '.(int)$id_shop.'
            AND cl.`id_lang` = '.(int)$id_lang.'
            AND c.`active` = 1
            ORDER BY `position`';

        return Db::getInstance()->executeS($sql);
    }

    public  function makeMenuOption()
    {
        $menu_item = $this->getMenuItems();
        $id_lang = (int)$this->context->language->id;
        $id_shop = (int)Shop::getContextShopID();
      //  echo "<pre>".print_r($menu_item,1);die;
        $html = '<select multiple="multiple" name="items[]" id="items" style="width: 300px; height: 160px;">';
        foreach ($menu_item as $item)
        {
            if (!$item)
                continue;
            preg_match($this->pattern, $item, $values);
            $id = (int)substr($item, strlen($values[1]), strlen($item));
            switch (substr($item, 0, strlen($values[1])))
            {
                case 'CAT':
                    $category = new Category((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category))
                        $html .= '<option selected="selected" value="CAT'.$id.'">'.$category->name.'</option>'.PHP_EOL;
                    break;

                case 'PRD':
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (Validate::isLoadedObject($product))
                        $html .= '<option selected="selected" value="PRD'.$id.'">'.$product->name.'</option>'.PHP_EOL;
                    break;
                case 'NEWPRODUCT':
                        $html .= '<option selected="selected" value="NEWPRODUCT'.$id.'">New Product</option>'.PHP_EOL;
                    break;
                case 'SALEPRODUCT':
                        $html .= '<option selected="selected" value="SALEPRODUCT'.$id.'">Sale Product</option>'.PHP_EOL;
                    break;

                case 'CMS':
                    $cms = new CMS((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($cms))
                        $html .= '<option selected="selected" value="CMS'.$id.'">'.$cms->meta_title.'</option>'.PHP_EOL;
                    break;

                case 'CMS_CAT':
                    $category = new CMSCategory((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category))
                        $html .= '<option selected="selected" value="CMS_CAT'.$id.'">'.$category->name.'</option>'.PHP_EOL;
                    break;

                // Case to handle the option to show all Manufacturers
                case 'ALLMAN':
                    $html .= '<option selected="selected" value="ALLMAN0">'.$this->l('All manufacturers').'</option>'.PHP_EOL;
                    break;

                case 'MAN':
                    $manufacturer = new Manufacturer((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($manufacturer))
                        $html .= '<option selected="selected" value="MAN'.$id.'">'.$manufacturer->name.'</option>'.PHP_EOL;
                    break;

                // Case to handle the option to show all Suppliers
                case 'ALLSUP':
                    $html .= '<option selected="selected" value="ALLSUP0">'.$this->l('All suppliers').'</option>'.PHP_EOL;
                    break;

                case 'SUP':
                    $supplier = new Supplier((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($supplier))
                        $html .= '<option selected="selected" value="SUP'.$id.'">'.$supplier->name.'</option>'.PHP_EOL;
                    break;

                case 'LNK':
                    $link = MegaTopLinks::get((int)$id, (int)$id_lang, (int)$id_shop);
                    if (count($link))
                    {
                        if (!isset($link[0]['label']) || ($link[0]['label'] == ''))
                        {
                            $default_language = Configuration::get('PS_LANG_DEFAULT');
                            $link = MegaTopLinks::get($link[0]['id_linksmegatop'], (int)$default_language, (int)Shop::getContextShopID());
                        }
                        $html .= '<option selected="selected" value="LNK'.(int)$link[0]['id_linksmegatop'].'">'.Tools::safeOutput($link[0]['label']).'</option>';
                    }
                    break;

                case 'SHOP':
                    $shop = new Shop((int)$id);
                    if (Validate::isLoadedObject($shop))
                        $html .= '<option selected="selected" value="SHOP'.(int)$id.'">'.$shop->name.'</option>'.PHP_EOL;
                    break;
            }
        }

        return $html.'</select>';
    }

    private function getMenuItems()
    {
        $items = Tools::getValue('items');
        if (is_array($items) && count($items))
            return $items;
        else
        {
            $conf = Configuration::get('LABMEGAMENU_ITEMS');
//print_r(Configuration::get('labcategoryslider_list_cate'));die;
            if (strlen($conf))
                return explode(',', Configuration::get('LABMEGAMENU_ITEMS'));
            else
                return array();
        }
    }

    public function installDb(){

            return (Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'linksmegatop` (
                `id_linksmegatop` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_shop` INT(11) UNSIGNED NOT NULL,
                `new_window` TINYINT( 1 ) NOT NULL,
                INDEX (`id_shop`)
            ) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;') &&
                Db::getInstance()->execute('
                 CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'linksmegatop_lang` (
                `id_linksmegatop` INT(11) UNSIGNED NOT NULL,
                `id_lang` INT(11) UNSIGNED NOT NULL,
                `id_shop` INT(11) UNSIGNED NOT NULL,
                `label` VARCHAR( 128 ) NOT NULL ,
                `link` VARCHAR( 128 ) NOT NULL ,
                INDEX ( `id_linksmegatop` , `id_lang`, `id_shop`)
            ) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;'));
    }

    private function uninstallDb() {
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'linksmegatop`');
        Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'linksmegatop_lang`');
        return true;
    }
    private function _installHookCustomer(){
        $hookspos = array(
                'megamenu',
            );
        foreach( $hookspos as $hook ){
            if( Hook::getIdByName($hook) ){
            } else {
                $new_hook = new Hook();
                $new_hook->name = pSQL($hook);
                $new_hook->title = pSQL($hook);
                $new_hook->add();
                $id_hook = $new_hook->id;
            }
        }
        return true;
    }


}