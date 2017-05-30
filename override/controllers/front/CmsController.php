<?php

class CmsController extends CmsControllerCore
{
	/*
    * module: prettyurls
    * date: 2016-07-28 12:52:54
    * version: 1.9.0
    */
    public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('cms_rewrite')));
		$cms_pattern = '/.*?content\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '')
			$link_rewrite = $url_array[2];
		$cms_category_rewrite 	= Tools::safeOutput(urldecode(Tools::getValue('cms_category_rewrite')));
		$cms_cat_pattern = '/.*?content\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_cat_pattern, $_SERVER['REQUEST_URI'], $url_cat_array);
		if (isset($url_cat_array[2]) && $url_cat_array[2] != '')
			$cms_category_rewrite = $url_cat_array[2];
		$id_lang = $this->context->language->id;
		$id_shop = $this->context->shop->id;
		if ($link_rewrite)
		{
			$sql = 'SELECT tl.id_cms
					FROM '._DB_PREFIX_.'cms_lang tl
					LEFT OUTER JOIN '._DB_PREFIX_.'cms_shop t ON (t.id_cms = tl.id_cms)
					WHERE tl.link_rewrite = \''.pSQL($link_rewrite).'\' AND tl.id_lang = '.(int)$id_lang.' AND t.id_shop = '.(int)$id_shop;
			$id_cms = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_cms != '')
			{
				$_POST['id_cms'] = $id_cms;
				$_GET['cms_rewrite'] = '';
			}
		}
		elseif ($cms_category_rewrite)
		{
			$sql = 'SELECT id_cms_category
					FROM '._DB_PREFIX_.'cms_category_lang
					WHERE link_rewrite = \''.pSQL($cms_category_rewrite).'\' AND id_lang = '.(int)$id_lang;
			$id_cms_category = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_cms_category != '')
			{
				$_GET['cms_category_rewrite'] = '';
				$_POST['id_cms_category'] = $id_cms_category;
			}
		}
		parent::init();
	}
    /*
    * module: pagecache
    * date: 2017-04-28 12:12:47
    * version: 3.17
    */
    public function displayAjax()
    {
        $result = array();
        $index = 0;
        do
        {
            $val = Tools::getValue('hook_' . $index);
            if ($val !== false)
            {
                list($hook_name, $id_module) = explode('|', $val);
                if (Validate::isHookName($hook_name)) {
                    $result[$hook_name . '_' . (int)$id_module] = Hook::exec($hook_name, array() , (int)$id_module);
                }
            }
            $index++;
        } while ($val !== false);
        if (Tools::version_compare(_PS_VERSION_,'1.6','>')) {
            Media::addJsDef(array(
                'isLogged' => (bool)$this->context->customer->isLogged(),
                'isGuest' => (bool)$this->context->customer->isGuest(),
                'comparedProductsIds' => $this->context->smarty->getTemplateVars('compared_products'),
            ));
            $this->context->smarty->assign(array(
                    'js_def' => Media::getJsDef(),
            ));
            $result['js'] = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_.'javascript.tpl');
        }
        $this->context->cookie->write();
        die(Tools::jsonEncode($result));
    }
}