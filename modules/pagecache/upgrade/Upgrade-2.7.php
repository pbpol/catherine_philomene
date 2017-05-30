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
* Enable statistics by default and update Dispatcher overriden class
*/
function upgrade_module_2_7($module)
{
    $module->setDefaultConfiguration_2_7();
    // Dispatcher has been modified so update it
    $upgraded_ok = $module->upgradeOverride('Dispatcher');
    return $upgraded_ok;
}
