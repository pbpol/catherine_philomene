<?php

class labscroll extends Module {
	
	public function __construct() {
		$this->name 		= 'labscroll';
		$this->tab 			= 'front_office_features';
		$this->version 		= '1.1';
		$this->author 		= 'labersthemes';
		$this->displayName 	= $this->l('Lab Scroll to top');
		$this->description 	= $this->l('Back to Top');
        
		parent :: __construct();
       
	}
	
	public function install() {
		return parent :: install()
            && $this->registerHook('labscroll')
            && $this->registerHook('header')
            ;
	}

  
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
    
    
    public function hookHeader($params){
        if ($this->psversion()==5){
            $this->context->controller->addJS(($this->_path).'scrolltop.js','all');
        } else {
            Tools::addCSS(($this->_path).'scrolltop.css');
            Tools::addJS(($this->_path).'scrolltop.js');
        }
    }
    
    
    // Hook footer
	public function hooklabscroll($params) {
		return $this->display(__FILE__, 'labscroll.tpl');
	}        
	
}