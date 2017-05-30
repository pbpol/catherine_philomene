<?php
class labhoverflipimg extends Module {
	public function __construct() {
		$this->name 		= 'labhoverflipimg';
		$this->tab 			= 'front_office_features';
		$this->version 		= '1.1';
		$this->author 		= 'labersthemes';
		$this->displayName 	= $this->l('Hover flip img');
		$this->description 	= $this->l('Hover flip img');
		parent :: __construct();
	}
	
	public function install() {
	   // Install SQL
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		return parent :: install()
			&& $this->registerHook('rotatorImg')
            && $this->registerHook('header')
            ;
	}
	
	public function uninstall(){
		include(dirname(__FILE__).'/sql/uninstall_sql.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		return parent::uninstall();
	}

	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
	
	public function hookRotatorImg($params) {
			$idproduct = $params['product']['id_product'];
			$id_shop = (int)Context::getContext()->shop->id;
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` img'; 
			$sql .= ' LEFT JOIN `'. _DB_PREFIX_ . 'image_shop` imgs';
			$sql .= ' ON img.id_image = imgs.id_image';
			$sql .= ' where imgs.`id_shop` ='.$id_shop ;
			$sql .= ' AND img.`id_product` ='.$idproduct ;
			$sql .= ' AND imgs.`rotator` =1' ;
			//echo $sql;
			$imageNew = Db::getInstance()->ExecuteS($sql);
			if(!$imageNew) {
				  $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` img'; 
				  $sql .= ' where img.`rotator` =1';
				  $sql .= ' AND img.`id_product` ='.$idproduct ;
				  $imageNew = Db::getInstance()->ExecuteS($sql);
			}

			$this->smarty->assign(
				array('rotator_img'=>$imageNew,
				'idproduct'=>$idproduct,
				'product'=>$params['product'],
				));

		return $this->display(__FILE__, 'labhoverflipimg.tpl');
	}    
	
	public function hookdisplayHeader($params)
	{
		$this->context->controller->addCSS($this->_path.'css/labhoverflipimg.css', 'all');
	}
	
}