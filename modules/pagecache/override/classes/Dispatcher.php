<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

class Dispatcher extends DispatcherCore
{

    // Flag to know if the pagecache module is active or not
    private static $_is_page_cache_active = -1;

    // Start time of the dispatcher action to mesure the duration of the page generation
    public $page_cache_start_time = -1;

    private static function _isPageCacheActive()
    {
        // Method added by PageCache
        if (self::$_is_page_cache_active == -1)
        {
            if (file_exists(dirname(__FILE__).'/../../modules/pagecache/pagecache.php'))
            {
                require_once(dirname(__FILE__).'/../../modules/pagecache/pagecache.php');
                self::$_is_page_cache_active = Module::isEnabled('pagecache');
            } else {
                Logger::addLog('Page cache has not been well uninstalled, please, remove manually the following functions in file '.__FILE__.': _isPageCacheActive(), dispatch(), dispatch_15() and dispatch_16(). If you need help contact our support.', 4);
                return false;
            }
        }
        return self::$_is_page_cache_active;
    }

    public function getControllerFromURL($url, $id_shop = null) {

        $controller = false;
        $is_fc_module = false;

        if (isset(Context::getContext()->shop) && $id_shop === null)
            $id_shop = (int)Context::getContext()->shop->id;

        // Try to find it in URL query string (if no URL rewritting)
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $query = html_entity_decode($query);
            $keyvaluepairs = explode('&', $query);
            if ($keyvaluepairs !== false) {
                foreach($keyvaluepairs as $keyvaluepair) {
                    if (strstr($keyvaluepair, '=') !== false) {
                        list($key, $value) = explode('=', $keyvaluepair);

                        if (strcmp('controller', $key) === 0) {
                            $controller = $value;
                        }
                        else if (strcmp('fc', $key) === 0) {
                            $is_fc_module = strcmp('module', $value) !== false;
                        }
                    }
                }
            }
        }

        if (!Validate::isControllerName($controller))
            $controller = false;

        // If not found, try routes (if URL rewritting)
        if (!$controller && $this->use_routes) {
            // Language removed in pagecache.php
            $url_without_lang = $url;

            if (isset($this->routes[$id_shop][Context::getContext()->language->id])) {
                foreach ($this->routes[$id_shop][Context::getContext()->language->id] as $route) {
                    if (preg_match($route['regexp'], $url_without_lang, $m)) {
                        // Route found!
                        $controller = $route['controller'] ? $route['controller'] : false;

                        // A patch for module friendly urls
                        if (preg_match('#module-([a-z0-9_-]+)-([a-z0-9_]+)$#i', $controller, $m)) {
                            $controller = $m[2];
                        }

                        if ($is_fc_module)
                            $controller = false;

                        break;
                    }
                }
            }
            if (!$controller && Tools::strlen($url_without_lang) == 0) {
                $controller = 'index';
            }
            else if ($controller == 'index' || preg_match('/^\/index.php(?:\?.*)?$/', $url_without_lang)) {
                if ($is_fc_module) {
                    $controller = false;
                }
            }
        }

        return $controller;
    }

    public function dispatch() {
        if (Tools::version_compare(_PS_VERSION_,'1.6','>')) {
            $this->dispatch_16();
        } else {
            $this->dispatch_15();
        }
    }

    private function dispatch_15()
    {
        // Get current controller
        $this->getController();
        if (!$this->controller)
            $this->controller = $this->default_controller;

        // <PageCache>
        $this->page_cache_start_time = microtime(true);
        if ($this->_isPageCacheActive())
        {
            $pre_display_html = PageCache::preDisplayStats();
            $cache_file = PageCache::getCacheFile();
            if ($cache_file !== false)
            {
                // Display the cached HTML file
                PageCache::readfile($cache_file);
                PageCache::displayStats(true, $pre_display_html);
                return;
            }
        }
        // </PageCache>

        $controller_class = '';

        // Dispatch with right front controller
        switch ($this->front_controller)
        {
            // Dispatch front office controller
            case self::FC_FRONT :
                $controllers = Dispatcher::getControllers(array(_PS_FRONT_CONTROLLER_DIR_, _PS_OVERRIDE_DIR_.'controllers/front/'));

                $controllers['index'] = 'IndexController';
                if (isset($controllers['auth']))
                    $controllers['authentication'] = $controllers['auth'];
                if (isset($controllers['compare']))
                    $controllers['productscomparison'] = $controllers['compare'];
                if (isset($controllers['contact']))
                    $controllers['contactform'] = $controllers['contact'];

                if (!isset($controllers[Tools::strtolower($this->controller)]))
                    $this->controller = $this->controller_not_found;
                $controller_class = $controllers[Tools::strtolower($this->controller)];
                $params_hook_action_dispatcher = array('controller_type' => self::FC_FRONT, 'controller_class' => $controller_class, 'is_module' => 0);
            break;

            // Dispatch module controller for front office
            case self::FC_MODULE :
                $module_name = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';
                $module = Module::getInstanceByName($module_name);
                $controller_class = 'PageNotFoundController';
                if (Validate::isLoadedObject($module) && $module->active)
                {
                    $controllers = Dispatcher::getControllers(_PS_MODULE_DIR_.$module_name.'/controllers/front/');
                    if (isset($controllers[Tools::strtolower($this->controller)]))
                    {
                        include_once(_PS_MODULE_DIR_.$module_name.'/controllers/front/'.$this->controller.'.php');
                        $controller_class = $module_name.$this->controller.'ModuleFrontController';
                    }
                }
                $params_hook_action_dispatcher = array('controller_type' => self::FC_FRONT, 'controller_class' => $controller_class, 'is_module' => 1);
            break;

            // Dispatch back office controller + module back office controller
            case self::FC_ADMIN :
                $tab = Tab::getInstanceFromClassName($this->controller);
                $retrocompatibility_admin_tab = null;

                if ($tab->module)
                {
                    if (file_exists(_PS_MODULE_DIR_.$tab->module.'/'.$tab->class_name.'.php'))
                        $retrocompatibility_admin_tab = _PS_MODULE_DIR_.$tab->module.'/'.$tab->class_name.'.php';
                    else
                    {
                        $controllers = Dispatcher::getControllers(_PS_MODULE_DIR_.$tab->module.'/controllers/admin/');
                        if (!isset($controllers[Tools::strtolower($this->controller)]))
                        {
                            $this->controller = $this->controller_not_found;
                            $controller_class = 'AdminNotFoundController';
                        }
                        else
                        {
                            // Controllers in modules can be named AdminXXX.php or AdminXXXController.php
                            include_once(_PS_MODULE_DIR_.$tab->module.'/controllers/admin/'.$controllers[Tools::strtolower($this->controller)].'.php');
                            $controller_class = $controllers[Tools::strtolower($this->controller)].(strpos($controllers[Tools::strtolower($this->controller)], 'Controller') ? '' : 'Controller');
                        }
                    }
                    $params_hook_action_dispatcher = array('controller_type' => self::FC_ADMIN, 'controller_class' => $controller_class, 'is_module' => 1);
                }
                else
                {
                    $controllers = Dispatcher::getControllers(array(_PS_ADMIN_DIR_.'/tabs/', _PS_ADMIN_CONTROLLER_DIR_, _PS_OVERRIDE_DIR_.'controllers/admin/'));

                    if (!isset($controllers[Tools::strtolower($this->controller)]))
                        $this->controller = $this->controller_not_found;
                    $controller_class = $controllers[Tools::strtolower($this->controller)];
                    $params_hook_action_dispatcher = array('controller_type' => self::FC_ADMIN, 'controller_class' => $controller_class, 'is_module' => 0);

                    if (file_exists(_PS_ADMIN_DIR_.'/tabs/'.$controller_class.'.php'))
                        $retrocompatibility_admin_tab = _PS_ADMIN_DIR_.'/tabs/'.$controller_class.'.php';
                }

                // @retrocompatibility with admin/tabs/ old system
                if ($retrocompatibility_admin_tab)
                {
                    include_once($retrocompatibility_admin_tab);
                    include_once(_PS_ADMIN_DIR_.'/functions.php');
                    runAdminTab($this->controller, !empty($_REQUEST['ajaxMode']));
                    return;
                }
            break;

            default :
                throw new PrestaShopException('Bad front controller chosen');
        }

        // Instantiate controller
        try
        {
            // Loading controller
            $controller = Controller::getController($controller_class);

            // Execute hook dispatcher
            if (isset($params_hook_action_dispatcher))
                Hook::exec('actionDispatcher', $params_hook_action_dispatcher);

            // Running controller
            $controller->run();

            // <PageCache>
            if ($this->_isPageCacheActive())
            {
                PageCache::displayStats(false, $pre_display_html);
            }
            // </PageCache>
        }
        catch (PrestaShopException $e)
        {
            $e->displayMessage();
        }
    }

    private function dispatch_16()
    {
        $controller_class = '';

        // Get current controller
        $this->getController();
        if (!$this->controller) {
            if (!method_exists($this, 'useDefaultController'))
                $this->controller = $this->default_controller;
            else
                $this->controller = $this->useDefaultController();
        }

        // <PageCache>
        $this->page_cache_start_time = microtime(true);
        if ($this->_isPageCacheActive())
        {
            $pre_display_html = PageCache::preDisplayStats();
            $cache_file = PageCache::getCacheFile();
            if ($cache_file !== false)
            {
                // Display the cached HTML file
                PageCache::readfile($cache_file);
                PageCache::displayStats(true, $pre_display_html);
                return;
            }
        }
        // </PageCache>

        // Dispatch with right front controller
        switch ($this->front_controller)
        {
            // Dispatch front office controller
            case self::FC_FRONT :
                $controllers = Dispatcher::getControllers(array(_PS_FRONT_CONTROLLER_DIR_, _PS_OVERRIDE_DIR_.'controllers/front/'));

                $controllers['index'] = 'IndexController';
                if (isset($controllers['auth']))
                    $controllers['authentication'] = $controllers['auth'];
                if (isset($controllers['compare']))
                    $controllers['productscomparison'] = $controllers['compare'];
                if (isset($controllers['contact']))
                    $controllers['contactform'] = $controllers['contact'];

                if (!isset($controllers[Tools::strtolower($this->controller)]))
                    $this->controller = $this->controller_not_found;
                $controller_class = $controllers[Tools::strtolower($this->controller)];
                $params_hook_action_dispatcher = array('controller_type' => self::FC_FRONT, 'controller_class' => $controller_class, 'is_module' => 0);
                break;

                // Dispatch module controller for front office
            case self::FC_MODULE :
                $module_name = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';
                $module = Module::getInstanceByName($module_name);
                $controller_class = 'PageNotFoundController';
                if (Validate::isLoadedObject($module) && $module->active)
                {
                    $controllers = Dispatcher::getControllers(_PS_MODULE_DIR_.$module_name.'/controllers/front/');
                    if (isset($controllers[Tools::strtolower($this->controller)]))
                    {
                        include_once(_PS_MODULE_DIR_.$module_name.'/controllers/front/'.$this->controller.'.php');
                        $controller_class = $module_name.$this->controller.'ModuleFrontController';
                    }
                }
                $params_hook_action_dispatcher = array('controller_type' => self::FC_FRONT, 'controller_class' => $controller_class, 'is_module' => 1);
                break;

                // Dispatch back office controller + module back office controller
            case self::FC_ADMIN :
                if (isset($this->use_default_controller) && !Tools::getValue('token') && Validate::isLoadedObject(Context::getContext()->employee) && Context::getContext()->employee->isLoggedBack())
                    Tools::redirectAdmin('index.php?controller='.$this->controller.'&token='.Tools::getAdminTokenLite($this->controller));

                $tab = Tab::getInstanceFromClassName($this->controller, Configuration::get('PS_LANG_DEFAULT'));
                $retrocompatibility_admin_tab = null;

                if ($tab->module)
                {
                    if (file_exists(_PS_MODULE_DIR_.$tab->module.'/'.$tab->class_name.'.php'))
                        $retrocompatibility_admin_tab = _PS_MODULE_DIR_.$tab->module.'/'.$tab->class_name.'.php';
                    else
                    {
                        $controllers = Dispatcher::getControllers(_PS_MODULE_DIR_.$tab->module.'/controllers/admin/');
                        if (!isset($controllers[Tools::strtolower($this->controller)]))
                        {
                            $this->controller = $this->controller_not_found;
                            $controller_class = 'AdminNotFoundController';
                        }
                        else
                        {
                            // Controllers in modules can be named AdminXXX.php or AdminXXXController.php
                            include_once(_PS_MODULE_DIR_.$tab->module.'/controllers/admin/'.$controllers[Tools::strtolower($this->controller)].'.php');
                            $controller_class = $controllers[Tools::strtolower($this->controller)].(strpos($controllers[Tools::strtolower($this->controller)], 'Controller') ? '' : 'Controller');
                        }
                    }
                    $params_hook_action_dispatcher = array('controller_type' => self::FC_ADMIN, 'controller_class' => $controller_class, 'is_module' => 1);
                }
                else
                {
                    $controllers = Dispatcher::getControllers(array(_PS_ADMIN_DIR_.'/tabs/', _PS_ADMIN_CONTROLLER_DIR_, _PS_OVERRIDE_DIR_.'controllers/admin/'));
                    if (!isset($controllers[Tools::strtolower($this->controller)]))
                    {
                        // If this is a parent tab, load the first child
                        if (Validate::isLoadedObject($tab) && $tab->id_parent == 0 && ($tabs = Tab::getTabs(Context::getContext()->language->id, $tab->id)) && isset($tabs[0]))
                            Tools::redirectAdmin(Context::getContext()->link->getAdminLink($tabs[0]['class_name']));
                        $this->controller = $this->controller_not_found;
                    }

                    $controller_class = $controllers[Tools::strtolower($this->controller)];
                    $params_hook_action_dispatcher = array('controller_type' => self::FC_ADMIN, 'controller_class' => $controller_class, 'is_module' => 0);

                    if (file_exists(_PS_ADMIN_DIR_.'/tabs/'.$controller_class.'.php'))
                        $retrocompatibility_admin_tab = _PS_ADMIN_DIR_.'/tabs/'.$controller_class.'.php';
                }

                // @retrocompatibility with admin/tabs/ old system
                if ($retrocompatibility_admin_tab)
                {
                    include_once($retrocompatibility_admin_tab);
                    include_once(_PS_ADMIN_DIR_.'/functions.php');
                    runAdminTab($this->controller, !empty($_REQUEST['ajaxMode']));
                    return;
                }
                break;

            default :
                throw new PrestaShopException('Bad front controller chosen');
        }

        // Instantiate controller
        try
        {
            // Loading controller
            $controller = Controller::getController($controller_class);

            // Execute hook dispatcher
            if (isset($params_hook_action_dispatcher))
                Hook::exec('actionDispatcher', $params_hook_action_dispatcher);

            // Running controller
            $controller->run();

            // <PageCache>
            if ($this->_isPageCacheActive())
            {
                PageCache::displayStats(false, $pre_display_html);
            }
            // </PageCache>
        }
        catch (PrestaShopException $e)
        {
            $e->displayMessage();
        }
    }
}