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
* Clear cache because file path has changed and update FrontController
*/
function upgrade_module_2_14($module)
{
    // Clear cache
    $module->clearCacheAndStats();

    // FrontController has been modified so update it
    $updated = $module->upgradeOverride('FrontController');

    return $updated;
}
