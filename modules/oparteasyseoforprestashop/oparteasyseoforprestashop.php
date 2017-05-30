<?php
/**
 * Module opartdevis
 *
 * @category Prestashop
 * @category Module
 * @author    Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright Op'art
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_'))
    exit;

class Oparteasyseoforprestashop extends Module {

    protected $config_form = false;
    protected $post_error = array();
    protected $success = array();
    protected $max_by_page_options = array(25, 50, 100, 200, 500);
    protected $max_by_page_default = 25;

    public function __construct() {
        $this->name = 'oparteasyseoforprestashop';
        $this->tab = 'seo';
        $this->version = '1.1.1';
        $this->author = 'Olivier CLEMENCE';
        $this->need_instance = 0;
        $this->module_key = '1c13082c1e6864aea5be9fefdddb768d';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Easy seo for prestashop');
        $this->description = $this->l('Automated search engin optimisation for Prestashop');

        $this->confirmUninstall = $this->l('Are you sure you wan\'t uninstal this module ?');
        $this->selected_element = 1; /* category tab is displayed by default */
        $this->selected_setting = 0;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install() {
        if (version_compare(_PS_VERSION_, '1.5.0', '<'))
            return false;

        include(dirname(__FILE__) . '/sql/install.php');

        //save token        
        Configuration::updateValue('OESFP_TOKEN', $this->getRandomString());
        
        return parent::install();
    }
    
    public function uninstall() {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        Configuration::deleteByName('OESFP_TOKEN');
        
        if (!parent::uninstall())
            return false;
        return true;
    }

    public function getRandomString() {
        return tools::substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,5);
    }
    /**
     * Load the configuration form
     */
    public function getContent() {
        if (Tools::getIsset('ajax') && Tools::getValue('ajax') == 1) {
            if (Tools::getValue('action') == 'saveLegend') {
                if ($this->saveLegend())
                    echo $this->l('saved');
                else
                    echo $this->l('error');
            }
            die();
        }
        if (Tools::getIsset('oesfp_select_setting'))
            $this->selected_setting = Tools::getValue('oesfp_select_setting');
        else
            $this->selected_setting = 0;

        if (Tools::getIsset('oesfp_element_type'))
            $this->selected_element = Tools::getValue('oesfp_element_type');

        $this->postProcess();

        $this->context->controller->addJS($this->_path . 'views/js/riot+compiler.min.js');
        $this->context->controller->addCss($this->_path . 'views/css/back.css');
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->context->controller->addCss($this->_path . 'views/css/back15.css');
            $this->context->controller->addJqueryPlugin('fancybox');
        }
        $admin_module_url = 'index.php?controller=AdminModules&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        $smarty = $this->context->smarty;

        //load meta data
        if ($this->selected_setting != 0 && $this->selected_setting != null)
            $meta_data = $this->getMetaSettings($this->selected_setting);
        else if (Tools::isSubmit('applySetting') || Tools::isSubmit('saveSetting'))
            $meta_data = $this->post2metadata($_POST);

        $selected_category = array();
        if (Tools::getIsset('oesfp_element_type') && Tools::getValue('oesfp_element_type') == 6) {
            $id_lang = (Tools::getIsset('oesfp_id_lang')) ? Tools::getValue('oesfp_id_lang') : $this->context->language->id;
            $max_img_by_page = (Tools::getIsset('oesfp_max_img_by_page')) ? tools::getValue('oesfp_max_img_by_page') : $this->max_by_page_default;
            $page_number = (Tools::getIsset('oesfp_page_number')) ? tools::getValue('oesfp_page_number') : 1;
            $empty_legend = (Tools::getIsset('oesfp_empty_legend')) ? tools::getValue('oesfp_empty_legend') : 0;
            $smarty->assign(array(
                'meta_images' => $this->getMetaImages($id_lang, $max_img_by_page, $page_number, $empty_legend),
                'max_img_by_page' => $max_img_by_page,
                'page_number' => $page_number,
                'empty_legend' => $empty_legend,
                'img_nb_page' => $this->getImgNbPage($max_img_by_page, $id_lang)
            ));
        } elseif (isset($meta_data)) {
            $id_lang = $meta_data['id_lang'];
            $selected_category = $meta_data['selected_category'];
            $smarty->assign('meta_data', $meta_data);
        } else
            $id_lang = $this->context->language->id;


        $saved_settings = $this->loadSettings($this->selected_element);

        if (Tools::getIsset('oesfp_element_type') && Tools::getValue('oesfp_element_type') == '9')
            $smarty->assign('metaToken', Tools::getAdminTokenLite('AdminMeta'));

        //add cron url
        
        $cronUrl = 'http://'.Configuration::get('PS_SHOP_DOMAIN')._MODULE_DIR_ . $this->name . '/oesfp_cron.php?t='.Configuration::get('OESFP_TOKEN');
        
        
        $smarty->assign(array(
            'saved_settings' => $saved_settings,
            'selected_element' => $this->selected_element,
            'admin_module_url' => $admin_module_url,
            'module_dir' => $this->_path,
            'moduledir' => _MODULE_DIR_ . $this->name . '/',
            'module_name' => $this->name,
            'module_local_path' => $this->local_path,
            'languages' => $this->context->language->getLanguages(false, $this->context->shop->id),
            'id_lang' => $id_lang,
            'baseDir' => __PS_BASE_URI__,
            'ps_version' => Tools::substr(_PS_VERSION_, 0, 3),
            'max_by_page_options' => $this->max_by_page_options,
            'cronUrl' => $cronUrl,
        ));

        //load categorie list
        $tree = $this->getCategoryTree($id_lang);
        $select_category_options = $this->nested2select($tree, '', $selected_category);
        $smarty->assign('select_category_options', $select_category_options);
        $output = $this->display(__FILE__, 'views/templates/admin/header.tpl');

        if (count($this->post_error) > 0)
            foreach ($this->post_error as $err)
                $output .= $this->displayError($err);

        $smarty->assign('success', $this->success);

        
        
        $output .= $smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $output .= $smarty->fetch($this->local_path . 'views/templates/admin/prestui/ps-tags.tpl');
        $output .= $this->display(__FILE__, 'views/templates/admin/help.tpl');
        return $output;
    }

    public function getImgNbPage($max_page, $id_lang) {
        $id_shop = $this->context->shop->id;

        $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'image_lang l, ' . _DB_PREFIX_ . 'image_shop s WHERE l.id_lang=' . (int) $id_lang . ' AND l.id_image=s.id_image AND s.id_shop=' . (int) $id_shop;
        $res = db::getInstance()->getValue($sql);
        $result = (($res / $max_page) > 1) ? ($res / $max_page) : 1;
        return ceil($result);
    }

    public function getMetaImages($id_lang, $max_img_by_page, $page_number, $empty_legend) {
        $start = ($page_number - 1) * $max_img_by_page;
        $id_shop = $this->context->shop->id;
        $add_to_where = ($empty_legend == 1) ? ' AND (TRIM(legend)="" OR legend IS NULL)' : '';
        $sql = 'SELECT il.legend,i.id_image,i.id_product,pl.name,pl.link_rewrite,il.id_lang ';
        $sql .= 'FROM ' . _DB_PREFIX_ . 'image_lang il, ' . _DB_PREFIX_ . 'image i, ' . _DB_PREFIX_ . 'product_lang  pl, ' . _DB_PREFIX_ . 'image_shop s ';
        //$sql .= 'WHERE i.id_image=il.id_image AND i.id_image=s.id_image AND pl.id_product=i.id_product AND pl.id_product=s.id_product AND il.id_lang=pl.id_lang AND s.id_shop='.(int)$id_shop.' AND il.id_lang='.(int)$id_lang.$add_to_where.' LIMIT '.(int)$start.','.(int)$max_img_by_page;
        $sql .= 'WHERE i.id_image=il.id_image AND pl.id_product=i.id_product AND il.id_lang=pl.id_lang AND il.id_lang=' . (int) $id_lang . ' AND pl.id_shop=s.id_shop AND s.id_shop=' . (int) $id_shop . ' AND s.id_image=i.id_image ' . $add_to_where . ' LIMIT ' . (int) $start . ',' . (int) $max_img_by_page;

        $result = db::getInstance()->Executes($sql);
        $link = new Link;
        $meta_images = array();
        foreach ($result as $img) {
            $img['src'] = $link->getImageLink($img['link_rewrite'], $img['id_image']);
            $meta_images[$img['id_product']][] = $img;
        }
        return $meta_images;
    }

    public function saveLegend() {
        $id_image = Tools::getValue('id_image');
        $id_lang = Tools::getValue('id_lang');        
        $legend = Tools::getValue('legend');
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'image_lang SET legend="' . pSQL($legend) . '" WHERE id_image=' . (int) $id_image . ' AND id_lang=' . (int) $id_lang;
        return db::getInstance()->execute($sql);
    }

    public function post2metadata($data) {
        $meta = array();
        $meta['id_oparteasyseoforprestashop_settings'] = $data['oesfp_select_settings'];
        $meta['name'] = $data['oesfp_setting_name'];
        $meta['element_type'] = $this->selected_element;
        $meta['meta_title'] = $data['oesfp_title'];
        $meta['meta_desc'] = $data['oesfp_desc'];
        $meta['id_lang'] = $data['oesfp_lang'];
        $meta['id_shop'] = $data['oesfp_setting_name'];
        $meta['selected_category'] = (isset($data['oesfp_selected_category'])) ? $data['oesfp_selected_category'] : array();
        $meta['override_meta'] = $data['oesfp_override'];
        $meta['automatic_update'] = $data['oesfp_automaticupdate'];
        return $meta;
    }

    public function loadSettings($element_type) {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'oparteasyseoforprestashop_settings WHERE element_type=' . (int) $element_type;
        $result = db::getInstance()->executeS($sql);
        return $result;
    }

    public function getCategoryTree($id_lang) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT c.id_category, c.id_parent, cl.name
			FROM `' . _DB_PREFIX_ . 'category` c
			' . Shop::addSqlAssociation('category', 'c') . '
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . '
			WHERE 1 ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '')
        );
        $dataset = array();
        foreach ($result as $value) {
            $dataset[$value['id_category']] = $value;
            if (!is_numeric($value['id_parent']) || $value['id_parent'] == 0)
                $dataset[$value['id_category']]['id_parent'] = null;
        }

        $tree = array();
        foreach ($dataset as $id => &$node) {
            if ($node['id_parent'] === null || $node['id_parent'] == '' || !is_numeric($node['id_parent'])) {
                $tree[$id] = &$node;
            } else {
                if (!isset($dataset[$node['id_parent']]['children']))
                    $dataset[$node['id_parent']]['children'] = array();
                $dataset[$node['id_parent']]['children'][$id] = &$node;
            }
        }
        return $tree;
    }

    public function nested2select($data, $spaces = '', $selected_category) {
        $result = array();
        if (sizeof($data) > 0) {
            foreach ($data as $entry) {
                $selected = (count($selected_category) > 0 && in_array($entry['id_category'], $selected_category)) ? 'selected="selected"' : '';
                $child = (isset($entry['children'])) ? $this->nested2select($entry['children'], $spaces . '&nbsp;', $selected_category) : '';
                $result[] = sprintf(
                        '<option value="%s" ' . $selected . '>' . $spaces . '%s </option>%s', $entry['id_category'], $entry['name'], $child
                );
            }
        }
        return implode($result);
    }

    public function getMetaSettings($id_settings) {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'oparteasyseoforprestashop_settings WHERE id_oparteasyseoforprestashop_settings=' . (int) $id_settings;
        $result = db::getInstance()->getRow($sql);
        $result['selected_category'] = explode(',', $result['selected_category']);
        $this->selected_element = $result['element_type'];
        return $result;
    }

    /**
     * Save form data.
     */
    protected function postProcess() {
        $id_lang = Tools::getValue('oesfp_lang');

        $selected_categories = array();
        if (Tools::getIsset('oesfp_title'))
            $title = Tools::getValue('oesfp_title');
        if (Tools::getIsset('oesfp_desc'))
            $desc = Tools::getValue('oesfp_desc');
        if (Tools::getIsset('oesfp_selected_category'))
            $selected_categories = Tools::getValue('oesfp_selected_category');
        if (Tools::getIsset('oesfp_override'))
            $is_override = Tools::getValue('oesfp_override');
        if (Tools::getIsset('oesfp_automaticupdate'))
            $automatic_update = Tools::getValue('oesfp_automaticupdate');

        $id_shop = $this->context->shop->id;
        $db = db::getInstance();

        if (Tools::getIsset('oesfp_delete_setting') && is_numeric(Tools::getValue('oesfp_delete_setting'))) {
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'oparteasyseoforprestashop_settings WHERE id_oparteasyseoforprestashop_settings=' . (int) Tools::getValue('oesfp_delete_setting');
            if ($db->execute($sql))
                $this->success[] = $this->l('Setting deleted');
            else
                $this->post_error[] = $this->l('Problem occured during delete settings');
        }

        /* save settings */
        if (Tools::isSubmit('saveSetting')) {
            if (Tools::getIsset('oesfp_setting_name') && Tools::getValue('oesfp_setting_name') != '')
                $setting_name = Tools::getValue('oesfp_setting_name');
            else {
                $this->post_error[] = $this->l('You have to add a setting name');
                return false;
            }

            /* selected category */
            if (count($selected_categories) > 0)
                $category_str = implode(',', $selected_categories);
            else
                $category_str = '';

            if (Tools::getIsset('oesfp_select_settings') && Tools::getValue('oesfp_select_settings') != '0') {
                $this->selected_setting = Tools::getValue('oesfp_select_settings');
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'oparteasyseoforprestashop_settings '
                        . 'SET name="' . pSQL($setting_name) . '",element_type=' . (int) Tools::getValue('oesfp_element_type') . ',meta_title="'.pSQL($title).'",meta_desc="' . pSQL($desc) . '",id_lang=' . (int) $id_lang . ',id_shop=' . (int) $id_shop . ',selected_category="' . pSQL($category_str) . '",override_meta=' . (int) $is_override . ',automatic_update=' . (int) $automatic_update . ' '
                        . 'WHERE id_oparteasyseoforprestashop_settings=' . (int) $this->selected_setting;
                if($db->execute($sql))
                    $this->success[] = $this->l('Settings has been saved successfully');
                else
                    $this->post_errors[] = $this->l('An error occured during settings save');
            } else {
                //name already exist
                $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'oparteasyseoforprestashop_settings WHERE name="' . pSQL($setting_name) . '"';
                $exist = $db->getValue($sql);
                if ($exist != false) {
                    $this->post_error[] = 'This setting name already exist. Please choose another name';
                    return false;
                }

                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'oparteasyseoforprestashop_settings (name,element_type,meta_title,meta_desc,id_lang,id_shop,selected_category,override_meta,automatic_update) ';
                $sql .='VALUES ("' . pSQL($setting_name) . '",' . (int) $this->selected_element . ',"' . pSQL($title) . '","' . pSQL($desc) . '",' . (int) $id_lang . ',' . (int) $id_shop . ',"' . pSQL($category_str) . '", '.(int)$is_override.', '.(int)$automatic_update.')';
                if($db->execute($sql))
                    $this->success[] = $this->l('Settings has been updated successfully');
                else
                    $this->post_errors[] = $this->l('An error occured during settings update');
                $this->selected_setting = $db->Insert_ID();
            }
        }

        if (Tools::isSubmit('applySetting')) {
           $this->applySettings($title, $desc, $id_lang, $id_shop, $selected_categories, $is_override, $this->selected_element);
        }
    }

    public function applySettings($title, $desc, $id_lang, $id_shop, $selected_categories, $is_override, $selected_element) {
         /** need product name ? * */
            $need_product_name = 0;
            if (strpos($title, '[FIRST_PRODUCT_NAME]') !== false || strpos($title, '[SECOND_PRODUCT_NAME]') !== false || strpos($title, '[THIRD_PRODUCT_NAME]') !== false || strpos($desc, '[FIRST_PRODUCT_NAME]') !== false || strpos($desc, '[SECOND_PRODUCT_NAME]') !== false || strpos($desc, '[THIRD_PRODUCT_NAME]') !== false)
                $need_product_name = 1;


            /* as we need category name ? */
            $need_cat = 0;
            $need_cat_parent = 0;
            if (strpos($title, '[CATEGORY_NAME]') !== false || strpos($desc, '[CATEGORY_NAME]') !== false)
                $need_cat = 1;
            if (strpos($title, '[CATEGORY_PARENT_NAME]') !== false || strpos($desc, '[CATEGORY_PARENT_NAME]') !== false) {
                $need_cat = 1;
                $need_cat_parent = 1;
            }
            
            /* category */
            if ($selected_element == 1)
                $list = $this->getCategories($id_lang, $id_shop, $selected_categories, $need_product_name);

            /* product */
            else if ($selected_element == 2)
                $list = $this->getProducts($id_lang, $need_cat, $need_cat_parent, $selected_categories);

            /* supplier */
            else if ($selected_element == 3)
                $list = $this->getSuppliers($id_lang, $need_product_name);

            /* cms */
            else if ($selected_element == 4)
                $list = $this->getCms($id_lang, $id_shop);

            /* manufacturer */
            else if ($selected_element == 5)
                $list = $this->getManufacturers($id_lang, $need_product_name);

            //update elements                
            if (isset($list) && count($list) > 0) 
                $this->updateElements($list, $selected_element, $is_override, $title, $desc, $id_lang, $id_shop);            
            else
                $this->post_errors[] = $this->l('no element has been found');
    }
    
    public function updateElements($list, $element_type, $is_override, $title, $desc, $id_lang, $id_shop) {        
        switch ($element_type) {
            case 1:
                $element_name = "category";
                $add_shop_sql = true;
                break;
            case 2:
                $element_name = "product";
                $add_shop_sql = true;
                break;
            case 3:
                $element_name = "supplier";
                $add_shop_sql = false;
                break;
            case 4:
                $element_name = "cms";
                $add_shop_sql = (version_compare(_PS_VERSION_, '1.6.0', '<'))?false:true;
                break;
            case 5:
                $element_name = "manufacturer";
                $add_shop_sql = false;
                break;
        }
        $db = db::getInstance();

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
            $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'shop WHERE id_shop=' . (int) $id_shop;
            $shop_name = $db->getValue($sql);
        }   
        else 
            $shop_name = Configuration::get('PS_SHOP_NAME');
        
        //exception for CMS
        $class_name = ($element_name != 'cms')?Tools::ucfirst($element_name):'CMS';
        $validation_obj_rules = ObjectModel::getValidationRules($class_name);
        $meta_title_obj_size = $validation_obj_rules['sizeLang']['meta_title'];
        $meta_desc_obj_size  = $validation_obj_rules['sizeLang']['meta_description'];
                
        foreach ($list as $element) {
            $update_title = false;
            $update_description = false;
            $element['shop_name'] = $shop_name;
            if ($element['meta_title'] == '' || $is_override == 1)
                $update_title = $this->createMeta($title, $element, $meta_title_obj_size);

            if ($element['meta_description'] == '' || $is_override == 1)
                $update_description = $this->createMeta($desc, $element, $meta_desc_obj_size);

            if ($update_title == false && $update_description == false)
                continue;

            $sql = 'UPDATE ' . _DB_PREFIX_ . $element_name . '_lang SET ';
            $set = '';
            if ($update_title != false)
                $set = ' meta_title = "' . pSQL($update_title) . '"';

            if ($update_description != false)
                $set .= (($set == '') ? '' : ',') . ' meta_description = "' . pSQL($update_description) . '"';

            $add_shop_sql = ($add_shop_sql == true) ? ' AND id_shop=' . (int) $id_shop : '';

            $where = ' WHERE id_lang=' . (int) $id_lang . $add_shop_sql . ' AND id_' . $element_name . '=' . (int) $element['id_' . $element_name];
            $sql = 'UPDATE ' . _DB_PREFIX_ . $element_name . '_lang SET ' . $set . $where;
            if(!$db->execute($sql))
                $this->post_errors[] = sprintf($this->l('An error occuring during the update of %s'), $element_name);
        }
        if(!isset($this->post_errors) || !count($this->post_errors)>0)
            $this->success[] = $this->l('Settings has been applied successfully');
    }

    public function createMeta($str, $obj, $limit) {
        $str = html_entity_decode($str);//inutile ?
        foreach ($obj AS $key => $value)
            $str = Tools::substr(str_ireplace('[' . $key . ']', $value, $str),0,$limit);

        $str = html_entity_decode($str);
        return $str;
    }

    public function getCategories($id_lang, $id_shop, $selected_categories, $need_product_name) {
        $sql_where = '';
       /* if (count($selected_categories) > 0) {
            foreach ($selected_categories as $id_cat)
                $sql_where .= (($sql_where == '') ? ' AND (' : ' OR ') . 'c.id_category=' . (int) $id_cat;
            $sql_where .=')';
        }*/
        
        $sql = 'SELECT cl.*,c.id_parent FROM ' . _DB_PREFIX_ . 'category_lang cl, ' . _DB_PREFIX_ . 'category c WHERE c.id_category=cl.id_category AND cl.id_lang=' . (int) $id_lang . ' AND cl.id_shop=' . (int) $id_shop;
        //$sql .= $sql_where;
        $result = db::getInstance()->executeS($sql);
        $categories = array();
        foreach ($result as $category)
            $categories[$category['id_category']] = $category;

        foreach ($categories as &$category)
            $category['parent_name'] = (isset($categories[$category['id_parent']])) ? $categories[$category['id_parent']]['name'] : '';

        if ($need_product_name == 1) {
            foreach ($categories as &$category) {
                $result = array();
                $sql = 'SELECT pl.name FROM ' . _DB_PREFIX_ . 'product_lang pl, ' . _DB_PREFIX_ . 'category_product cp WHERE pl.id_product = cp.id_product AND pl.id_lang=' . (int) $id_lang . ' AND cp.id_category=' . (int) $category['id_category'] . ' ORDER BY cp.position LIMIT 0,3';
                $result = db::getInstance()->executeS($sql);
                $category['FIRST_PRODUCT_NAME'] = (isset($result[0])) ? $result[0]['name'] : '';
                $category['SECOND_PRODUCT_NAME'] = (isset($result[1])) ? $result[1]['name'] : '';
                $category['THIRD_PRODUCT_NAME'] = (isset($result[2])) ? $result[2]['name'] : '';
            }
        }
        if (count($selected_categories) > 0) {
            $return_categories = array();
            foreach ($selected_categories as $id_cat) 
                $return_categories[$id_cat] = $categories[$id_cat];
            
        }
        else 
            $return_categories = $categories;
          
        
        return $return_categories;
    }

    public function getSuppliers($id_lang, $need_product_name) {
        $sql = 'SELECT sl.*,s.name FROM ' . _DB_PREFIX_ . 'supplier_lang sl, ' . _DB_PREFIX_ . 'supplier s WHERE sl.id_supplier = s.id_supplier AND sl.id_lang=' . $id_lang;
        $result = db::getInstance()->executeS($sql);
        $suppliers = array();
        foreach ($result as $supplier)
            $suppliers[$supplier['id_supplier']] = $supplier;

        if ($need_product_name == 1) {
            foreach ($suppliers as &$supplier) {
                $result = array();
                $sql = 'SELECT pl.name FROM ' . _DB_PREFIX_ . 'product_lang pl, ' . _DB_PREFIX_ . 'product_supplier ps WHERE pl.id_product = ps.id_product AND pl.id_lang=' . (int) $id_lang . ' AND ps.id_supplier=' . (int) $supplier['id_supplier'] . ' ORDER BY ps.id_product LIMIT 0,3';
                $result = db::getInstance()->executeS($sql);
                $supplier['FIRST_PRODUCT_NAME'] = (isset($result[0])) ? $result[0]['name'] : '';
                $supplier['SECOND_PRODUCT_NAME'] = (isset($result[1])) ? $result[1]['name'] : '';
                $supplier['THIRD_PRODUCT_NAME'] = (isset($result[2])) ? $result[2]['name'] : '';
            }
        }
        return $suppliers;
    }

    public function getManufacturers($id_lang, $need_product_name) {
        $sql = 'SELECT sl.*,s.name FROM ' . _DB_PREFIX_ . 'manufacturer_lang sl, ' . _DB_PREFIX_ . 'manufacturer s WHERE sl.id_manufacturer = s.id_manufacturer AND sl.id_lang=' . $id_lang;
        $result = db::getInstance()->executeS($sql);
        $manufacturers = array();
        foreach ($result as $manufacturer)
            $manufacturers[$manufacturer['id_manufacturer']] = $manufacturer;

        if ($need_product_name == 1) {
            foreach ($manufacturers as &$manufacturer) {
                $result = array();
                $sql = 'SELECT pl.name FROM ' . _DB_PREFIX_ . 'product_lang pl, ' . _DB_PREFIX_ . 'product p WHERE pl.id_product = p.id_product AND pl.id_lang=' . (int) $id_lang . ' AND p.id_manufacturer=' . (int) $manufacturer['id_manufacturer'] . ' ORDER BY p.id_product LIMIT 0,3';
                $result = db::getInstance()->executeS($sql);
                $manufacturer['FIRST_PRODUCT_NAME'] = (isset($result[0])) ? $result[0]['name'] : '';
                $manufacturer['SECOND_PRODUCT_NAME'] = (isset($result[1])) ? $result[1]['name'] : '';
                $manufacturer['THIRD_PRODUCT_NAME'] = (isset($result[2])) ? $result[2]['name'] : '';
            }
        }
        return $manufacturers;
    }

    public function getCms($id_lang, $id_shop) {
        if (version_compare(_PS_VERSION_, '1.6.0', '<'))
            $sql = 'SELECT cl.* FROM ' . _DB_PREFIX_ . 'cms_lang cl WHERE cl.id_lang=' . $id_lang;
        else
            $sql = 'SELECT cl.* FROM ' . _DB_PREFIX_ . 'cms_lang cl WHERE cl.id_lang=' . $id_lang . ' AND cl.id_shop=' . (int) $id_shop;
        ;
        $result = db::getInstance()->executeS($sql);
        return $result;
    }

    public function getProducts($id_lang, $need_cat, $need_cat_parent, $selected_categories) {
        /* ici je peux remplacer les 0 par des valeur pour start et limit
         *  et faire plusieurs passage si trop de prod
         */
        $result = Product::getProducts($id_lang, '0', '0', 'id_product', 'ASC');

        $products = array();
        foreach ($result as $product)
            $products[$product['id_product']] = $product;

         foreach ($products as &$prod) {
            /*$prod['price_ht'] = Tools::displayPrice($prod['price'], $this->context->currency, false);
            $ttc_price = $prod['price'] + ($prod['price'] * $prod['rate'] / 100);
            $prod['price_ttc'] = Tools::displayPrice($ttc_price);*/
            
            $specific_price_output = null;
            $prod['price_ht'] = Tools::displayPrice(Product::getPriceStatic($prod['id_product'], false, null, 6, null, false, false, 1, false, null, null, null, $specific_price_output, true, true, null, false));            
            $prod['price_ttc'] = Tools::displayPrice(Product::getPriceStatic($prod['id_product'], true, null, 6, null, false, false, 1, false, null, null, null, $specific_price_output, true, true, null, false));
 
            //cat name
            if ($need_cat == 1) {
                $sql = 'SELECT cl.*,c.id_parent FROM ' . _DB_PREFIX_ . 'category_lang cl, ' . _DB_PREFIX_ . 'category c WHERE c.id_category=cl.id_category AND cl.id_lang=' . (int) $id_lang . ' AND cl.id_shop=' . (int) $prod['id_shop'] . ' AND c.id_category=' . (int) $prod['id_category_default'];
                $cat_result = db::getInstance()->getRow($sql);
                $prod['category_name'] = $cat_result['name'];
                if ($need_cat_parent == 1) {
                    $sql = 'SELECT cl.*,c.id_parent FROM ' . _DB_PREFIX_ . 'category_lang cl, ' . _DB_PREFIX_ . 'category c WHERE c.id_category=cl.id_category AND cl.id_lang=' . (int) $id_lang . ' AND cl.id_shop=' . (int) $prod['id_shop'] . ' AND c.id_category=' . (int) $cat_result['id_parent'];
                    $cat_parent_result = db::getInstance()->getRow($sql);
                    $prod['category_parent_name'] = $cat_parent_result['name'];
                }
            }
        }
        //category filter
        if (count($selected_categories) > 0) {
            $sql_where = '';
            foreach ($selected_categories as $id_cat)
                $sql_where .= (($sql_where == '') ? '' : ' OR ') . 'id_category=' . (int) $id_cat;

            $sql = 'select id_product FROM ' . _DB_PREFIX_ . 'category_product WHERE ' . $sql_where;
            $res = db::getInstance()->executeS($sql);

            $result = array();
            foreach ($res as $val)
                $result[] = $val['id_product'];

            foreach ($products AS $key => $value) {
                if (!in_array($key, $result))
                    unset($products[$key]);
            }
        }
        //die();
        return $products;
    }
}
