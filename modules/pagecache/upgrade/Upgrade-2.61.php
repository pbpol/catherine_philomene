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
* Update Hook overriden class
*/
function upgrade_module_2_61($module)
{
    // Be sure new javascript is taken
    if (method_exists('Media','clearCache')) {
        Media::clearCache();
    }

    // Clear cache because JS will change
    $module->clearCache();

    // Dispatcher has been modified so update it
    return $module->upgradeOverride('Hook');
}
