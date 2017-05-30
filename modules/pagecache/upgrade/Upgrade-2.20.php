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
* Reinstall some override
*/
function upgrade_module_2_20($module)
{
    return $module->upgradeOverride('Hook')
        && $module->upgradeOverride('Dispatcher')
        && $module->upgradeOverride('FrontController');
}
