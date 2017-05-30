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
* Drop 'ps_jm_pagecache.modules' column
*/
function upgrade_module_2_58()
{
    $sql = 'ALTER TABLE `'._DB_PREFIX_.'jm_pagecache` DROP COLUMN `modules`';
    return Db::getInstance()->execute($sql);
}
