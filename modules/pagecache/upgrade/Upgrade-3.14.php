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
 * Register stock hooks
 */
function upgrade_module_3_14($module)
{
    $module->registerHook('actionObjectStockAddAfter');
    $module->registerHook('actionObjectStockUpdateAfter');
    $module->registerHook('actionObjectStockDeleteBefore');
    return true;
}
