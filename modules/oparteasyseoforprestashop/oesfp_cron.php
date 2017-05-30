<?php
/**
 * Module oparteasyseoforprestashop
 *
 * @category Prestashop
 * @category Module
 * @author    Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright Op'art
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
require_once _PS_MODULE_DIR_.'oparteasyseoforprestashop/oparteasyseoforprestashop.php';

if(!Tools::getIsset('t') || Tools::getValue('t') != Configuration::get('OESFP_TOKEN'))
    die('token invalid');

/* select all automatic settings */
$sql='select * from '._DB_PREFIX_.'oparteasyseoforprestashop_settings where automatic_update = 1';

$settings = db::getInstance()->executeS($sql);
if(!count($settings)>0)
    die('no settings found');

$oesfp = new Oparteasyseoforprestashop();
foreach($settings as $setting) {
    $selected_categories = explode(',',$setting['selected_category']);
    $oesfp->applySettings($setting['meta_title'], $setting['meta_desc'], $setting['id_lang'], $setting['id_shop'], $selected_categories, $setting['override_meta'], $setting['element_type']);
}
die('oesfp_cron ok');