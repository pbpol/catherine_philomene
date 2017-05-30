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
*  @version   2.1.3
*  @author    E-Transactions <support@e-transactions.fr>
*  @copyright 2012-2016 E-Transactions
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.e-transactions.fr/
*/

$dir = dirname(dirname(dirname(__FILE__)));
$action = isset($_GET['a']) ? $_GET['a'] : null;

require_once($dir.'/config/config.inc.php');
if (version_compare(_PS_VERSION_, '1.5', '<') || !in_array($action, array('i', 'j'))) {
    require_once($dir.'/init.php');
}
require_once(dirname(__FILE__).'/etransactions.php');

$c = new ETransactionsController();
try {
    switch ($action) {
        // Cancel
        case 'c':
            $c->cancelAction();
            break;

        // Failure
        case 'f':
            $c->failureAction();
            break;

        // Redirect
        case 'r':
            $c->redirectAction();
            break;

        // Success
        case 's':
            $c->successAction();
            break;

        // IPN
        case 'i':
            //file_put_contents('debug.log', file_get_contents('php://input'));die();
            $c->ipnAction();
            break;
        case 'j':
            $c->ipnAction();
            break;
        
        default:
            $c->defaultAction();
    }
}
catch (Exception $e) {
    header('Status: 500 Error', true, 500);
    echo $e->getMessage();
}