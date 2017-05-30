<?php

class SupplierController extends SupplierControllerCore
{
	/*
    * module: prettyurls
    * date: 2016-07-28 12:52:54
    * version: 1.9.0
    */
    public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('supplier_rewrite')));
		$sup_pattern = '/.*?([0-9]+)\_\_([_a-zA-Z0-9-\pL]*)/';
		preg_match($sup_pattern, $_SERVER['REQUEST_URI'], $sup_array);
		if (isset($sup_array[2]) && $sup_array[2] != '')
			$link_rewrite = $sup_array[2];
		$id_shop = $this->context->shop->id;
		if ($link_rewrite)
		{
			$supplier = Tools::strtolower(str_replace('-', '%', $link_rewrite));
			$sql = 'SELECT t1.id_supplier
					FROM '._DB_PREFIX_.'supplier t1
					LEFT JOIN '._DB_PREFIX_.'supplier_shop t2 ON (t1.id_supplier = t2.id_supplier)
					WHERE t1.name LIKE (\''.pSQL($supplier).'\') AND t2.id_shop = '.(int)$id_shop;
			$id_supplier = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_supplier != '')
			{
				$_POST['id_supplier'] = $id_supplier;
				$_GET['supplier_rewrite'] = '';
			}
		}
		if (preg_match('/\?/', $_SERVER['REQUEST_URI']))
		{
			$req_uri_qmark = explode('?', $_SERVER['REQUEST_URI']);
			$req_uri_without_qmark = $req_uri_qmark[0];
			$req_uri_without_qmark = explode('/', $req_uri_without_qmark);
			$request = end($req_uri_without_qmark);
			$clearify_request = str_replace('-', ' ', $request);
			$supp_existance = (int)$this->getKeyExistanceSup($clearify_request);
			if ($supp_existance > 0)
				$_POST['id_supplier'] = $supp_existance;
		}
		parent::init();
	}
	/*
    * module: prettyurls
    * date: 2016-07-28 12:52:54
    * version: 1.9.0
    */
    private function getKeyExistanceSup($request)
	{
		$sql = 'SELECT `id_supplier`
					FROM '._DB_PREFIX_.'supplier
					WHERE `name` LIKE "'.pSQL($request).'"';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
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