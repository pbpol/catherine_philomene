<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

/*
* Create tables and clear cache because we need to feed this tables.
* Also, patch FrontController to use new function cacheThis()
*/
function upgrade_module_2_0($module)
{
    // Creates BDD
    PageCacheDAO::createTables();

    // Set default configuration
    $module->setDefaultConfiguration_2_0();

    // Fix an issue with 3 controllers
    Configuration::updateValue('pagecache_newproducts', Configuration::get('pagecache_new-products', false));
    Configuration::updateValue('pagecache_pricesdrop', Configuration::get('pagecache_prices-drop', false));
    Configuration::updateValue('pagecache_bestsales', Configuration::get('pagecache_best-sales', false));
    Configuration::updateValue('pagecache_newproducts_timeout', Configuration::get('pagecache_new-products_timeout', 60 * 24 * 1));
    Configuration::updateValue('pagecache_pricesdrop_timeout', Configuration::get('pagecache_prices-drop_timeout', 60 * 24 * 1));
    Configuration::updateValue('pagecache_bestsales_timeout', Configuration::get('pagecache_best-sales_timeout', 60 * 24 * 1));
    Configuration::deleteByName('pagecache_new-products');
    Configuration::deleteByName('pagecache_prices-drop');
    Configuration::deleteByName('pagecache_best-sales');
    Configuration::deleteByName('pagecache_new-products_timeout');
    Configuration::deleteByName('pagecache_prices-drop_timeout');
    Configuration::deleteByName('pagecache_best-sales_timeout');

    // FrontController has been modified so update it
    $module->upgradeOverride('FrontController');

    // Remove FrontController::init() if it has not been changed (not done in removeOverride function)
    $override_path = _PS_ROOT_DIR_.'/'.Autoload::getInstance()->getClassPath('FrontController');
    $old_init_function = 'public function init\(\)[\n\t \r]*{[\n\t \r]*\/\/ <PageCache>[\n\t \r]*if \(\$this->_isPageCacheActive\(\) && PageCache::canBeCached\(\)\)[\n\t \r]*{[\n\t \r]*\/\/ Remove cookie, cart and customer informations to cache[\n\t \r]*\/\/ a \'standard\' page[\n\t \r]*\$this->context->cookie = new Cookie\(\'pagecache\'\);([\n\t \r]*\$this->context->cookie->id_lang = \$this->context->language->id;)?[\n\t \r]*\$this->context->cart = new Cart\(\);[\n\t \r]*\$this->context->customer = new Customer\(\);[\n\t \r]*}[\n\t \r]*\/\/ REPLACE by \$this->init2\(\) if you override manually[\n\t \r]*return parent::init\(\);[\n\t \r]*\/\/ <\/PageCache>[\n\t \r]*}';
    $content = Tools::file_get_contents($override_path);
    $change_count = 0;
    $class_without_old_init = preg_replace('/'.$old_init_function.'/', '', $content, 1, $change_count);
    if ($class_without_old_init !== null && $change_count > 0) {
        file_put_contents($override_path, $class_without_old_init);
    }

    return true;
}
