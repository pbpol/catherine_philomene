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
 * Update all front controller for security fix
 */
function upgrade_module_3_09($module)
{
    $upgraded_ok = $module->upgradeOverride('IndexController');
    $upgraded_ok &= $module->upgradeOverride('CategoryController');
    $upgraded_ok &= $module->upgradeOverride('BestSalesController');
    $upgraded_ok &= $module->upgradeOverride('CmsController');
    $upgraded_ok &= $module->upgradeOverride('ContactController');
    $upgraded_ok &= $module->upgradeOverride('ManufacturerController');
    $upgraded_ok &= $module->upgradeOverride('NewProductsController');
    $upgraded_ok &= $module->upgradeOverride('PricesDropController');
    $upgraded_ok &= $module->upgradeOverride('ProductController');
    $upgraded_ok &= $module->upgradeOverride('SitemapController');
    $upgraded_ok &= $module->upgradeOverride('SupplierController');
    return $upgraded_ok;
}
