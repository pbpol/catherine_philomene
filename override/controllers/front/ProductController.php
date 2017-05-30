<?php

class ProductController extends ProductControllerCore
{
	/*
    * module: prettyurls
    * date: 2016-07-28 12:52:54
    * version: 1.9.0
    */
    public function init()
	{
		$link_rewrite 	= Tools::safeOutput(urldecode(Tools::getValue('product_rewrite')));
		$prod_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)\.html/';
		preg_match($prod_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '')
			$link_rewrite = $url_array[2];
		
		if ($link_rewrite)
		{
			$id_lang = $this->context->language->id;
			$id_shop = $this->context->shop->id;
			$sql = 'SELECT id_product
					FROM '._DB_PREFIX_.'product_lang
					WHERE link_rewrite = \''.pSQL($link_rewrite).'\' AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
			$id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_product > 0)
			{
				$_POST['id_product'] = $id_product;
				$_GET['product_rewrite'] = '';
			}
			else
			{
				$prod_pattern_sec = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*\-[0-9\pL]*)\.html/';
				preg_match($prod_pattern_sec, $_SERVER['REQUEST_URI'], $url_array_sec);
			
				if (isset($url_array_sec[2]) && $url_array_sec[2] != '')
				{
					$segments = explode('-', $url_array_sec[2]);
					array_pop($segments);
					$link_rewrite = implode('-', $segments);
				}
				$sql = 'SELECT id_product
					FROM '._DB_PREFIX_.'product_lang
					WHERE link_rewrite = \''.pSQL($link_rewrite).'\' AND id_lang = '.(int)$id_lang.' AND id_shop = '.(int)$id_shop;
				$id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
				if ($id_product > 0)
				{
					$_POST['id_product'] = $id_product;
					$_GET['product_rewrite'] = '';
				}
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
                    $result[$hook_name . '_' . (int)$id_module] = Hook::exec($hook_name, array('product' => $this->product, 'category' => $this->category) , (int)$id_module);
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