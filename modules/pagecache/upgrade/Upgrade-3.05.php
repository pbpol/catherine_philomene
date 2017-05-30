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
*
*/
function upgrade_module_3_05($module)
{
    Configuration::deleteByName('pagecache_allow_infosbox');

    // FrontController has been modified so update it
    $upgraded_ok = $module->upgradeOverride('FrontController');

    return $upgraded_ok;
}
