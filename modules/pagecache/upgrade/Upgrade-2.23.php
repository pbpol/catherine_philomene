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
* Reinstall some overrides
*/
function upgrade_module_2_23($module)
{
    return $module->upgradeOverride('FrontController')
        && $module->upgradeOverride('Dispatcher')
        && $module->upgradeOverride('Hook');
}
