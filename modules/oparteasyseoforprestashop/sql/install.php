<?php
/**
 * Module opartdevis
 *
 * @category Prestashop
 * @category Module
 * @author    Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright Op'art
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'oparteasyseoforprestashop_settings` (
    `id_oparteasyseoforprestashop_settings` int(11) NOT NULL AUTO_INCREMENT,
    `name` longtext,
    `element_type` int(1),
    `meta_title` longtext,
    `meta_desc` longtext,
    `id_lang` int(2),
    `id_shop` int(2),
    `selected_category` longtext,
    `override_meta` int(1),
    `automatic_update` int(1),
    PRIMARY KEY  (`id_oparteasyseoforprestashop_settings`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query)
    if (Db::getInstance()->execute($query) == false)
	return false;
