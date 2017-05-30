<?php

// Security
if (!defined('_PS_VERSION_'))
    exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');
// Loading Models
require_once(_PS_MODULE_DIR_ .'labcustomfooter/models/Staticfooter.php');

class labcustomfooter extends Module {
    public  $hookAssign   = array();
    public $_staticModel =  "";
    public function __construct() {
        $this->name = 'labcustomfooter';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'labersthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->hookAssign = array('footer');
        $this->_staticModel = new Staticfooter();
        parent::__construct();

        $this->displayName = $this->l('Lab Custom Footer');
        $this->description = $this->l('Manager Static blocks');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->admin_tpl_path = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/';
    }
    public function install() {
		$res = $this->installDb();
          // Install Tabs
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
        $tab->name[$language['id_lang']] = $this->l('Manager Static Footer');
        $tab->class_name = 'Adminlabcustomfooter';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminLabMenu'); 
        $tab->module = $this->name;
        $tab->add();
        // Set some defaults
        return parent::install() &&
                $this->registerHook('footer') &&
		$this->_installHookCustomer()&&
		$this->registerHook('blockFooter1')&&
		$this->registerHook('blockFooter2')&&
		$this->registerHook('blockFooter3')&&
		$this->registerHook('blockFooter4')&&
        $this->registerHook('displayBackOfficeHeader');
		return (bool)$res;
    }

    public function uninstall() {
        Configuration::deleteByName('labcustomfooter');
		$res = $this->uninstallDb();
          //  $res &= $this->uninstallModuleTab('AdminLabMenu');
        $tab = new Tab((int) Tab::getIdFromClassName('Adminlabcustomfooter'));
        $tab->delete();
        // Uninstall Module
        if (!parent::uninstall())
            return false;
        return true;
		return (bool)$res;
    }
/* database */
public function installDb(){
        $res = Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lab_staticfooter` (
			  `id_staticblock` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `identify` varchar(128) NOT NULL,
			  `hook_position` varchar(128) NOT NULL,
			  `name_module` varchar(128) NOT NULL,
			  `hook_module` varchar(128) NOT NULL,
			  `order` int(10) unsigned NOT NULL,
			  `insert_module` int(10) unsigned NOT NULL,
			  `active` int(10) unsigned NOT NULL,
			  `is_default` int(10) unsigned NOT NULL DEFAULT "0",
			  `showhook` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id_staticblock`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15'
        );
        if ($res)
            $res &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lab_staticfooter_lang` (
				  `id_staticblock` int(11) unsigned NOT NULL,
				  `id_lang` int(11) unsigned NOT NULL,
				  `title` varchar(128) NOT NULL,
				  `description` longtext,
				  PRIMARY KEY (`id_staticblock`,`id_lang`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8');
		if ($res)
            $res &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lab_staticfooter_shop` (
				  `id_staticblock` int(11) unsigned NOT NULL,
				  `id_shop` int(11) unsigned NOT NULL,
				  PRIMARY KEY (`id_staticblock`,`id_shop`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8');
		
		$sql =  "INSERT INTO `"._DB_PREFIX_."lab_staticfooter` (`id_staticblock`, `identify`, `hook_position`, `name_module`, `hook_module`, `order`, `insert_module`, `active`, `is_default`, `showhook`) VALUES
			(3, 'blockcms', 'blockFooter1', 'blockcms', 'displayFooter', 0, 1, 0, 1, 1),
			(4, 'blockmyaccountfooter', 'blockFooter1', 'blockmyaccountfooter', 'displayFooter', 0, 1, 0, 1, 1),
			(5, 'blocknewsletter', 'blockFooter2', 'blocknewsletter', 'displayFooter', 0, 1, 0, 1, 1),
			(6, 'blocksocial', 'blockFooter2', 'blocksocial', 'displayFooter', 0, 1, 0, 1, 1),
			(7, 'copyright ', 'blockFooter3', 'Chose Module', 'displayFooter', 0, 0, 0, 1, 1),
			(9, 'logo-paypal', 'blockFooter3', 'Chose Module', 'displayFooter', 0, 0, 0, 1, 1)";
        $sql1 = "INSERT INTO `"._DB_PREFIX_."lab_staticfooter_lang` (`id_staticblock`, `id_lang`, `title`, `description`) VALUES
			(3, 1, 'blockcms', NULL),
			(3, 2, 'blocknewsletter', NULL),
			(4, 1, 'blockmyaccountfooter', NULL),
			(4, 2, 'blocksocial', NULL),
			(5, 1, 'blocknewsletter', NULL),
			(5, 2, 'blocknewsletter', NULL),
			(6, 1, 'blocksocial', NULL),
			(6, 2, 'blockcategories', NULL),
			(7, 1, 'copyright ', '<div class=\"copyright col-lg-6 col-md-6 col-sm-6 col-xs-12\">Copyright © 2015 <a href=\"http://landthemes.com/\">landthemes. </a>All Rights Reserved</div>'),
			(7, 2, 'blockcms', '<div class=\"copyright col-lg-6 col-md-6 col-sm-6 col-xs-12\">Copyright © 2015 <a href=\"http://landthemes.com/\">landthemes. </a>All Rights Reserved</div>'),
			(9, 1, 'logo paypal', '<div class=\"paypal col-lg-6 col-md-6 col-sm-6 col-xs-12\">\r\n<p><img src=\"/lab_noraure/img/cms/payment_1.png\" alt=\"\" /></p>\r\n</div>'),
			(9, 2, 'blockmyaccountfooter', '<div class=\"paypal col-lg-6 col-md-6 col-sm-6 col-xs-12\">\r\n<p><img src=\"/lab_noraure/img/cms/payment_1.png\" alt=\"\" /></p>\r\n</div>')";
        $sql2 = "INSERT INTO `"._DB_PREFIX_."lab_staticfooter_shop` (`id_staticblock`, `id_shop`) VALUES
			(3, 1),
			(3, 2),
			(3, 3),
			(4, 1),
			(4, 2),
			(4, 3),
			(5, 1),
			(5, 2),
			(5, 3),
			(6, 1),
			(6, 2),
			(6, 3),
			(7, 1),
			(7, 2),
			(7, 3),
			(9, 1),
			(9, 2),
			(9, 3)";
        
		if ($res){
              $res &=  Db::getInstance()->Execute($sql);
              $res &=  Db::getInstance()->Execute($sql1);
              $res &=  Db::getInstance()->Execute($sql2);
        }
        return (bool)$res;
    }
	
private function uninstallDb() {
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'lab_staticfooter`');
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'lab_staticfooter_lang`');
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'lab_staticfooter_shop`');
    return true;
}

/*  */
      
    public function hookFooter($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayFooter');
      //  echo "<pre>"
        if(count($staticBlocks)<1) return null;
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
    
     public function hookDisplayBackOfficeHeader($params) {
	if (method_exists($this->context->controller, 'addJquery'))
	 {        
	  $this->context->controller->addJquery();
	  $this->context->controller->addJS(($this->_path).'js/staticblock.js');
	 }
    }	
    /* define some hook customer */
	public function hookBlockFooter1($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'blockFooter1');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
	public function hookBlockFooter2($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'blockFooter2');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
	public function hookBlockFooter3($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'blockFooter3');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
    public function hookBlockFooter4($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'blockFooter4');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    public function getModulById($id_module) {
        return Db::getInstance()->getRow('
            SELECT m.*
            FROM `' . _DB_PREFIX_ . 'module` m
            JOIN `' . _DB_PREFIX_ . 'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = ' . (int) ($this->context->shop->id) . ')
            WHERE m.`id_module` = ' . $id_module);
    }

    public function getHooksByModuleId($id_module) {
        $module = self::getModulById($id_module);
        $moduleInstance = Module::getInstanceByName($module['name']);
        $hooks = array();
        if ($this->hookAssign)
            foreach ($this->hookAssign as $hook) {
                if (_PS_VERSION_ < "1.5") {
                    if (is_callable(array($moduleInstance, 'hook' . $hook))) {
                        $hooks[] = $hook;
                    }
                } else {
                    $retro_hook_name = Hook::getRetroHookName($hook);
                    if (is_callable(array($moduleInstance, 'hook' . $hook)) || is_callable(array($moduleInstance, 'hook' . $retro_hook_name))) {
                        $hooks[] = $retro_hook_name;
                    }
                }
            }
        $results = self::getHookByArrName($hooks);
        return $results;
    }

    public static function getHookByArrName($arrName) {
        $result = Db::getInstance()->ExecuteS('
		SELECT `id_hook`, `name`
		FROM `' . _DB_PREFIX_ . 'hook` 
		WHERE `name` IN (\'' . implode("','", $arrName) . '\')');
        return $result;
    }
  //$hooks = $this->getHooksByModuleId(10);
    public function getListModuleInstalled() {
        $mod = new labcustomfooter();
        $modules = $mod->getModulesInstalled(0);
        $arrayModule = array();
        foreach($modules as $key => $module) {
            if($module['active']==1) {
                $arrayModule[0] = array('id_module'=>0, 'name'=>'Chose Module');
                $arrayModule[$key] = $module;
            }
        }
        if ($arrayModule)
            return $arrayModule;
        return array();
    }
	
	private function _installHookCustomer(){
		$hookspos = array(
				'blockFooter1',
				'blockFooter2',
				'blockFooter3',
				'blockFooter4',
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