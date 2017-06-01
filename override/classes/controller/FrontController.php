<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */
class FrontController extends FrontControllerCore
{
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    private static $_is_page_cache_active = -1;
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    private static function _isPageCacheActive()
    {
        if (self::$_is_page_cache_active == -1)
        {
            if (file_exists(dirname(__FILE__).'/../../../modules/pagecache/pagecache.php'))
            {
                require_once(dirname(__FILE__).'/../../../modules/pagecache/pagecache.php');
                self::$_is_page_cache_active = Module::isEnabled('pagecache');
            } else {
                Logger::addLog('Page cache has not been well uninstalled, please, remove manually the following functions in file '.__FILE__.': _isPageCacheActive(), smartyOutputContent(), smartyOutputContent_15() and smartyOutputContent_16(). If you need help contact our support.', 4);
                return false;
            }
        }
        return self::$_is_page_cache_active;
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    protected function smartyOutputContent($content)
    {
        if ($this->_isPageCacheActive() && PageCache::canBeCached())
        {
            if (Tools::version_compare(_PS_VERSION_,'1.6','>'))
            {
                $this->smartyOutputContent_16($content);
            }
            else
            {
                $this->smartyOutputContent_15($content);
            }
        }
        else
        {
            return parent::smartyOutputContent($content);
        }
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    private function smartyOutputContent_15($content)
    {
        $html = $this->context->smarty->fetch($content);
        PageCache::cacheThis($html, $this->getLayout());
        echo $html;
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    private function smartyOutputContent_16($content)
    {
        $js_tag = 'js_def';
        $this->context->smarty->assign($js_tag, $js_tag);
        if (is_array($content))
            foreach ($content as $tpl)
                $html = $this->context->smarty->fetch($tpl);
        else
            $html = $this->context->smarty->fetch($content);
        $html = trim($html);
        if (method_exists('Media','deferInlineScripts') && $this->controller_type == 'front' && !empty($html) && $this->getLayout())
        {
            $live_edit_content = '';
            if (method_exists($this, 'useMobileTheme') && !$this->useMobileTheme() && $this->checkLiveEditAccess())
                $live_edit_content = $this->getLiveEditFooter();
 			$dom_available = extension_loaded('dom') ? true : false;
            $defer = (bool)Configuration::get('PS_JS_DEFER') || Tools::version_compare(_PS_VERSION_,'1.6.0.6','<=');
 			if ($defer && $dom_available)
                $html = Media::deferInlineScripts($html);
            $html = trim(str_replace(array('</body>', '</html>'), '', $html))."\n";
            $this->context->smarty->assign(array(
                $js_tag => Media::getJsDef(),
                'js_files' =>  $defer ? array_unique($this->js_files) : array(),
                'js_inline' => ($defer && $dom_available) ? Media::getInlineScript() : array()
            ));
            $javascript = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_.'javascript.tpl');
            $html = ($defer ? $html.$javascript : str_replace($js_tag, $javascript, $html)).$live_edit_content.((!isset($this->ajax) || ! $this->ajax) ? '</body></html>' : '');
        }
        PageCache::cacheThis($html, $this->getLayout());
        echo $html;
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public function geolocationManagementPublic($default_country)
    {
        return $this->geolocationManagement($default_country);
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public function isRestrictedCountry()
    {
        return $this->restrictedCountry;
    }
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public static function getCurrentCustomerGroups()
    {
        if (!is_array(self::$currentCustomerGroups))
        {
            if (!Group::isFeatureActive())
            {
                self::$currentCustomerGroups = array();
            }
            else
            {
                $context = Context::getContext();
                if (!isset($context->customer) || !$context->customer->id)
                {
                    self::$currentCustomerGroups = Customer::getGroupsStatic(null);
                }
                else
                {
                    self::$currentCustomerGroups = array();
                    $result = Db::getInstance()->executeS('SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.(int)$context->customer->id);
                    foreach ($result as $row)
                        self::$currentCustomerGroups[] = $row['id_group'];
                }
            }
        }
        return self::$currentCustomerGroups;
    }
	
	public function setMobileMedia()
    {
        $this->addJquery();

        if (!file_exists($this->getThemeDir().'js/autoload/')) {
            $this->addJS(_THEME_MOBILE_JS_DIR_.'jquery.mobile-1.3.0.min.js');
            $this->addJS(_THEME_MOBILE_JS_DIR_.'jqm-docs.js');
            $this->addJS(_PS_JS_DIR_.'tools.js');
            $this->addJS(_THEME_MOBILE_JS_DIR_.'global.js');
            $this->addJqueryPlugin('fancybox');
        }

        if (!file_exists($this->getThemeDir().'css/autoload/')) {
            $this->addCSS(_THEME_MOBILE_CSS_DIR_.'jquery.mobile-1.3.0.min.css', 'all');
            $this->addCSS(_THEME_MOBILE_CSS_DIR_.'jqm-docs.css', 'all');
			
			
			$this->addCSS($this->getThemeDir().'css/font-pt-sans/font-pt-sans.css', 'all');
			$this->addCSS($this->getThemeDir().'css/font-roboto/font-roboto.css', 'all');
			$this->addCSS($this->getThemeDir().'css/font-robotoslab/font-robotoslab.css', 'all');
			$this->addCSS($this->getThemeDir().'css/font-open-sans/font-open-sans.css', 'all');
		
			$this->addCSS($this->getThemeDir().'css/font-awesome/font-awesome.css', 'all');
		    $this->addCSS($this->getThemeDir().'css/font-awesome/font-awesome-ie7.css', 'all');
			
			
            $this->addCSS(_THEME_MOBILE_CSS_DIR_.'global.css', 'all');
			
			
			$this->addCSS(_THEME_MOBILE_CSS_DIR_.'responsive.css', 'all');
        }
    }
	
	
    public function setMedia()
    {
        /**
         * If website is accessed by mobile device
         * @see FrontControllerCore::setMobileMedia()
         */
        if ($this->useMobileTheme()) {
            $this->setMobileMedia();
            return true;
        }

		$this->addCSS($this->getThemeDir().'css/font-pt-sans/font-pt-sans.css', 'all');
		$this->addCSS($this->getThemeDir().'css/font-roboto/font-roboto.css', 'all');
		$this->addCSS($this->getThemeDir().'css/font-robotoslab/font-robotoslab.css', 'all');
		$this->addCSS($this->getThemeDir().'css/font-open-sans/font-open-sans.css', 'all');
		
		$this->addCSS($this->getThemeDir().'css/font-awesome/font-awesome.css', 'all');
		$this->addCSS($this->getThemeDir().'css/font-awesome/font-awesome-ie7.css', 'all');
		
		 
        $this->addCSS(_THEME_CSS_DIR_.'grid_prestashop.css', 'all');  // retro compat themes 1.5.0.1
        $this->addCSS(_THEME_CSS_DIR_.'global.css', 'all');	
        $this->addJquery();
        $this->addJqueryPlugin('easing');
        $this->addJS(_PS_JS_DIR_.'tools.js');
        $this->addJS(_THEME_JS_DIR_.'global.js');

        // Automatically add js files from js/autoload directory in the template
        if (@filemtime($this->getThemeDir().'js/autoload/')) {
            foreach (scandir($this->getThemeDir().'js/autoload/', 0) as $file) {
                if (preg_match('/^[^.].*\.js$/', $file)) {
                    $this->addJS($this->getThemeDir().'js/autoload/'.$file);
                }
            }
        }
        // Automatically add css files from css/autoload directory in the template
        if (@filemtime($this->getThemeDir().'css/autoload/')) {
            foreach (scandir($this->getThemeDir().'css/autoload', 0) as $file) {
                if (preg_match('/^[^.].*\.css$/', $file)) {
                    $this->addCSS($this->getThemeDir().'css/autoload/'.$file);
                }
            }
        }


		$this->addCSS($this->getThemeDir().'css/responsive.css', 'all');

        if (Tools::isSubmit('live_edit') && Tools::getValue('ad') && Tools::getAdminToken('AdminModulesPositions'.(int)Tab::getIdFromClassName('AdminModulesPositions').(int)Tools::getValue('id_employee'))) {
            $this->addJqueryUI('ui.sortable');
            $this->addjqueryPlugin('fancybox');
            $this->addJS(_PS_JS_DIR_.'hookLiveEdit.js');
        }

        if (Configuration::get('PS_QUICK_VIEW')) {
            $this->addjqueryPlugin('fancybox');
        }

        if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0) {
            $this->addJS(_THEME_JS_DIR_.'products-comparison.js');
        }

        // Execute Hook FrontController SetMedia
        Hook::exec('actionFrontControllerSetMedia', array());

        return true;
    }
	
	
}
