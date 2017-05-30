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
* Upgrade Hook override
*/
function upgrade_module_2_44($module)
{

    // Clear cache because HTML will change
    $module->clearCache();

    return $module->upgradeOverride('Hook');
}
