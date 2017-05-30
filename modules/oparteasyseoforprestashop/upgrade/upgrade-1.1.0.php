<?php
/**
 * Module oparteasyseo
 *
 * @category Prestashop
 * @category Module
 * @author    Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright Op'art
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_0($module)
{
	if (Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'._DB_PREFIX_.'oparteasyseoforprestashop_settings` LIKE \'override_meta\'') == false)
		Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'oparteasyseoforprestashop_settings` ADD `override_meta` int(1) AFTER `selected_category`');

	if (Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'._DB_PREFIX_.'oparteasyseoforprestashop_settings` LIKE \'automatic_update\'') == false)
		Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'oparteasyseoforprestashop_settings` ADD `automatic_update` int(1) AFTER `override_meta`');
	
        if(!Configuration::get('OESFP_TOKEN')) {
            $oesfp = new Oparteasyseoforprestashop();
            Configuration::updateValue('OESFP_TOKEN', $oesfp->getRandomString());
        }
        return $module;
}