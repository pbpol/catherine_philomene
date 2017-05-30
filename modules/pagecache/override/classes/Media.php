<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

class Media extends MediaCore
{

    // Flag to know if the pagecache module is active or not
    private static $_is_page_cache_active = -1;

    private static function _isPageCacheActive()
    {
        // Overidde added by PageCache
        if (self::$_is_page_cache_active == -1)
        {
            if (file_exists(dirname(__FILE__).'/../../modules/pagecache/pagecache.php'))
            {
                require_once(dirname(__FILE__).'/../../modules/pagecache/pagecache.php');
                self::$_is_page_cache_active = Module::isEnabled('pagecache');
            } else {
                Logger::addLog('Page cache has not been well uninstalled, please, remove manually the following functions in file '.__FILE__.': _isPageCacheActive(), cccCss(), cccJS() and clearCache(). If you need help contact our support.', 4);
                return false;
            }
        }
        return self::$_is_page_cache_active;
    }

    public static function clearCache()
    {
        parent::clearCache();
        if (self::_isPageCacheActive()) {
            PageCache::clearCache();
        }
    }
}
