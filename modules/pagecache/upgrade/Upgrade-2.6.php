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
* Update database
*/
function upgrade_module_2_6($module)
{
    // We will modify database so clear the entire chache
    $module->clearCache();
    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'jm_pagecache`;');
    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'jm_pagecache_bl`;');

    // A new table to avoid doing "like %stuff%"
    $sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jm_pagecache_mods`(
        `id` int(11) UNSIGNED NOT NULL,
        `module` VARCHAR(50) NOT NULL,
        KEY (`id`),
        KEY (`module`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
    $sql11 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` CHANGE COLUMN `id_pagecache` `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT';
    $sql12 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache_bl` CHANGE COLUMN `id_pagecache` `id` int(11) UNSIGNED NOT NULL';
    $sql2 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` DROP COLUMN `modules`';
    $sql3 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` ADD COLUMN `url_hash` VARCHAR(32) NOT NULL AFTER id';
    $sql4 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` CHANGE COLUMN `file` `file` VARCHAR(60) NOT NULL';
    $sql5 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache_bl` CHANGE `backlink` `backlink_hash` VARCHAR(32) NOT NULL';

    // Add some indexes
    $sql6 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` ADD INDEX (`controller`,`id_object`)';
    $sql7 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` DROP INDEX `idx_file`';
    $sql8 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` ADD UNIQUE (`url_hash`)';
    $sql9 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` ADD UNIQUE (`file`)';
    $sql10 = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache_bl` ADD INDEX (`id`)';

    // Executes the queries
    $upgraded_ok = Db::getInstance()->execute($sql1)
        && Db::getInstance()->execute($sql11)
        && Db::getInstance()->execute($sql12)
        && Db::getInstance()->execute($sql2)
        && Db::getInstance()->execute($sql3)
        && Db::getInstance()->execute($sql4)
        && Db::getInstance()->execute($sql5)
        && Db::getInstance()->execute($sql6)
        && Db::getInstance()->execute($sql7)
        && Db::getInstance()->execute($sql8)
        && Db::getInstance()->execute($sql9)
        && Db::getInstance()->execute($sql10)
        ;

    return $upgraded_ok;
}
