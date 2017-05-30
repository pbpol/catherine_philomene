<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

if (!defined('_PS_VERSION_'))
    exit;

include_once(dirname(__FILE__) . '/../../pagecache.php');

class pagecacheClearcacheModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('clearcache.tpl');

        $result = 'Not OK';
        if (Module::isEnabled("pagecache")) {
            $token = Tools::getValue('token', '');
            $goodToken = Configuration::get('pagecache_cron_token');
            if ($goodToken && strcmp($goodToken, $token) === 0) {
                $is_specific = false;
                foreach (PageCache::$managed_controllers as $controller) {
                    if (Tools::getIsset($controller)) {
                        // It's not a global reset
                        $is_specific = true;

                        if (strcmp($controller, 'index') === 0
                            || strcmp($controller, 'newproducts') === 0
                            || strcmp($controller, 'bestsales') === 0
                            || strcmp($controller, 'pricesdrop') === 0
                            || strcmp($controller, 'sitemap') === 0
                            || strcmp($controller, 'contact') === 0
                            || strcmp($controller, 'sitemap') === 0
                        ) {
                            // No ids for this controller
                            //echo "Deleting $controller <br/>";
                            PageCacheDAO::clearCacheOfObject($controller, null, true, 'from CRON');
                        } else {
                            $ids_str = Tools::getValue($controller);
                            $ids = self::parseIds($ids_str);
                            foreach ($ids as $id) {
                                // Delete object one after the other
                                //echo "Deleting $controller # $id <br/>";
                                PageCacheDAO::clearCacheOfObject($controller, $id, true, 'from CRON');
                            }
                        }
                    }
                }
                if (!$is_specific) {
                    // Clear the whole cache
                    $module = Module::getInstanceByName("pagecache");
                    $module->clearCache();
                }
                $result = 'OK';
            } else {
                header("HTTP/1.0 404 Not Found");
                $result = 'Not OK: bad token ' . $token;
            }
        } else {
            $result = 'Not OK: module not active';
        }
        $this->context->smarty->assign(array(
            'result' => $result
        ));
    }

    public function getLayout()
    {
        return _PS_MODULE_DIR_ . 'pagecache/views/templates/front/layout.tpl';
    }

    /**
     * @param string $ids Comma separated list of IDs
     * @return multitype:number Array of ID
     */
    private function parseIds($ids)
    {
        $ids_array = array();
        if (!empty($ids)) {
            $ids_str = explode(',', $ids);
            foreach ($ids_str as $id_str) {
                $id = (int) $id_str;
                if ($id > 0) {
                    $ids_array[] = $id;
                }
            }
        }
        return $ids_array;
    }
}
