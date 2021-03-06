<?php

class CategoryController extends CategoryControllerCore
{
	/*
    * module: prettyurls
    * date: 2016-07-28 12:52:54
    * version: 1.9.0
    */
    public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('category_rewrite')));
		$cat_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cat_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '')
			$link_rewrite = $url_array[2];
		if ($link_rewrite)
		{
			$id_lang = $this->context->language->id;
			$id_shop = $this->context->shop->id;
			$sql = 'SELECT `id_category`
					FROM '._DB_PREFIX_.'category_lang
					WHERE `link_rewrite` = \''.pSQL($link_rewrite).'\'
					AND `id_lang` = '.(int)$id_lang.'
					AND `id_shop` = '.(int)$id_shop;
			$id_category = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_category > 0)
			{
				$_POST['id_category'] = $id_category;
				$_GET['category_rewrite'] = '';
			}
			elseif ($id_category <= 0)
			{
				$_GET['category_rewrite'] = '';
				Tools::redirect($this->context->link->getPageLink('page-not-found'));
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