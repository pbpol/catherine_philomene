<?php
if (!defined('_PS_VERSION_'))
    exit;

class LabSearch extends Module {

    public function __construct() {
        $this->name = 'labsearch';
        $this->tab = 'search_filter';
        $this->version = '0.1';
        $this->author = 'Labersthemes';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Search block');
        $this->description = $this->l('Search field to your website.');
    }

    public function install() {
        if (!parent::install() || !$this->registerHook('top') || !$this->registerHook('header')|| !$this->registerHook('searchtop') )
            return false;
        return true;
    }

    public function hookHeader($params) {
       if (Configuration::get('PS_SEARCH_AJAX'))
			$this->context->controller->addJqueryPlugin('autocomplete');
			$this->context->controller->addCSS($this->_path.'css/labsearch.css');
		if (Configuration::get('PS_SEARCH_AJAX'))
		{
			Media::addJsDef(array('search_url' => $this->context->link->getPageLink('search', Tools::usingSecureMode())));
		}
    }

    public function hookLeftColumn($params) {
        return $this->hookTop($params);
    }

    public function hookRightColumn($params) {
        return $this->hookTop($params);
    }
	public function hookSearchtop($params) {
		return $this->hookTop($params);
	}
    public function hookTop($params) {
		$key = $this->getCacheId('blocksearch-top'.((!isset($params['hook_mobile']) || !$params['hook_mobile']) ? '' : '-hook_mobile'));
			if (Tools::getValue('search_query') || !$this->isCached('labsearch_top.tpl', $key))
				{
					$this->calculHookCommon($params);
					$this->smarty->assign(array(
						'ENT_QUOTES' => ENT_QUOTES,
						'search_ssl' => Tools::usingSecureMode(),
						'ajaxsearch' => Configuration::get('PS_SEARCH_AJAX'),
						'instantsearch' => Configuration::get('PS_INSTANT_SEARCH'),
						'self' => dirname(__FILE__),
						'labsearchmod' => __PS_BASE_URI__ . 'modules/labsearch/labsearchAjax.php',
						'blocksearch_type' => 'top',
						'search_query' => (string)Tools::getValue('search_query')
						)
					);
				}
		Media::addJsDef(array('blocksearch_type' => 'top'));
        return $this->display(__FILE__, 'labsearch_top.tpl', Tools::getValue('search_query') ? null : $key);
    }
	private function calculHookCommon($params)
	{
		$this->smarty->assign(array(
			'ENT_QUOTES' =>		ENT_QUOTES,
			'search_ssl' =>		Tools::usingSecureMode(),
			'ajaxsearch' =>		Configuration::get('PS_SEARCH_AJAX'),
			'instantsearch' =>	Configuration::get('PS_INSTANT_SEARCH'),
			'self' =>			dirname(__FILE__),
		));

		return true;
	}
	
}