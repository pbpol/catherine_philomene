<?php

if (!defined('_PS_VERSION_'))
    exit;
if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');
class labthemeoptions extends Module
{
    var $prefix = '';
    var $amounts = 4;
    var $base_config_url = '';
    var $overrideHooks = array();
    public function __construct()
    {

        global $currentIndex;
        $this->name = 'labthemeoptions';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->bootstrap = true ;
        $this->author = 'labersthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->currentIndex = $currentIndex;
        parent::__construct();
        $this->displayName = $this->l('Lab Theme Option');
        $this->description = $this->l('Manage theme skins');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->base_config_url = $this->currentIndex . '&configure=' . $this->name . '&token=' . Tools::getValue('token');
        if (!Configuration::get('THEMEOPTIONS'))
            $this->warning = $this->l('No name provided');
    }

   public function install()
    {
		if(!(int)Tab::getIdFromClassName('AdminLabMenu')) {
			$parent_tab = new Tab();
			// Need a foreach for the language
			$parent_tab->name[$this->context->language->id] = $this->l('LabExtentions');
			$parent_tab->class_name = 'AdminLabMenu';
			$parent_tab->id_parent = 0; // Home tab
			$parent_tab->module = $this->name;
			$parent_tab->add();
		}
        $tab = new Tab();
        // Need a foreach for the language
		foreach (Language::getLanguages() as $language)
        $tab->name[$language['id_lang']] = $this->l('Lab Themeoptions');
        $tab->class_name = 'Adminlabthemeoptions';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminLabMenu'); 
        $tab->module = $this->name;
        $tab->add();
        if (parent::install() && $this->registerHook('labthemeoption') && $this->registerHook('displayHeader') && $this->registerHook('displayTop')) {
            $res = Configuration::updateValue('lab_hbgnav','');
            $res &= Configuration::updateValue('lab_htextnav','');
            $res &= Configuration::updateValue('lab_maincolor','');
            $res &= Configuration::updateValue('lab_hbgheadercolor','');
            $res &= Configuration::updateValue('lab_htextheader','');
            $res &= Configuration::updateValue('lab_hhovertext','');
            $res &= Configuration::updateValue('lab_hhovertextnav','');

            $res &= Configuration::updateValue('lab_mbgcolor','');
            $res &= Configuration::updateValue('lab_mbghover','');
            $res &= Configuration::updateValue('lab_mtext','');
            $res &= Configuration::updateValue('lab_mtexthover','');
            $res &= Configuration::updateValue('lab_mbgsubmenu','');
            $res &= Configuration::updateValue('lab_mtextsub','');
            $res &= Configuration::updateValue('lab_mhovertextsub','');

            $res &= Configuration::updateValue('lab_cbgcolor','');
            $res &= Configuration::updateValue('lab_ccolorlink','');
            $res &= Configuration::updateValue('lab_chovercolorlink','');
            $res &= Configuration::updateValue('lab_ccolortext','');
            $res &= Configuration::updateValue('lab_ccolorprice','');
            $res &= Configuration::updateValue('lab_ccoloroldprice','');
            $res &= Configuration::updateValue('lab_ciconcolor','');
            $res &= Configuration::updateValue('lab_cbgbuttom','');
            $res &= Configuration::updateValue('lab_ctextbuttom','');
            $res &= Configuration::updateValue('lab_cbgbuttomhover','');
            $res &= Configuration::updateValue('lab_ctextbuttomhover','');

            $res &= Configuration::updateValue('lab_fbgcolor','');
            $res &= Configuration::updateValue('lab_fcolortext','');
            $res &= Configuration::updateValue('lab_flinkcolor','');
            $res &= Configuration::updateValue('lab_flinkcolorhover','');
            $res &= Configuration::updateValue('labthemecolor','');
            $res &= Configuration::updateValue('labshowthemecolor','');
            $res &= Configuration::updateValue('labskin','');
            $res &= Configuration::updateValue('show_color','0');
            $res &= Configuration::updateValue('show_fontend','0');
            $res &= Configuration::updateValue('show_labskin',0);
            $res &= Configuration::updateValue('lab_mode_theme','box');
        return (bool)$res;
        }
    }

    public function uninstall()
    {
        Configuration::deleteByName('THEMEOPTIONS');
        $tab = new Tab((int)Tab::getIdFromClassName('Adminlabthemeoptions'));
        $tab->delete();
        // Uninstall Module
        if (!parent::uninstall()||
            !Configuration::deleteByName('labbgcolor')||!Configuration::deleteByName('lab_mode_theme')|| !Configuration::deleteByName('lablinkcolor')||!Configuration::deleteByName('labmaincolor')||!Configuration::deleteByName('labpntool')
        )
            return false;
        return true;
    }

    function getContent()
    {
        $this->context->controller->addCSS(_PS_BASE_URL_ . __PS_BASE_URI__ . "modules/" . $this->name . "/views/templates/front/colortool/css/lab.cltool.css");
        $errors = array();
        $this->_html = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitUpdate')) {

            Configuration::updateValue('lab_maincolor',Tools::getValue('lab_maincolor'));
            Configuration::updateValue('lab_cbgcolor',Tools::getValue('lab_cbgcolor'));
            Configuration::updateValue('lab_ccolorlink',Tools::getValue('lab_ccolorlink'));
            Configuration::updateValue('lab_chovercolorlink',Tools::getValue('lab_chovercolorlink'));
            Configuration::updateValue('lab_ccolortext',Tools::getValue('lab_ccolortext'));
            Configuration::updateValue('lab_ccolorprice',Tools::getValue('lab_ccolorprice'));
            Configuration::updateValue('lab_ccoloroldprice',Tools::getValue('lab_ccoloroldprice'));
            Configuration::updateValue('lab_cbgbuttom',Tools::getValue('lab_cbgbuttom'));
            Configuration::updateValue('lab_fbgcolor',Tools::getValue('lab_fbgcolor'));
            Configuration::updateValue('lab_fcolortext',Tools::getValue('lab_fcolortext'));
            Configuration::updateValue('lab_flinkcolor',Tools::getValue('lab_flinkcolor'));
            Configuration::updateValue('show_color',Tools::getValue('show_color'));
            Configuration::updateValue('show_fontend',Tools::getValue('show_fontend'));
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }
        if(Tools::isSubmit('submitUpdateheader')){
            Configuration::updateValue('lab_hbgnav',Tools::getValue('lab_hbgnav'));
            Configuration::updateValue('lab_htextnav',Tools::getValue('lab_htextnav'));
            Configuration::updateValue('lab_hbgheadercolor',Tools::getValue('lab_hbgheadercolor'));
            Configuration::updateValue('lab_htextheader',Tools::getValue('lab_htextheader'));
            Configuration::updateValue('lab_hhovertext',Tools::getValue('lab_hhovertext'));
            Configuration::updateValue('lab_hhovertextnav',Tools::getValue('lab_hhovertextnav'));
        }
        if(Tools::isSubmit('submitUpdateMegamenu')){
            Configuration::updateValue('lab_mbghover',Tools::getValue('lab_mbghover'));
            Configuration::updateValue('lab_mbgcolor',Tools::getValue('lab_mbgcolor'));
            Configuration::updateValue('lab_mtext',Tools::getValue('lab_mtext'));
            Configuration::updateValue('lab_mtexthover',Tools::getValue('lab_mtexthover'));
            Configuration::updateValue('lab_mbgsubmenu',Tools::getValue('lab_mbgsubmenu'));
            Configuration::updateValue('lab_mtextsub',Tools::getValue('lab_mtextsub'));
            Configuration::updateValue('lab_mhovertextsub',Tools::getValue('lab_mhovertextsub'));
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }
        if(Tools::isSubmit('submitUpdateMaincontent')){
             Configuration::updateValue('lab_cbgcolor',Tools::getValue('lab_cbgcolor'));
             Configuration::updateValue('lab_ccolorlink',Tools::getValue('lab_ccolorlink'));
             Configuration::updateValue('lab_chovercolorlink',Tools::getValue('lab_chovercolorlink'));
             Configuration::updateValue('lab_ccolortext',Tools::getValue('lab_ccolortext'));
             Configuration::updateValue('lab_ccolorprice',Tools::getValue('lab_ccolorprice'));
             Configuration::updateValue('lab_ccoloroldprice',Tools::getValue('lab_ccoloroldprice'));
             Configuration::updateValue('lab_ciconcolor',Tools::getValue('lab_ciconcolor'));
             Configuration::updateValue('lab_cbgbuttom',Tools::getValue('lab_cbgbuttom'));
             Configuration::updateValue('lab_ctextbuttom',Tools::getValue('lab_ctextbuttom'));
             Configuration::updateValue('lab_cbgbuttomhover',Tools::getValue('lab_cbgbuttomhover'));
             Configuration::updateValue('lab_ctextbuttomhover',Tools::getValue('lab_ctextbuttomhover'));
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));

        }
        if(Tools::isSubmit('submitUpdateFooter')){
            Configuration::updateValue('lab_fbgcolor',Tools::getValue('lab_fbgcolor'));
            Configuration::updateValue('lab_fcolortext',Tools::getValue('lab_fcolortext'));
            Configuration::updateValue('lab_flinkcolor',Tools::getValue('lab_flinkcolor'));
            Configuration::updateValue('lab_flinkcolorhover',Tools::getValue('lab_flinkcolorhover'));
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }
        if(Tools::isSubmit('submitUpdateThemeskin')){
            if(Tools::getValue('labskin')!=''){
                Configuration::updateValue('labskin',Tools::getValue('labskin'));
            }
            if (Tools::getValue('labthemecolor')!=''){
                Configuration::updateValue('labthemecolor',Tools::getValue('labthemecolor'));
            }
            Configuration::updateValue('lab_mode_theme',Tools::getValue('lab_mode_theme'));
            Configuration::updateValue('show_labskin',Tools::getValue('show_labskin'));
            Configuration::updateValue('labshowthemecolor',Tools::getValue('labshowthemecolor'));
            Configuration::updateValue('show_color',Tools::getValue('show_color'));
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }
        if (sizeof($errors)) {
            foreach ($errors AS $err) {
                $this->_html .= '<div class="alert error">' . $err . '</div>';
            }
        }
        $this->_html .= $this->renderForm();
        return $this->_html;
    }


    public  function renderForm(){
        $this->context->controller->addJqueryPlugin('colorpicker');
        $action = 'index.php?controller=AdminModules&configure='.$this->name.'&tab_module=front_office_features&module_name='.$this->name.'&token='.Tools::getValue('token').' ';
		$this->context->controller->addCSS(__PS_BASE_URI__ . "modules/" . $this->name . "/views/templates/admin/style.css");
        $lab_hbgnav = Tools::getValue('lab_hbgnav',Configuration::get('lab_hbgnav'));
        $lab_htextnav = Tools::getValue('lab_htextnav',Configuration::get('lab_htextnav'));
        $lab_hbgheadercolor = Tools::getValue('lab_hbgheadercolor', Configuration::get('lab_hbgheadercolor'));
        $lab_htextheader =  Tools::getValue('lab_htextheader',Configuration::get('lab_htextheader'));


        $lab_hhovertext = Tools::getValue('lab_hhovertext',Configuration::get('lab_hhovertext'));
        $lab_hhovertextnav = Tools::getValue('lab_hhovertextnav',Configuration::get('lab_hhovertextnav'));
        //megamenu
        $lab_mbghover = Tools::getValue('lab_mbghover',Configuration::get('lab_mbghover'));
        $lab_mbgcolor = Tools::getValue('lab_mbgcolor',Configuration::get('lab_mbgcolor'));
        $lab_mtext = Tools::getValue('lab_mtext',Configuration::get('lab_mtext'));
        $lab_mtexthover = Tools::getValue('lab_mtexthover',Configuration::get('lab_mtexthover'));
        $lab_mbgsubmenu = Tools::getValue('lab_mbgsubmenu',Configuration::get('lab_mbgsubmenu'));
        $lab_mtextsub = Tools::getValue('lab_mtextsub',Configuration::get('lab_mtextsub'));
        $lab_mhovertextsub = Tools::getValue('lab_mhovertextsub',Configuration::get('lab_mhovertextsub'));
        //maincontetn
        $lab_cbgcolor = Tools::getValue('lab_cbgcolor', Configuration::get('lab_cbgcolor'));
        $lab_ccolorlink = Tools::getValue('lab_ccolorlink',Configuration::get('lab_ccolorlink'));
        $lab_chovercolorlink = Tools::getValue('lab_chovercolorlink',Configuration::get('lab_chovercolorlink'));
        $lab_ccolortext = Tools::getValue('lab_ccolortext',Configuration::get('lab_ccolortext'));
        $lab_ccolorprice = Tools::getValue('lab_ccolorprice',Configuration::get('lab_ccolorprice'));
        $lab_ccoloroldprice = Tools::getValue('lab_ccoloroldprice',Configuration::get('lab_ccoloroldprice'));
        $lab_ciconcolor = Tools::getValue('lab_ciconcolor',Configuration::get('lab_ciconcolor'));
        $lab_cbgbuttom = Tools::getValue('lab_cbgbuttom',Configuration::get('lab_cbgbuttom'));
        $lab_ctextbuttom = Tools::getValue('lab_ctextbuttom',Configuration::get('lab_ctextbuttom'));
        $lab_ctextbuttomhover = Tools::getValue('lab_ctextbuttomhover',Configuration::get('lab_ctextbuttomhover'));
        $lab_cbgbuttomhover = Tools::getValue('lab_cbgbuttomhover',Configuration::get('lab_cbgbuttomhover'));
        // footer
        $labskin = Tools::getValue('labskin',Configuration::get('labskin'));
        $lab_mode_theme = Tools::getValue('lab_mode_theme',Configuration::get('lab_mode_theme'));
        $labshowskin = Tools::getValue('show_labskin',Configuration::get('show_labskin'));
        $show_color = Tools::getValue('show_color',Configuration::get('show_color'));
        $lab_showthemecolor = Tools::getValue('labshowthemecolor',Configuration::get('labshowthemecolor'));
        $labthemecolor = Tools::getValue('labthemecolor',Configuration::get('labthemecolor'));
        $lab_fbgcolor = Tools::getValue('lab_fbgcolor',Configuration::get('lab_fbgcolor'));
        $lab_fcolortext = Tools::getValue('lab_fcolortext',Configuration::get('lab_fcolortext'));
        $lab_flinkcolor = Tools::getValue('lab_flinkcolor',Configuration::get('lab_flinkcolor'));
        $lab_flinkcolorhover = Tools::getValue('lab_flinkcolorhover',Configuration::get('lab_flinkcolorhover'));
        $this->smarty->assign(array(
                 'lab_hbgnav' =>$lab_hbgnav, // header
                 'lab_htextnav' =>$lab_htextnav,
                 'lab_hbgheadercolor' =>$lab_hbgheadercolor,
                 'lab_htextheader' =>$lab_htextheader,
                 'lab_hhovertext' =>$lab_hhovertext,
                 'lab_hhovertextnav' =>$lab_hhovertextnav,
                 'lab_mbghover' =>$lab_mbghover, //  megamenu
                 'lab_mbgcolor' =>$lab_mbgcolor,
                 'lab_mtext' =>$lab_mtext,
                 'lab_mtexthover' =>$lab_mtexthover,
                 'lab_mbgsubmenu' =>$lab_mbgsubmenu,
                 'lab_mtextsub' =>$lab_mtextsub,
                 'lab_mhovertextsub' =>$lab_mhovertextsub,
                 //maincontent
                 'lab_cbgcolor' =>$lab_cbgcolor,
                 'lab_ccolorlink' =>$lab_ccolorlink,
                 'lab_chovercolorlink' =>$lab_chovercolorlink,
                 'lab_ccolortext' =>$lab_ccolortext,
                 'lab_ccolorprice' =>$lab_ccolorprice,
                 'lab_ccoloroldprice' =>$lab_ccoloroldprice,
                 'lab_ciconcolor' =>$lab_ciconcolor,
                 'lab_cbgbuttom' =>$lab_cbgbuttom,
                 'lab_ctextbuttom' =>$lab_ctextbuttom,
                 'lab_ctextbuttomhover' =>$lab_ctextbuttomhover,
                 'lab_cbgbuttomhover' =>$lab_cbgbuttomhover,
                    //footer
                 'lab_fbgcolor' => $lab_fbgcolor,
                 'lab_fcolortext'=>$lab_fcolortext ,
                 'lab_flinkcolor'=>$lab_flinkcolor ,
                 'lab_flinkcolorhover'=>$lab_flinkcolorhover ,
                 //skin
                  'labskin' => $labskin,
                  'lab_mode_theme' => $lab_mode_theme,
                  'labthemecolor' => $labthemecolor,
                  'labshowthemecolor' => $lab_showthemecolor,
                  'labshowcolor' => $show_color,
                  'labshowskin' => $labshowskin,
                 'action'=> $action,

                 'search_query' => (string)Tools::getValue('search_query')
            )
        );
		return $this->display(__FILE__, 'views/templates/admin/adminform.tpl');
    }

    function hookdisplayHeader()
    {
	   $this->context->controller->addCSS(__PS_BASE_URI__ . "modules/" . $this->name . "/views/templates/front/colortool/css/lab.cltool.css");
	   $this->context->controller->addJS($this->_path. "js/colorpicker.js");
	   $this->context->controller->addCSS($this->_path. 'views/templates/front/colortool/css/colorpicker.css' );
	   $this->context->controller->addJS($this->_path. "js/jquery.cookie.js");
	   
	   $this->context->controller->addJS($this->_path. "js/owl.carousel.js");
	   $this->context->controller->addJS($this->_path. "js/wow.min.js");
	   $this->context->controller->addJS($this->_path. "js/laberthemes.js");
	   
	   $this->context->controller->addCSS($this->_path. 'css/animate.css' );
	   $this->context->controller->addCSS($this->_path. 'css/owl.carousel.css' );
	   $this->context->controller->addCSS($this->_path. 'css/laberthemes.css' );
	   $this->display(__FILE__, 'views/templates/admin/fortawesome.tpl');
	   
		global  $smarty;
        //header
        $lab_hbgnav = Configuration::get('lab_hbgnav');
        $lab_htextnav = Configuration::get('lab_htextnav');
        $lab_hbgheadercolor = Configuration::get('lab_hbgheadercolor');
        $lab_htextheader = Configuration::get('lab_htextheader');
        $lab_hhovertext = Configuration::get('lab_hhovertext');
        $lab_hhovertextnav =Configuration::get('lab_hhovertextnav');
        //megamenu
        $lab_mbghover = Configuration::get('lab_mbghover');
        $lab_mbgcolor = Configuration::get('lab_mbgcolor');
        $lab_mtext = Configuration::get('lab_mtext');
        $lab_mtexthover = Configuration::get('lab_mtexthover');
        $lab_mbgsubmenu = Configuration::get('lab_mbgsubmenu');
        $lab_mtextsub =Configuration::get('lab_mtextsub');
        $lab_mhovertextsub =Configuration::get('lab_mhovertextsub');
        //maincontetn
        $lab_cbgcolor = Configuration::get('lab_cbgcolor');
        $lab_ccolorlink = Configuration::get('lab_ccolorlink');
        $lab_chovercolorlink = Configuration::get('lab_chovercolorlink');
        $lab_ccolortext = Configuration::get('lab_ccolortext');
        $lab_ccolorprice = Configuration::get('lab_ccolorprice');
        $lab_ccoloroldprice = Configuration::get('lab_ccoloroldprice');
        $lab_ciconcolor = Configuration::get('lab_ciconcolor');
        $lab_cbgbuttom = Configuration::get('lab_cbgbuttom');
        $lab_ctextbuttom = Configuration::get('lab_ctextbuttom');
        $lab_ctextbuttomhover = Configuration::get('lab_ctextbuttomhover');
        $lab_cbgbuttomhover = Configuration::get('lab_cbgbuttomhover');
        // footer
        $labskin = Configuration::get('labskin');
        $labshowskin = Configuration::get('show_labskin');
        $show_color = Configuration::get('show_color');
        $labshowthemecolor = Configuration::get('labshowthemecolor');
        $labthemecolor = Configuration::get('labthemecolor');
        $lab_fbgcolor = Configuration::get('lab_fbgcolor');
        $lab_fcolortext = Configuration::get('lab_fcolortext');
        $lab_flinkcolor = Configuration::get('lab_flinkcolor');
        $lab_flinkcolorhover = Configuration::get('lab_flinkcolorhover');
        $lab_mode_theme = Configuration::get('lab_mode_theme');
          $ps = array(
              'LAB_THEMENAME' => _THEME_NAME_,
              'PS_BASE_URL' => _PS_BASE_URL_,
              'PS_BASE_URI' => __PS_BASE_URI__,
              'PS_BASE_URL' => _PS_BASE_URL_,
              //start color
              'lab_hbgnav' =>$lab_hbgnav, // header
              'lab_htextnav' =>$lab_htextnav,
              'lab_hbgheadercolor' =>$lab_hbgheadercolor,
              'lab_htextheader' =>$lab_htextheader,
              'lab_hhovertext' =>$lab_hhovertext,
              'lab_hhovertextnav' =>$lab_hhovertextnav,
              'lab_mbghover' =>$lab_mbghover, //  megamenu
              'lab_mbgcolor' =>$lab_mbgcolor,
              'lab_mtext' =>$lab_mtext,
              'lab_mtexthover' =>$lab_mtexthover,
              'lab_mbgsubmenu' =>$lab_mbgsubmenu,
              'lab_mtextsub' =>$lab_mtextsub,
              'lab_mhovertextsub' =>$lab_mhovertextsub,
              //maincontent
              'lab_cbgcolor' =>$lab_cbgcolor,
              'lab_ccolorlink' =>$lab_ccolorlink,
              'lab_chovercolorlink' =>$lab_chovercolorlink,
              'lab_ccolortext' =>$lab_ccolortext,
              'lab_ccolorprice' =>$lab_ccolorprice,
              'lab_ccoloroldprice' =>$lab_ccoloroldprice,
              'lab_ciconcolor' =>$lab_ciconcolor,
              'lab_cbgbuttom' =>$lab_cbgbuttom,
              'lab_ctextbuttom' =>$lab_ctextbuttom,
              'lab_ctextbuttomhover' =>$lab_ctextbuttomhover,
              'lab_cbgbuttomhover' =>$lab_cbgbuttomhover,
              //footer
              'lab_fbgcolor' => $lab_fbgcolor,
              'lab_fcolortext'=>$lab_fcolortext ,
              'lab_flinkcolor'=>$lab_flinkcolor ,
              'lab_flinkcolorhover'=>$lab_flinkcolorhover ,
              // end color
              'lab_show_color' =>(int)$show_color, //show color
              'labshowthemecolor' => $labshowthemecolor, // color skin
              'labthemecolor' => $labthemecolor, // color skin
              'lab_mode_theme' => $lab_mode_theme, // mode theme
              'labskin' => Configuration::get('labskin'), // bachground skin
          );
        $smarty->assign($ps);
        return $this->display(__FILE__, 'labthemeoptions.tpl');
    }
	function hooklabthemeoption() {
        
    }
	function hookdisplayTop (){

        global  $smarty;
        $ps = array(
            'lab_skin' => Configuration::get('labskin'), // name skin
            'show_labskin' => Configuration::get('show_labskin'), // ennable /disable name skin show_fontend
            'this_path' => $this->_path,
            'LAB_THEMENAME' => _THEME_NAME_,
            'PS_BASE_URL' => _PS_BASE_URL_,
            'PS_BASE_URI' => __PS_BASE_URI__,
            'lab_paneltool' =>(int)Configuration::get('show_fontend'), //show fontend 

            'lab_hbgnav' => Configuration::get('lab_hbgnav'),
            'PS_BASE_URL' => _PS_BASE_URL_,
        );
        $smarty->assign($ps);
        return $this->display(__FILE__, 'colortool.tpl');
    }
}
