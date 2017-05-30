<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class PageCacheDAO
{
    const TABLE = 'jm_pagecache';
    const TABLE_BACKLINK = 'jm_pagecache_bl';
    const TABLE_MODULE = 'jm_pagecache_mods';
    const TABLE_SPECIFIC_PRICES = 'jm_pagecache_sp';

    public static function createTables()
    {
        $sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE.'`(
            `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` int(11) NOT NULL DEFAULT \'1\',
            `url_crc32` INT NOT NULL,
            `url` TEXT NOT NULL,
            `file` VARCHAR(60) NOT NULL,
            `controller` VARCHAR(15) NOT NULL,
            `id_object` int(11),
            `count_missed` int(11) NOT NULL DEFAULT \'0\',
            `count_hit` int(11) NOT NULL DEFAULT \'0\',
            PRIMARY KEY (`id`),
            KEY (`controller`,`id_object`),
            KEY (`id_shop`),
            UNIQUE (`url_crc32`),
            UNIQUE (`file`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
        if (!Db::getInstance()->execute($sql1)) {
            return false;
        }

        $sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_BACKLINK.'`(
            `id` int(11) UNSIGNED NOT NULL,
            `backlink_crc32` INT NOT NULL,
            KEY (`id`),
            KEY (`backlink_crc32`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
        if (!Db::getInstance()->execute($sql2)) {
            return false;
        }

        $sql3 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_MODULE.'`(
            `id` int(11) UNSIGNED NOT NULL,
            `id_module` int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`id`,`id_module`),
            KEY (`id_module`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
        if (!Db::getInstance()->execute($sql3)) {
            return false;
        }

        $sql4 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'`(
            `id_specific_price` int(10) unsigned NOT NULL,
            `id_product` int(10) unsigned NOT NULL,
            `date_from` datetime,
            `date_to` datetime,
            PRIMARY KEY (`id_specific_price`),
            KEY `idxfrom` (`date_from`),
            KEY `idxto` (`date_to`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
        if (!Db::getInstance()->execute($sql4)) {
            return false;
        }

        // Feed TABLE_SPECIFIC_PRICES to trigger cache reffreshment when a reduction starts or ends
        $now = date('Y-m-d H:i:00');
        $inserts = 'INSERT INTO `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` (`id_specific_price`,`id_product`,`date_from`,`date_to`) VALUES ';
        $select_existing = 'SELECT * FROM `'._DB_PREFIX_.'specific_price` WHERE `from`>\''.$now.'\' OR `to`>\''.$now.'\'';
        $rows = Db::getInstance()->executeS($select_existing);
        $index = 0;
        if (self::isIterable($rows)) {
            foreach ($rows as $row) {
                $inserts .= '('.$row['id_specific_price'].','.$row['id_product'].',\''.$row['from'].'\',\''.$row['to'].'\')';
                $index++;
                if ($index < count($rows)) {
                    $inserts .= ',';
                }
            }
        }
        if ($index > 0 && !Db::getInstance()->execute($inserts . ';')) {
            return false;
        }
        return true;
    }

    public static function optimizeHash2_39()
    {
        // Make it reentrant, try to delete before creating
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE.'` DROP COLUMN `url_crc32`;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_BACKLINK.'` DROP COLUMN `backlink_crc32`;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'` DROP COLUMN `id_module`;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }

        // Creates new columns
        $result = Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE.'` ADD `url_crc32` INT NOT NULL;');
        $result = $result && Db::getInstance()->execute('CREATE UNIQUE INDEX `url_crc32` ON `'._DB_PREFIX_.self::TABLE.'` (`url_crc32`);');

        $result = $result && Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_BACKLINK.'` ADD `backlink_crc32` INT NOT NULL;');
        $result = $result && Db::getInstance()->execute('CREATE INDEX `backlink_crc32` ON `'._DB_PREFIX_.self::TABLE_BACKLINK.'` (`backlink_crc32`);');

        $result = $result && Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'` ADD `id_module` int(11) UNSIGNED NOT NULL;');
        $result = $result && Db::getInstance()->execute('CREATE INDEX `id_module` ON `'._DB_PREFIX_.self::TABLE_MODULE.'` (`id_module`);');
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'` DROP PRIMARY KEY;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }
        $result = $result && Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'` ADD PRIMARY KEY (`id`,`id_module`);');

        // Delete old columns.
        // Be tolerent, do not check result here since it will not block the module
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE.'` DROP COLUMN `url_hash`;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_BACKLINK.'` DROP COLUMN `backlink_hash`;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }
        try {
            Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'` DROP COLUMN `module`;');
        } catch (PrestaShopDatabaseException $e) {
            // Just ignore it
        }

        return $result;
    }

    public static function insertSpecificPrice($id, $id_product, $from, $to)
    {
        $query = 'INSERT INTO `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` (id_specific_price,id_product,date_from,date_to) VALUES ';
        $query .= '('.(int)$id.','.(int)$id_product.',\''.pSQL($from).'\',\''.pSQL($to).'\');';
        Db::getInstance()->execute($query);
    }

    public static function updateSpecificPrice($id, $id_product, $from, $to)
    {
        $query = 'UPDATE `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` SET id_product='.(int)$id_product.', date_from=\''.pSQL($from).'\', date_to=\''.pSQL($to).'\'
            WHERE id_specific_price='.(int)$id.';';
        Db::getInstance()->execute($query);
    }

    public static function deleteSpecificPrice($id)
    {
        $query = 'DELETE FROM `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` WHERE id_specific_price='.(int)$id.';';
        Db::getInstance()->execute($query);
    }

    /**
     * Reffresh cache if any specific sprice started or ended since last check
     */
    public static function triggerReffreshment()
    {
        $now = date('Y-m-d H:i:00');
        $query = 'SELECT id_product FROM `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` WHERE date_from<=\''.pSQL($now).'\' OR date_to<\''.pSQL($now).'\';';
        $rows = Db::getInstance()->executeS($query);
        if (self::isIterable($rows)) {
            foreach ($rows as $row) {
                // Clear product cache and linkning pages because price has changed
                if ((int)$row['id_product']) {
                    self::clearCacheOfObject('product', $row['id_product'], true);
                }
            }
            if (count($rows) > 0) {
                $query = 'UPDATE `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` SET date_from=\'6666-01-01 00:00:00\' WHERE date_from<=\''.pSQL($now).'\';';
                Db::getInstance()->execute($query);
                $query = 'UPDATE `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` SET date_to=\'6666-01-01 00:00:00\' WHERE date_to<\''.pSQL($now).'\';';
                Db::getInstance()->execute($query);
                // Clean useless rows
                $query = 'DELETE FROM `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` WHERE date_from=\'6666-01-01 00:00:00\' AND date_to=\'6666-01-01 00:00:00\';';
                Db::getInstance()->execute($query);
            }
        }
    }

    public static function hasTriggerIn2H() {
        $now = date('Y-m-d H:i:00');
        $now_plus_2h = date('Y-m-d H:i:00');
        $now_plus_2h = new DateTime();
        $now_plus_2h->modify('+2 hour');
        $now_plus_2h = $now_plus_2h->format('Y-m-d H:i:00');
        $query = 'SELECT * FROM `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'` WHERE (date_from >= \''.pSQL($now).'\' AND date_from <= \''.pSQL($now_plus_2h).'\') OR (date_to >= \''.pSQL($now).'\'   AND date_to <= \''.pSQL($now_plus_2h).'\');';
        $rows = Db::getInstance()->executeS($query);
        if (self::isIterable($rows)) {
            return (count($rows) > 0);
        } else {
            return false;
        }
    }

    public static function dropTables()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_MODULE.'`;');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_BACKLINK.'`;');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE.'`;');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_SPECIFIC_PRICES.'`;');
    }

    public static function incrementCountHit($url)
    {
        $db = Db::getInstance();
        $query = 'UPDATE `'._DB_PREFIX_.self::TABLE.'` SET count_hit=count_hit+1 WHERE `url_crc32`='.pSQL(self::getHash($url)).';';
        $db->execute($query);
    }

    public static function getStats($url)
    {
        $db = Db::getInstance();
        $query = 'SELECT count_hit, count_missed FROM `'._DB_PREFIX_.self::TABLE.'` WHERE `url_crc32`='.pSQL(self::getHash($url)).';';
        $result = $db->executeS($query);
        if (self::isIterable($result) && count($result) == 1) {
            return array('hit' => (int)$result[0]['count_hit'], 'missed' => (int)$result[0]['count_missed']);
        }
        return array('hit' => -1, 'missed' => -1);
    }

    public static function getAllStats($ids_shop = null)
    {
        $db = Db::getInstance();
        if (empty($ids_shop)) {
            $query = 'SELECT id_shop, url, count_missed as missed, count_hit as hit, 100*(count_hit/(count_missed+count_hit)) as percent
            FROM `'._DB_PREFIX_.self::TABLE.'` ORDER BY count_missed+count_hit DESC LIMIT 100';
        } else {
            $query = 'SELECT id_shop, url, count_missed as missed, count_hit as hit, 100*(count_hit/(count_missed+count_hit)) as percent
            FROM `'._DB_PREFIX_.self::TABLE.'` WHERE id_shop IN ('.pSQL(implode(',', $ids_shop)).') ORDER BY count_missed+count_hit DESC LIMIT 100';
        }
        $result = $db->executeS($query);
        return $result;
    }

    /**
     * @param $url string URL to be cached
     * @param $file string Cache file path
     * @param $controller string Controller that manage the URL
     * @param $id_object integer ID of the object (product, category, supplier, etc.) if any
     * @param $module_ids array IDs of called module on this page
     * @param $backlinks string[] List of link present in this page
     */
    public static function insert($url, $file, $controller, $id_shop, $id_object = null, $module_ids, $backlinks, $log_level=0, $stats_it=true)
    {
        $startTime1 = microtime(true);
        if (!$id_object || empty($id_object)) {
            $id_object_str = 'NULL';
        } else {
            $id_object_str = (int)$id_object;
        }

        $url_crc32 = self::getHash($url);
        $db = Db::getInstance();
        $query = 'SELECT id FROM `'._DB_PREFIX_.self::TABLE.'` WHERE `url_crc32`='.pSQL($url_crc32).';';
        $existing_rows = $db->executeS($query);
        $startTime2 = microtime(true);

        if (self::isIterable($existing_rows) && count($existing_rows) > 0) {
            //
            // ALREADY CACHED PAGE
            //
            $id_pagecache = (int)$existing_rows[0]['id'];
            if ($stats_it) {
                $query = 'UPDATE `'._DB_PREFIX_.self::TABLE.'` SET
                    `count_missed`=`count_missed` + 1
                    WHERE id='.(int)$id_pagecache.';';
                $db->execute($query);
            }
        } else {
            //
            // NEW PAGE
            //
            $query = 'INSERT INTO `'._DB_PREFIX_.self::TABLE.'` (`url_crc32`, `url`, `file`, `controller`, `id_shop`, `id_object`, `count_missed`, `count_hit`)
                VALUES (
                '.$url_crc32.',
                \''.$db->escape($url).'\',
                \''.$db->escape(PageCache::normalizeFile($file)).'\',
                \''.$db->escape($controller).'\',
                '.(int)$id_shop.',
                '.$id_object_str.',
                1, 0);';
            $db->execute($query);
            $id_pagecache = (int)$db->Insert_ID();
        }
        //
        // MODULES
        //
        $startTime3 = microtime(true);
        $startTime4 = microtime(true);
        $db->execute('DELETE FROM `'._DB_PREFIX_.self::TABLE_MODULE.'` WHERE `id`='.(int)$id_pagecache.';');
        if (count($module_ids) > 0) {
            $module_query = 'INSERT INTO `'._DB_PREFIX_.self::TABLE_MODULE.'` (`id`, `id_module`) VALUES ';
            $idx = 0;
            foreach($module_ids as $id_module) {
                $module_query .= '('.$id_pagecache.',' . $id_module . ')';
                $idx++;
                if ($idx < count($module_ids)) {
                    $module_query .= ',';
                }
            }
            $startTime4 = microtime(true);
            $db->execute($module_query);
        }
        //
        // BACKLINKS
        //
        $startTime5 = microtime(true);
        $startTime6 = microtime(true);
        $db->execute('DELETE FROM `'._DB_PREFIX_.self::TABLE_BACKLINK.'` WHERE `id`='.(int)$id_pagecache.';');
        if (count($backlinks) > 0) {
            $backlink_query = 'INSERT INTO `'._DB_PREFIX_.self::TABLE_BACKLINK.'` (`id`, `backlink_crc32`) VALUES ';
            $idx = 0;
            foreach($backlinks as $backlink) {
                $backlink_query .= '('.(int)$id_pagecache.',' . pSQL(self::getHash($backlink)) . ')';
                $idx++;
                if ($idx < count($backlinks)) {
                    $backlink_query .= ',';
                }
            }
            $startTime6 = microtime(true);
            $db->execute($backlink_query);
        }
        if (((int)$log_level)>0) {
            Logger::addLog("PageCache | insert | added cache for $controller#$id_object during "
                . number_format($startTime2 - $startTime1, 3) . '+'
                . number_format($startTime3 - $startTime2, 3) . '+'
                . number_format($startTime4 - $startTime3, 3) . '+'
                . number_format($startTime5 - $startTime4, 3) . '+'
                . number_format($startTime6 - $startTime5, 3) . '+'
                . number_format(microtime(true) - $startTime6, 3) . '='
                . number_format(microtime(true) - $startTime1, 3)
            . " second(s) with ".count($backlinks)." backlinks", 1, null, null, null, true);
        }
    }

    public static function clearCacheOfObject($controller, $id_object, $delete_linking_pages, $action_origin='', $log_level=0) {
        $startTime1 = microtime(true);
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        if ($id_object != null) {
            $query = 'SELECT id, file, url_crc32 FROM `'._DB_PREFIX_.self::TABLE.'`
                WHERE controller=\''.$db->escape($controller).'\' AND id_object='.((int)$id_object).';';
        } else {
            $query = 'SELECT id, file, url_crc32 FROM `'._DB_PREFIX_.self::TABLE.'`
                WHERE controller=\''.$db->escape($controller).'\' AND id_object IS NULL;';
        }
        $results = $db->executeS($query);
        $startTime2 = microtime(true);

        $url_query = '(';
        $idx = 0;
        if (self::isIterable($results)) {
            foreach ($results as $result) {
                @unlink(PageCache::unnormalizeFile($result['file']));
                $idx++;
                $url_query .=  pSQL($result['url_crc32']);
                if ($idx < count($results)) {
                    $url_query .= ',';
                }
            }
        }
        if (((int)$log_level)>0) {
            Logger::addLog("PageCache | $action_origin | reffreshed $idx pages from $controller#$id_object during "
                . number_format($startTime2 - $startTime1, 3) . '+'
                . number_format(microtime(true) - $startTime2, 3) . '='
                . number_format(microtime(true) - $startTime1, 3)
                . " second(s)", 1, null, null, null, true);
            $startTime1 = microtime(true);
        }
        if ($delete_linking_pages) {
            // Also add the default link of the object in case that the page has never been cached
            $default_link = self::_getDefaultLink($controller, $id_object);
            if ($default_link !== false) {
                if (count($results) > 0) {
                    $url_query .= ',';
                }
                $url_query .=  pSQL(self::getHash(PageCache::getPseudoRequestURI($default_link)));
            }
            $startTime2 = microtime(true);
            // Delete pages that link to these pages
            if (Tools::strlen($url_query) > 1) {
                $idx = 0;
                $query = 'SELECT DISTINCT pc.id, pc.file FROM `'._DB_PREFIX_.self::TABLE.'` AS pc
                    LEFT JOIN `'._DB_PREFIX_.self::TABLE_BACKLINK.'` AS bl ON (bl.id = pc.id)
                    WHERE `backlink_crc32` IN ' . $url_query . ')';
                $results = $db->executeS($query);
                $startTime3 = microtime(true);
                if (self::isIterable($results)) {
                    foreach ($results as $result) {
                        @unlink(PageCache::unnormalizeFile($result['file']));
                        $idx++;
                    }
                }
            }
            if (((int)$log_level)>0) {
                Logger::addLog("PageCache | $action_origin | reffreshed $idx pages that were linking to $controller#$id_object during "
                    . number_format($startTime2 - $startTime1, 3) . '+'
                    . number_format($startTime3 - $startTime2, 3) . '+'
                    . number_format(microtime(true) - $startTime3, 3) . '='
                    . number_format(microtime(true) - $startTime1, 3)
                    . " second(s)", 1, null, null, null, true);
            }
        }
    }

    private static function _getDefaultLink($controller, $id_object) {
        if ($id_object == null) {
            return false;
        }
        $context = Context::getContext();
        if (!isset($context->link)) {
            /* Link should be initialized in the context but sometimes it is not */
            $https_link = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
            $context->link = new Link($https_link, $https_link);
        }
        switch ($controller) {
            case 'cms':
                return $context->link->getCMSLink((int)($id_object));
            case 'product':
                return $context->link->getProductLink((int)($id_object));
            case 'category':
                return $context->link->getCategoryLink((int)($id_object));
            case 'manufacturer':
                return $context->link->getManufacturerLink((int)($id_object));
            case 'supplier':
                return $context->link->getSupplierLink((int)($id_object));
        }
        return false;
    }

    public static function clearCacheOfModule($module_name, $action_origin='', $log_level=0) {
        $startTime1 = microtime(true);
        $module = Module::getInstanceByName($module_name);
        if ($module instanceof Module) {
            $id_module = $module->id;
            if (!empty($id_module)) {
                $query = 'SELECT pc.id, pc.file FROM `'._DB_PREFIX_.self::TABLE.'` AS pc
                    LEFT JOIN `'._DB_PREFIX_.self::TABLE_MODULE.'` AS mods ON (mods.id = pc.id)
                    WHERE `id_module`='.((int)$id_module);
                $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
                $results = $db->executeS($query);

                $startTime2 = microtime(true);
                $idx = 0;
                if (self::isIterable($results)) {
                    foreach ($results as $result) {
                        @unlink(PageCache::unnormalizeFile($result['file']));
                        $idx++;
                    }
                }
                if (((int)$log_level)>0) {
                    Logger::addLog("PageCache | $action_origin | reffreshed $idx pages containing module $module_name during "
                        . number_format($startTime2 - $startTime1, 3) . '+'
                        . number_format(microtime(true) - $startTime2, 3) . '='
                        . number_format(microtime(true) - $startTime1, 3)
                        . " second(s)", 1, null, null, null, true);
                }
            }
        }
    }

    public static function resetCache($ids_shop = null) {
        if (empty($ids_shop)) {
            Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.self::TABLE.'`;');
            Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.self::TABLE_BACKLINK.'`;');
            Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'`;');
        } else {
            Db::getInstance()->execute('DELETE bl, mods, pc FROM `'._DB_PREFIX_.self::TABLE.'` AS pc
                LEFT JOIN `'._DB_PREFIX_.self::TABLE_BACKLINK.'` AS bl ON pc.id=bl.id
                LEFT JOIN `'._DB_PREFIX_.self::TABLE_MODULE.'` AS mods ON pc.id=mods.id
                WHERE pc.id_shop IN ('.pSQL(implode(',', $ids_shop)).');');
        }
    }

    public static function clearAllCache() {
        try {
            Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.self::TABLE_BACKLINK.'`;');
            Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.self::TABLE_MODULE.'`;');
        } catch (Exception $e) {
            error_log('Warning, cannot delete cache backlinks ' . $e->getMessage());
        }
    }

    private static function getHash($url) {
        return current(unpack('l', pack('l', crc32($url))));
    }

    /**
     * Determine if a variable is iterable. i.e. can be used to loop over.
     *
     * @return bool
     */
    private static function isIterable($var)
    {
        return $var !== null && (is_array($var) || $var instanceof Traversable);
    }
}