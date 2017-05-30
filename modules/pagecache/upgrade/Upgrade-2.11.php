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
* Set default ignored URL parameters
*/
function upgrade_module_2_11($module)
{
    $module->setDefaultConfiguration_2_11();
    $module->clearCacheAndStats();
    return true;
}
