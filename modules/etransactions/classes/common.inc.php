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
*  @version   2.0.0
*  @author    E-Transactions <support@e-transactions.fr>
*  @copyright 2012-2016 E-Transactions
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.e-transactions.fr/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Base includes
 */
$dir = dirname(__FILE__).'/';
require_once($dir.'ETransactionsAbstract.php');
require_once($dir.'ETransactionsConfig.php');
require_once($dir.'ETransactionsController.php');
require_once($dir.'ETransactionsCurl.php');
require_once($dir.'ETransactionsEncrypt.php');
require_once($dir.'ETransactionsHelper.php');
require_once($dir.'ETransactionsInstaller.php');
require_once($dir.'ETransactionsKwixo.php');
require_once($dir.'ETransactionsDb.php');
