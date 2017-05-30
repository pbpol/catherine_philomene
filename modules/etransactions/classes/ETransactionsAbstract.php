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
 * Base class for admin page helpers
 */
abstract class ETransactionsAbstract
{
    private $_module;

    // Retrocompatibility 1.4/1.5
    private function initContext()
    {
        if (class_exists('Context')) {
            $this->context = Context::getContext();
        } else {
            global $smarty, $cookie, $link;
            $this->context = new StdClass();
            $this->context->smarty = $smarty;
            $this->context->cookie = $cookie;
            $this->context->link = $link;
        }
    }
    public function __construct(ETransactions $module)
    {
        $this->_module = $module;
        $this->initContext();
    }

    public function l($msg)
    {
        return $this->_module->l($msg, strtolower(get_class($this)));
    }

    public function getConfig()
    {
        return $this->getModule()->getConfig();
    }

    public function getHelper()
    {
        return $this->getModule()->getHelper();
    }

    public function getModule()
    {
        return $this->_module;
    }
}
