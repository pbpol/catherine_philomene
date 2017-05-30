<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('labcustomfooter.php');
 $pos = new posstaticfooter();
 $name_module = $_POST['module_id'];
 $module = Module::getInstanceByName($name_module);
 $id_module = $module->id;
 $hooks = $pos->getHooksByModuleId($id_module);
 $hookArrays = array();
 foreach($hooks as $key => $hook) {
	$hookArrays[$key] = array('id_hook'=>$hook['name'], 'name' => $hook['name']);
 }
 $json = json_encode($hookArrays); 
die(json_encode($json));

?>
