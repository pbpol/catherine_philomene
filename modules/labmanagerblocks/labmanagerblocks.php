<?php

// Security
if (!defined('_PS_VERSION_'))
    exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');

// Loading Models
require_once(_PS_MODULE_DIR_ . 'labmanagerblocks/models/Managerblock.php');

class Labmanagerblocks extends Module {
    public  $hookAssign   = array();
    public $_staticModel =  "";
    public function __construct() {
        $this->name = 'labmanagerblocks';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'Labersthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->hookAssign = array('rightcolumn','leftcolumn','home','top','footer','extraLeft');
        $this->_staticModel = new ManagerBlock();
        parent::__construct();
        $this->displayName = $this->l('Lab Static Block');
        $this->description = $this->l('Manager Content Blocks');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->admin_tpl_path = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/';
    }

    public function install() {

        // Install SQL
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
            $tab->name[$language['id_lang']] = $this->l('Manage Static blocks');
        $tab->class_name = 'AdminLabManagerBlocks';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminLabMenu'); 
        $tab->module = $this->name;
        $tab->add();
        // Set some defaults
        return parent::install() &&
                $this->registerHook('top') &&
                $this->registerHook('blockPosition1') &&
		$this->registerHook('blockPosition2') &&
		$this->registerHook('blockPosition3') &&
		$this->registerHook('blockPosition4') &&
		$this->registerHook('blockPosition5') &&
		$this->registerHook('blockPosition6') &&
		$this->registerHook('blockPosition7') &&
		$this->registerHook('bannerSlide') &&
		$this->registerHook('labhomefeatured') &&
		$this->registerHook('labtabproductslider') &&
		$this->registerHook('labmanufacturer') &&
                $this->registerHook('leftColumn') &&
                $this->registerHook('rightColumn') &&
                $this->registerHook('home') &&
                $this->registerHook('footer') &&
                $this->registerHook('displayHeader')&&
                $this->registerHook('displayBackOfficeHeader');
				
		return (bool)$res;
    }
	
    public function uninstall() {

        Configuration::deleteByName('labmanagerblocks');
		$res = $this->uninstallDb();
	
        $tab = new Tab((int) Tab::getIdFromClassName('Adminlabmanagerblocks'));
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
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lab_managerblock` (
			  `id_labmanagerblock` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `identify` varchar(128) NOT NULL,
			  `hook_position` varchar(128) NOT NULL,
			  `name_module` varchar(128) NOT NULL,
			  `hook_module` varchar(128) NOT NULL,
			  `position` int(10) unsigned NOT NULL,
			  `insert_module` int(10) unsigned NOT NULL,
			  `active` int(10) unsigned NOT NULL,
			  `showhook` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id_labmanagerblock`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 '
        );
        if ($res)
            $res &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lab_managerblock_lang` (
			  `id_labmanagerblock` int(11) unsigned NOT NULL,
			  `id_lang` int(11) unsigned NOT NULL,
			  `title` varchar(128) NOT NULL,
			  `description` longtext,
			  PRIMARY KEY (`id_labmanagerblock`,`id_lang`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8');
		if ($res)
            $res &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lab_managerblock_shop` (
				  `id_labmanagerblock` int(11) unsigned NOT NULL,
				  `id_shop` int(11) unsigned NOT NULL,
				  PRIMARY KEY (`id_labmanagerblock`,`id_shop`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8');
		
		$sql =  "INSERT INTO `"._DB_PREFIX_."lab_managerblock` (`id_labmanagerblock`, `identify`, `hook_position`, `name_module`, `hook_module`, `position`, `insert_module`, `active`, `showhook`) VALUES
			('11','banner-static','blockPosition1','Chose Module','top','0','0','0','1'),
			('12','banner-static','blockPosition3','Chose Module','top','0','0','0','1'),
			('13','lab_menu_idcat_3','top','Chose Module','top','0','0','0','0'),
			('14','lab_menu_idcat_5_right','top','Chose Module','top','0','0','0','0'),
			('15','banner-static','rightColumn','Chose Module','top','0','0','0','1'),
			('16','banner-static','leftColumn','Chose Module','top','0','0','0','1'),
			('17','text-static','labhomefeatured','Chose Module','top','0','0','0','1'),
			('18','text-static','labtabproductslider','Chose Module','top','0','0','0','1'),
			('19','logo-static','labmanufacturer','Chose Module','top','0','0','0','1')";

        $sql1 = "INSERT INTO `"._DB_PREFIX_."lab_managerblock_lang` (`id_labmanagerblock`, `id_lang`, `title`, `description`) VALUES
				(11, 1, 'banner static blockPosition1', '<div class=\"lab-static\">\r\n<div class=\"row\">\r\n<div class=\"col-xs-12 col-sm-4 col-md-4 no-left-gutter\">\r\n<div class=\"row\">\r\n<div class=\"col-xs-12 col-sm-12 col-md-12 banner-padding-bottom banner-arrow\">\r\n<div class=\"banner-top banner1 border-arrow-right\">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><a class=\"overlay\" href=\"#\"> </a> <img class=\"img-responsive\" src=\"/noraure/img/cms/banner1.jpg\" alt=\"\" /></div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-xs-12 col-sm-12 col-md-12\">\r\n<div class=\"banner-top banner2\">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content banner-home-text\">\r\n<div class=\"text-banner text-banner-1\">\r\n<div class=\"banner-texthome\">\r\n<h2>the biggest</h2>\r\n<h3>winter collection</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">go to shop</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-12 col-sm-4 col-md-4 no-center-gutter \">\r\n<div class=\"row\">\r\n<div class=\"col-xs-12 col-sm-12 col-md-12 banner-padding-bottom \">\r\n<div class=\"banner-top banner3\">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content banner-home-text\">\r\n<div class=\"text-banner text-banner-1\">\r\n<div class=\"banner-texthome\">\r\n<h2>new design</h2>\r\n<h3>send her your love</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">go to now</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-xs-12 col-sm-12 col-md-12 banner-arrow \">\r\n<div class=\"banner-top banner3 border-arrow-left\">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><a class=\"overlay\" href=\"#\"> </a> <img class=\"img-responsive\" src=\"/noraure/img/cms/banner2.jpg\" alt=\"\" /></div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-12 col-sm-4 col-md-4 no-right-gutter \">\r\n<div class=\"banner-top banner1\">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><a class=\"overlay\" href=\"#\"> </a> <img class=\"img-responsive\" src=\"/noraure/img/cms/banner3.jpg\" alt=\"\" />\r\n<div class=\"text-banner text-banner-1 banner-position banner-home-text\">\r\n<div class=\"banner-texthome\">\r\n<h2>men’s fashion</h2>\r\n<h3>mid season sale</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">view collection</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (11, 2, 'banner static blockPosition1', '<div class=\"lab-static\">\n<div class=\"row\">\n<div class=\"col-xs-12 col-sm-4 col-md-4 no-left-gutter\">\n<div class=\"row\">\n<div class=\"col-xs-12 col-sm-12 col-md-12 banner-padding-bottom banner-arrow\">\n<div class=\"banner-top banner1 border-arrow-right\">\n<div class=\"banner-inner\">\n<div class=\"banner-inner-content\"><img class=\"img-responsive\" src=\"/noraure/img/cms/banner1.jpg\" alt=\"\" /></div>\n</div>\n</div>\n</div>\n</div>\n<div class=\"row\">\n<div class=\"col-xs-12 col-sm-12 col-md-12\">\n<div class=\"banner-top banner2\">\n<div class=\"banner-inner\">\n<div class=\"banner-inner-content banner-home-text\">\n<div class=\"text-banner text-banner-1\">\n<div class=\"banner-texthome\">\n<h2>the biggest</h2>\n<h3>winter collection</h3>\n<h4><a class=\"btn-button\" href=\"#\">go to shop</a></h4>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<div class=\"col-xs-12 col-sm-4 col-md-4 no-center-gutter \">\n<div class=\"row\">\n<div class=\"col-xs-12 col-sm-12 col-md-12 banner-padding-bottom \">\n<div class=\"banner-top banner3\">\n<div class=\"banner-inner\">\n<div class=\"banner-inner-content banner-home-text\">\n<div class=\"text-banner text-banner-1\">\n<div class=\"banner-texthome\">\n<h2>new design</h2>\n<h3>send her your love</h3>\n<h4><a class=\"btn-button\" href=\"#\">go to now</a></h4>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<div class=\"row\">\n<div class=\"col-xs-12 col-sm-12 col-md-12 banner-arrow \">\n<div class=\"banner-top banner3 border-arrow-left\">\n<div class=\"banner-inner\">\n<div class=\"banner-inner-content\"><img class=\"img-responsive\" src=\"/noraure/img/cms/banner2.jpg\" alt=\"\" /></div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<div class=\"col-xs-12 col-sm-4 col-md-4 no-right-gutter \">\n<div class=\"banner-top banner1\">\n<div class=\"banner-inner\">\n<div class=\"banner-inner-content\"><img class=\"img-responsive\" src=\"/noraure/img/cms/banner3.jpg\" alt=\"\" />\n<div class=\"text-banner text-banner-1 banner-position banner-home-text\">\n<div class=\"banner-texthome\">\n<h2>men’s fashion</h2>\n<h3>mid season sale</h3>\n<h4><a class=\"btn-button\" href=\"#\">view collection</a></h4>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>'),
                (12, 1, 'banner static blockPosition3', '<div class=\"labpolicy\">\r\n<div class=\"row\">\r\n<div class=\"labpolicy-i col-lg-4  col-xs-12 wow fadeInUp \" data-wow-delay=\"200ms\">\r\n<div class=\"icon\">\r\n<p><em class=\"icon-truck\">  </em></p>\r\n</div>\r\n<div class=\"container-i\">\r\n<h4>Free shipping ON all order</h4>\r\n<p>Donec at mattis purus, ut accumsan nisl. Lorem ipsum dolor sit amet</p>\r\n</div>\r\n</div>\r\n<div class=\"labpolicy-i  col-lg-4  col-xs-12 wow fadeInUp \" data-wow-delay=\"300ms\">\r\n<div class=\"icon\">\r\n<p><em class=\"icon-share\"> </em></p>\r\n</div>\r\n<div class=\"container-i\">\r\n<h4>100% MONEY BACK GUARANTEE</h4>\r\n<p>Donec at mattis purus, ut accumsan nisl. Lorem ipsum dolor sit amet</p>\r\n</div>\r\n</div>\r\n<div class=\"labpolicy-i  col-lg-4  col-xs-12 wow fadeInUp \" data-wow-delay=\"400ms\">\r\n<div class=\"icon\">\r\n<p><em class=\"icon-comments\"> </em></p>\r\n</div>\r\n<div class=\"container-i\">\r\n<h4>ONLINE SUPPORT 24/24</h4>\r\n<p>Donec at mattis purus, ut accumsan nisl. Lorem ipsum dolor sit amet</p>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (12, 2, 'banner static blockPosition3', '<div class=\"labpolicy\">\r\n<div class=\"row\">\r\n<div class=\"labpolicy-i col-lg-4  col-xs-12 wow fadeInUp \" data-wow-delay=\"200ms\">\r\n<div class=\"icon\">\r\n<p><em class=\"icon-truck\">  </em></p>\r\n</div>\r\n<div class=\"container-i\">\r\n<h4>Free shipping ON all order</h4>\r\n<p>Donec at mattis purus, ut accumsan nisl. Lorem ipsum dolor sit amet</p>\r\n</div>\r\n</div>\r\n<div class=\"labpolicy-i  col-lg-4  col-xs-12 wow fadeInUp \" data-wow-delay=\"300ms\">\r\n<div class=\"icon\">\r\n<p><em class=\"icon-share\">   </em></p>\r\n</div>\r\n<div class=\"container-i\">\r\n<h4>100% MONEY BACK GUARANTEE</h4>\r\n<p>Donec at mattis purus, ut accumsan nisl. Lorem ipsum dolor sit amet</p>\r\n</div>\r\n</div>\r\n<div class=\"labpolicy-i  col-lg-4  col-xs-12 wow fadeInUp \" data-wow-delay=\"400ms\">\r\n<div class=\"icon\">\r\n<p><em class=\"icon-comments\">   </em></p>\r\n</div>\r\n<div class=\"container-i\">\r\n<h4>ONLINE SUPPORT 24/24</h4>\r\n<p>Donec at mattis purus, ut accumsan nisl. Lorem ipsum dolor sit amet</p>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (13, 1, 'lab custum menu', '<div class=\"lab_customhtml\">\r\n<div class=\"img\"><a title=\"\" href=\"#\"><img src=\"/noraure/img/cms/img_menu_1.jpg\" alt=\"\" width=\"540\" height=\"127\" /></a></div>\r\n<div class=\"img\"><a title=\"\" href=\"#\"><img src=\"/noraure/img/cms/img_menu_2.jpg\" alt=\"\" width=\"540\" height=\"127\" /></a></div>\r\n</div>'),
                (13, 2, 'lab custum menu', '<div class=\"lab_customhtml\">\r\n<div class=\"img\"><a title=\"\" href=\"#\"><img src=\"/noraure/img/cms/img_menu_1.jpg\" alt=\"\" width=\"540\" height=\"127\" /></a></div>\r\n<div class=\"img\"><a title=\"\" href=\"#\"><img src=\"/noraure/img/cms/img_menu_2.jpg\" alt=\"\" width=\"540\" height=\"127\" /></a></div>\r\n</div>'),
                (14, 1, 'banner menu right', '<div class=\"lab_customhtml\">\r\n<div class=\"img\"><a title=\"\" href=\"#\"><img src=\"/noraure/img/cms/banermenu-righ.jpg\" alt=\"\" width=\"540\" height=\"163\" /></a></div>\r\n</div>'),
                (14, 2, 'banner menu right', '<div class=\"lab_customhtml\">\r\n<div class=\"img\"><a title=\"\" href=\"#\"><img src=\"/lab_bozon/img/cms/img_menu_3.jpg\" alt=\"\" /></a></div>\r\n</div>'),
                (15, 1, 'banner right', '<div class=\"banner-sibar\">\r\n<div class=\"banner-top \">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><img class=\"img-responsive\" src=\"/noraure/img/cms/banner3.jpg\" alt=\"\" />\r\n<div class=\"text-banner text-banner-1 banner-position banner-home-text\">\r\n<div class=\"banner-texthome\">\r\n<h2>Men’s fashion</h2>\r\n<h3>mid season sale</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">Shop now</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (15, 2, 'banner right', '<div class=\"banner-sibar\">\r\n<div class=\"banner-top \">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><img class=\"img-responsive\" src=\"/noraure/img/cms/banner3.jpg\" alt=\"\" />\r\n<div class=\"text-banner text-banner-1 banner-position banner-home-text\">\r\n<div class=\"banner-texthome\">\r\n<h2>Men’s fashion</h2>\r\n<h3>mid season sale</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">Shop now</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (16, 1, 'banner static left', '<div class=\"banner-sibar\">\r\n<div class=\"banner-top \">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><a class=\"overlay\" href=\"#\"> </a> <img class=\"img-responsive\" src=\"/lab_noraure/img/cms/banner-sibar.jpg\" alt=\"\" />\r\n<div class=\"text-banner text-banner-1 banner-position banner-home-text\">\r\n<div class=\"banner-texthome\">\r\n<h2>Women’s fashion</h2>\r\n<h3>mid season sale</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">Shop now</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (16, 2, 'banner static left', '<div class=\"banner-sibar\">\r\n<div class=\"banner-top \">\r\n<div class=\"banner-inner\">\r\n<div class=\"banner-inner-content\"><img class=\"img-responsive\" src=\"/lab_noraure/img/cms/banner-sibar.jpg\" alt=\"\" />\r\n<div class=\"text-banner text-banner-1 banner-position banner-home-text\">\r\n<div class=\"banner-texthome\">\r\n<h2>Women’s fashion</h2>\r\n<h3>mid season sale</h3>\r\n<h4><a class=\"btn-button\" href=\"#\">Shop now</a></h4>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'),
                (17, 1, 'static block home Featured ', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>'),
                (17, 2, 'static block home Featured ', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>'),
                (18, 1, 'static block tab product', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>'),
                (18, 2, 'static block tab product', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>'),
                (19, 1, 'logo manufacturer', '<p><img src=\"/noraure/img/cms/logomanu.png\" alt=\"\" /></p>'),
                (19, 2, 'logo manufacturer', '<p><img src=\"/noraure/img/cms/logomanu.png\" alt=\"\" /></p>')";

        $sql2 = "INSERT INTO `"._DB_PREFIX_."lab_managerblock_shop` (`id_labmanagerblock`, `id_shop`) VALUES
			(11, 1),
			(12, 1),
			(13, 1),
			(14, 1),
			(15, 1),
			(16, 1),
			(17, 1),
			(18, 1),
			(19, 1)
			";
        
		if ($res){
              $res &=  Db::getInstance()->Execute($sql);
              $res &=  Db::getInstance()->Execute($sql1);
              $res &=  Db::getInstance()->Execute($sql2);
        }
        return (bool)$res;
}

private function uninstallDb() {
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'lab_managerblock`');
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'lab_managerblock_lang`');
    Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'lab_managerblock_shop`');
    return true;
}

/*  */
    
    public function hookTop($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'top');
        if(count($staticBlocks)<1) return null;
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
    public function hookLeftColumn($param) {
       $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'leftColumn');
        if(count($staticBlocks)<1) return null;
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
     public function hookRightColumn($param) { 
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'rightColumn');
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
    public function hookFooter($param) { 
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'footer');
        if(count($staticBlocks)<1) return null;
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
    public function hookHome($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'home');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
    public function hookBlockPosition1($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'blockPosition1');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
    public function hookBlockPosition2($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'blockPosition2');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
    public function hookBlockPosition3($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'blockPosition3');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
	public function hookBlockPosition4($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'blockPosition4');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    public function hookbannerSlide($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'bannerSlide');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
	public function hooklabhomefeatured($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'labhomefeatured');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
	public function hooklabtabproductslider($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'labtabproductslider');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
	public function hooklabmanufacturer($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticblockLists($id_shop,'labmanufacturer');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block.tpl');
    }
    
     public function hookDisplayBackOfficeHeader($params) {
		if (method_exists($this->context->controller, 'addJquery'))
		{        
		$this->context->controller->addJquery();
		$this->context->controller->addJS(($this->_path).'js/staticblock.js');
		}
		return $this->display(__FILE__, 'views/templates/admin/fortawesome.tpl');
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
        $mod = new labmanagerblocks();
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
				'blockPosition1',
				'blockPosition2',
				'blockPosition3',
				'blockPosition4',
				'bannerSlide',
				'labtabproductslider',
				'labmanufacturer',
				'labhomefeatured'
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