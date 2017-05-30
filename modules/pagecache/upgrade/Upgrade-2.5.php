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
* Update Dispatcher overriden class
*/
function upgrade_module_2_5($module)
{
    // Dispatcher has been modified so update it
    $module->upgradeOverride('Dispatcher');
    return true;
}
