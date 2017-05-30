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
* Add id_shop column and reset cache
*/
function upgrade_module_2_12($module)
{
    $module->clearCacheAndStats();
    $sql = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` ADD COLUMN `id_shop` int(11) NOT NULL DEFAULT \'1\' AFTER id';
    $upgraded_ok = Db::getInstance()->execute($sql);
    $sqlindex = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` ADD INDEX (`id_shop`)';
    $upgraded_ok = Db::getInstance()->execute($sqlindex);
    $module->setDefaultConfiguration_2_12();
    return $upgraded_ok;
}
