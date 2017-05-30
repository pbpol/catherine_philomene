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

    // Flag to know if the pagecache module is active or not
    private static $_is_page_cache_active = -1;

    private static function _isPageCacheActive()
    {
        // Method added by PageCache
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
            // Standard way
            return parent::smartyOutputContent($content);
        }
    }

    private function smartyOutputContent_15($content)
    {
        // Function for Prestashop 1.5

        // Save the generated HTML into a file and display it => create a cache
        $html = $this->context->smarty->fetch($content);
        PageCache::cacheThis($html, $this->getLayout());
        echo $html;
    }

    private function smartyOutputContent_16($content)
    {
        // Function for Prestashop 1.6

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

        // Save the generated HTML into a file and display it => create a cache
        PageCache::cacheThis($html, $this->getLayout());

        echo $html;
    }

    public function geolocationManagementPublic($default_country)
    {
        return $this->geolocationManagement($default_country);
    }

    public function isRestrictedCountry()
    {
        return $this->restrictedCountry;
    }

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

}
