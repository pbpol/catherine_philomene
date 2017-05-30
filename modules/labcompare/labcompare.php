<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class labcompare extends Module
{
    private $_html = '';
    public $fields_form;
	public function __construct()
	{
		$this->name          = 'labcompare';
		$this->tab           = 'front_office_features';
		$this->version       = '1.0';
		$this->author        = 'labersthemes';
		$this->need_instance = 0;
		
		parent::__construct();
		
		$this->displayName   = $this->l('Products Comparison');
		$this->description   = $this->l('Ajax add to compare.');
	}

	public function install()
	{
		if (!parent::install() ||
			!$this->registerHook('displayAnywhere') ||
			!$this->registerHook('header') ||
			!$this->registerHook('productActions')
        )
			return false;
		return true;
	}
	public function hookDisplayAnywhere($params)
	{
	    if(!isset($params['caller']) || $params['caller']!=$this->name)
            return false;
		if (!Configuration::get('PS_COMPARATOR_MAX_ITEM'))
            return false;
        if (isset($params['cookie']->id_compare))
        {
            $ids = CompareProduct::getCompareProducts($params['cookie']->id_compare);
            if(!empty($ids))
            {
                $listProducts = array();
                foreach ($ids as $k => $id)
        		{
        			$curProduct = new Product((int)$id, true, $this->context->language->id);
        			if (!Validate::isLoadedObject($curProduct) || !$curProduct->active || !$curProduct->isAssociatedToShop())
        			{
        				if (isset($this->context->cookie->id_compare))
        					CompareProduct::removeCompareProduct($this->context->cookie->id_compare, $id);
        				unset($ids[$k]);
        				continue;
        			}
        			$listProducts[] = $curProduct;
        		}  
                $this->smarty->assign(array(
                    'compare_nbr' => count($listProducts),
                    'listProducts'=>$listProducts,
                ));  
            }
        }
        return $this->display(__FILE__, 'labcompare.tpl');
	}
    public function hookHeader()
    {
		$this->context->controller->addCSS($this->_path. 'views/css/labcompare.css');
		if (!Configuration::get('PS_COMPARATOR_MAX_ITEM'))
            return false;
		$this->context->controller->addJS(($this->_path).'views/js/global.js');
		
        return false;
    }
	public function hookTop($params)
	{
        return $this->display(__FILE__, 'labcompare-top.tpl');
    }
	public function hookProductActions($params)
	{
	    $comparator_max_item = Configuration::get('PS_COMPARATOR_MAX_ITEM');
		if (!$comparator_max_item)
            return false;
        $id_product = (int)Tools::getValue('id_product');
        if(!$id_product)
            return false;
        $product = new Product((int)$id_product, false, $this->context->language->id);
        if (!Validate::isLoadedObject($product) || !$product->active || !$product->isAssociatedToShop())
            return false;
        $image = Product::getCover((int)$product->id);
		$this->smarty->assign(array(
            'product' => $product,
            'product_cover' => (int)$image['id_image'],
            'product_link' => $product->getLink(),
            'comparator_max_item' => $comparator_max_item,
        ));
		return $this->display(__FILE__, 'labcompare-extra.tpl');
	}
}