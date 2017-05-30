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
* Reinstall overrides for dynamics modules
*/
function upgrade_module_2_26($module)
{

    $module->clearCache();

    if (Tools::version_compare(_PS_VERSION_,'1.6','<')) {
        return $module->upgradeOverride('BestSalesController')
            && $module->upgradeOverride('CategoryController')
            && $module->upgradeOverride('ContactController')
            && $module->upgradeOverride('IndexController')
            && $module->upgradeOverride('ManufacturerController')
            && $module->upgradeOverride('NewProductsController')
            && $module->upgradeOverride('PricesDropController')
            && $module->upgradeOverride('ProductController')
            && $module->upgradeOverride('SitemapController')
            && $module->upgradeOverride('StoresController')
            && $module->upgradeOverride('SupplierController')
            && $module->upgradeOverride('CmsController');
    }
    return true;
}
