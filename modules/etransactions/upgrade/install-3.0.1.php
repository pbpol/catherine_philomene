<?php
/**
* E-Transactions PrestaShop Module
*
* Feel free to contact E-Transactions at support@e-transactions.fr for any
* question.
*
* LICENSE: This source file is subject to the version 3.0 of the Open
* Software License (OSL-3.0) that is available through the world-wide-web
* at the following URI: http://opensource.org/licenses/OSL-3.0. If
* you did not receive a copy of the OSL-3.0 license and are unable 
* to obtain it through the web, please send a note to
* support@e-transactions.fr so we can mail you a copy immediately.
*
*  @category  Module / payments_gateways
*  @version   3.0.1
*  @author    E-Transactions <support@e-transactions.fr>
*  @copyright 2012-2016 E-Transactions
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.e-transactions.fr/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_1($object)
{
    // Add Back Office actions management
    Configuration::updateValue('ETRANS_BO_ACTIONS', 1);

    // Clean 'payment_by' value in 'etransactions_order' table
    $sql = array();
    $sql[] = 'UPDATE `'._DB_PREFIX_.'etransactions_order` SET `payment_by` = "E-Transactions" WHERE `payment_by` = "E-Transactions"';
    $sql[] = 'UPDATE `'._DB_PREFIX_.'etransactions_order` SET `payment_by` = "E-TransactionsRecurring" WHERE `payment_by` = "E-TransactionsRecurring"';

    foreach ($sql as $query) 
        if (!Db::getInstance()->Execute($query))
            return false;

    return true;
}
