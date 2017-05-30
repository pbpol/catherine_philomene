<?php
if (!defined('_PS_VERSION_'))
    exit;

// Loading Models
include_once(_PS_MODULE_DIR_ . 'labtestimonials/libs/Params.php');
include_once(_PS_MODULE_DIR_ . 'labtestimonials/classes/LabTestimonial.php');
class labtestimonials extends Module
{
   private $_html = '';
   protected $params = null;
   const INSTALL_SQL_FILE = 'install.sql';
   const UNINSTALL_SQL_FILE = 'uninstall.sql';
   public function __construct()
        {
            $this->name ='labtestimonials';
            $this->version = '1.6';
            $this->author = 'labersthemes';
            $this->bootstrap = true;
            $this->tab = 'front_office_features';
            $this->need_instance = 0;
            $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
            parent::__construct();
            $this->displayName = $this->l('Lab Testimonials ');
            $this->description = $this->l('Module manager Testimonials');
            $this->_searched_email = null;
            $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
            $this->_params = new Params($this->name, $this);
        }

    public function initConfigs()
        {
            return array(
                'test_limit' => 10,
                'type_image' => 'png|jpg|gif',
                'type_video' => 'flv|mp4|avi',
                'size_limit' => 6,
                'captcha' => 1,
                'auto_post' => 1,
            );
        }

    public function install()
    {
        if (parent::install() &&  $this->registerHook('blockPosition2')) {
            $res = $this->installDb();
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
                $tab->name[$language['id_lang']] = $this->l('Manage Testimonials');
            $tab->class_name = 'AdminTestimonials';
            $tab->id_parent = (int)Tab::getIdFromClassName('AdminLabMenu');
            $tab->module = $this->name;
            $tab->add();
            $configs = $this->initConfigs();
            $this->_params->batchUpdate($configs);
            return (bool)$res;
        }
        return false;
    }

    public function uninstall()
    {
        if (parent::uninstall()) {
            $res = $this->uninstallDb();
            $tab = new Tab((int) Tab::getIdFromClassName('AdminTestimonials'));
            $tab->delete();
          //  $res &= $this->uninstallModuleTab('AdminLabMenu');
            return (bool)$res;
        }
        return false;
    }
    public function installDb(){
        $res = Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'labtestimonial` (
            `id_labtestimonial` int(11) NOT NULL AUTO_INCREMENT,
            `name_post` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `company` varchar(255) DEFAULT NULL,
            `address` varchar(500) NOT NULL,
            `media` varchar(255) DEFAULT NULL,
            `media_type` varchar(25) DEFAULT NULL,
            `content` text NOT NULL,
            `date_add` datetime DEFAULT NULL,
            `active` tinyint(1) DEFAULT NULL,
            `position` int(11) DEFAULT NULL ,
            PRIMARY KEY (`id_labtestimonial`))
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
        );
        if ($res)
            $res &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'labtestimonial_shop` (
                `id_labtestimonial` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_labtestimonial`,`id_shop`))
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
      $sql =  "INSERT INTO `"._DB_PREFIX_."labtestimonial`(`name_post`,`email`,`content`,`date_add`,`active`) VALUES
            ('Name Test','test@gmail.com', 'Lorem ipsum dolor sit amet conse ctetu
Sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', '2014-10-29', 1),
			('Name Test','test@gmail.com', 'Lorem ipsum dolor sit amet conse ctetu
Sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', '2014-10-29', 1),
			('Name Test','test@gmail.com', 'Lorem ipsum dolor sit amet conse ctetu
Sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', '2014-10-29', 1)";
        $sql1 = "INSERT INTO `"._DB_PREFIX_."labtestimonial_shop`(`id_shop`,`id_labtestimonial`) VALUES
        (1,1),
        (1,2),
        (1,3),
        (1,4)";
        if ($res){
              $res &=  Db::getInstance()->Execute($sql);
              $res &=  Db::getInstance()->Execute($sql1);

        }
        return (bool)$res;
    }
    private function uninstallDb() {
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'labtestimonial`');
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'labtestimonial_shop`');
    return true;
}

    public function getContent()
    {
        $this->_html .= '<h2>' . $this->displayName . ' and Custom Fields.</h2>';
        if (Tools::isSubmit('submitUpdate')) {
            if ($this->_postValidation()) {
                $configs = $this->initConfigs();
               $res = $this->_params->batchUpdate($configs);
                if (!$res) {
                    $this->_html .= $this->displayError($this->l('Configuration could not be updated'));
                } else {
                    $this->_html .= $this->displayConfirmation($this->l('Configuration updated'));
                }
            }
        }
        return $this->_html . $this->initForm();
    }

    protected function initForm()
    {
        $configs = $this->initConfigs();
        $params = $this->_params;
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Global Setting'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                $params->inputTags('test_limit', 'Testimonial Limit:', false, 'The number items on a page.'),
                $params->inputTags('type_image', 'Image type:', false, 'allow upload image type.'),
                $params->inputTags('type_video', 'Video type:', false, 'allow upload video type.'),
                $params->inputTags('size_limit', 'Size limit upload:', false, 'Mb .Max size file upload.'),
                $params->switchTags('captcha', 'Display captcha:'),
                $params->switchTags('auto_post', 'Admin approve', 'Admin can set enable or disable auto post'),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUpdate';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = $this->context->language->id;
        $helper->tpl_vars = array(
            'fields_value' => $params->getConfigFieldsValues($configs),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm($this->fields_form);
    }
    public function _postValidation()
    {
        $errors = array();
        if (Tools::isSubmit('submitUpdate')) {
            if (!Tools::getValue(('test_limit')) || !Validate::isInt(Tools::getValue('test_limit')))
                $errors[] = $this->l('False! Check again with testimonial limit.');
            if (!Tools::getValue('size_limit') || !Validate::isInt(Tools::getValue('size_limit')))
                $errors[] = $this->l('False! Check again with size upload limit.');
        }
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
            return false;
        }
        return true;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function hookdisplayLeftColumn($params)
    {
        $this->context->controller->addJqueryPlugin('bxslider');
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name. '/assets/front/css/styleRightColumn.css');
        $this->context->controller->addJqueryPlugin('fancybox');
        $testLimit = $this->getParams()->get('test_limit');
        $get_testimonials = labtestimonial::getAllTestimonials($testLimit);
        $img_types = explode('|', $this->getParams()->get('type_image'));
        $video_types = explode('|', $this->getParams()->get('type_video'));
        $this->context->smarty->assign(array(
           'testimonials' => $get_testimonials,
            'arr_img_type' => $img_types,
            'video_types' => $video_types,
            'mediaUrl' => _PS_IMG_ . 'labtestimonial/',
            'video_post' => _MODULE_DIR_ . $this->name . '/assets/front/img/video.jpg',
            'video_youtube' => _MODULE_DIR_ . $this->name . '/assets/front/img/youtube.jpg',
            'video_vimeo' => _MODULE_DIR_ . $this->name . '/assets/front/img/vimeo.jpg',
        ));
        return $this->display(__FILE__,'/views/templates/front/testimonials_random.tpl');
    }

    public function hookblockPosition2($params)
    {
        return $this->hookdisplayLeftColumn($params);
    }
}
