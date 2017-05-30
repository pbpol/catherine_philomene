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
 * Update Media to avoid the cache to be cleared to many times
 */
function upgrade_module_3_11($module)
{
    $upgraded_ok = $module->upgradeOverride('Media');
    return $upgraded_ok;
}
