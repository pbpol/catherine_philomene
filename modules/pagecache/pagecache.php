<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

if (!defined('_CAN_LOAD_FILES_'))
    exit;

require_once 'URLNormalizer.php';
require_once 'PageCacheDAO.php';
require_once 'http_build_url.php';

class PageCache extends Module
{

    const PAGECACHE_DIR = 'pagecache';
    const INSTALL_STEP_INSTALL = 1;
    const INSTALL_STEP_BUY_FROM = 2;
    const INSTALL_STEP_IN_ACTION = 3;
    const INSTALL_STEP_EU_COOKIE = 6;
    const INSTALL_STEP_CART = 4;
    const INSTALL_STEP_LOGGED_IN = 5;
    const INSTALL_STEP_VALIDATE = 7;
    const LAST_INSTALL_STEP = 8;
    const INSTALL_STEP_BACK_TO_TEST = self::INSTALL_STEP_IN_ACTION;

    public static $managed_controllers = array(
        'index',
        'category',
        'product',
        'cms',
        'newproducts',
        'bestsales',
        'supplier',
        'manufacturer',
        'contact',
        'pricesdrop',
        'sitemap');

    private static $default_dyn_hooks = array(
        'displayproducttabcontent',
        'displayrightcolumn',
        'displayleftcolumn',
        'displaytop',
        'displaynav',
        'displayproducttab',
        'actionproductoutofstock',
        'displayfooterproduct',
        'displayleftcolumnproduct',
        'displayhome',
        'displayfooter',
        'displaysidebarright',
        'displayrightbar');

    private static $default_dyn_modules = array(
        'blockuserinfo',
        'blockviewed',
        'blockmyaccount',
        'favoriteproducts',
        'blockwishlist',
        'blockviewed_mod',
        'stcompare');

    private $contact_url, $rating_url;

    const JPRESTA_PROTO = 'http://';
    const JPRESTA_DOMAIN = 'jpresta';
    const DOC_PROTO = 'https://';
    const DOC_DOMAIN = 'docs.google';
    const DOC_URL_EN = '.com/document/d/18AboJ_CGq24Q7Y96NlaWTYwpfWwSSUcrRumhUfTOPdM/edit?usp=sharing';
    const DOC_URL_FR = '.com/document/d/1cMVk6zn2xb3B2PA3UvRsy8rHCCfjzU1fb05vWww9ia8/edit?usp=sharing';

    public function __construct()
    {
        $this->name = 'pagecache';
        $this->tab = 'administration';
        $this->version = '3.17';
        $this->author = 'JPresta.com';
        $this->module_key = 'e00d068863a4c8a3684e984f80756e61';
        $this->ps_versions_compliancy = array('min' => '1.5.2.0', 'max' => '1.6');

        parent::__construct();

        $this->displayName = $this->l('Page Cache');
        $this->description = $this->l('Enable full page caching for home, categories, products, CMS and much more pages. Even with page caching you can enable some modules like \'viewed products\' or \'my account\' blocks to load dynamically in ajax. Go from seconds to few milliseconds of loading time!');

        // Check tokens
        $token_enabled = (int)(Configuration::get('PS_TOKEN_ENABLE')) == 1 ? true : false;
        if ($token_enabled) {
            $this->warning = $this->l('You must disable tokens in order for cached pages to do ajax call.');
        }
        // Check for bvkdispatcher module
        if (Module::isInstalled('bvkseodispatcher')) {
            $this->warning = $this->l('Module "SEO Pretty URL Module" (bvkseodispatcher) is not compatible with PageCache because it does not respect Prestashop standards. You have to choose between this module and PageCache.');
        }
        // Check for overrides (after an upgrade it is disabled)
        if (!self::isOverridesEnabled()) {
            $this->warning = $this->l('Overrides are disabled in Performances tab so PageCache is disabled.');
        }

        $seller = Configuration::get('pagecache_seller');
        if (isset($seller) && strcmp($seller, 'addons') === 0) {
            // Prestashop Addons links

            // Contact URL
            $this->contact_url = 'https://addons.prestashop.com/en/write-to-developper?id_product=7939';
            if (strcmp('fr', Language::getIsoById($this->context->language->id)) == 0) {
                $this->contact_url = 'https://addons.prestashop.com/fr/ecrire-au-developpeur?id_product=7939';
            }

            // Rating
            $this->rating_url = 'https://addons.prestashop.com/'.Language::getIsoById($this->context->language->id).'/ratings.php';
        } else {
            // JPresta.com links

            // Contact URL
            $this->contact_url = self::JPRESTA_PROTO . self::JPRESTA_DOMAIN . '.com/en/contact-us';
            if (strcmp('fr', Language::getIsoById($this->context->language->id)) == 0) {
                $this->contact_url = self::JPRESTA_PROTO . self::JPRESTA_DOMAIN . '.com/fr/contactez-nous';
            }

            // Rating
            $this->rating_url = self::JPRESTA_PROTO . self::JPRESTA_DOMAIN . '.com/'.Language::getIsoById($this->context->language->id).'/home/1-page-cache.html#new_comment_form';
        }
    }

    public function install()
    {
        $install_ok = true;
        // Check buggy version 1.6.0.8
        if (Tools::version_compare(_PS_VERSION_,'1.6.0.8','=')) {
            // Check that a fix has been applied
            $moduleClass = Tools::file_get_contents(_PS_CLASS_DIR_ . 'module/Module.php');
            if (substr_count($moduleClass, '#^\s*<\?(?:php)?#') != 4) {
                $this->_errors[] = $this->l('Prestashop 1.6.0.8 has a bug (http://forge.prestashop.com/browse/PSCSX-2500) that must be fixed in order to install PageCache. Please upgrade your shop or apply a patch (replace 4 occurences of "#^\s*<\?(?:php)?\s#" by "#^\s*<\?(?:php)?#" in file ' . _PS_CLASS_DIR_ . 'module/Module.php).');
                $install_ok = false;
            }
        }
        // Check for bvkdispatcher module
        if (Module::isInstalled('bvkseodispatcher')) {
            $this->_errors[] = $this->l('Module "SEO Pretty URL Module" (bvkseodispatcher) is not compatible with PageCache because it does not respect Prestashop standards. You have to choose between this module and PageCache.');
            $install_ok = false;
        }
        // Check for expresscache module
        if (Module::isInstalled('expresscache')) {
            $this->_errors[] = $this->l('Module "Express Cache" (expresscache) cannot be used with Page Cache because you can have only one HTML cache module. In order to install Page Cache you must uninstall Express Cache.');
            $install_ok = false;
        }
        if ($install_ok) {
            // Install module
            $install_ok = parent::install();
            if ($install_ok) {
                $install_ok = PageCacheDAO::createTables();
                $this->_setDefaultConfiguration();
            } else {
                foreach (Tools::scandir($this->getLocalPath().'override', 'php', '', true) as $file) {
                    $class = basename($file, '.php');
                    if (Tools::version_compare(_PS_VERSION_,'1.6','>=')) {
                        if (PrestaShopAutoload::getInstance()->getClassPath($class.'Core')) {
                            $this->removeOverride($class);
                        }
                    } else {
                        if (Autoload::getInstance()->getClassPath($class.'Core')) {
                            $this->removeOverride($class);
                        }
                    }
                }
                // Retry after uninstalling overrides with our own method
                $install_ok = parent::install();
                if ($install_ok) {
                    $install_ok = PageCacheDAO::createTables();
                    $this->_setDefaultConfiguration();
                }
            }
        }
        // Display error if any
        if (!$install_ok) {
            $this->_errors[] = $this->l('An error occured during PageCache installation. If you need help ask for support here: ' . $this->contact_url);
        }
        return $install_ok;
    }

    /**
     * Override Module::updateModuleTranslations()
     */
    public function updateModuleTranslations()
    {
        // Speeds up installation: do nothing because PageCache translation are not in Prestashop language pack
    }

    public function uninstall()
    {
        $this->clearCache(true);
        Configuration::deleteByName('pagecache_install_step');
        Configuration::deleteByName('pagecache_always_infosbox');
        Configuration::deleteByName('pagecache_debug');
        Configuration::deleteByName('pagecache_skiplogged');
        Configuration::deleteByName('pagecache_logs');
        Configuration::deleteByName('pagecache_stats');
        Configuration::deleteByName('pagecache_show_stats');
        Configuration::deleteByName('pagecache_groups');
        Configuration::deleteByName('pagecache_cron_token');
        Configuration::deleteByName('pagecache_seller');
        Configuration::deleteByName('pagecache_ignored_params');
        Configuration::deleteByName('pagecache_dyn_hooks');
        foreach (self::$managed_controllers as $controller) {
            Configuration::deleteByName('pagecache_'.$controller);
            Configuration::deleteByName('pagecache_'.$controller.'_timeout');
            Configuration::deleteByName('pagecache_'.$controller.'_expires');
            Configuration::deleteByName('pagecache_'.$controller.'_u_bl');
            Configuration::deleteByName('pagecache_'.$controller.'_d_bl');
            Configuration::deleteByName('pagecache_'.$controller.'_a_mods');
            Configuration::deleteByName('pagecache_'.$controller.'_u_mods');
            Configuration::deleteByName('pagecache_'.$controller.'_d_mods');
        }
        Configuration::deleteByName('pagecache_product_home_u_bl');
        Configuration::deleteByName('pagecache_product_home_d_bl');
        Configuration::deleteByName('pagecache_product_home_a_mods');
        Configuration::deleteByName('pagecache_product_home_u_mods');
        Configuration::deleteByName('pagecache_product_home_d_mods');
        PageCacheDAO::dropTables();

        $ret = parent::uninstall();

        // Clean cache in case of a reset
        Cache::clean('Module::getModuleIdByName_'.pSQL($this->name));

        return $ret;
    }

    private function _setDefaultConfiguration($id_shop_group = null, $id_shop = null)
    {
        // Register hooks
        $this->registerHook('actionDispatcher');
        $this->registerHook('displayHeader');
        $this->registerHook('displayMobileHeader');
        $this->registerHook('actionCategoryAdd');
        $this->registerHook('actionCategoryUpdate');
        $this->registerHook('actionCategoryDelete');
        $this->registerHook('actionObjectCmsAddAfter');
        $this->registerHook('actionObjectCmsUpdateAfter');
        $this->registerHook('actionObjectCmsDeleteBefore');
        $this->registerHook('actionObjectStockAddAfter');
        $this->registerHook('actionObjectStockUpdateAfter');
        $this->registerHook('actionObjectStockDeleteBefore');
        $this->registerHook('actionObjectManufacturerAddAfter');
        $this->registerHook('actionObjectManufacturerUpdateAfter');
        $this->registerHook('actionObjectManufacturerDeleteBefore');
        $this->registerHook('actionObjectAddressAddAfter');
        $this->registerHook('actionObjectAddressUpdateAfter');
        $this->registerHook('actionObjectAddressDeleteBefore');
        $this->registerHook('actionAttributeSave');
        $this->registerHook('actionAttributeDelete');
        $this->registerHook('actionAttributeGroupDelete');
        $this->registerHook('actionAttributeGroupSave');
        $this->registerHook('actionFeatureSave');
        $this->registerHook('actionFeatureDelete');
        $this->registerHook('actionFeatureValueSave');
        $this->registerHook('actionFeatureValueDelete');
        $this->registerHook('actionProductAdd');
        $this->registerHook('actionProductUpdate');
        $this->registerHook('actionProductDelete');
        $this->registerHook('actionProductAttributeUpdate');
        $this->registerHook('actionProductAttributeDelete');
        $this->registerHook('actionUpdateQuantity');
        $this->registerHook('actionHtaccessCreate');
        // New shop creation
        $this->registerHook('actionShopDataDuplication');
        // Add hook for specific prices
        $this->registerHook('actionObjectSpecificPriceAddAfter');
        $this->registerHook('actionObjectSpecificPriceUpdateAfter');
        $this->registerHook('actionObjectSpecificPriceDeleteBefore');

        // Use backlink heuristic...
        Configuration::updateValue('pagecache_cms_u_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_cms_d_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_supplier_u_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_supplier_d_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_manufacturer_u_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_manufacturer_d_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_u_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_d_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_home_u_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_home_d_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_category_u_bl', true, false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_category_d_bl', true, false, $id_shop_group, $id_shop);

        // Default impacted modules
        Configuration::updateValue('pagecache_category_a_mods', 'blockcategories', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_category_u_mods', 'blockcategories', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_category_d_mods', 'blockcategories', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_supplier_a_mods', 'blocksupplier', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_supplier_u_mods', 'blocksupplier', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_supplier_d_mods', 'blocksupplier', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_manufacturer_a_mods', 'blockmanufacturer', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_manufacturer_u_mods', 'blockmanufacturer', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_manufacturer_d_mods', 'blockmanufacturer', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_a_mods', 'blocknewproducts', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_home_a_mods', 'homefeatured', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_home_u_mods', 'homefeatured', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_product_home_d_mods', 'homefeatured', false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_cms_a_mods', 'blockcms', false, $id_shop_group, $id_shop);

        // Enable cache on all managed_controllers and timeout = 1 day
        foreach (self::$managed_controllers as $controller) {
            Configuration::updateValue('pagecache_'.$controller, true, false, $id_shop_group, $id_shop);
            Configuration::updateValue('pagecache_'.$controller.'_timeout', 60 * 24 * 1, false, $id_shop_group, $id_shop);
        }

        // Set default dynamic hooks
        $pagecache_dyn_hooks = '';
        $module_list = Hook::getHookModuleExecList();
        foreach ($module_list as $hook_name => $modules) {
            foreach ($modules as $module) {
                if (in_array($hook_name, self::$default_dyn_hooks) && in_array($module['module'], self::$default_dyn_modules)) {
                    $pagecache_dyn_hooks .= $hook_name . '|' . $module['module'] . ',';
                }
                /** Special case: blockcart will be dynamic if ajax is disabled */
                elseif (in_array($hook_name, self::$default_dyn_hooks) && strcmp($module['module'], 'blockcart') == 0) {
                    if (!(int)(Configuration::get('PS_BLOCK_CART_AJAX'))) {
                        $pagecache_dyn_hooks .= $hook_name . '|' . $module['module'] . ',';
                    }
                }
            }
        }
        Configuration::updateValue('pagecache_dyn_hooks', $pagecache_dyn_hooks, false, $id_shop_group, $id_shop);

        // Set default javascript to execute
        $cfgadvancedjs = <<<EOT
// Force cart to refresh
$.ajax({ type: 'POST', headers: { "cache-control": "no-cache"}, url: baseUri + '?rand=' + new Date().getTime(), async: true, cache: false, dataType: "json", data: 'controller=cart&ajax=true&token=' + static_token, success: function (jsonData) { ajaxCart.updateCart(jsonData);}
});
EOT;
        Configuration::updateValue('pagecache_cfgadvancedjs', $cfgadvancedjs, false, $id_shop_group, $id_shop);

        // First install step is 0 (none)
        Configuration::updateValue('pagecache_install_step', 0, false, $id_shop_group, $id_shop);

        // Do not always display infos box by default
        Configuration::updateValue('pagecache_always_infosbox', false, false, $id_shop_group, $id_shop);

        // Not in production by default
        Configuration::updateValue('pagecache_debug', true, false, $id_shop_group, $id_shop);

        // Cache logged in users by default
        Configuration::updateValue('pagecache_skiplogged', false, false, $id_shop_group, $id_shop);

        // Disable logs by default
        Configuration::updateValue('pagecache_logs', false, false, $id_shop_group, $id_shop);

        // Enable statistics by default
        Configuration::updateValue('pagecache_stats', true, false, $id_shop_group, $id_shop);

        // Default browser cache to 15 minutes
        foreach (self::$managed_controllers as $controller) {
            Configuration::updateValue('pagecache_'.$controller.'_expires', 15, false, $id_shop_group, $id_shop);
        }

        // Default ad tracking parameters
        Configuration::updateValue('pagecache_ignored_params', 'utm_campaign,utm_content,utm_medium,utm_source,utm_term,_openstat,cm_cat,cm_ite,cm_pla,cm_ven,owa_ad,owa_ad_type,owa_campaign,owa_medium,owa_source,pk_campaign,pk_kwd,WT.mc_t', false, $id_shop_group, $id_shop);

        // Generate CRON URL token
        if (!Configuration::get('pagecache_cron_token')) {
            Configuration::updateValue('pagecache_cron_token', self::generateRandomString(), false, $id_shop_group, $id_shop);
        }

        // Disable tokens on front
        Configuration::updateValue('PS_TOKEN_ENABLE', 0, false, $id_shop_group, $id_shop);

        // CCC versions
        Configuration::updateValue('pagecache_CCCCSS_VERSION', Configuration::get('PS_CCCCSS_VERSION'), false, $id_shop_group, $id_shop);
        Configuration::updateValue('pagecache_CCCJS_VERSION', Configuration::get('PS_CCCJS_VERSION'), false, $id_shop_group, $id_shop);
    }

    public static function normalizeFile($file) {
        $subdir = _PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/';
        return Tools::substr($file, Tools::strlen($subdir));
    }

    public static function unnormalizeFile($file) {
        $subdir = _PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/';
        return $subdir . $file;
    }

    public function getContent()
    {
        $modules = Module::getModulesInstalled(0);
        $instances = array();
        foreach ($modules as $module) {
            if ($tmp_instance = Module::getInstanceById($module['id_module'])) {
                $instances[$tmp_instance->id] = $tmp_instance;
            }
        }
        $trigered_events = array(
            'pagecache_cms_a' => array('title' => $this->l('On new CMS'), 'desc' => $this->l(''), 'bl' => false),
            'pagecache_cms_u' => array('title' => $this->l('On CMS update'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_cms_d' => array('title' => $this->l('On CMS deletion'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_supplier_a' => array('title' => $this->l('On new supplier'), 'desc' => $this->l(''), 'bl' => false),
            'pagecache_supplier_u' => array('title' => $this->l('On supplier update'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_supplier_d' => array('title' => $this->l('On supplier deletion'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_manufacturer_a' => array('title' => $this->l('On new manufacturer'), 'desc' => $this->l(''), 'bl' => false),
            'pagecache_manufacturer_u' => array('title' => $this->l('On manufacturer update'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_manufacturer_d' => array('title' => $this->l('On manufacturer deletion'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_product_a' => array('title' => $this->l('On new product'), 'desc' => $this->l(''), 'bl' => false),
            'pagecache_product_u' => array('title' => $this->l('On product update'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_product_d' => array('title' => $this->l('On product deletion'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_product_home_a' => array('title' => $this->l('On new home featured product'), 'desc' => $this->l(''), 'bl' => false),
            'pagecache_product_home_u' => array('title' => $this->l('On home featured product update'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_product_home_d' => array('title' => $this->l('On home featured product deletion'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_category_a' => array('title' => $this->l('On new category'), 'desc' => $this->l(''), 'bl' => false),
            'pagecache_category_u' => array('title' => $this->l('On category update'), 'desc' => $this->l(''), 'bl' => true),
            'pagecache_category_d' => array('title' => $this->l('On category deletion'), 'desc' => $this->l(''), 'bl' => true)
            );

        $html = '';

        // To display advanced options add URL parameter "adv"
        $advanced_mode = Tools::getIsset("adv");

        // If we try to update the settings
        if (Tools::isSubmit('submitModule'))
        {
            if (_PS_MODE_DEMO_ && !$this->context->employee->isSuperAdmin()) {
                $html .= $this->displayError($this->l('In DEMO mode you cannot modify the Page Cache configuration.'));
            } else {
                //
                // Update Pages and timeouts
                //
                if (Tools::getIsset('submitModuleTimeouts')) {
                    foreach (self::$managed_controllers as $controller) {
                        if (Tools::getValue('pagecache_'.$controller, false)) {
                            Configuration::updateValue('pagecache_'.$controller, true);
                            Configuration::updateValue('pagecache_'.$controller.'_timeout', Tools::getValue('pagecache_'.$controller.'_timeout', 1440));
                            Configuration::updateValue('pagecache_'.$controller.'_expires', max(0, min(60, Tools::getValue('pagecache_'.$controller.'_expires', 15))));
                        } else {
                            Configuration::updateValue('pagecache_'.$controller, false);
                        }
                    }
                    $html .= $this->displayConfirmation($this->l('Pages and timeouts have been updated'));
                }
                //
                // Action: Clear cache
                //
                else if (Tools::getIsset('submitModuleClearCache')) {
                    $this->clearCache();
                    $html .= $this->displayConfirmation($this->l('Cache has been deleted'));
                }
                //
                // Install steps
                //
                else if (Tools::getIsset('pagecache_install_step')) {
                    // Disable tokens if requested
                    if (strcmp(Tools::getValue('pagecache_disable_tokens', 'false'), 'true') == 0) {
                        Configuration::updateValue('PS_TOKEN_ENABLE', 0);
                        $html .= $this->displayConfirmation($this->l('Tokens have been disabled'));
                    }
                    if (Tools::getIsset('pagecache_seller')) {
                        Configuration::updateValue('pagecache_seller', Tools::getValue('pagecache_seller', 'jpresta'));
                    }
                    $pagecache_disable_loggedin = (int) Tools::getValue('pagecache_disable_loggedin', 0);
                    if ($pagecache_disable_loggedin != 0) {
                        // Enable / Disable cache for logged in users
                        Configuration::updateValue('pagecache_skiplogged', $pagecache_disable_loggedin > 0 ? true : false);
                    } else {
                        // New install step
                        Configuration::updateValue('pagecache_install_step', Tools::getValue('pagecache_install_step', self::INSTALL_STEP_BUY_FROM));
                        if (Tools::getValue('pagecache_install_step', self::INSTALL_STEP_BUY_FROM) < self::LAST_INSTALL_STEP) {
                            // Stay or go in test mode
                            Configuration::updateValue('pagecache_debug', 1);
                        } else {
                            // Go in production mode
                            Configuration::updateValue('pagecache_debug', 0);
                        }
                    }
                }
                //
                // Update dynamics hooks
                //
                else if (Tools::getIsset('submitModuleDynhooks')) {
                    $pagecache_dyn_hooks = '';
                    if (Tools::getValue('pagecache_hooks') !== false) {
                        $hooks = Tools::getValue('pagecache_hooks');
                        if (is_array($hooks)) {
                            foreach ($hooks as $value) {
                                list($hook_name, $module_name) = explode('|', $value);
                                $empty_box = (int) Tools::getValue('pagecache_hooks_empty_'.$hook_name.'_'.$module_name, 0);
                                $pagecache_dyn_hooks .=  $hook_name.'|'.$module_name.'|'.$empty_box.',';
                            }
                        } else {
                            list($hook_name, $module_name) = explode('|', $hooks);
                            $empty_box = (int) Tools::getValue('pagecache_hooks_empty_'.$hook_name.'_'.$module_name, 0);
                            $pagecache_dyn_hooks .=  $hook_name.'|'.$module_name.'|'.$empty_box.',';
                        }
                    }
                    Configuration::updateValue('pagecache_dyn_hooks', $pagecache_dyn_hooks);
                    Configuration::updateValue('pagecache_cfgadvancedjs', trim(Tools::getValue('cfgadvancedjs', '')));
                    $html .= $this->displayConfirmation($this->l('Dynamics hooks and javascript to execute have been updated'));
                }
                //
                // Statistics
                //
                else if (Tools::getIsset('submitModuleResetStats')) {
                    // Reset statistics
                    $this->clearCacheAndStats();
                    $html .= $this->displayConfirmation($this->l('Statistics have been reset and cache has been deleted'));
                }
                else if (Tools::getIsset('submitModuleOnOffStats')) {
                    // Enable / disable statistics
                    Configuration::updateValue('pagecache_stats', !Configuration::get('pagecache_stats'));
                }
                //
                // Cache management
                //
                else if (Tools::getIsset('submitModuleCacheManagement')) {
                    foreach ($trigered_events as $key => $trigered_event) {
                        Configuration::updateValue($key.'_mods', Tools::getValue($key.'_mods', ''));
                        Configuration::updateValue($key.'_bl', Tools::getValue($key.'_bl', false));
                    }
                    $html .= $this->displayConfirmation($this->l('Configuration updated'));
                }
                //
                // Options
                //
                else {
                    Configuration::updateValue('pagecache_always_infosbox', Tools::getValue('pagecache_always_infosbox', false));
                    Configuration::updateValue('pagecache_skiplogged', Tools::getValue('pagecache_skiplogged', false));
                    Configuration::updateValue('pagecache_logs', Tools::getValue('pagecache_logs', false));
                    $ignored_params_str = '';
                    $ignored_params = explode(',', Tools::getValue('pagecache_ignored_params', ''));
                    foreach ($ignored_params as $ignored_param) {
                        $p = Tools::strtolower(trim($ignored_param));
                        if (!empty($p)) {
                            if (!empty($ignored_params_str)) {
                                $ignored_params_str .= ',';
                            }
                            $ignored_params_str .= $p;
                        }
                    }
                    Configuration::updateValue('pagecache_ignored_params', $ignored_params_str);
                    $html .= $this->displayConfirmation($this->l('Configuration updated'));
                }
            }
        } else {
            foreach (self::$managed_controllers as $controller) {
                if (!Configuration::hasKey('pagecache_'.$controller, null, Shop::getContextShopGroupID(true), Shop::getContextShopID(true))) Configuration::updateValue('pagecache_'.$controller, true);
                if (!Configuration::hasKey('pagecache_'.$controller.'_timeout', null, Shop::getContextShopGroupID(true), Shop::getContextShopID(true))) Configuration::updateValue('pagecache_'.$controller.'_timeout', 60 * 24 * 1);
            }
            if (!Configuration::hasKey('pagecache_show_stats', null, Shop::getContextShopGroupID(true), Shop::getContextShopID(true))) Configuration::updateValue('pagecache_show_stats', true);
        }

        $html .= '
        <style>
        #pagecachecfg {margin-top: 10px;}
        #pagecachecfg ul, #pagecachecfg ol{margin-left:20px;}
        #pagecachecfg .dynhooks label{line-height:18px;}
        #pagecachecfg .cachemanagement table{width:100%;border:1px solid #CCCED7;border-right:none;}
        #pagecachecfg .cachemanagement td, #pagecachecfg .cachemanagement th{border-right:1px solid #CCCED7;padding:3px;}
        #pagecachecfg .cachemanagement td{border-bottom:1px solid #CCCED7;}
        #pagecachecfg .cachemanagement th{background-color:#eee;border-bottom:1px solid #CCCED7;}
        #pagecachecfg .tag{background-color:#eee;border:1px solid #CCCED7;border-radius:4px;display:inline-block;margin:2px;padding:3px;}
        #linkadvanced{font-weight:700;display:block;margin:15px 5px;}
        #pagecachecfg input[disabled]{opacity:0.5;filter:alpha(opacity=50);}
        #pagecachecfg .bootstrap .nav-tabs{margin-left:0;}
        #pagecachecfg .bootstrap .nav-tabs li a{font-size:1.2em;}
        #pagecachecfg .bootstrap .nav-tabs li.active a, #pagecachecfg .bootstrap .nav-tabs li.active a:visited,.bootstrap .nav-tabs li.active a:hover, #pagecachecfg .bootstrap .nav-tabs li.active a:focus{background-color:#ebedf4;}
        #pagecachecfg .nobootstrap fieldset{border:1px solid #ddd;margin:0;}
        #pagecachecfg .installstep{font-size:1.3em;margin:5px 0 20px;}
        #pagecachecfg a.browsebtn{display:inline-block;color:#FFF;background-color:#F0AD4E;border:1px solid #EEA236;border-radius:3px;text-decoration:none;padding:2px;}
        #pagecachecfg a.browsebtn:hover{background-color:#F5C177}
        #pagecachecfg a.okbtn{display:inline-block;color:#FFF;background-color:#59C763;border:1px solid #4EA948;border-radius:3px;text-decoration:none;margin:3px;padding:2px;}
        #pagecachecfg a.okbtn:hover{background-color:#7DD385}
        #pagecachecfg a.kobtn{display:inline-block;color:#DA0000;border-radius:3px;margin:3px;padding:2px;}
        #pagecachecfg a.kobtn:hover{color:#ED8080}
        #pagecachecfg div.step{margin:5px 0 5px 20px;}
        #pagecachecfg .step span{border-radius:.8em;color:#FFF;display:inline-block;font-weight:700;line-height:1.6em;margin-right:15px;text-align:center;width:1.6em;}
        #pagecachecfg .step img{margin-right:15px;}
        #pagecachecfg .steptodo span{background:#CCC;}
        #pagecachecfg .stepok span{background:#5EA226;color:#FFF;}
        #pagecachecfg .stepok{color:#5EA226;}
        #pagecachecfg .stepdesc{border-left:2px solid #CCCED7;margin-left:44px;padding:10px 0 10px 24px;}
        #pagecachecfg .stepdesc img{margin:2px;}
        #pagecachecfg .stepdesc ol,.stephelp ol{margin:0;padding:0 0 0 24px;}
        #pagecachecfg .stephelp {display:none;border: 1px solid rgb(229, 229, 29);background-color: lightyellow;border-radius: 8px;padding: 10px;margin: 10px 0;}
        #pagecachecfg .morehook {display: none}
        #pagecachecfg .actions {margin: 15px 0 0 15px;}
        #pagecachecfg .btn {margin-right: 5px}
        #pagecachecfg.ps15 ul.nav-tabs li{display: inline-block; padding: 5px; margin: 0 5px 0 0; border-radius: 5px 5px 0 0; background-color: #EBEDF4; border: 1px solid #CCCED7; border-bottom: none;}
        #pagecachecfg.ps15 ul.nav-tabs li.active{background-color: #49B2FF; color:white}
        #pagecachecfg.ps15 ul.nav-tabs li a, #pagecachecfg.ps15 a.okbtn, #pagecachecfg.ps15 a.browsebtn {text-decoration: none;}
        #pagecachecfg.ps15 .bootstrap .nav-tabs li.active a {background-color: #49B2FF; color:white;text-decoration: none;}
        #pagecachecfg.ps15 a {text-decoration: underline;}
        #pagecachecfg.ps15 ol {list-style-type: decimal;}

        </style>
        <script type="text/javascript">
            $( document ).ready(function() {
                switch (window.location.hash) {
                    case "#tabinstall":	displayTab("install"); break;
                    case "#tabdynhooks":	displayTab("dynhooks"); break;
                    case "#tabdynhooksjs":	displayTab("dynhooks"); break;
                    case "#taboptions":	displayTab("options"); break;
                    case "#tabtimeouts":	displayTab("timeouts"); break;
                    case "#tabstats":	displayTab("stats"); break;
                    case "#tabcron":	displayTab("cron"); break;
                    case "#tabstats":	displayTab("stats"); break;
                    case "#tabcachemanagement":	displayTab("cachemanagement"); break;
                }
            });
            function displayTab($tab) {
                $(".pctab").hide();
                $("#"+$tab).show();
                $(".nav-tabs .active").removeClass("active");
                $("#li"+$tab).addClass("active");
            }
        </script>
        <div id="pagecachecfg"';
        if (Tools::version_compare(_PS_VERSION_,'1.6','<')) {
            $html .= ' class="ps15"';
        }
        $html .= '>';

        //
        // Tabs
        //
        $html .= '<div class="bootstrap"><ul class="nav nav-tabs">';
        $html .= '<li id="liinstall" role="presentation"'.(strcmp(Tools::getValue('pctab', 'install'), 'install') == 0 ? ' class="active"' : Tools::getValue('pctab', 'install')).'><a href="#tabinstall" onclick="displayTab(\'install\');return true;"><img width="16" height="16" src="../img/admin/prefs.gif" alt=""/>&nbsp;'.$this->l('Installation').'</a></li>';
        $html .= '<li id="lidynhooks" role="presentation"'.(Tools::getValue('pctab') == 'dynhooks' ? ' class="active"' : '').'><a href="#tabdynhooks" onclick="displayTab(\'dynhooks\');return true;"><img width="16" height="16" src="../img/admin/tab-plugins.gif" alt=""/>&nbsp;'.$this->l('Dynamic modules').'</a></li>';
        if ($advanced_mode) {
            $html .= '<li id="lioptions" role="presentation"'.(Tools::getValue('pctab') == 'options' ? ' class="active"' : '').'><a href="#taboptions" onclick="displayTab(\'options\');return true;"><img width="16" height="16" src="../img/admin/AdminPreferences.gif" alt=""/>&nbsp;'.$this->l('Options').'</a></li>';
        }
        $html .= '<li id="litimeouts" role="presentation"'.(Tools::getValue('pctab') == 'timeouts' ? ' class="active"' : '').'><a href="#tabtimeouts" onclick="displayTab(\'timeouts\');return true;"><img width="16" height="16" src="../img/admin/time.gif" alt=""/>&nbsp;'.$this->l('Pages & timeouts').'</a></li>';
        $html .= '<li id="listats" role="presentation"'.(Tools::getValue('pctab') == 'stats' ? ' class="active"' : '').'><a href="#tabstats" onclick="displayTab(\'stats\');return true;"><img width="16" height="16" src="../img/admin/AdminStats.gif" alt=""/>&nbsp;'.$this->l('Statistics').'</a></li>';
        $html .= '<li id="licron" role="presentation"'.(Tools::getValue('pctab') == 'cron' ? ' class="active"' : '').'><a href="#tabcron" onclick="displayTab(\'cron\');return true;"><img width="16" height="16" src="../img/admin/subdomain.gif" alt=""/>&nbsp;'.$this->l('CRON').'</a></li>';
        if ($advanced_mode) {
            $html .= '<li id="licachemanagement" role="presentation"'.(Tools::getValue('pctab') == 'cachemanagement' ? ' class="active"' : '').'><a href="#tabcachemanagement" onclick="displayTab(\'cachemanagement\');return true;"><img width="16" height="16" src="../img/admin/AdminTools.gif" alt=""/>&nbsp;'.$this->l('Cache management').'</a></li>';
        }
        $html .= '</ul></div>';

        //
        // Installation
        //
        $cur_step = (int) Configuration::get('pagecache_install_step');
        $html .= '<form id="pagecache_form_install" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">
            <input type="hidden" name="submitModule" value="true"/>
            <input type="hidden" name="pctab" value="install"/>
            <input type="hidden" name="pagecache_disable_tokens" value="false" id="pagecache_disable_tokens"/>';
        $html .= '<fieldset id="install" class="pctab" '.(Tools::getValue('pctab', 'install') == 'install' ? '' : 'style="display:none"').'><div style="clear: both; padding-top:15px;">';

        // Check errors or compatiblity problem
        $errors = $this->showErrors();
        if (!empty($errors)) {
            // Back to install step 1 and test mode to resolve errors
            Configuration::updateValue('pagecache_debug', true);
            Configuration::updateValue('pagecache_install_step', self::INSTALL_STEP_INSTALL);
            $html .= $errors;
        } else {
            $cur_step = (int) Configuration::get('pagecache_install_step');
            if ($cur_step <= 1) {
                // Validate step 1 because there is no error
                Configuration::updateValue('pagecache_install_step', self::INSTALL_STEP_BACK_TO_TEST);
            }
        }

        // Some Prestashop settings advises
        $html .= $this->showAdvices();

        if (Configuration::get('pagecache_debug')) {
            $cur_step = (int) Configuration::get('pagecache_install_step');
            $html .= '<input type="hidden" name="pagecache_install_step" id="pagecache_install_step" value="'.($cur_step + 1).'"/>';
            $html .= '<input type="hidden" name="pagecache_disable_loggedin" id="pagecache_disable_loggedin" value="0"/>';
            $html .= '<input type="hidden" name="pagecache_seller" id="pagecache_seller" value="'.Configuration::get('pagecache_seller').'"/>';

            if ($cur_step > self::INSTALL_STEP_INSTALL) {
                $html .= '<div class="installstep">'.$this->l('Congratulations!').' '.$this->displayName . ' ' . $this->l('is currently installed in'). ' <b>' . $this->l('test mode').'</b>' . $this->l(', that means it\'s not yet activated to your visitors.').'</div>';
            }

            $html .= '<div class="installstep">'.$this->l('To complete the installation, please follow these steps:');
            $html .= $this->_displayStep(self::INSTALL_STEP_INSTALL).$this->l('Install the module and enable test mode');
            if ($cur_step == self::INSTALL_STEP_INSTALL) {
                $html .= '<div class="stepdesc"><ol>';
                $html .= '<li>'.$this->l('Resolve displayed errors above').'</li>';
                $html .= '</ol>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= $this->_displayStep(self::INSTALL_STEP_BUY_FROM).$this->l('Tell us where did you buy the module');
            if ($cur_step == self::INSTALL_STEP_BUY_FROM) {
                $html .= '<div class="stepdesc"><ol>';
                $html .= '<li>'.$this->l('In order to display correct links for support just tell us where you bought ').$this->displayName.'</li>';
                $html .= '</ol>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_seller\').val(\'addons\');$(\'#pagecache_form_install\').submit();return false;">'.$this->l('Prestashop Addons').'</a>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_seller\').val(\'jpresta\');$(\'#pagecache_form_install\').submit();return false;">'.$this->l('JPresta.com').'</a>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= $this->_displayStep(self::INSTALL_STEP_IN_ACTION).$this->l('Check that the module is well installed');
            if ($cur_step == self::INSTALL_STEP_IN_ACTION) {
                $html .= '<div class="stepdesc"><ol>';
                $html .= '<li><a href="../?dbgpagecache=1" target="_blank">'.$this->l('Click here to browse your site in test mode').'</a></li>';
                $html .= '<li>'.$this->l('You must see a box displayed in bottom left corner of your store').'</li>';
                $html .= '<li>'.$this->l('You must be able to play with these buttons');
                $html .= '&nbsp;&nbsp;<img src="../modules/'.$this->name.'/views/img/on.png" alt="" width="16" height="16"/><img src="../modules/'.$this->name.'/views/img/reload.png" alt="" width="16" height="16"/><img src="../modules/'.$this->name.'/views/img/trash.png" alt="" width="16" height="16"/><img src="../modules/'.$this->name.'/views/img/close.png" alt="" width="16" height="16"/></li>';
                $html .= '</ol>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_form_install\').submit();return false;">'.$this->l('OK, I validate this step').'</a>';
                $html .= '<a href="#" class="kobtn" onclick="$(\'#help'.self::INSTALL_STEP_IN_ACTION.'\').toggle();return false;">'.$this->l('No, I\'m having trouble').'</a>';
                $html .= '<div class="stephelp" id="help'.self::INSTALL_STEP_IN_ACTION.'"><ol>';
                $html .= '<li>'.$this->l('Reset the module and see if it\'s better').'</li>';
                $html .= '<li>'.$this->l('If, after resetting the module, you are still having trouble,').' <a href="'.$this->contact_url.'" target="_blank">'.$this->l('contact us here').'</a></li>';
                $html .= '</ol>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= $this->_displayStep(self::INSTALL_STEP_CART).$this->l('Check that the cart is working good');
            if ($cur_step == self::INSTALL_STEP_CART) {
                $html .= '<div class="stepdesc"><ol>';
                $html .= '<li><a href="../?dbgpagecache=1" target="_blank">'.$this->l('Click here to browse your site in test mode').'</a></li>';
                $html .= '<li>'.$this->l('Check that you can add products into the cart as usual').'</li>';
                $html .= '<li>'.$this->l('Once you have a product in your cart, display an other page and see if cart still contains the products you added').'</li>';
                $html .= '</ol>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_form_install\').submit();return false;">'.$this->l('OK, I validate this step').'</a>';
                $html .= '<a href="#" class="kobtn" onclick="$(\'#help'.self::INSTALL_STEP_CART.'\').toggle();return false;">'.$this->l('No, I\'m having trouble').'</a>';
                $html .= '<div class="stephelp" id="help'.self::INSTALL_STEP_CART.'"><ol>';
                $html .= '<li>'.$this->l('When you display an other page, check that you have the parameter dbgpagecache=1 in the URL. If not, just add it.').'</li>';
                $html .= '<li>'.$this->l('When refreshing the cart, PageCache may remove some "mouse over" behaviours. To set them back you can execute some javascript after all dynamics modules have been displayed.').' <a href="#tabdynhooksjs" onclick="displayTab(\'dynhooks\');return true;">'.$this->l('Go in "Dynamic modules" tab in Javascript form.').'</a></li>';
                $html .= '<li>'.$this->l('If you cannot make it work,').' <a href="'.$this->contact_url.'" target="_blank">'.$this->l('contact us here').'</a></li>';
                $html .= '</ol>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= $this->_displayStep(self::INSTALL_STEP_LOGGED_IN).$this->l('Check that logged in users are recognized');
            if ($cur_step == self::INSTALL_STEP_LOGGED_IN) {
                $html .= '<div class="stepdesc"><ol>';
                if (Configuration::get('pagecache_skiplogged')) {
                    if (Tools::version_compare(_PS_VERSION_,'1.6','>=')) {
                        $html .= '<div class="bootstrap"><div class="alert alert-info" style="display: block;">&nbsp;'.$this->l('Cache is disabled for logged in users so this step should be OK now, but you should check this out anyway ;-)');
                        $html .= '<br/>'.$this->l('If you want you can').' <a href="#" class="browsebtn" onclick="$(\'#pagecache_disable_loggedin\').val(-1);$(\'#pagecache_form_install\').submit();return false;">'.$this->l('reactivate cache for logged in users').'</a>';
                        $html .= '</div></div>';
                    } else {
                        $html .= '<div class="hint clear" style="display: block;">&nbsp;'.$this->l('Cache is disabled for logged in users so this step should be OK now, but you should check this out anyway ;-)');
                        $html .= '<br/>'.$this->l('If you want you can').' <a href="#" class="browsebtn" onclick="$(\'#pagecache_disable_loggedin\').val(-1);$(\'#pagecache_form_install\').submit();return false;">'.$this->l('reactivate cache for logged in users').'</a>';
                        $html .= '</div>';
                    }
                }
                $html .= '';
                $html .= '<li><a href="../?dbgpagecache=1" target="_blank">'.$this->l('Click here to browse your site in test mode').'</a></li>';
                $html .= '<li>'.$this->l('You must see the "sign in" link when you are not logged in').'</li>';
                $html .= '<li>'.$this->l('You must see the the user name when you are logged in').'</li>';
                $html .= '<li>'.$this->l('Of course it depends on your theme so just check that being logged in or not has the same behaviour with PageCache').'</li>';
                $html .= '</ol>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_form_install\').submit();return false;">'.$this->l('OK, I validate this step').'</a>';
                $html .= '<a href="#" class="kobtn" onclick="$(\'#help'.self::INSTALL_STEP_LOGGED_IN.'\').toggle();return false;">'.$this->l('No, I\'m having trouble').'</a>';
                $html .= '<div class="stephelp" id="help'.self::INSTALL_STEP_LOGGED_IN.'">';
                if (!Configuration::get('pagecache_skiplogged')) {
                    $html .= '<ol>';
                    $html .= '<li>'.$this->l('Make sure that module displaying user informations or sign in links are set as "dynamic".').'</li>';
                    $html .= '<li>'.$this->l('Your theme may be uncompatible with this feature, specially if these informations are "hard coded" in theme without using a module. In this case just disable PageCache for logged in users.').'</li>';
                    $html .= '</ol>';
                    $html .= '<a href="#" class="browsebtn" onclick="$(\'#pagecache_disable_loggedin\').val(1);$(\'#pagecache_form_install\').submit();return false;">'.$this->l('Disable cache for logged in users').'</a>';
                } else {
                    $html .= '<ol>';
                    $html .= '<li>'.$this->l('Still having problem? Then ').' <a href="'.$this->contact_url.'" target="_blank">'.$this->l('contact us here').'</a></li>';
                    $html .= '</ol>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= $this->_displayStep(self::INSTALL_STEP_EU_COOKIE).$this->l('Check your european law module if any');
            if ($cur_step == self::INSTALL_STEP_EU_COOKIE) {
                $html .= '<div class="stepdesc"><ol>';
                $html .= '<li><a href="../?dbgpagecache=1" target="_blank">'.$this->l('Click here to browse your site in test mode').'</a></li>';
                $html .= '<li>'.$this->l('Remove your cookies, reset the cache, then display a page').'</li>';
                $html .= '<li>'.$this->l('You should see the cookie law message; click to hide it').'</li>';
                $html .= '<li>'.$this->l('Reload the page, you should not see the message again').'</li>';
                $html .= '</ol>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_form_install\').submit();return false;">'.$this->l('OK, I validate this step').'</a>';
                $html .= '<a href="#" class="kobtn" onclick="$(\'#help'.self::INSTALL_STEP_EU_COOKIE.'\').toggle();return false;">'.$this->l('No, I\'m having trouble').'</a>';
                $html .= '<div class="stephelp" id="help'.self::INSTALL_STEP_EU_COOKIE.'"><ol>';
                if (strcmp('fr', Language::getIsoById($this->context->language->id)) == 0) {
                    $html .= '<li><a href="'.self::JPRESTA_PROTO.self::JPRESTA_DOMAIN.'.com/fr/blog/le-message-d-information-pour-les-cookies-s-affiche-tout-le-temp-n4" target="_blank">'.$this->l('Read this article').'</a> '.$this->l('to know how to solve this issue');
                } else {
                    $html .= '<li><a href="'.self::JPRESTA_PROTO.self::JPRESTA_DOMAIN.'.com/en/blog/my-cookie-law-banner-module-always-display-n4" target="_blank">'.$this->l('Read this article').'</a> '.$this->l('to know how to solve this issue');
                }
                $html .= '</ol>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= $this->_displayStep(self::INSTALL_STEP_VALIDATE).$this->l('Push in production mode');
            if ($cur_step == self::INSTALL_STEP_VALIDATE) {
                $html .= '<div class="stepdesc"><ol>';
                $html .= '<li><a href="../?dbgpagecache=1" target="_blank">'.$this->l('Clic here to browse your site in test mode').'</a></li>';
                $html .= '<li>'.$this->l('You can do more tests and once your are ready...').'</li>';
                $html .= '</ol>';
                $html .= '<a href="#" class="okbtn" onclick="$(\'#pagecache_form_install\').submit();return false;">'.$this->l('Enable PageCache for my customers!').'</a>';
                $html .= '<a href="#" class="kobtn" onclick="$(\'#help'.self::INSTALL_STEP_VALIDATE.'\').toggle();return false;">'.$this->l('No, I\'m having trouble').'</a>';
                $html .= '<div class="stephelp" id="help'.self::INSTALL_STEP_VALIDATE.'"><ol>';
                $html .= '<li>'.$this->l('Make sure that the problem you have does not occur if you disable PageCache module').'</li>';
                $html .= '<li>'.$this->l('If your problem is only occuring with PageCache enabled, then').' <a href="'.$this->contact_url.'" target="_blank">'.$this->l('contact us here').'</a></li>';
                $html .= '</ol>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '<div class="bootstrap actions">';
            $html .= '<button type="submit" value="1" onclick="$(\'#pagecache_install_step\').val('.self::INSTALL_STEP_BUY_FROM.'); return true;" id="submitModuleRestartInstall" name="submitModuleRestartInstall" class="btn btn-default">
                        <i class="process-icon-cancel" style="color:red"></i> '.$this->l('Restart from first step').'
                    </button>';
            $html .= '<button type="submit" value="1" id="submitModuleClearCache" name="submitModuleClearCache" class="btn btn-default">
                        <i class="process-icon-delete" style="color:orange"></i> '.$this->l('Clear cache').'
                    </button>';
            $html .= '</div></div>';
        } else {
            $html .= '<input type="hidden" name="pagecache_install_step" id="pagecache_install_step" value="'.self::INSTALL_STEP_BACK_TO_TEST.'"/>';
            $html .= '<div class="installstep">'.$this->l('Congratulations!').' '.$this->displayName . ' ' . $this->l('is currently installed in'). ' <b>' . $this->l('production mode').'</b> ';
            if (Configuration::get('pagecache_skiplogged')) {
                $html .= $this->l(' for not logged in users');
            }
            $html .= $this->l(', that means your site is now faster than ever!').'</div>';
            $html .= '<div class="installstep">'.$this->l('If you are having trouble, ').'<a href="#" class="browsebtn" onclick="$(\'#pagecache_form_install\').submit();return false;">'.$this->l('go back to test mode').'</a></div>';
            $html .= '<div class="installstep">'.$this->l('And now, what do I do?');
            $html .= '<ul><li>'.$this->l('Just enjoy the new speed of your store!').'</li>';
            $html .= '<li>'.$this->l('Give us some feedback and').' <a href="'.$this->rating_url.'" target="_blank"><img src="../modules/'.$this->name.'/views/img/rating.png" alt="" style="vertical-align:baseline;padding: 0 0 0 4px;" width="16" height="16"/> '.$this->l('rate the module and write a review').'</a></li>';
            if (strcmp('fr', Language::getIsoById($this->context->language->id)) == 0) {
                $html .= '<li>'.$this->l('Help or get help in ').' <a href="http://www.prestashop.com/forums/topic/280030-module-page-cache-boostez-votre-boutique/" target="_blank"><img src="../modules/'.$this->name.'/views/img/forum.png" alt="" style="vertical-align:baseline;padding: 0 0 0 4px;" width="16" height="16"/> '.$this->l('the PageCache forum thread').'</a></li>';
                $html .= '<li>'.$this->l('You need to know more? Then you can read and comment ').' <a href="'.self::DOC_PROTO.self::DOC_DOMAIN.self::DOC_URL_FR.'" target="_blank"><img src="../modules/'.$this->name.'/views/img/book.png" alt="" style="vertical-align:baseline;padding: 0 0 0 4px;" width="16" height="16"/> '.$this->l('the online documentation').'</a></li>';
            } else {
                $html .= '<li>'.$this->l('Help or get help in ').' <a href="http://www.prestashop.com/forums/topic/281654-module-page-cache-speedup-your-shop/" target="_blank"><img src="../modules/'.$this->name.'/views/img/forum.png" alt="" style="vertical-align:baseline;padding: 0 0 0 4px;" width="16" height="16"/> '.$this->l('the PageCache forum thread').'</a></li>';
                $html .= '<li>'.$this->l('You need to know more? Then you can read and comment ').' <a href="'.self::DOC_PROTO.self::DOC_DOMAIN.self::DOC_URL_EN.'" target="_blank"><img src="../modules/'.$this->name.'/views/img/book.png" alt="" style="vertical-align:baseline;padding: 0 0 0 4px;" width="16" height="16"/> '.$this->l('the online documentation').'</a></li>';
            }
            $html .= '</ul>';
            $html .= '<div class="bootstrap actions">';
            $html .= '<button type="submit" value="1" id="submitModuleClearCache" name="submitModuleClearCache" class="btn btn-default">
                        <i class="process-icon-delete" style="color:orange"></i> '.$this->l('Clear cache').'
                    </button>';
            $html .= '</div></div>';
        }
        $html .= '</div></fieldset></form>';

        //
        // Options
        //
        if ($advanced_mode) {
            $html .= '<form id="pagecache_form_options" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">
            <input type="hidden" name="submitModule" value="true"/>
            <input type="hidden" name="pctab" value="options"/>
            <fieldset id="options" class="pctab" '.(Tools::getValue('pctab') == 'options' ? '' : 'style="display:none"').'>
            <div style="clear: both; padding-top:15px;">
                <label class="conf_title">'.$this->l('No cache for logged in users').'</label>
                <div class="margin-form">
                    <label class="t" for="pagecache_pagecache_skiplogged_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'"></label>
                    <input type="radio" name="pagecache_skiplogged" id="pagecache_pagecache_skiplogged_on" value="1" '. (Configuration::get('pagecache_skiplogged') ? 'checked' : '') .'>
                    <label class="t" for="pagecache_pagecache_skiplogged_on"> '.$this->l('Yes').'</label>
                    <label class="t" for="pagecache_pagecache_skiplogged_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" style="margin-left: 10px;"></label>
                    <input type="radio" name="pagecache_skiplogged" id="pagecache_pagecache_skiplogged_off" value="0" '. (Configuration::get('pagecache_skiplogged') ? '' : 'checked') .'>
                    <label class="t" for="pagecache_pagecache_skiplogged_off"> '.$this->l('No').'</label>
                    <p class="preference_description">'.$this->l('Disable cache for visitors that are logged in').'</p>
                </div>
                <label class="conf_title">'.$this->l('Enable logs').'</label>
                <div class="margin-form">
                    <label class="t" for="pagecache_logs_debug"><img src="../img/admin/enabled.gif" alt="'.$this->l('Debug').'" title="'.$this->l('Debug').'"><img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'"></label>
                    <input type="radio" name="pagecache_logs" id="pagecache_logs_debug" value="2" '. (Configuration::get('pagecache_logs') == 2 ? 'checked' : '') .'>
                    <label class="t" for="pagecache_logs_debug"> '.$this->l('Debug').'</label>
                    <label class="t" for="pagecache_logs_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Info').'" title="'.$this->l('Info').'"></label>
                    <input type="radio" name="pagecache_logs" id="pagecache_logs_on" value="1" '. (Configuration::get('pagecache_logs') == 1 ? 'checked' : '') .'>
                    <label class="t" for="pagecache_logs_on"> '.$this->l('Info').'</label>
                    <label class="t" for="pagecache_logs_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('None').'" title="'.$this->l('None').'" style="margin-left: 10px;"></label>
                    <input type="radio" name="pagecache_logs" id="pagecache_logs_off" value="0" '. (Configuration::get('pagecache_logs') ? '' : 'checked') .'>
                    <label class="t" for="pagecache_logs_off"> '.$this->l('None').'</label>
                    <p class="preference_description">'.$this->l('Logs informations into the Prestashop logger. You should only enable it to debug or understand how the cache works.').'</p>
                </div>
                <label class="conf_title">'.$this->l('Ignored URL parameters').'</label>
                <div class="margin-form">
                    <input type="text" name="pagecache_ignored_params" id="pagecache_ignored_params" value="'. Configuration::get('pagecache_ignored_params') .'" size="100">
                    <p class="preference_description">'.$this->l('URL parameters are used to identify a unique page content. Some URL parameters do not affect page content like tracking parameters for analytics (utm_source, utm_campaign, etc.) so we can ignore them. You can set a comma separated list of these parameters here.').'</p>
                </div>
                <label class="conf_title">'.$this->l('Always display infos box').'</label>
                <div class="margin-form">
                    <label class="t" for="pagecache_pagecache_always_infosbox_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'"></label>
                    <input type="radio" name="pagecache_always_infosbox" id="pagecache_pagecache_always_infosbox_on" value="1" '. (Configuration::get('pagecache_always_infosbox') ? 'checked' : '') .'>
                    <label class="t" for="pagecache_pagecache_always_infosbox_on"> '.$this->l('Yes').'</label>
                    <label class="t" for="pagecache_pagecache_always_infosbox_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" style="margin-left: 10px;"></label>
                    <input type="radio" name="pagecache_always_infosbox" id="pagecache_pagecache_always_infosbox_off" value="0" '. (Configuration::get('pagecache_always_infosbox') ? '' : 'checked') .'>
                    <label class="t" for="pagecache_pagecache_always_infosbox_off"> '.$this->l('No').'</label>
                    <p class="preference_description">'.$this->l('Only used for demo').'</p>
                </div>
            </div>
            <div class="bootstrap">
                <button type="submit" value="1" id="submitModuleOptions" name="submitModuleOptions" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> '.$this->l('Save').'
                </button>
            </div>
            </fieldset></form>';
        }

        //
        // Pages and timeouts
        //
        $html .= '<form id="pagecache_form_timeouts" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">
            <input type="hidden" name="submitModule" value="true"/>
            <input type="hidden" name="pctab" value="timeouts"/>';
        $html .= '<fieldset id="timeouts" class="pctab" '.(Tools::getValue('pctab') == 'timeouts' ? '' : 'style="display:none"').'><div style="clear: both; padding-top:15px;">';
        foreach (self::$managed_controllers as $controller) {
            switch ($controller) {
                case 'index':$controler_title = $this->l('Home page');break;
                case 'category':$controler_title = $this->l('Category page');break;
                case 'product':$controler_title = $this->l('Product page');break;
                case 'cms':$controler_title = $this->l('CMS page');break;
                case 'newproducts':$controler_title = $this->l('New products page');break;
                case 'bestsales':$controler_title = $this->l('Best sales page');break;
                case 'supplier':$controler_title = $this->l('Suppliers page');break;
                case 'manufacturer':$controler_title = $this->l('Manufacturers page');break;
                case 'contact':$controler_title = $this->l('Contact form page');break;
                case 'pricesdrop':$controler_title = $this->l('Prices drop page');break;
                case 'sitemap':$controler_title = $this->l('Sitemap page');break;
                default:$controler_title = $this->l('Page for controller '+$controller);break;
            }
            $html .= '
                    <label class="conf_title">'.$controler_title.'</label>
                    <div class="margin-form">
                        <label class="t" for="pagecache_'.$controller.'_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'"></label>
                        <input type="radio" name="pagecache_'.$controller.'" id="pagecache_'.$controller.'_on" value="1" '. (Configuration::get('pagecache_'.$controller) ? 'checked' : '') .' onclick="$(\'.pagecache_'.$controller.'_input\').removeAttr(\'disabled\'); return true;">
                        <label class="t" for="pagecache_'.$controller.'_on"> '.$this->l('Yes').'</label>
                        <label class="t" for="pagecache_'.$controller.'_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" style="margin-left: 10px;"></label>
                        <input type="radio" name="pagecache_'.$controller.'" id="pagecache_'.$controller.'_off" value="0" '. (Configuration::get('pagecache_'.$controller) ? '' : 'checked') .' onclick="$(\'.pagecache_'.$controller.'_input\').attr(\'disabled\', \'true\'); return true;">
                        <label class="t" for="pagecache_'.$controller.'_off"> '.$this->l('No').'</label>
                        <p class="preference_description">'.$this->l('Enable or disable page cache on this page').'</p>
                    </div>

                    <div class="margin-form">
                        '.$this->l('Server cache maximum duration').': <input type="text" size="5" '.(Configuration::get('pagecache_'.$controller) ? '' : 'disabled').' class="pagecache_'.$controller.'_input" name="pagecache_'.$controller.'_timeout" id="pagecache_'.$controller.'_timeout" value="'.(Tools::safeOutput(Configuration::get('pagecache_'.$controller.'_timeout'))).'" />&nbsp;'.$this->l(' minutes').'
                        <p class="preference_description">'.$this->l('Set the timeout of the cache on the server; after this delay the cache will expired and the page will be refreshed on next access. Set to -1 if you want unlimited time.').'</p>
                    </div>

                    <div class="margin-form">
                        '.$this->l('Browser cache duration').': <input type="text" size="5" '.(Configuration::get('pagecache_'.$controller) ? '' : 'disabled').' class="pagecache_'.$controller.'_input" name="pagecache_'.$controller.'_expires" id="pagecache_'.$controller.'_expires" value="'.(Tools::safeOutput(Configuration::get('pagecache_'.$controller.'_expires'))).'" />&nbsp;'.$this->l(' minutes').'
                        <p class="preference_description">'.$this->l('Set the duration of the cache into the user browser. That means the page will not be reffreshed by the browser during this time so when the user go back to a page he has already seen it is extremly fast! This value is limited 60 minutes maximum.').'</p>
                    </div>

                    <hr style="border-bottom: 1px solid gray;margin: 0 0 15px 0;"/>
            ';
        }
        $html .= '</div>
                  <div class="bootstrap">
                    <button type="submit" value="1" id="submitModuleTimeouts" name="submitModuleTimeouts" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i> '.$this->l('Save').'
                    </button>
                  </div>
                </fieldset></form>';

        //
        // Dynamic hooks (only in test mode)
        //
        $module_list = Hook::getHookModuleExecList();
        $standardHooks = array(
            'displaytopcolumn', 'displaytop', 'displayrightcolumnproduct', 'displayrightcolumn', 'displayproducttabcontent', 'displayproducttab', 'displayproductbuttons',
            'displaynav', 'displayleftcolumnproduct', 'displayleftcolumn', 'displayhometabcontent', 'displayhometab', 'displayhome', 'displayfooterproduct',
            'displayfooter', 'displaybanner', 'actionproductoutofstock'
            );
        $standardHooks = array_flip($standardHooks);

        $html .= '<form id="pagecache_form_dynhooks" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">
            <input type="hidden" name="submitModule" value="true"/>
            <input type="hidden" name="pctab" value="dynhooks"/>';
        $html .= '<fieldset class="dynhooks pctab" id="dynhooks" '.(Tools::getValue('pctab') == 'dynhooks' ? '' : 'style="display:none"').'>';
        $dyn_is_disabled = "";
        if (!Configuration::get('pagecache_debug')) {
            $dyn_is_disabled = ' disabled="true" ';
            if (Tools::version_compare(_PS_VERSION_,'1.6','>=')) {
                $html .= '<div class="bootstrap"><div class="alert alert-warning" style="display: block;">&nbsp;'.$this->l('To be able to modify dynamic modules you must go back in "test mode" in first tab').'</div></div>';
            } else {
                $html .= '<div class="warn" style="display: block;">&nbsp;'.$this->l('To be able to modify dynamic modules you must go back in "test mode" in first tab').'</div>';
            }
        }
        $html .= '<p>'.$this->l('Some modules need to be loaded dynamically like blockmyaccount that will display only if a customer is logged in. Blockviewed is another example and can be marked as dynamic. If you enabled ajax in blockcart then do not make it dynamic.').'</p>';
        if (Tools::version_compare(_PS_VERSION_,'1.6','>=')) {
            $html .= '<div class="bootstrap"><div class="alert alert-info" style="display: block;">&nbsp;'.$this->l('Note that dynamic module Ajax call are done all at once (one HTTP request)').'</div></div>';
        } else {
            $html .= '<div class="hint clear" style="display: block;">&nbsp;'.$this->l('Note that dynamic module Ajax call are done all at once (one HTTP request)').'</div>';
        }
        $html .= '<p><label class="t">'.$this->l('Display all hooks').': <input type="checkbox" onclick="$(\'.morehook\').toggle()" name="displayall"/></label></p>
        ';
        $dynHooks = PageCache::getDynamicHooks();
        $indexRow = 0;
        foreach ($module_list as $hook_name => $modules) {
            $hideOrShow = '';
            if (!array_key_exists($hook_name, $standardHooks)) {
                $hideOrShow = ' class="morehook"';
            }
            $html .= '<div style="clear: both;"'.$hideOrShow.'>
                    <label class="conf_title">'.$hook_name.'</label>
                    <div class="margin-form">';
            foreach ($modules as $module) {
                if (strcmp($this->name, $module['module']) != 0) {
                    $dyn_is_checked = '';
                    $empty_option_checked = '';
                    $empty_option_display = 'style="display:none"';
                    if (isset($dynHooks[$hook_name]) && isset($dynHooks[$hook_name][$module['module']])) {
                        $dyn_is_checked = 'checked';
                        $empty_option_display = '';
                        if ($dynHooks[$hook_name][$module['module']]['empty_box']) {
                            $empty_option_checked = 'checked';
                        }
                    }
                    if (isset($instances[$module['id_module']])) {
                        $html .= '<input '.$dyn_is_checked.' '.$dyn_is_disabled.' type="checkbox" name="pagecache_hooks[]" id="dyn'.$indexRow.'" value="'.$hook_name.'|'.$module['module'].'" onclick="$(\'#emptyspan'.$indexRow.'\').toggle();"/>
                        <label class="t" for="dyn'.$indexRow.'" title="'.$instances[$module['id_module']]->description.'"><img src="../modules/'.$module['module'].'/logo.gif" width="16" height="16" alt=""/>'.$instances[$module['id_module']]->displayName.'</label>
                        <span '.$empty_option_display.' id="emptyspan'.$indexRow.'"><input '.$dyn_is_disabled.' type="checkbox" '.$empty_option_checked.' name="pagecache_hooks_empty_'.$hook_name.'_'.$module['module'].'" id="emptyoption'.$indexRow.'" value="1"/>&nbsp;<label class="t" for="emptyoption'.$indexRow.'">' . $this->l('First, display an empty box') . '</label></span>
                        <br/>';
                    } else {
                        $html .= '<input '.$dyn_is_checked.' '.$dyn_is_disabled.' type="checkbox" name="pagecache_hooks[]" id="dyn'.$indexRow.'" value="'.$hook_name.'|'.$module['module'].'"/>
                        <label class="t" for="dyn'.$indexRow.'"><img src="../modules/'.$module['module'].'/logo.gif" width="16" height="16" alt=""/>'.$module['module'].'</label>
                        <span '.$empty_option_display.' id="emptyspan'.$indexRow.'"><input '.$dyn_is_disabled.' type="checkbox" '.$empty_option_checked.' name="pagecache_hooks_empty_'.$hook_name.'_'.$module['module'].'" id="emptyoption'.$indexRow.'" value="1"/>&nbsp;<label class="t" for="emptyoption'.$indexRow.'">' . $this->l('First, display an empty box') . '</label></span>
                        <br/>';
                    }
                }
                $indexRow++;
            }
            $html .= '</div></div>';
        }
        $html .= '<br/><hr/><h3 id="tabdynhooksjs">' . $this->l('Javascript to execute') .'</h3>
            <div id="cfgadvanced">
                <p>' . $this->l('Here you can modify javascript code that is executed after dynamic modules have been displayed on the page.') . '</p>
                <p>' . $this->l('If you meet problems with your theme, ask your theme designer what javascript you should add here.') . '</p>
                <textarea '.$dyn_is_disabled.' name="cfgadvancedjs" style="width:95%" rows="20">'.Configuration::get('pagecache_cfgadvancedjs').'</textarea>
            </div>';

        $html .= '<div class="bootstrap">
                <button '.$dyn_is_disabled.' type="submit" value="1" id="submitModuleDynhooks" name="submitModuleDynhooks" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> '.$this->l('Save').'
                </button>
              </div>
            </fieldset></form>';

        //
        // Statistics
        //
        $html .= '<form id="pagecache_form_stats" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">';
        $html .= '<fieldset class="cachemanagement pctab" id="stats" '.(Tools::getValue('pctab') == 'stats' ? '' : 'style="display:none"').'>';
        $html .= '<input type="hidden" name="submitModule" value="true"/>';
        $html .= '<input type="hidden" name="pctab" value="stats"/>';
        if (Tools::version_compare(_PS_VERSION_,'1.6','>=')) {
            $html .= '<div class="bootstrap"><div class="alert alert-info" style="display: block;">&nbsp;'.$this->l('This table shows you the 100 most viewed pages. You can see how many times the cache is used (hit) and how many times the cache is built (missed).').'</div></div>';
        } else {
            $html .= '<div class="hint clear" style="display: block;">&nbsp;'.$this->l('This table shows you the 100 most viewed pages. You can see how many times the cache is used (hit) and how many times the cache is built (missed).').'</div>';
        }
        $html .= '<div class="bootstrap">
                <button type="submit" value="1" id="submitModuleOnOffStats" name="submitModuleOnOffStats" class="btn btn-default">
                    <i class="process-icon-off" style="color:'.(Configuration::get('pagecache_stats') ? 'red' : 'rgb(139, 201, 84)').'"></i> '.(Configuration::get('pagecache_stats') ? $this->l('Disable statistics') : $this->l('Enable statistics')).'
                </button>';
        if (Configuration::get('pagecache_stats')) {
            $html .= '<button type="submit" value="1" id="submitModuleResetStats" name="submitModuleResetStats" class="btn btn-default">
                    <i class="process-icon-delete" style="color:orange"></i> '.$this->l('Clear cache and reset statistics').'
                </button>';
        }
        $html .= '</div>';
        if (Configuration::get('pagecache_stats')) {
            $stats = PageCacheDAO::getAllStats(Shop::getContextListShopID());
            $html .= '<div style="clear: both; padding-top:15px;"><table cellspacing="0" cellspadding="0"><tr><th width="40%" title="'.$this->l('Click on the link to open the page in a new window').'">'.$this->l('Page').'</th><th width="20%" title="'.$this->l('Cache has been used, this is good').'">'.$this->l('Hit').'</th><th width="20%" title="'.$this->l('Cache has not been used, this is bad').'">'.$this->l('Missed').'</th><th width="20%" title="'.$this->l('The higher the value, the better it is.').'">'.$this->l('Percent hit').'</th></tr>';
            if (count($stats) > 0) {
                foreach ($stats as $stat) {
                    $html .= '<tr><td><a href="' . $stat['url'] . '" target="_blank" title="'.$stat['url'].'">'.$stat['url'].'</a></td><td>'.$stat['hit'].'</td><td>'.$stat['missed'].'</td><td>'.number_format($stat['percent'], 1).'</td></tr>';
                }
            } else {
                $html .= '<tr><td colspan="4"><i>'.$this->l('No statistics available yet').'</i></td></tr>';
            }
            $html .= '</table></div>';
        }
        $html .= '</fieldset></form>';

        //
        // CRON
        //
        $html .= '<form id="pagecache_form_stats" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">';
        $html .= '<fieldset class="cron pctab" id="cron" '.(Tools::getValue('pctab') == 'cron' ? '' : 'style="display:none"').'>';
        $html .= '<input type="hidden" name="submitModule" value="true"/>';
        $html .= '<input type="hidden" name="pctab" value="cron"/>';
        if (Tools::version_compare(_PS_VERSION_,'1.6','>=')) {
            $html .= '<div class="bootstrap"><div class="alert alert-info" style="display: block;">&nbsp;'.$this->l('CRON jobs are scheduled tasks. Here you will find URLs that will allow you to refresh cache in scheduled tasks.').'</div></div>';
        } else {
            $html .= '<div class="hint clear" style="display: block;">&nbsp;'.$this->l('CRON jobs are scheduled tasks. Here you will find URLs that will allow you to refresh cache in scheduled tasks.').'</div>';
        }
        $html .= '<p>'.$this->l('People who want to clear cache with a CRON job can use the following URLs (one per shop, returns 200 if OK, 404 if there is an error): ').$this->getCronClearCacheURL().'</p>';
        $html .= '<p class="preference_description">'.$this->l('To refresh cache of a specific product add "&product=<product\'s ids separated by commas>", for a category add "&category=<category\'s ids separated by commas>", for home page add "&index", etc.').'</p>';
        $html .= '</fieldset></form>';

        //
        // Advanced cache management
        //
        if ($advanced_mode) {
            $distinct_module_list = array();
            foreach ($module_list as $hook_name => $modules) {
                foreach ($modules as $module) {
                    $distinct_module_list[$module['module']] = $module['id_module'];
                }
            }
            $options_array = array();
            $options = '<option value="">'.$this->l('Select to add an impacted module').'</option>';
            foreach ($distinct_module_list as $module => $id_module) {
                if (strcmp($this->name, $module) != 0) {
                    if (isset($instances[$id_module])) {
                        $options_array[Tools::strtolower(Tools::replaceAccentedChars($instances[$id_module]->displayName))] = '<option value="'.$module.'">'.$instances[$id_module]->displayName.'</option>';
                    } else {
                        $options_array[$module] = '<option value="'.$module.'">'.$module.'</option>';
                    }
                }
            }
            ksort($options_array);
            foreach ($options_array as $option) {
                $options .= $option;
            }
            $html .= '<script type="text/javascript">';
            $html .= 'function addModule(tr, trigered_event) {';
            $html .= 	'if (tr.find("select").get(0).selectedIndex == 0) {alert("'.$this->l('Please select a module before').'");return;};';
            $html .= 	'if ($("#"+tr.attr("id")+"_mods").val().indexOf(" "+tr.find("select").val()+" ") != -1) {alert("'.$this->l('This module is already in the list').'");return;};';
            $html .= 	'$("#"+tr.attr("id")+"_mods").val($("#"+tr.attr("id")+"_mods").val()+" "+tr.find("select").val()+" ");';
            $html .= 	'tr.find(".tags").append(\'<span class="tag"><img src="../modules/\'+tr.find("select").val()+\'/logo.gif" width="16" height="16" alt=""/>\'+tr.find("select option:selected").text()+\'<a href="#" onclick="removeModule($(this).parent(), \\\'\'+tr.find("select").val()+\'\\\', \\\'\'+trigered_event+\'\\\');return false"><img style="margin:0 0 0 3px" src="../img/admin/forbbiden.gif" alt=""/></a></span>\')';
            $html .= '}';
            $html .= 'function removeModule(tag, module, trigered_event) {';
            $html .= 	'$("#"+trigered_event+"_mods").val($("#"+trigered_event+"_mods").val().replace(" "+module+" ", ""));';
            $html .= 	'tag.fadeOut();';
            $html .= '}';
            $html .= '</script>';

            $html .= '<form id="pagecache_form_cachemanagement" action="'.Tools::htmlentitiesutf8(self::getServerValue('REQUEST_URI')).'" method="post">';
            $html .= '<fieldset class="cachemanagement pctab" id="cachemanagement" '.(Tools::getValue('pctab') == 'cachemanagement' ? '' : 'style="display:none"').'>';
            $html .= '<input type="hidden" name="submitModule" value="true"/>';
            $html .= '<input type="hidden" name="pctab" value="cachemanagement"/>';
            $html .= '<p>'.$this->l('Here you can customize how the cache will be refreshed when you do modifications in the backoffice.').'</p>';
            $html .= '<p>'.$this->l('Prestashop triggers events when you create, modify or delete something (a product, a category, a CMS page, etc.). In this table you can define which module must be updated on each event; for example the CMS block will be impacted "on new CMS" event. This will cause all pages with CMS block to be refreshed each time you create a new CMS page.').'</p>';
            $html .= '<p>'.$this->l('An other possibility is to refresh only pages that have a link on the modified or deleted object. For example, when you modify a product price, if some modules display a resume of this product on some pages these will be refreshed because they have a link to this product.').'</p>';
            $html .= '<div style="clear: both; padding-top:15px;">
                    <table cellspacing="0" cellspadding="0"><tr><th width="15%">'.$this->l('Event').'</th><th width="20%">'.$this->l('Impacts pages that link to it').'</th><th width="50%">'.$this->l('Impacted modules').'</th><th width="20%"></th></tr>';
            foreach($trigered_events as $key => $trigered_event) {
                $impacted_modules = Configuration::get($key.'_mods');
                $html .= '<tr id="'.$key.'">
                    <td><span title="'.$trigered_event['desc'].'">'.$trigered_event['title'].'</span><input type="hidden" name="'.$key.'_mods" id="'.$key.'_mods" value="'.$impacted_modules.'"/></td>
                    <td style="text-align:center">';
                if ($trigered_event['bl']) {
                    $html .= '<input type="checkbox" name="'.$key.'_bl" id="'.$key.'_bl" value="1" '. (Configuration::get($key.'_bl') ? 'checked' : '') .'></td>';
                }
                $html .= '<td class="tags">';
                    if ($impacted_modules) {
                        $impacted_modules = explode(' ', $impacted_modules);
                        foreach ($impacted_modules as $impacted_module) {
                            $impacted_module = trim($impacted_module);
                            if (Tools::strlen($impacted_module) > 0) {
                                if (isset($distinct_module_list[$impacted_module]) && isset($instances[$distinct_module_list[$impacted_module]])) {
                                    $html .= '<span class="tag"><img src="../modules/'.$impacted_module.'/logo.gif" width="16" height="16" alt=""/>'.$instances[$distinct_module_list[$impacted_module]]->displayName.'<a href="#" onclick="removeModule($(this).parent(), \''.$impacted_module.'\', \''.$key.'\');return false"><img style="margin:0 0 0 3px" src="../img/admin/forbbiden.gif" alt=""/></a></span>';
                                } else {
                                    $html .= '<span class="tag"><img src="../modules/'.$impacted_module.'/logo.gif" width="16" height="16" alt=""/>'.$impacted_module.'<a href="#" onclick="removeModule($(this).parent(), \''.$impacted_module.'\', \''.$key.'\');return false"><img style="margin:0 0 0 3px" src="../img/admin/forbbiden.gif" alt=""/></a></span>';
                                }
                            }
                        }
                    }
                    $html .= '</td>
                    <td style="white-space:nowrap"><select>'.$options.'</select><a href="#" onclick="addModule($(this).parent().parent(), \''.$key.'\');return false"><img style="margin:3px" src="../img/admin/add.gif" alt=""/></a></td>
                </tr>';
            }
            $html .= '</table></div>';
            $html .= '<br/><br/><div class="bootstrap">
                    <button type="submit" value="1" id="submitModuleCacheManagement" name="submitModuleCacheManagement" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i> '.$this->l('Save').'
                    </button>
                  </div></fieldset></form>';
        }
        $html .="</div>";

        return $html;
    }

    public function hookDisplayHeader() {
        if (PageCache::canBeCached() || self::isDisplayStats()) {
            // A bug in PS 1.6.0.6 insert jquery multiple times in CCC mode
            $already_inserted = false;
            $already_inserted_cooki = false;
            $already_inserted_cookie = false;
            foreach ($this->context->controller->js_files as $js_uri)
            {
                $already_inserted = $already_inserted || (strstr($js_uri, 'jquery-') !== false) || (strstr($js_uri, 'jquery.js') !== false);
                $already_inserted_cooki = $already_inserted_cooki || (strstr($js_uri, 'cooki-plugin') !== false);
                $already_inserted_cookie = $already_inserted_cookie || (strstr($js_uri, 'cookie-plugin') !== false);
            }
            if (!$already_inserted) {
                $this->context->controller->addJquery();
            }
            if (!$already_inserted_cooki) {
                $this->context->controller->addJqueryPlugin('cooki-plugin');
            }
            if (!$already_inserted_cookie) {
                $this->context->controller->addJqueryPlugin('cookie-plugin');
            }

            $this->context->controller->addJS($this->_path.'views/js/pagecache.js');
            $this->context->controller->addCSS($this->_path.'views/css/pagecache.css');

            // Make sure pagecache will be the first javascript to be loaded. This avoid
            // other javascript errors to block pagecache treatments. So we place it just after
            // jquery.
            $new_js_files = array();
            $pagecache_js_file = null;
            $jquery_js_files = array();
            foreach ($this->context->controller->js_files as $js_file) {
                if (strstr($js_file, '/js/jquery/') !== false || strstr($js_file, 'jquery.js') !== false) {
                    $jquery_js_files[] = $js_file;
                }
                else if (empty($pagecache_js_file) && strstr($js_file, 'pagecache.js') !== false) {
                    $pagecache_js_file = $js_file;
                } else {
                    $new_js_files[] = $js_file;
                }
            }
            if (!empty($pagecache_js_file)) {
                array_unshift($new_js_files, $pagecache_js_file);
            }
            $jquery_js_files = array_reverse($jquery_js_files);
            foreach ($jquery_js_files as $jquery_js_file) {
                array_unshift($new_js_files, $jquery_js_file);
            }
            $this->context->controller->js_files = $new_js_files;

            // Old footer, now in header for better compatibility
            $js = trim(Configuration::get('pagecache_cfgadvancedjs'));
            if (!empty($js)) {
                $this->smarty->assign(array(
                        'cfgadvancedjs' => $js
                ));
            }
            return $this->display(__FILE__, 'pagecache.tpl');
        }
        else if (Configuration::get('pagecache_skiplogged') && Context::getContext()->customer->isLogged()) {
            // User want to disable cache for logged in users so we add a random URL parameter
            // to all links to disable previous cache done by browser
            return $this->display(__FILE__, 'pagecache-disablecache.tpl');
        } else {
            return '';
        }
    }

    public function hookdisplayMobileHeader() {
        $this->hookDisplayHeader();
    }

    public function hookActionShopDataDuplication($params) {
        //(int)$params['new_id_shop']
        //(int)$params['old_id_shop']
        $new_id_shop = (int)$params['new_id_shop'];
        $this->_setDefaultConfiguration(Shop::getGroupFromShop($new_id_shop), $new_id_shop);
    }

    public function hookActionDispatcher() {
        if (PageCache::canBeCached())
        {
            // Remove cookie, cart and customer informations to cache
            // a 'standard' page

            // Write cookie if needed (language changed, etc.) before we remove it
            $this->context->cookie->write();

            $new_cookie = new Cookie('pc'.rand(), '', 1);
            $new_cookie->id_lang = $this->context->language->id;
            if (!isset($this->context->cookie->detect_language)) {
                unset($new_cookie->detect_language);
            }
            $new_cookie->id_currency = $this->context->cookie->id_currency;
            $new_cookie->no_mobile = $this->context->cookie->no_mobile;
            if (isset($this->context->cookie->iso_code_country)) {
                $new_cookie->iso_code_country = $this->context->cookie->iso_code_country;
            }

            if (isset($this->context->customer)) {
                $id_customer = (int)$this->context->customer->id;
                $new_cookie->pc_groups = implode(',', Customer::getGroupsStatic($id_customer));
                if ($id_customer === 0) {
                    $new_cookie->pc_group_default = (int)Configuration::get('PS_UNIDENTIFIED_GROUP');
                }
                else {
                    $new_cookie->pc_group_default = Customer::getDefaultGroupId($id_customer);
                }
                $new_cookie->pc_is_logged = $this->context->customer->isLogged();
                $new_cookie->pc_is_logged_guest = $this->context->customer->isLogged(true);
            }

            $country = self::getCountry($this->context);
            if ($country) {
                $this->context->country = $country;
                // Save it for pagecache so pseudo url can be the same before and after
                $new_cookie->pc_id_country = $country->id;
            }

            $this->context->cookie = $new_cookie;
            $this->context->cart = new Cart();
            $this->context->customer = new Customer();
            // Needed because some modules do Validate the id (Validate::isUnsignedId($id_customer))
            $this->context->customer->id = 0;
            // Needed for product specific price calculation
            if (isset($new_cookie->pc_group_default)) {
                $this->context->customer->id_default_group = (int) $new_cookie->pc_group_default;
            } else {
                $this->context->customer->id_default_group = (int) Configuration::get('PS_CUSTOMER_GROUP');
            }
        }
        else if (self::isDisplayStats()) {
            // Also needed to display stats when cache is disabled (dbgpagecache=0)
            // Save it for pagecache so pseudo url can be the same before and after
            $country = self::getCountry($this->context);
            if ($country) {
                $this->context->cookie->pc_id_country = $country->id;
            }
        }
    }

    public static function getDynamicHooks() {
        $hooksModules = array();
        $dyn_hooks = Configuration::get('pagecache_dyn_hooks', '');
        $hooks_modules = explode(',', $dyn_hooks);
        foreach ($hooks_modules as $hook_module) {
            if (!empty($hook_module))
            {
                list($hook, $module, $empty_box) = array_pad(explode('|', $hook_module), 3, 0);
                if (!isset($hooksModules[$hook])) {
                    $hooksModules[$hook] = array();
                }
                $hooksModules[$hook][$module] = array('empty_box' => $empty_box);
            }
        }
        return $hooksModules;
    }

    public static function isDynamicHooks($hook_name, $module) {
        $dyn_hooks = Configuration::get('pagecache_dyn_hooks', '');
        return strstr($dyn_hooks, Tools::strtolower($hook_name) . '|' . $module) !== false;
    }

    public static function getDynamicHookInfos($hook_name, $module) {
        if (!self::canBeCached()) {
            return false;
        }
        $dyn_hooks = Configuration::get('pagecache_dyn_hooks', '');
        $dyn_hook_part = strstr($dyn_hooks, Tools::strtolower($hook_name) . '|' . $module);
        if ($dyn_hook_part !== false) {
            $comma_pos = strpos($dyn_hook_part, ',');
            if ($comma_pos !== false) {
                $dyn_hook_part =  Tools::substr($dyn_hook_part, 0, $comma_pos);
            }
            $dyn_hook_part_array = array_pad(explode('|', $dyn_hook_part), 3, 0);
            $dyn_hook_part = array('empty_box' => $dyn_hook_part_array[2]);
        }
        return $dyn_hook_part;
    }

    public static function canBeCached() {
        if (Tools::getIsset('ajax') || Tools::getValue('fc') == 'module') {
            return false;
        }
        if (!Configuration::get('pagecache_debug') && !Configuration::get('pagecache_always_infosbox') && (Tools::getIsset('dbgpagecache') || Tools::getIsset('delpagecache'))) {
            // Remove module's parameters in production mode to avoid them to be referenced in search engines
            $url = self::getCurrentURL();
            $url = preg_replace('/&?dbgpagecache=[0-1]?/', '', $url);
            $url = preg_replace('/&?delpagecache=[0-1]?/', '', $url);
            $url = str_replace('?&', '?', $url);
            $url = preg_replace('/\?$/', '', $url);
            header('Status: 301 Moved Permanently', false, 301);
            Tools::redirect($url);
        }
        $controller = Dispatcher::getInstance()->getController();
        $canBeCached = strcmp(self::getServerValue('REQUEST_METHOD'), 'GET') == 0
            && (Configuration::get('pagecache_'.$controller))
            && !self::isGoingToBeRedirected()
            && !self::isCustomizedProduct($controller)
            && (!Configuration::get('pagecache_debug') || ((int)Tools::getValue('dbgpagecache', 0) == 1))
            && ((int)(Configuration::get('PS_TOKEN_ENABLE')) != 1)
            && self::isOverridesEnabled()
            // Following are exceptions for logout action
            && Tools::getValue('logout') === false && Tools::getValue('mylogout') === false
            && (!Configuration::get('pagecache_skiplogged') || !Context::getContext()->customer->isLogged())
        ;
        return $canBeCached;
    }

    /**
     * Customization is not a module and therefore cannot be refreshed. The workaround is to disable
     * cache for these products
     * @param string $controller Controller name
     * @return boolean true if current page is a customized product
     */
    private static function isCustomizedProduct($controller) {
        if (strcmp($controller, 'product') != 0 || !Customization::isFeatureActive()) {
            return false;
        }
        if ($id_product = (int)Tools::getValue('id_product')) {
            $result = Db::getInstance()->executeS('
                SELECT `id_customization_field`, `type`, `required`
                FROM `'._DB_PREFIX_.'customization_field`
                WHERE `id_product` = '.$id_product);
            return count($result) > 0;
        }
        return false;
    }

    /**
     * Do not cache if status code is not 200
     * @return boolean true if user will be redirected to an other page or if statuts is not 200
     */
    private static function isGoingToBeRedirected() {
        if (function_exists('http_response_code')) {
            $code = http_response_code();
            if (!empty($code)) {
                if (http_response_code() !== 200) {
                    return true;
                }
            }
        }
        if (self::isSSLRedirected() || self::isMaintenanceEnabled() || self::isRestrictedCountry()) {
            return true;
        }
        return false;
    }

    private static function isSSLRedirected() {
        return (Configuration::get('PS_SSL_ENABLED') && self::getServerValue('REQUEST_METHOD') != 'POST' && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') != Tools::usingSecureMode());
    }

    private static function isMaintenanceEnabled() {
        if (!(int)Configuration::get('PS_SHOP_ENABLE')) {
            if (!in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')))) {
                return true;
            }
        }
        return false;
    }

    private static function isRestrictedCountry() {
        $controller_instance = self::getControllerInstance();
        return $controller_instance->isRestrictedCountry();
    }

    private static function isOverridesEnabled() {
        return Tools::version_compare(_PS_VERSION_,'1.6','<') || ((int)(Configuration::get('PS_DISABLE_OVERRIDES')) != 1);
    }

    /**
     * return filepath to the cache if it is available, false otherwise
     */
    public static function getCacheFile() {
        $cache = false;
        $can_be_cached = PageCache::canBeCached();
        if ($can_be_cached) {
            // Before checking cache, lets check cache reffreshment triggers (specific prices)
            PageCacheDAO::triggerReffreshment();

            // Check if CSS or JS have been changed
            self::clearCacheIfMediaChanged();

            $controller = Dispatcher::getInstance()->getController();
            $cache_life = 60 * ((int)Configuration::get('pagecache_'.$controller.'_timeout'));
            $cache_file = PageCache::_getCacheFilepath();

            if (Tools::getIsset('delpagecache') && file_exists($cache_file)) {
                unlink($cache_file);
            }

            $pseudo_uri = self::getPseudoRequestURI();
            $filemtime = @filemtime($cache_file);
            if ($filemtime && ($cache_life < 0 or (microtime(true) - $filemtime < $cache_life))) {
                if (Configuration::get('pagecache_stats')) {
                    PageCacheDAO::incrementCountHit($pseudo_uri);
                }
                $cache = $cache_file;
            }

            // Store cache used in a readable cookie (0=no cache; 1=server cache; 2=browser cache)
            if (self::isDisplayStats()) {
                $cache_type = 0; // no cache available
                if ($cache) {
                    // Server cache
                    $cache_type = 1;
                }
                if (PHP_VERSION_ID <= 50200) /* PHP version > 5.2.0 */
                    setcookie('pc_type_' . md5($pseudo_uri), $cache_type, time()+60*60*1, '/', null, 0);
                else
                    setcookie('pc_type_' . md5($pseudo_uri), $cache_type, time()+60*60*1, '/', null, 0, false);
            }
        }
        else if (self::isDisplayStats()) {
            // Cache disabled
            $pseudo_uri = self::getPseudoRequestURI();
            if (PHP_VERSION_ID <= 50200) /* PHP version > 5.2.0 */
                setcookie('pc_type_' . md5($pseudo_uri), 3, time()+60*60*1, '/', null, 0);
            else
                setcookie('pc_type_' . md5($pseudo_uri), 3, time()+60*60*1, '/', null, 0, false);
        }
        if (Configuration::get('pagecache_logs') > 1) {
            // Log debug
            $controller = Dispatcher::getInstance()->getController();
            $is_ajax = Tools::getIsset('ajax') ? 'true' : 'false';
            $is_get = strcmp(self::getServerValue('REQUEST_METHOD'), 'GET') == 0 ? 'true' : 'false';
            $ctrl_enabled = Configuration::get('pagecache_'.$controller) ? 'true' : 'false';
            $is_debug = Configuration::get('pagecache_debug') ? 'true' : 'false';
            $token_ok = (int)(Configuration::get('PS_TOKEN_ENABLE')) != 1 ? 'true' : 'false';
            $is_logout = Tools::getValue('logout') === false && Tools::getValue('mylogout') === false ? 'false' : 'true';
            $can_be_cached = $can_be_cached ? 'true' : 'false';
            $cache_life = 60 * ((int)Configuration::get('pagecache_'.$controller.'_timeout'));
            $cache_file = PageCache::_getCacheFilepath();
            $exists = file_exists($cache_file) ? 'true' : 'false';
            $date_infos = '';
            if (file_exists($cache_file)) {
                $now = date("d/m/Y H:i:s", microtime(true));
                $last_date = date("d/m/Y H:i:s", filemtime($cache_file));
                $date_infos = "now=$now file=$last_date";
            }
            Logger::addLog("PageCache | cache | !is_ajax($is_ajax) && is_get($is_get) && ctrl_enabled($ctrl_enabled) ".
                "&& !is_debug($is_debug) && token_ok($token_ok) && !is_logout($is_logout) = $can_be_cached ".
                "controller=$controller cache_life=$cache_life cache_file=$cache_file exists=$exists $date_infos", 1, null, null, null, true);
        }
        return $cache;
    }

    /**
     * Clear the cache if CSS or JS changed
     */
    public static function clearCacheIfMediaChanged() {
        $changed = false;
        $version_css = (int)Configuration::get('PS_CCCCSS_VERSION');
        $version_css_pc = (int)Configuration::get('pagecache_CCCCSS_VERSION');
        $changed = $version_css !== $version_css_pc;
        if (!$changed) {
            $version_js = (int)Configuration::get('PS_CCCJS_VERSION');
            $version_js_pc = (int)Configuration::get('pagecache_CCCJS_VERSION');
            $changed = $version_js !== $version_js_pc;
        }
        if ($changed) {
            Logger::addLog("PageCache | Clearing cache because medias (CSS or JS) have been changed", 1);
            PageCache::clearCache();
        }
    }

    /**
     * Return request URI and may add some info like current currency
     */
    public static function getPseudoRequestURI($url = null) {
        if (empty ( $url )) {
            $url = self::getCurrentURL ();
        }

        // Normalize the URL
        $un = new URLNormalizer ();
        $un->setUrl ( html_entity_decode ( $url ) );
        $normalized_url = $un->normalize ();

        // Add some parameters to set currency or mobile version status
        $context = Context::getContext();
        $param_to_add = '&pc_cur=' . self::getCurrencyId($context);
        $param_to_add .= '&pc_groups=' . implode(',', self::getGroupsIds($context));
        $country = self::getCountry($context);
        if ($country) {
            $param_to_add .= '&pc_ctry=' . $country->iso_code . '-' . $country->id;
        }
        $country2 = self::getCountry2($context, $country);
        if ($country2) {
            $param_to_add .= '&pc_ctry2=' . $country2->iso_code . '-' . $country2->id;
        }
        if ($context->getMobileDevice() == true) {
            $param_to_add .= '&pc_mob=1';
        }
        if (method_exists($context, 'getDevice')) {
            $param_to_add .= '&pc_dev=' . $context->getDevice();
        }

        // Check if shop is enable
        if (!(int)Configuration::get('PS_SHOP_ENABLE')) {
            $param_to_add .= '&pc_off=1';
        }

        // Strip ignored parameters (tracking data that do not change page content)
        // and sort them
        $ignored_params = explode ( ',', Configuration::get ( 'pagecache_ignored_params' ) );
        $ignored_params[] = 'delpagecache';
        $ignored_params[] = 'dbgpagecache';
        $ignored_params[] = 'cfgpagecache';
        $query_string = parse_url ( $normalized_url, PHP_URL_QUERY );
        $new_query_string = self::filterAndSortParams ( $query_string . $param_to_add, $ignored_params );
        $uri = http_build_url($normalized_url, array("query" => $new_query_string));

        return $uri;
    }

    public static function getCurrencyId($context) {
        $id_currency = -1;
        if (!isset($context->cookie->id_currency)) {
            $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        } else {
            $id_currency = $context->cookie->id_currency;
        }
        return $id_currency;
    }

    public static function getGroupsIds($context) {
        if (isset($context->cookie->pc_groups)) {
            // Use cookie set in dispatcher hook
            $groupsIds = explode(',', $context->cookie->pc_groups);
        }
        else if (isset($context->customer)) {
            // Compute groups IDs like in dispatcher hook
            $groupsIds = Customer::getGroupsStatic((int)$context->customer->id);
        } else {
            $groupsIds = Customer::getGroupsStatic(0);
        }
        return $groupsIds;
    }

    public static function getCountry($context) {
        $country = false;
        if (isset($context->cookie->pc_id_country)) {
            // We already computed the country
            $country = new Country($context->cookie->pc_id_country, $context->language->id);
        }
        else if (isset($context->cart)) {
            if ($context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) {
                $infos = Address::getCountryAndState((int)($context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
                $country = new Country((int)$infos['id_country'], $context->language->id);
            }
        }
        else if ($context->cookie->id_customer) {
            $id_address = (int)(Address::getFirstCustomerAddressId($context->cookie->id_customer));
            if ($id_address) {
                $infos = Address::getCountryAndState($id_address);
                $country = new Country((int)$infos['id_country'], $context->language->id);
            }
        }
        else if (Configuration::get('PS_GEOLOCATION_ENABLED')) {
            // Detect country now to get it right
            $controller_instance = self::getControllerInstance();
            if ($controller_instance !== false && method_exists($controller_instance, 'geolocationManagementPublic')) {
                if (($newDefault = $controller_instance->geolocationManagementPublic($context->country)) && Validate::isLoadedObject($newDefault)) {
                    $context->country = $newDefault;
                }
                if (isset($context->country)) {
                    $country = $context->country;
                }
            }
        }
        return $country;
    }

    /**
     * Country 2 is used for specific prices and can be used for tax calculation so
     * we need to put it in the cache key
     * @param unknown $context
     * @param unknown $country1
     * @return Country or false
     */
    public static function getCountry2($context, $country1) {
        $country2 = false;
        if (method_exists('Tools', 'getCountry')) {
            $country2_id = Tools::getCountry();
            if ($country2_id) {
                if (!$country1 || $country1->id != $country2_id) {
                    $country2 = new Country((int)$country2_id, $context->language->id);
                }
            }
        }
        return $country2;
    }

    private static function getControllerInstance() {
        $controller = false;
        // Load controllers classes
        $controllers = Dispatcher::getControllers(array(_PS_FRONT_CONTROLLER_DIR_, _PS_OVERRIDE_DIR_.'controllers/front/'));
        $controllers['index'] = 'IndexController';
        // Get controller name
        $controller_name = Dispatcher::getInstance()->getController();
        if (isset($controllers[Tools::strtolower($controller_name)])) {
            // Create controller instance
            $controller_class = $controllers[Tools::strtolower($controller_name)];
            $context = Context::getContext();
            if ($context->controller) {
                $controller = $context->controller;
            } else {
                $controller = Controller::getController($controller_class);
            }
        }
        return $controller;
    }

    public static function getCurrentURL() {
        $pageURL = 'http';
		$https = self::getServerValue('HTTPS');
        if (!empty($https) && $https !== 'off' || self::getServerValue('SERVER_PORT') == 443) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if (self::getServerValue("SERVER_PORT") != "80") {
            $pageURL .= self::getServerValue("SERVER_NAME") . ":" . self::getServerValue("SERVER_PORT") . self::getServerValue("REQUEST_URI");
        } else {
            $pageURL .= self::getServerValue("SERVER_NAME") . self::getServerValue("REQUEST_URI");
        }
        return $pageURL;
    }

    public static function filterAndSortParams($query_string, $ignored_params) {
        $new_query_string = '';
        $keyvalues = explode('&', $query_string);
        sort($keyvalues);
        foreach ($keyvalues as $keyvalue) {
            if (Tools::strlen($keyvalue) > 0) {
                $key = '';
                $value = '';
                $current_key_value = explode('=', $keyvalue);
                if (count($current_key_value) > 0) {
                    $key = $current_key_value[0];
                }
                if (count($current_key_value) > 1) {
                    $value = $current_key_value[1];
                }
                if (!in_array($key, $ignored_params)) {
                    $new_query_string .= '&' . $key . '=' . $value;
                }
            }
        }
        if (Tools::strlen($new_query_string) > 0) {
            $new_query_string = Tools::substr($new_query_string, 1);
        }
        return $new_query_string;
    }

    public static function generateRandomString($length = 16) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; // length:36
        $final_rand = '';
        for($i = 0; $i < $length; $i ++) {
            $final_rand .= $chars [rand ( 0, Tools::strlen ( $chars ) - 1 )];
        }
        return $final_rand;
    }

    public function getCronClearCacheURL() {
        $urls = '';
        foreach (Shop::getContextListShopID() as $id_shop) {
            $shop_url = new ShopUrl($id_shop);
            $url = $shop_url->getURL();
            if (Tools::strlen($url) > 0) {
                $urls .= '<li>' . $shop_url->getURL() . '?fc=module&amp;module='.$this->name.'&amp;controller=clearcache&amp;token=' . Configuration::get('pagecache_cron_token') . '</li>';
            }
        }
        return '<ul>'.$urls.'</ul>';
    }

    public static function readfile($cache_file) {
        $controller = Dispatcher::getInstance()->getController();
        $offset = 60 * Configuration::get('pagecache_'.$controller.'_expires', 0);
        if ($offset > 0) {
            if (headers_sent()) {
                Logger::addLog("PageCache | Cannot use browser cache because headers have already been sent", 3);
            }
            else if (!PageCacheDAO::hasTriggerIn2H()) {
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $offset) . ' GMT');
                header('Cache-Control: max-age='.$offset.', private');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
            }
        }
        readfile($cache_file);
    }

    public static function cacheThis($html, $is_retro_compatible = false) {
        // BEGIN - 1.4 retrocompatibility (display is done in multiple segment)
        $appendFlag = 0;
        if (!$is_retro_compatible && stripos($html, '<html') === false) {
            if (Configuration::get('pagecache_logs') > 1) {
                // Log debug
                Logger::addLog("PageCache | 1.4 retrocompatibility", 1, null, null, null, true);
            }
            $appendFlag = FILE_APPEND;
        }
        // END - 1.4 retrocompatibility (display is done in multiple segment)

        // Save the html into a file
        $cache_file = self::_getCacheFilepath();
        $write_ok = file_put_contents($cache_file, $html, $appendFlag);
        if ($write_ok === false) {
            Logger::addLog("PageCache | Cannot write file $cache_file", 4);
        }
        else if (Configuration::get('pagecache_logs') > 1) {
            // Log debug
            $exists = file_exists($cache_file) ? 'true' : 'false';
            $date_infos = '';
            if (file_exists($cache_file)) {
                $now = date("d/m/Y H:i:s", microtime(true));
                $last_date = date("d/m/Y H:i:s", filemtime($cache_file));
                $date_infos = "now=$now file=$last_date";
            }
            Logger::addLog("PageCache | cached | cache_file=$cache_file exists=$exists $date_infos", 1, null, null, null, true);
        }
        if ($write_ok !== false) {
            @chmod($cache_file, 0666);
        }

        // Parse this file to find all backlinks
        $backlinks = array();
        $shop_url = new ShopUrl(Shop::getContextShopID());
        $base = $shop_url->getURL();
        $base_exp = preg_replace('/([^a-zA-Z0-9])/', '\\\\$1', $base);

        //  Original PHP code by Chirp Internet: www.chirp.com.au
        //  Please acknowledge use of this code by including this header.

        $regexp = '<a\s[^>]*href=(\"??)' . $base_exp . '([^\" >]*?)\\1[^>]*>(.*)<\/a>';
        if(preg_match_all("/$regexp/siU", $html, $matches, PREG_SET_ORDER)) {
            // The links array will help us to remove duplicates
            foreach($matches as $match) {
                // $match[2] = link address
                // $match[3] = link text
                // Insert backlinks that correspond to a possibily cached page into the database

                $url = $match[2];
                // Add leading /
                if (strrpos($url, "/", -Tools::strlen($url)) === FALSE) {
                    $url = "/" . $url;
                }

                // Remove language part if any
                $url_without_lang = $url;
                if (Language::isMultiLanguageActivated() && preg_match('#^/([a-z]{2})(?:/.*)?$#', $url, $m)) {
                    $url_without_lang = Tools::substr($url, 3);
                }

                $bl_controller = Dispatcher::getInstance()->getControllerFromURL($url_without_lang);
                if ($bl_controller === false) {
                    // To avoid re-installation of override we have this workaround
                    $bl_controller = Dispatcher::getInstance()->getControllerFromURL('en'. $url_without_lang);
                }
                if (in_array($bl_controller, self::$managed_controllers)) {
                    $link = self::getPseudoRequestURI($base . $match[2]);
                    $backlinks[$link] = $link;
                }
            }
        }

        // Find all called modules
        $module_ids = array();
        foreach (Hook::$executed_hooks as $hook_name) {
            if (strcmp($hook_name, 'displayHeader') != 0) {
                $module_list = Hook::getHookModuleExecList($hook_name);
                foreach ($module_list as $array) {
                    $module_ids[$array['id_module']] = $array['id_module'];
                }
            }
        }

        // Insert in database
        $controller = Dispatcher::getInstance()->getController();
        $id_object = Tools::getValue('id_' . $controller, null);
        PageCacheDAO::insert(
            self::getPseudoRequestURI(),
            $cache_file,
            $controller,
            Shop::getContextShopID(),
            $id_object,
            $module_ids,
            $backlinks,
            Configuration::get('pagecache_logs'),
            Configuration::get('pagecache_stats'));
    }

    private static function _getCacheFilepath() {
        $controller = Dispatcher::getInstance()->getController();
        $id_shop = Shop::getContextShopID();
        $subdir = _PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/' . $id_shop . '/' . $controller;
        $filekey = md5(self::getPseudoRequestURI());
        for($i = 0; $i < 2; $i ++) {
            $subdir .= '/' . $filekey[$i];
        }
        if (!file_exists($subdir)) {
            // Creates subdirectory with same chmod as root cache directory
            $grants = 0777;
            if (!mkdir($subdir, $grants, true)) {
                Logger::addLog("PageCache | Cannot create directory $subdir with grants $grants", 4);
            }
        }
        $cache_file = $subdir . '/' . $filekey . '.htm';
        return $cache_file;
    }

    public static function preDisplayStats() {
        if (Tools::getIsset('ajax')) {
            // Skip useless work
            return array();
        }

        $infos = array();
        if (self::isDisplayStats()) {
            $context = Context::getContext();
            $currency = new Currency(self::getCurrencyId($context));
            $controller = Dispatcher::getInstance()->getController();
            if (in_array($controller, self::$managed_controllers)) {
                $country = self::getCountry($context);
                $country2 = self::getCountry2($context, $country);
                $cache_path = self::_getCacheFilepath();
                $infos['cacheable'] = PageCache::canBeCached() ? 'true' : 'false';
                $filemtime = @filemtime($cache_path);
                if ($filemtime) {
                    $age = microtime(true) - $filemtime;
                    if ($age < (3*60)) {
                        $infos['age'] = round($age, 0) . ' seconds';
                    }
                    else if ($age < (3*60*60)) {
                        $infos['age'] = round($age/60, 0) . ' minutes';
                    }
                    else if ($age < (3*60*60*24)) {
                        $infos['age'] = round($age/3600, 0) . ' hours';
                    } else {
                        $infos['age'] = round($age/86400, 0) . ' days';
                    }
                } else {
                    $infos['age'] = '-';
                }
                $infos['timeout_server'] = Configuration::get('pagecache_'.$controller.'_timeout') . ' minute(s)';
                $infos['timeout_browser'] = Configuration::get('pagecache_'.$controller.'_expires') . ' minute(s)';
                $infos['controller'] = $controller;
                $infos['currency'] = $currency->name;
                if ($country) {
                    if (is_array($country->name)) {
                        $infos['country'] = $country->name[$context->language->id];
                    }
                    else {
                        $infos['country'] = $country->name;
                    }
                } else {
                    $infos['country'] = '-';
                }
                if ($country2) {
                    if (is_array($country2->name)) {
                        $infos['country2'] = $country2->name[$context->language->id];
                    }
                    else {
                        $infos['country2'] = $country2->name;
                    }
                }
                else {
                    $infos['country2'] = '-';
                }
                $infos['file'] = $cache_path;
                $infos['pseudo_url'] = self::getPseudoRequestURI();
                $infos['exists'] = file_exists($cache_path) ? 'true' : 'false';
            }
        }
        return $infos;
    }

    public static function displayStats($from_cache, $infos) {
        if (self::isDisplayStats()) {
            $controller = Dispatcher::getInstance()->getController();
            if (in_array($controller, self::$managed_controllers)) {
                // Prepare datas
                $pseudo_uri = self::getPseudoRequestURI();
                $startTime = Dispatcher::getInstance()->page_cache_start_time;
                $infos['speed'] = number_format((microtime(true) - $startTime)*1000, 0, ',', ' ').' ms';
                $context = Context::getContext();
                $infos['groups'] = '';
                $groupsIds = PageCache::getGroupsIds($context);
                foreach ($groupsIds as $groupId) {
                    if (((int)$groupId) > 0) {
                        $group = new Group($groupId);
                        $infos['groups'] = $infos['groups'].$group->name[$context->language->id].', ';
                    }
                }
                $infos['cookie_groups'] = $context->cookie->pc_groups;
                $infos['cookie_group_default'] = $context->cookie->pc_group_default;
                $infos['pseudo_url_after'] = $pseudo_uri;
                $infos['from_cache'] = $from_cache;
                $stats = PageCacheDAO::getStats($pseudo_uri);
                if ($stats['hit'] != -1) {
                    $infos['hit'] = $stats['hit'];
                    $infos['missed'] = $stats['missed'];
                    $infos['perfs'] = number_format((100*$stats['hit']/($stats['hit']+$stats['missed'])), 1).'%';
                } else {
                    $infos['hit'] = '-';
                    $infos['missed'] = '-';
                    $infos['perfs'] = '-';
                }
                $infos['pagehash'] = md5($pseudo_uri);

                $infos['url_on_off'] = http_build_url(self::getCleanURL(), array("query" => 'dbgpagecache='.((int)Tools::getValue('dbgpagecache', 0) == 0 ? 1 : 0)), HTTP_URL_JOIN_QUERY);
                $infos['url_del'] = http_build_url(self::getCleanURL(), array("query" => 'dbgpagecache='.Tools::getValue('dbgpagecache', 0).'&delpagecache=1'), HTTP_URL_JOIN_QUERY);
                $infos['url_reload'] = http_build_url(self::getCleanURL(), array("query" => 'dbgpagecache='.Tools::getValue('dbgpagecache', 1)), HTTP_URL_JOIN_QUERY);
                $infos['url_close'] = self::getCleanURL();
                $infos['dbgpagecache'] = (int)Tools::getValue('dbgpagecache', 0);
                $infos['base_dir'] = _PS_BASE_URL_.__PS_BASE_URI__;

                // Display the box
                $context->smarty->assign($infos);
                $context->smarty->display(_PS_MODULE_DIR_.basename(__FILE__, '.php').'/views/templates/hook/pagecache-infos.tpl');
            }
        }
    }

    public static function getCleanURL($url = null)
    {
        if ($url == null) {
            $url = self::getCurrentURL();
        }
        $new_query = '';
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query != null) {
            $query = html_entity_decode($query);
            $keyvals = explode('&', $query);
            foreach($keyvals as $keyval) {
                $x = explode('=', $keyval);
                if (strcmp($x[0], 'dbgpagecache') != 0 && strcmp($x[0], 'delpagecache') != 0) {
                    $new_query .= '&'.$x[0].'='.(count($x)>1 ? $x[1] : '');
                }
            }
        }
        $un = new URLNormalizer();
        $un->setUrl (http_build_url($url, array("query" => $new_query), HTTP_URL_REPLACE));
        return $un->normalize();
    }

    public static function clearCache($delete_dir = false) {
        // Delete cache of current shop(s)
        if (Shop::isFeatureActive()) {
            foreach (Shop::getContextListShopID() as $id_shop) {
                if (file_exists(_PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/' . $id_shop)) {
                    Tools::deleteDirectory(_PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/' . $id_shop, $delete_dir);
                }
            }
        } else {
            if (file_exists(_PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/')) {
                Tools::deleteDirectory(_PS_CACHE_DIR_ . self::PAGECACHE_DIR, $delete_dir);
            }
        }
        Configuration::updateValue('pagecache_CCCCSS_VERSION', Configuration::get('PS_CCCCSS_VERSION'));
        Configuration::updateValue('pagecache_CCCJS_VERSION', Configuration::get('PS_CCCJS_VERSION'));
        PageCacheDAO::clearAllCache();
    }

    public function clearCacheAndStats() {
        // Delete cache and stats of current shop(s)
        if (Shop::isFeatureActive()) {
            foreach (Shop::getContextListShopID() as $id_shop) {
                if (file_exists(_PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/' . $id_shop)) {
                    Tools::deleteDirectory(_PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/' . $id_shop, false);
                }
            }
            PageCacheDAO::resetCache(Shop::getContextListShopID());
        } else {
            if (file_exists(_PS_CACHE_DIR_ . self::PAGECACHE_DIR . '/')) {
                Tools::deleteDirectory(_PS_CACHE_DIR_ . self::PAGECACHE_DIR, false);
            }
            PageCacheDAO::resetCache();
        }
        Configuration::updateValue('pagecache_CCCCSS_VERSION', Configuration::get('PS_CCCCSS_VERSION'));
        Configuration::updateValue('pagecache_CCCJS_VERSION', Configuration::get('PS_CCCJS_VERSION'));
    }

    private function _clearCacheModules($event, $action_origin='') {
        $mods = explode(' ', Configuration::get($event.'_mods'));
        foreach ($mods as $mod) {
            $module_name = trim($mod);
            if (Tools::strlen($mod) > 0) {
                PageCacheDAO::clearCacheOfModule($module_name, $action_origin, Configuration::get('pagecache_logs'));
            }
        }
    }

    public function hookActionAttributeDelete($params) {
        $this->hookActionAttributeSave($params);
    }

    public function hookActionAttributeSave($params) {
        if (isset($params['id_attribute'])) {
            $productsIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT pa.id_product
                FROM '._DB_PREFIX_.'product_attribute pa
                LEFT JOIN '._DB_PREFIX_.'product_attribute_combination pac ON (pac.id_product_attribute = pa.id_product_attribute)
                WHERE pac.id_attribute = '.(int)$params['id_attribute']
            );
            foreach ($productsIds as $productId) {
                PageCacheDAO::clearCacheOfObject('product', $productId['id_product'], Configuration::get('pagecache_product_u_bl'), 'hookActionAttributeSave', Configuration::get('pagecache_logs'));
            }
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionAttributeSave');
    }

    public function hookActionAttributeGroupDelete($params) {
        $this->hookActionAttributeGroupSave($params);
    }

    public function hookActionAttributeGroupSave($params) {
        if (isset($params['id_attribute_group'])) {
            $productsIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT pa.id_product
                FROM '._DB_PREFIX_.'product_attribute pa
                LEFT JOIN '._DB_PREFIX_.'product_attribute_combination pac ON (pac.id_product_attribute = pa.id_product_attribute)
                LEFT JOIN '._DB_PREFIX_.'attribute a ON (a.id_attribute = pac.id_attribute)
                WHERE a.id_attribute_group = '.(int)$params['id_attribute_group']
            );
            foreach ($productsIds as $productId) {
                PageCacheDAO::clearCacheOfObject('product', $productId['id_product'], Configuration::get('pagecache_product_u_bl'), 'hookActionAttributeGroupSave', Configuration::get('pagecache_logs'));
            }
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionAttributeGroupSave');
    }

    public function hookActionFeatureDelete($params) {
        $this->hookActionFeatureSave($params);
    }

    public function hookActionFeatureSave($params) {
        if (isset($params['id_feature'])) {
            $id_feature = $params['id_feature'];
            $productsIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT p.id_product
                FROM '._DB_PREFIX_.'product p
                LEFT JOIN '._DB_PREFIX_.'feature_product f ON (f.id_product = p.id_product)
                WHERE f.id_feature = '.(int)$id_feature
            );
            foreach ($productsIds as $productId) {
                PageCacheDAO::clearCacheOfObject('product', $productId['id_product'], Configuration::get('pagecache_product_u_bl'), 'hookActionFeatureSave', Configuration::get('pagecache_logs'));
            }
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionFeatureSave');
    }

    public function hookActionFeatureValueDelete($params) {
        $this->hookActionFeatureValueSave($params);
    }

    public function hookActionFeatureValueSave($params) {
        if (isset($params['id_feature_value'])) {
            $id_feature_value = $params['id_feature_value'];
            $productsIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT p.id_product
                FROM '._DB_PREFIX_.'product p
                LEFT JOIN '._DB_PREFIX_.'feature_product fp ON (fp.id_product = p.id_product)
                WHERE fp.id_feature = '.(int)$id_feature_value
            );
            foreach ($productsIds as $productId) {
                PageCacheDAO::clearCacheOfObject('product', $productId['id_product'], Configuration::get('pagecache_product_u_bl'), 'hookActionFeatureValueSave', Configuration::get('pagecache_logs'));
            }
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionFeatureValueSave');
    }

    public function hookActionObjectCmsAddAfter($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('cms', $params['object']->id, false, 'hookActionObjectCmsAddAfter', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_cms_a', 'hookActionObjectCmsAddAfter');
    }

    public function hookActionObjectCmsUpdateAfter($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('cms', $params['object']->id, Configuration::get('pagecache_cms_u_bl'), 'hookActionObjectCmsUpdateAfter', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_cms_u', 'hookActionObjectCmsUpdateAfter');
    }

    public function hookActionObjectCmsDeleteBefore($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('cms', $params['object']->id, Configuration::get('pagecache_cms_d_bl'), 'hookActionObjectCmsDeleteBefore', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_cms_d', 'hookActionObjectCmsDeleteBefore');
    }

    public function hookActionObjectManufacturerAddAfter($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('manufacturer', $params['object']->id, false, 'hookActionObjectManufacturerAddAfter', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_manufacturer_a', 'hookActionObjectManufacturerAddAfter');
    }

    public function hookActionObjectManufacturerUpdateAfter($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('manufacturer', $params['object']->id, Configuration::get('pagecache_manufacturer_u_bl'), 'hookActionObjectManufacturerUpdateAfter', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_manufacturer_u', 'hookActionObjectManufacturerUpdateAfter');
    }

    public function hookActionObjectManufacturerDeleteBefore($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('manufacturer', $params['object']->id, Configuration::get('pagecache_manufacturer_d_bl'), 'hookActionObjectManufacturerDeleteBefore', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_manufacturer_d', 'hookActionObjectManufacturerDeleteBefore');
    }

    public function hookActionObjectStockAddAfter($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('product', $params['object']->id_product, false, 'hookActionObjectStockAddAfter', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_product_a', 'hookActionObjectStockAddAfter');
    }

    public function hookActionObjectStockUpdateAfter($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('product', $params['object']->id_product, Configuration::get('pagecache_product_u_bl'), 'hookActionObjectStockUpdateAfter', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionObjectStockUpdateAfter');
    }

    public function hookActionObjectStockDeleteBefore($params) {
        if (isset($params['object'])) {
            PageCacheDAO::clearCacheOfObject('product', $params['object']->id_product, Configuration::get('pagecache_product_d_bl'), 'hookActionObjectStockDeleteBefore', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_product_d', 'hookActionObjectStockDeleteBefore');
    }

    public function hookActionObjectAddressAddAfter($params) {
        if (isset($params['object']) && !empty($params['object']->id_supplier)) {
            $this->_clearCacheModules('pagecache_supplier_a', 'hookActionObjectAddressAddAfter');
        }
    }

    public function hookActionObjectAddressUpdateAfter($params) {
        if (isset($params['object']) && !empty($params['object']->id_supplier)) {
            PageCacheDAO::clearCacheOfObject('supplier', $params['object']->id_supplier, Configuration::get('pagecache_supplier_u_bl'), 'hookActionObjectAddressUpdateAfter', Configuration::get('pagecache_logs'));
            $this->_clearCacheModules('pagecache_supplier_u', 'hookActionObjectAddressUpdateAfter');
        }
    }

    public function hookActionObjectAddressDeleteBefore($params) {
        if (isset($params['object']) && !empty($params['object']->id_supplier)) {
            PageCacheDAO::clearCacheOfObject('supplier', $params['object']->id_supplier, Configuration::get('pagecache_supplier_d_bl'), 'hookActionObjectAddressDeleteBefore', Configuration::get('pagecache_logs'));
            $this->_clearCacheModules('pagecache_supplier_d', 'hookActionObjectAddressDeleteBefore');
        }
    }

    public function hookActionProductAttributeDelete($params) {
        if (isset($params['id_product'])) {
            PageCacheDAO::clearCacheOfObject('product', $params['id_product'], Configuration::get('pagecache_product_u_bl'), 'hookActionProductAttributeDelete', Configuration::get('pagecache_logs'));
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionProductAttributeDelete');
    }

    public function hookActionProductAttributeUpdate($params) {
        if (isset($params['id_product_attribute'])) {
            $productsIds = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT pa.id_product
                FROM '._DB_PREFIX_.'product_attribute pa
                WHERE pa.id_product_attribute = '.(int)$params['id_product_attribute']
            );
            foreach ($productsIds as $productId) {
                PageCacheDAO::clearCacheOfObject('product', $productId['id_product'], Configuration::get('pagecache_product_u_bl'), 'hookActionProductAttributeUpdate', Configuration::get('pagecache_logs'));
            }
        }
        $this->_clearCacheModules('pagecache_product_u', 'hookActionProductAttributeUpdate');
    }

    public function hookActionCategoryAdd($params) {
        if (isset($params['category'])) {
            PageCacheDAO::clearCacheOfObject('category', $params['category']->id, false, 'hookActionCategoryAdd', Configuration::get('pagecache_logs'));
            $this->_checkRootCategory($params['category']->id, 'a', 'hookActionCategoryAdd');
        }
        $this->_clearCacheModules('pagecache_category_a', 'hookActionCategoryAdd');
    }

    public function hookActionCategoryUpdate($params) {
        if (isset($params['category'])) {
            PageCacheDAO::clearCacheOfObject('category', $params['category']->id, Configuration::get('pagecache_category_u_bl'), 'hookActionCategoryUpdate', Configuration::get('pagecache_logs'));
            $this->_checkRootCategory($params['category']->id, 'u', 'hookActionCategoryUpdate');
        }
        $this->_clearCacheModules('pagecache_category_u', 'hookActionCategoryUpdate');
    }

    public function hookActionCategoryDelete($params) {
        if (isset($params['category'])) {
            PageCacheDAO::clearCacheOfObject('category', $params['category']->id, Configuration::get('pagecache_category_d_bl'), 'hookActionCategoryDelete', Configuration::get('pagecache_logs'));
            $this->_checkRootCategory($params['category']->id, 'd', 'hookActionCategoryDelete');
        }
        $this->_clearCacheModules('pagecache_category_d', 'hookActionCategoryDelete');
    }

    public function hookActionProductAdd($params) {
        if (!isset($params['product']) && isset($params['id_product'])) {
            $params['product'] = new Product($params['id_product']);
        }
        if (isset($params['product'])) {
            $product = $params['product'];
            PageCacheDAO::clearCacheOfObject('new-products', null, false, 'hookActionProductAdd', Configuration::get('pagecache_logs'));
            $categoriesIds = $product->getCategories();
            foreach ($categoriesIds as $categoryId) {
                PageCacheDAO::clearCacheOfObject('category', $categoryId, false, 'hookActionProductAdd#'.$product->id, Configuration::get('pagecache_logs'));
                $this->_checkRootCategory($categoryId, 'a', 'hookActionProductAdd#'.$product->id);
            }
            $this->_clearCacheModules('pagecache_product_a', 'hookActionProductAdd#'.$product->id);
        }
    }

    public function hookActionProductUpdate($params) {
        if (!isset($params['product']) && isset($params['id_product'])) {
            $params['product'] = new Product($params['id_product']);
        }
        if (isset($params['product'])) {
            PageCacheDAO::clearCacheOfObject('product', $params['product']->id, Configuration::get('pagecache_product_u_bl'), 'hookActionProductUpdate', Configuration::get('pagecache_logs'));
            $product = $params['product'];
            $categoriesIds = $product->getCategories();
            foreach ($categoriesIds as $categoryId) {
                PageCacheDAO::clearCacheOfObject('category', $categoryId, false, 'hookActionProductUpdate#'.$product->id, Configuration::get('pagecache_logs'));
                $this->_checkRootCategory($categoryId, 'u', 'hookActionProductUpdate#'.$product->id);
            }
            $this->_clearCacheModules('pagecache_product_u', 'hookActionProductUpdate#'.$product->id);
        }
    }

    public function hookActionProductDelete($params) {
        if (!isset($params['product']) && isset($params['id_product'])) {
            $params['product'] = new Product($params['id_product']);
        }
        if (isset($params['product'])) {
            PageCacheDAO::clearCacheOfObject('product', $params['product']->id, Configuration::get('pagecache_product_d_bl'), 'hookActionProductDelete', Configuration::get('pagecache_logs'));
            $product = $params['product'];
            $categoriesIds = $product->getCategories();
            foreach ($categoriesIds as $categoryId) {
                PageCacheDAO::clearCacheOfObject('category', $categoryId, false, 'hookActionProductDelete#'.$product->id, Configuration::get('pagecache_logs'));
                $this->_checkRootCategory($categoryId, 'd', 'hookActionProductDelete#'.$product->id);
            }
            $this->_clearCacheModules('pagecache_product_d', 'hookActionProductDelete#'.$product->id);
        }
    }

    public function hookActionObjectSpecificPriceAddAfter($params) {
        if (isset($params['object'])) {
            $sp = $params['object'];
            PageCacheDAO::insertSpecificPrice($sp->id, $sp->id_product, $sp->from, $sp->to);
            $this->hookActionProductUpdate(array('id_product' => $params['object']->id_product));
        }
    }

    public function hookActionObjectSpecificPriceUpdateAfter($params) {
        if (isset($params['object'])) {
            $sp = $params['object'];
            PageCacheDAO::updateSpecificPrice($sp->id, $sp->id_product, $sp->from, $sp->to);
            $this->hookActionProductUpdate(array('id_product' => $params['object']->id_product));
        }
    }

    public function hookActionObjectSpecificPriceDeleteBefore($params) {
        if (isset($params['object'])) {
            $sp = $params['object'];
            PageCacheDAO::deleteSpecificPrice($sp->id);
            $this->hookActionProductUpdate(array('id_product' => $params['object']->id_product));
        }
    }

    private function _checkRootCategory($id_category, $suffix, $origin_action='') {
        if ((bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_shop` FROM `'._DB_PREFIX_.'shop` WHERE `id_category` = '.(int)$id_category)) {
            $this->_clearCacheModules('pagecache_product_home_'.$suffix, $origin_action);
        }
    }

    public function hookActionUpdateQuantity($params) {
        if (isset($params['id_product'])) {
            $product = new Product($params['id_product']);
            if (!$product->checkQty(1)) {
                // Out of stock, do like a product update
                PageCacheDAO::clearCacheOfObject('product', $params['id_product'], true, 'hookActionUpdateQuantity', Configuration::get('pagecache_logs'));
                $categoriesIds = $product->getCategories();
                foreach ($categoriesIds as $categoryId) {
                    PageCacheDAO::clearCacheOfObject('category', $categoryId, false, 'hookActionUpdateQuantity#'.$params['id_product'], Configuration::get('pagecache_logs'));
                    $this->_checkRootCategory($categoryId, 'd', 'hookActionUpdateQuantity#'.$params['id_product']);
                }
                $this->_clearCacheModules('pagecache_product_u', 'hookActionUpdateQuantity#'.$params['id_product']);
            } else {
                // Still in stock, just update product page
                PageCacheDAO::clearCacheOfObject('product', $params['id_product'], false, 'hookActionUpdateQuantity', Configuration::get('pagecache_logs'));
            }
        }
    }

    public function hookActionHtaccessCreate($params) {
        $this->clearCache();
    }

    /**
     * Just in case: display error message with solution if overidde did not worked (v2.0)
     */
    public static function getCacheFilepath() {
        die('<br>ERROR: the file /override/classes/controller/FrontController.php <b>must be patched for PageCache</b>.
            <br><br><b>SOLUTION</b>: Comment or delete this line in <b>/override/classes/controller/FrontController.php</b><pre style="border:1px solid gray;background-color:#cdcdcd;padding:5px">$cache_file = PageCache::getCacheFilepath();</pre>
            and replace <pre style="border:1px solid gray;background-color:#cdcdcd;padding:5px">file_put_contents($cache_file, $html);</pre> by <pre style="border:1px solid gray;background-color:#cdcdcd;padding:5px">PageCache::cacheThis($html);</pre>
        ');
    }

    public function upgradeOverride($class_name) {
        $reset_ok = true;
        if (Tools::version_compare(_PS_VERSION_,'1.6','>=')
            || (!class_exists($class_name . 'OverrideOriginal') && (!class_exists($class_name . 'OverrideOriginal_remove')))) {
            $reset_ok = $this->removeOverride($class_name) && $this->addOverride($class_name);
        }
        return $reset_ok;
    }

    private function showAdvices() {
        $html = '';

        return $html;
    }

    private function showErrors() {
        $html = '';

        // Check tokens
        $token_enabled = (int)(Configuration::get('PS_TOKEN_ENABLE')) == 1 ? true : false;
        if ($token_enabled) {
            $html .= $this->displayError($this->l('You must disable tokens in order for cached pages to do ajax call.').' <a href="#" onclick="$(\'#pagecache_disable_tokens\').val(\'true\');$(\'#pagecache_form_install\').submit();return false;">'.$this->l('Resolve this for me!').'</a>');
        }

        // Check for bvkdispatcher module
        if (Module::isInstalled('bvkseodispatcher')) {
            $html .= $this->displayError($this->l('Module "SEO Pretty URL Module" (bvkseodispatcher) is not compatible with PageCache because it does not respect Prestashop standards. You have to choose between this module and PageCache.'));
        }

        // Check for overrides (after an upgrade it is disabled)
        if (!self::isOverridesEnabled()) {
            $html .= $this->displayError($this->l('Overrides are disabled in Performances tab so PageCache is disabled.'));
        }

        return $html;
    }

    /** @return bool true if infos block must be displayed on front end */
    private static function isDisplayStats() {
        if (Tools::getIsset('ajax') || strcmp(self::getServerValue('REQUEST_METHOD'), 'GET') != 0) {
            return false;
        }
        return Configuration::get('pagecache_always_infosbox')
            || (Configuration::get('pagecache_debug') && Tools::getIsset('dbgpagecache'));
    }

    private function _displayStep($step) {
        $html = '<div class="step';
        $cur_step = (int) Configuration::get('pagecache_install_step');
        if ($cur_step > $step) {
            $html .= ' stepok"><img src="../modules/'.$this->name.'/views/img/check.png" alt="ok" width="24" height="24"/>';
        }
        else if ($cur_step < $step) {
            $html .= ' steptodo"><span>'.$step.'</span>';
        } else {
            // $cur_step == $step
            $html .= '"><img src="../modules/'.$this->name.'/views/img/curstep.gif" alt="todo" width="24" height="24"/>';
        }
        return $html;
    }

    private static function getServerValue($key) {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        return '';
    }
}
?>
