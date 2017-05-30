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
* Clear cache, upgrade database and Dispatcher.php
*/
function upgrade_module_2_39($module)
{

    // Clear cache and stats because database schema will be modified
    $module->clearCacheAndStats();

    // Dispatcher has been updated
    $upgrade_ok = $module->upgradeOverride('Dispatcher');

    // FrontController has been updated
    $upgrade_ok = $module->upgradeOverride('FrontController');

    // Modify database schema
    $upgrade_ok = $upgrade_ok && PageCacheDAO::optimizeHash2_39();

    // For css and js
    $module->registerHook('displayHeader');

    return $upgrade_ok;
}
