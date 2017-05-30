<?php
class Staticfooter extends ObjectModel
{
    /** @var string Name */
    public $description;
    public $title;
    public $hook_position;
    public $name_module;
    public $hook_module;
    public $position;
    public $active;
    public $insert_module;
    public $showhook;
    public $posorder;
    public $identify;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'lab_staticfooter',
        'multishop' => true,
		'multilang' => true,
        'primary' => 'id_staticblock',
        'fields' => array(
            'order' =>           array('type' => self::TYPE_INT,'lang' => false),
            'active' =>           array('type' => self::TYPE_INT,'lang' => false),
            'insert_module' =>           array('type' => self::TYPE_INT,'lang' => false),
            'showhook' =>           array('type' => self::TYPE_INT,'lang' => false),
			'identify' =>          array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'hook_position' =>          array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'required' => false, 'size' => 128),
            'name_module' =>          array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'required' => false, 'size' => 128),
            'hook_module' =>          array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'required' => false, 'size' => 128),
            // Lang fields
            'title' =>          array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'description' =>            array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),

        ),
    );
    
    public  function getStaticfooterLists($id_shop = NULL, $hook_position= 'top') {

        if (!Combination::isFeatureActive())
			return array();
		$id_lang = (int)Context::getContext()->language->id;
		$object =  Db::getInstance()->executeS('
                    SELECT * FROM '._DB_PREFIX_.'lab_staticfooter AS psb 
					LEFT JOIN '._DB_PREFIX_.'lab_staticfooter_lang AS psl ON psb.id_staticblock = psl.id_staticblock
                    LEFT JOIN '._DB_PREFIX_.'lab_staticfooter_shop AS pss ON psb.id_staticblock = pss.id_staticblock
					WHERE id_shop ='.$id_shop.'
						AND id_lang ='.$id_lang.'
						AND `hook_position` = "'.$hook_position.'" 
						AND `showhook` = 1
		');
                $newObject = array();
                if(count($newObject>0)) {
                    foreach($object as $key=>$ob) {
                       $nameModule = $ob['name_module'];
                       $hookModule = $ob['hook_module'];
                       $blockModule = $this->getModuleAssign($nameModule, $hookModule);
                       $ob['block_module'] = $blockModule;
		       $description = $ob['description'];
			$description = str_replace('/noraure/',__PS_BASE_URI__,$description);
			$ob['description'] = $description;
                      // array_push($ob, $blockModule);
			if($ob['is_default'] ==1) {
				//$ob['description'] = str_replace(__PS_BASE_URI__.'img/cms',__PS_BASE_URI__.'modules/labstaticblocks/images', $ob['description']);
			}
		   $newObject[$key] = $ob;
                    }
                  return $newObject;

                }
                return null;
                
    }
    
    
       public  function getModuleAssign( $module_name = '', $name_hook = '' ){
		//$module_id = 7 ; $id_hook = 21 ;
		
		if(!$module_name || !$name_hook)  return ;
			$module = Module::getInstanceByName($module_name);	
			$module_id = $module->id;
			$id_hook = Hook::getIdByName($name_hook);
			$hook_name = $name_hook;
			if(!$module) return ;
			$module_name = $module->name;
		if( Validate::isLoadedObject($module) && $module->id ){
			$array = array();
			$array['id_hook']   = $id_hook;
			$array['module'] 	= $module_name;
			$array['id_module'] = $module->id;
			if(_PS_VERSION_ < "1.5"){ 
				return self::lofHookExec( $hook_name, array(), $module->id, $array );
			}else{ 
				$hook_name = substr($hook_name, 7, strlen($hook_name));
				return self::renderModuleByHookV15( $hook_name, array(), $module->id, $array );
			}
		}
		return '';			
	}
	
	
	public static function renderModuleByHook( $hook_name, $hookArgs = array(), $id_module = NULL, $array = array() ){
		global $cart, $cookie;
                if(!$hook_name || !$id_module) return ;
		if ((!empty($id_module) AND !Validate::isUnsignedId($id_module)) OR !Validate::isHookName($hook_name))
			die(Tools::displayError());

		$live_edit = false;
		if (!isset($hookArgs['cookie']) OR !$hookArgs['cookie'])
			$hookArgs['cookie'] = $cookie;
		if (!isset($hookArgs['cart']) OR !$hookArgs['cart'])
			$hookArgs['cart'] = $cart;
		$hook_name = strtolower($hook_name);
		$altern = 0;
		
		if ($id_module AND $id_module != $array['id_module'])
			return;
		if (!($moduleInstance = Module::getInstanceByName($array['module'])))
			return;
		/*
		$exceptions = $moduleInstance->getExceptions((int)$array['id_hook'], (int)$array['id_module']);
		foreach ($exceptions AS $exception)
			if (strstr(basename($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING'], $exception['file_name']) && !strstr($_SERVER['QUERY_STRING'], $exception['file_name']))
				return;
		*/
		if (is_callable(array($moduleInstance, 'hook'.$hook_name)))
		{
			$hookArgs['altern'] = ++$altern;
			$output = call_user_func(array($moduleInstance, 'hook'.$hook_name), $hookArgs);
		}
		return $output;
	}
	
	public static function renderModuleByHookV15( $hook_name, $hookArgs = array(), $id_module = NULL, $array = array() ){
		global $cart, $cookie;
               
                if(!$hook_name || !$id_module) return ;
		if ((!empty($id_module) AND !Validate::isUnsignedId($id_module)) OR !Validate::isHookName($hook_name))
			die(Tools::displayError());
		
		if (!isset($hookArgs['cookie']) OR !$hookArgs['cookie'])
			$hookArgs['cookie'] = $cookie;
		if (!isset($hookArgs['cart']) OR !$hookArgs['cart'])
			$hookArgs['cart'] = $cart;
		
		if ($id_module AND $id_module != $array['id_module'])
			return ;
		if (!($moduleInstance = Module::getInstanceByName($array['module'])))
			return ;
		$retro_hook_name = Hook::getRetroHookName($hook_name);
		
		$hook_callable = is_callable(array($moduleInstance, 'hook'.$hook_name));
		$hook_retro_callable = is_callable(array($moduleInstance, 'hook'.$retro_hook_name));
		
		$output = '';
		if (($hook_callable || $hook_retro_callable) && Module::preCall($moduleInstance->name))
		{ 
			if ($hook_callable)
				$output = $moduleInstance->{'hook'.$hook_name}($hookArgs);
			else if ($hook_retro_callable)
				$output = $moduleInstance->{'hook'.$retro_hook_name}($hookArgs);
		}
		return $output;
	}

}