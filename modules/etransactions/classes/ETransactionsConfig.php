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
*  @version   3.0.3
*  @author    E-Transactions <support@e-transactions.fr>
*  @copyright 2012-2016 E-Transactions
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.e-transactions.fr/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Module configuration
 */
class ETransactionsConfig
{
    private $_defaults = array(
        'ETRANS_3DS'                            => 0,
        'ETRANS_3DS_MIN_AMOUNT'                 => '',
        'ETRANS_DEBUG_MODE'                     => 'FALSE',
        'ETRANS_HASH'                           => 'SHA512',
        'ETRANS_IDENTIFIANT'                    => '259207933',
        'ETRANS_KEYTEST'                        => '4642EDBBDFF9790734E673A9974FC9DD4EF40AA2929925C40B3A95170FF5A578E7D2579D6074E28A78BD07D633C0E72A378AD83D4428B0F3741102B69AD1DBB0',
        'ETRANS_PASS'                           => 'ETRANSACTIONS',
        'ETRANS_PRODUCTION'                     => 0,
        'ETRANS_RANG'                           => '95',
        'ETRANS_SITE'                           => '9999999',
        'ETRANS_WEB_CASH_DIFF_DAY'              => 0,
        'ETRANS_WEB_CASH_TYPE'                  => 'immediate',
        'ETRANS_AUTORIZE_WALLET_CARD'           => 'CB,VISA,EUROCARD_MASTERCARD',
        'ETRANS_WEB_CASH_ENABLE'                => 1,
        'ETRANS_WEB_CASH_VALIDATION'            => '',
        'ETRANS_WEB_CASH_STATE'                 => 2,
        'ETRANS_WEB_CASH_DIRECT'                => 1,
        'ETRANS_RECURRING_ENABLE'               => '',
        'ETRANS_RECURRING_NUMBER'               => '0',
        'ETRANS_RECURRING_PERIODICITY'          => '',
        'ETRANS_RECURRING_ADVANCE'              => '',
        'ETRANS_RECURRING_MIN_AMOUNT'           => '',
        'ETRANS_RECURRING_MODE'                 => 'NX',
        'ETRANS_LAST_STATE_NX'                  => 2,
        'ETRANS_MIDDLE_STATE_NX'                => '',
        'ETRANS_SUBSCRIBE_NUMBER'               => '0',
        'ETRANS_SUBSCRIBE_PERIODICITY'          => '',
        'ETRANS_SUBSCRIBE_DAY'                  => '1',
        'ETRANS_SUBSCRIBE_DELAY'                => '0',
        'ETRANS_DIRECT_ACTION'                  => 'N',
        'ETRANS_DIRECT_VALIDATION'              => '',
        'ETRANS_WALLET_ACTION'                  => 'N',
        'ETRANS_WALLET_PERSONNAL_DATA'          => 0,
        'ETRANS_DEFAULTCATEGORYID'              => '',
        'ETRANS_WEB_CASH_ACTION'                => 'N',
        'ETRANS_BO_ACTIONS'                     => 1,
        'ETRANS_PAYMENT_DISPLAY'                => 0,
        //'ETRANS_CANCEL_URL'                     => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php',
        //'ETRANS_NOTIFICATION_URL'               => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php',
        //'ETRANS_RETURN_URL'                     => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation.php',
        //'ETRANS_NOTIFICATION_NX_URL'            => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation_nx.php',
        //'ETRANS_RETURN_NX_URL'                  => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/validation_nx.php',
    );

    private $_urls = array(
        'system' => array(
            'test' => array(
                'https://preprod-tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi'
            ),
            'production' => array(
                'https://tpeweb.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
                'https://tpeweb1.e-transactions.fr/cgi/MYchoix_pagepaiement.cgi',
            ),
        ),
        'kwixo' => array(
            'test' => array(
                'https://preprod-tpeweb.e-transactions.fr/php/'
            ),
            'production' => array(
                'https://tpeweb.e-transactions.fr/php/',
                'https://tpeweb1.e-transactions.fr/php/',
            ),
        ),
        'mobile' => array(
            'test' => array(
                'https://preprod-tpeweb.e-transactions.fr/cgi/MYframepagepaiement_ip.cgi'
            ),
            'production' => array(
                'https://tpeweb.e-transactions.fr/cgi/MYframepagepaiement_ip.cgi',
                'https://tpeweb1.e-transactions.fr/cgi/MYframepagepaiement_ip.cgi',
            ),
        ),
        'direct' => array(
            'test' => array(
                'https://preprod-ppps.e-transactions.fr/PPPS.php'
            ),
            'production' => array(
                'https://ppps.e-transactions.fr/PPPS.php',
                'https://ppps1.e-transactions.fr/PPPS.php',
            ),
        ),
        'resabo' => array(
            'test' => array(
                'https://preprod-tpeweb.e-transactions.fr/cgi-bin/ResAbon.cgi'
            ),
            'production' => array(
                'https://tpeweb.e-transactions.fr/cgi-bin/ResAbon.cgi',
                'https://tpeweb1.e-transactions.fr/cgi-bin/ResAbon.cgi',
            ),
        ),
    );

    // Remaining
    // 'ETRANS_AUTORIZE_WALLET_CARD'           => 'CB,VISA,EUROCARD_MASTERCARD',
    // 'ETRANS_WEB_CASH_ENABLE'                => 1,
    // 'ETRANS_WEB_CASH_STATE'                 => 2,
    // 'ETRANS_WEB_CASH_DIRECT'                => 1,
    // 'ETRANS_RECURRING_ENABLE'               => '',
    // 'ETRANS_RECURRING_NUMBER'               => '0',
    // 'ETRANS_RECURRING_PERIODICITY'          => '',
    // 'ETRANS_RECURRING_ADVANCE'              => '',
    // 'ETRANS_RECURRING_MIN_AMOUNT'           => '',
    // 'ETRANS_RECURRING_MODE'                 => 'NX',
    // 'ETRANS_LAST_STATE_NX'                  => 2,
    // 'ETRANS_MIDDLE_STATE_NX'                => '',
    // 'ETRANS_SUBSCRIBE_NUMBER'               => '0',
    // 'ETRANS_SUBSCRIBE_PERIODICITY'          => '',
    // 'ETRANS_SUBSCRIBE_DAY'                  => '1',
    // 'ETRANS_SUBSCRIBE_DELAY'                => '0',
    // 'ETRANS_DIRECT_ACTION'                  => 'N',
    // 'ETRANS_DIRECT_VALIDATION'              => '',
    // 'ETRANS_WALLET_ACTION'                  => 'N',
    // 'ETRANS_WALLET_PERSONNAL_DATA'          => 0,
    // 'ETRANS_DEFAULTCATEGORYID'              => ''

    private function _get($name)
    {
        $value = Configuration::get($name);
        if (is_null($value)) {
            $value = false;
        }

        if (($value === false) || ($name=='ETRANS_HASH' && $value === '') && isset($this->_defaults[$name])) {
            $value = $this->_defaults[$name];
        }

        return $value;
    }

    public function get3DSEnabled()
    {
        return $this->_get('ETRANS_3DS');
    }

    public function get3DSAmount()
    {
        return $this->_get('ETRANS_3DS_MIN_AMOUNT');
    }

    public function getAllowedIps()
    {
        return array('194.2.122.158','195.25.7.166','195.101.99.76','194.2.122.190', '195.25.67.22');
    }

    public function getAutoCaptureState()
    {
        $value = $this->_get('ETRANS_WEB_CASH_VALIDATION');
        return empty($value) ? -1 : intval($value);
    }

    public function getDebitType()
    {
        return $this->_get('ETRANS_WEB_CASH_TYPE');
    }

    public function getDefaults()
    {
        return $this->_defaults;
    }

    public function getDelay()
    {
        return $this->_get('ETRANS_WEB_CASH_DIFF_DAY');
    }

    public function getDeliveryDelay()
    {
        return $this->_get('ETRANS_NBDELIVERYDAYS');
    }

    public function getHmacAlgo()
    {
        return $this->_get('ETRANS_HASH');
    }

    public function getHmacKey()
    {
        $value = $this->_get('ETRANS_KEYTEST');
        $crypt = new ETransactionsEncrypt();
        $value = $crypt->decrypt($value);

        return $value;
    }

    public function getIdentifier()
    {
        return $this->_get('ETRANS_IDENTIFIANT');
    }

    public function getKwixoSuccessState()
    {
        return $this->_get('ETRANS_KWIXO');
    }

    public function getPassword()
    {
        $value = $this->_get('ETRANS_PASS');
        $crypt = new ETransactionsEncrypt();
        $value = $crypt->decrypt($value);

        return $value;
    }

    public function getRank()
    {
        return $this->_get('ETRANS_RANG');
    }

    public function getRecurringMinimalAmount()
    {
        return floatval($this->_get('ETRANS_RECURRING_MIN_AMOUNT'));
    }

    public function getSite()
    {
        return $this->_get('ETRANS_SITE');
    }

    public function getSubscription()
    {
        return $this->_get('ETRANS_WEB_CASH_DIRECT');
    }

    public function getSuccessState()
    {
        return $this->_get('ETRANS_WEB_CASH_STATE');
    }

    protected function _getUrls($type)
    {
           $environment = $this->isProduction() ? 'production' : 'test';
        if (isset($this->_urls[$type][$environment])) {
            return $this->_urls[$type][$environment];
        }

        return array();
    }

    public function getDirectUrls()
    {
        return $this->_getUrls('direct');
    }

    public function getKwixoUrls()
    {
        return $this->_getUrls('kwixo');
    }

    public function getMobileUrls()
    {
        return $this->_getUrls('mobile');
    }

    public function getSystemUrls()
    {
        return $this->_getUrls('system');
    }

    public function getResAboUrls()
    {
        return $this->_getUrls('resabo');
    }

    public function isDebug()
    {
        return $this->_get('ETRANS_DEBUG_MODE') == 1;
    }

    public function isRecurringEnabled()
    {
        return $this->_get('ETRANS_RECURRING_ENABLE') == 1;
    }

    public function getDebitTypeForCard()
    {
        $type = $this->getDebitType();
        if ('immediate' === $type) {
            return 'immediat';
        } elseif ('delayed' === $type) {
            return 'differe';
        } elseif ('receive' === $type) {
            return 'expedition';
        } else {
            return $type;
        }
    }

    public function isRecurringCard($method)
    {
        if (in_array($method['type_card'], array('CB', 'VISA', 'EUROCARD_MASTERCARD', 'AMEX'))) {
            return true;
        }

        return false;
    }

    public function isProduction()
    {
        return $this->_get('ETRANS_PRODUCTION') == 1;
    }
}
