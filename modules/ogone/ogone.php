<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop etSA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'DirectLink.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'CurrencyCacheCleaner.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'ProductCacheCleaner.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'SpecificPriceCacheCleaner.php';

if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'OgoneTransactionLog.php';
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'OgoneAlias.php';
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'OgonePM.php';
} else {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'OgoneTransactionLog14.php';
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'OgoneAlias14.php';
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'OgonePM14.php';
}

class Ogone extends PaymentModule
{

    const AUTHORIZATION_CANCELLED = 'OGONE_AUTHORIZATION_CANCELLED';
    const CANCELLED = 'OGONE_CANCELLED';
    const PAYMENT_ACCEPTED = 'OGONE_PAYMENT_ACCEPTED';
    const PAYMENT_AUTHORIZED = 'OGONE_PAYMENT_AUTHORIZED';
    const PAYMENT_CANCELLED = 'OGONE_PAYMENT_CANCELLED';
    const PAYMENT_ERROR = 'OGONE_PAYMENT_ERROR';
    const PAYMENT_IN_PROGRESS = 'OGONE_PAYMENT_IN_PROGRESS';
    const PAYMENT_UNCERTAIN = 'OGONE_PAYMENT_UNCERTAIN';
    const REFUND = 'OGONE_REFUND';
    const REFUND_ERROR = 'OGONE_REFUND_ERROR';
    const REFUND_IN_PROGRESS = 'OGONE_REFUND_IN_PROGRESS';

    const OPERATION_SALE = 'SAL';
    const OPERATION_AUTHORISE = 'RES';

    const MAX_LOG_FILES_ADVISED = 10;

    const DL_ALIAS_RET_PAYMENT_DONE = 1;
    const DL_ALIAS_RET_INJECT_HTML  = 2;
    const DL_ALIAS_RET_ERROR        = 3;

    const INGENICO_ECI_ECOMMERCE = 7; // ECI value "9" must be sent for reccurring transactions. - asking for CVC
    const INGENICO_ECI_DL = 9; // ECI value "9" must be sent for reccurring transactions. - not asking for CVC


    /*
     * flag for eu_legal
     * @see https://github.com/EU-Legal/
     */
    public $is_eu_compatible = true;

    /**
     *
     * @var unknown
     */
    protected $log_file = null;

    /**
     * DirectLink library instance
     * @var DirectLink
     */
    protected $direct_link_instance = null;

    /**
     * Cache of colors associated with Ingenico payment statuses
     * @var array
     */
    protected $return_code_list_colors = array();

    /**
     * List of fields to ignore in sha sign generation
     * @var array
     */
    protected $ignore_key_list = array('secure_key', 'ORIG', 'controller', '3ds', 'aid', 'dg', 'result', 'RESULT',
        'alias_full', 'fc', 'module', 'controller', 'id_lang', 'aip', 'isolang');

    /**
     * List of required fields for payment return
     * @var array
     */
    protected $needed_key_list = array('ACCEPTANCE', 'amount', 'BRAND', 'CARDNO', 'currency', 'NCERROR', 'orderID',
        'PAYID', 'PM', 'SHASIGN', 'STATUS');

    protected $sha_out_fields = array('AAVADDRESS', 'AAVCHECK', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT', 'BIC',
        'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CURRENCY', 'CVCCHECK', 'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE', 'DCC_EXCHRATETS', 'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE', 'DCC_VALIDHOURS', 'DEVICE', 'DIGESTCARDNO', 'ED', 'HTML_ANSWER', 'IP', 'IPCTY',
        'MANDATEID', 'NCERROR', 'NCERRORPLUS', 'NCSTATUS', 'ORDERID', 'PARAMPLUS', 'PAYID', 'PAYIDSUB', 'PM', 'PSPID',
        'SCO_CATEGORY', 'SCORING', 'SEQUENCETYPE', 'SIGNDATE', 'STATUS', 'SUBBRAND', 'SUBSCRIPTION_ID', 'TRXDATE', 'VC'
    );

    /**
     * List of operations allowed
     * @var array
     */
    protected $allowed_operations = array(self::OPERATION_SALE, self::OPERATION_AUTHORISE);

    /**
     * Return codes
     * @var array
     */
    protected $return_codes = array(
        0 => array('Incomplete or invalid', self::PAYMENT_CANCELLED),
        1 => array('Cancelled by customer', self::PAYMENT_CANCELLED),
        2 => array('Authorisation declined', self::PAYMENT_ERROR),
        4 => array('Order stored', self::PAYMENT_AUTHORIZED),
        40 => array('Stored waiting external result', self::PAYMENT_AUTHORIZED),
        41 => array('Waiting for client payment	', self::PAYMENT_AUTHORIZED),
        5 => array('Authorised', self::PAYMENT_AUTHORIZED),
        50 => array('Authorized waiting external result', self::PAYMENT_AUTHORIZED),
        51 => array('Authorisation waiting', self::PAYMENT_AUTHORIZED),
        52 => array('Authorisation not known', self::PAYMENT_AUTHORIZED),
        55 => array('Standby', self::PAYMENT_AUTHORIZED),
        56 => array('OK with scheduled payments', self::PAYMENT_AUTHORIZED),
        57 => array('Not OK with scheduled payments', self::PAYMENT_ERROR),
        59 => array('Authoris. to be requested manually', self::PAYMENT_ERROR),
        6 => array('Authorised and cancelled', self::AUTHORIZATION_CANCELLED),
        61 => array('Author. deletion waiting', self::AUTHORIZATION_CANCELLED),
        62 => array('Author. deletion uncertain', self::AUTHORIZATION_CANCELLED),
        63 => array('Author. deletion refused', self::AUTHORIZATION_CANCELLED),
        64 => array('Authorised and cancelled', self::AUTHORIZATION_CANCELLED),
        7  => array('Payment deleted', self::CANCELLED),
        71 => array('Payment deletion pending', self::CANCELLED),
        72 => array('Payment deletion uncertain', self::CANCELLED),
        73 => array('Payment deletion refused', self::CANCELLED),
        74 => array('Payment deleted', self::CANCELLED),
        75 => array('Deletion processed by merchant', self::CANCELLED),
        8 => array('Refund', self::REFUND),
        81 => array('Refund pending', self::REFUND_IN_PROGRESS),
        82 => array('Refund uncertain', self::REFUND_IN_PROGRESS),
        83 => array('Refund refused', self::REFUND_ERROR),
        84 => array('Payment declined by the acquirer', self::REFUND_ERROR),
        85 => array('Refund processed by merchant', self::REFUND),
        9 => array('Payment requested', self::PAYMENT_ACCEPTED),
        91 => array('Payment processing', self::PAYMENT_IN_PROGRESS),
        92 => array('Payment uncertain', self::PAYMENT_UNCERTAIN),
        93 => array('Payment refused', self::PAYMENT_ERROR),
        94 => array('Refund declined', self::PAYMENT_ERROR),
        95 => array('Payment processed by merchant', self::PAYMENT_ACCEPTED),
        96 => array('Refund reversed', self::PAYMENT_ACCEPTED),
        99 => array('Being processed', self::PAYMENT_IN_PROGRESS),
    );

    /**
     * List of new states to install
     * At list names['en'] is mandatory
     * @var array
     */
    protected $new_statuses = array(
        self::PAYMENT_IN_PROGRESS => array(
            'names' => array('en' => 'Ingenico ePayments - payment in progress', 'fr' => 'Ingenico ePayments - paiement en cours'),
            'properties' => array('color' => 'royalblue', 'logable' => true),
        ),
        self::PAYMENT_UNCERTAIN => array(
            'names' => array('en' => 'Ingenico ePayments - payment uncertain', 'fr' => 'Ingenico ePayments - paiement incertain'),
            'properties' => array('color' => 'orange'),
        ),
        self::PAYMENT_AUTHORIZED => array(
            'names' => array('en' => 'Ingenico ePayments - payment reserved', 'fr' => 'Ingenico ePayments - paiement reservé'),
            'properties' => array('color' => 'royalblue'),
        ),
    );

    /**
     * List of disponible languages for Ingenico urls and docs
     * @var array
     */
    protected $documentation_languages = array('en', 'fr', 'es', 'de', 'nl');

    protected $test_account_url = 'https://secure.ogone.com/Ncol/Test/BackOffice/accountcreation/create';
    protected $ingenico_server = 'https://payment-services.ingenico.com/';
    protected $int_guide = '%s/%s/ogone/support/guides/integration%%20guides/prestashop-extension';
    protected $dl_guide = '%s/%s/ogone/support/guides/integration%%20guides/directlink';
    protected $support_url = '%s/%s/ogone/support/contact';

    /* Public API to check whether TSL version is correct */
    protected $check_tls_api = 'https://www.howsmyssl.com/a/check';

    protected $cipher_tool = null;

    protected $tls_version_expected = '1.2';

    /**
     * Localized contact and documentation data
     * Used in backoffice templates
     * @var array
     */
    protected $localized_contact_data = array(
        'en' => array(
            'support_email' => 'support@ecom.ingenico.com',
            'support_phone_number' => '+44 (0)203 147 4966',
            'sales_email' => 'salesuk.ecom@ingenico.com',
            'sales_phone_number' => '+44 (0)203 147 4966',
            'test_account_query' => 'BRANDING=ogone&ISP=OGB&SubId=7&MODE=STD&SOLPRO=prestashopCOSP&ACOUNTRY=GB&Lang=1',
        ),
        'fr' => array(
            'support_email' => 'support@ecom.ingenico.com',
            'support_phone_number' => '+33 (0)1 70 70 09 03',
            'sales_email' => 'salesfr.ecom@ingenico.com',
            'sales_phone_number' => '+33 (0)1 70 70 09 03',
            'test_account_query' => 'BRANDING=ogone&ISP=OFR&SubId=3&MODE=STD&SOLPRO=prestashopCOSP&ACOUNTRY=FR&Lang=2',
        ),
        'es' => array(
            'support_email' => 'support@ecom.ingenico.com',
            'support_phone_number' => '+34 91 312 74 00',
            'sales_email' => 'salesfr.ecom@ingenico.com',
            'sales_phone_number' => '+34 91 312 74 00',
            'test_account_query' => 'BRANDING=ogone&ISP=ODE&SubId=5&MODE=STD&SOLPRO=prestashopCOSP&ACOUNTRY=ES&Lang=6',
        ),
        'de' => array(
            'support_email' => 'support@ecom.ingenico.com',
            'support_phone_number' => '+49 0800 673 50 00',
            'sales_email' => 'salesde.ecom@ingenico.com',
            'sales_phone_number' => '+49 0800 673 50 00',
            'test_account_query' => '?BRANDING=ogone&ISP=ODE&SubId=5&MODE=STD&SOLPRO=prestashopCOSP&ACOUNTRY=DE&Lang=5',
        ),
        'nl' => array(
            'support_email' => 'support@ecom.ingenico.com',
            'support_phone_number' => '+31 (0)20 840 8400',
            'sales_email' => 'salesnl.ecom@ingenico.com',
            'sales_phone_number' => '+31 (0)20 840 8400',
            'test_account_query' => 'BRANDING=ogone&ISP=ONL&SubId=2&MODE=STD&SOLPRO=prestashopcosp&ACOUNTRY=NL&Lang=8',
        ),

    );

    /**
     * List of allowed fonts for static template
     * @var array
     */
    protected $static_template_fonts = array('Arial', 'Charcoal', 'Courier', 'Helvetica',
        'Impact', 'Monaco', 'Tahoma', 'Verdana');

    /**
     * List of required fields for alias creation return
     * @var array
     */
    protected $expected_alias_return_fields = array('ALIAS', 'CARDNO', 'CN', 'ED', 'NCERROR', 'STATUS', 'BRAND');

    protected $tpl_fields = array('TITLE', 'BGCOLOR', 'TXTCOLOR', 'TBLBGCOLOR',
        'TBLTXTCOLOR', 'BUTTONBGCOLOR', 'BUTTONTXTCOLOR', 'FONTTYPE');

    protected $selected_tab = null;

    protected $klarna_countries = array('SE', 'FI', 'DK', 'NO', 'DE', 'NL');

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->name = 'ogone';
        $this->tab = 'payments_gateways';
        $this->version = '3.3.2';
        $this->author = 'Ingenico ePayments';
        $this->module_key = 'bd2bfda4b61f90c8f852ff252d8baaef';

        parent::__construct();

        $this->displayName = 'Ingenico ePayments (formerly Ogone)';
        $this->description = $this->l('Ingenico ePayment offers you one single platform to handle all your online transactions whatever the channel.');

        /* Backward compatibility */
        require_once _PS_MODULE_DIR_ . 'ogone/backward_compatibility/backward.php';
        if (!isset($this->context) && version_compare(_PS_VERSION_, 1.5, 'lt')) {

            $this->context = Context::getContext();
        }


    }

    /* INSTALL */

    /**
     * Install
     */
    public function install()
    {
        $this->updatePaymentConfig();
        $result = parent::install() &&
        $this->addStatuses() &&
        $this->installDBTables() &&
        $this->installHooks() &&
        $this->initConfigVars() &&
        $this->installTabs() &&
        $this->addDefaultPaymentModes();
        return $result;
    }

    public function uninstall()
    {
        return
        $this->removeTabs() &&
        $this->uninstallDBTables() &&
        parent::uninstall();
    }

    /**
     * Creates config values for payment states for 1.4.3 and less compatibility
     *
     */
    protected function updatePaymentConfig()
    {
        $states = array('PS_OS_CHEQUE', 'PS_OS_PAYMENT', 'PS_OS_PREPARATION', 'PS_OS_SHIPPING', 'PS_OS_CANCELED',
            'PS_OS_REFUND', 'PS_OS_ERROR', 'PS_OS_OUTOFSTOCK', 'PS_OS_BANKWIRE', 'PS_OS_PAYPAL', 'PS_OS_WS_PAYMENT');
        if (!Configuration::get('PS_OS_PAYMENT')) {
            foreach ($states as $u) {
                if (!Configuration::get($u) && defined('_' . $u . '_')) {
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                }
            }
        }

        return true;
    }

    /**
     * Install hooks
     */
    public function installHooks()
    {
        $result = true;
        foreach ($this->getHooksList() as $hook) {
            $result = $result && $this->registerHook($hook);
        }
        return $result;

    }

    protected function getHooksList()
    {
        $hooks = array('payment', 'header', 'orderConfirmation', 'backOfficeHeader', 'customerAccount');
        if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
            $hooks[] = 'displayAdminOrder';
        } else {
            $hooks[] = 'adminOrder';
        }
        if (method_exists('Hook', 'getIdByName') &&
            is_callable(array('Hook', 'getIdByName')) &&
            Hook::getIdByName('displayPaymentEU')) {
            $hooks[] = 'displayPaymentEU';
        }
        return $hooks;
    }

    /**
     * Init config variables
     */
    public function initConfigVars()
    {

        $new_alias_pm = array(
            'CreditCard' => 1,
            'DirectDebits DE' => 0,
            'DirectDebits NL' => 0,
            'DirectDebits AT' => 0,
        );

        $result = Configuration::updateValue('OGONE_ALIAS_PM', Tools::jsonEncode($new_alias_pm)) &&
        Configuration::updateValue('OGONE_ALIAS_SHA_IN', '') &&
        Configuration::updateValue('OGONE_BGCOLOR', '#ffffff') &&
        Configuration::updateValue('OGONE_BUTTONBGCOLOR', '') &&
        Configuration::updateValue('OGONE_BUTTONTXTCOLOR', '#000000') &&
        Configuration::updateValue('OGONE_DL_PASSWORD', '') &&
        Configuration::updateValue('OGONE_DL_SHA_IN', '') &&
        Configuration::updateValue('OGONE_DL_TIMEOUT', 30) &&
        Configuration::updateValue('OGONE_DL_USER', '') &&
        Configuration::updateValue('OGONE_FONTTYPE', 'Verdana') &&
        Configuration::updateValue('OGONE_LOGO', '') &&
        Configuration::updateValue('OGONE_MODE', 0) &&
        Configuration::updateValue('OGONE_OPERATION', self::OPERATION_SALE) &&
        Configuration::updateValue('OGONE_TBLBGCOLOR', '#ffffff') &&
        Configuration::updateValue('OGONE_TBLTXTCOLOR', '#000000') &&
        Configuration::updateValue('OGONE_TITLE', '') &&
        Configuration::updateValue('OGONE_TXTCOLOR', '#000000') &&
        Configuration::updateValue('OGONE_USE_ALIAS', 0) &&
        Configuration::updateValue('OGONE_USE_DL', 0) &&
        Configuration::updateValue('OGONE_USE_KLARNA', 0) &&
        Configuration::updateValue('OGONE_USE_LOG', 0) &&
        Configuration::updateValue('OGONE_USE_PM', 0) &&
        Configuration::updateValue('OGONE_USE_TPL', 0) &&
        Configuration::updateValue('OGONE_ALIAS_BY_DL', 0) &&
        Configuration::updateValue('OGONE_USE_D3D', 0) &&
        Configuration::updateValue('OGONE_WIN3DS', 'MAINW') &&
        Configuration::updateValue('OGONE_SKIP_AC', 0) &&
        Configuration::updateValue('OGONE_MAKE_IP', 0) &&
        Configuration::updateValue('OGONE_DISPLAY_FRAUD_SCORING', 0) &&
        Configuration::updateValue('OGONE_PROPOSE_ALIAS', 0) &&
        Configuration::updateValue('OGONE_DONT_STORE_ALIAS', 0)
        ;


        $ogone_default_name = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $ogone_default_name[$language['id_lang']] = '';
        }
        $value = Tools::jsonEncode($ogone_default_name);
        $result = $result && Configuration::updateValue('OGONE_DEFAULT_NAME', $value);

        return $result;

    }

    /**
     * Installs tabs
     */
    public function installTabs()
    {
        $id_parent = (int) Tab::getIdFromClassName('AdminOrders');
        $tabs_to_add = array(
            'AdminOgoneTransactions' => $this->l('Ingenico ePayments Transactions'),
            'AdminOgoneOrders' => $this->l('Ingenico ePayments Orders'),
        );
        foreach ($tabs_to_add as $class_name => $name) {
            if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
                $class_name = $class_name.'14';
            }
            if (Tab::getIdFromClassName($class_name)) {
                continue;
            }
            if (!$this->addTab($class_name, $name, $id_parent)) {
                return false;
            }
        }
        return true;
    }

    protected function addTab($class_name, $name, $id_parent)
    {
        $tab = new Tab();
        $tab->id_parent = $id_parent;
        $tab->module = $this->name;
        $tab->class_name = $class_name;
        $tab->active = 1;
        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = $this->l($name);
        }

        return $tab->save();
    }

    protected function removeTabs()
    {
        $result = true;
        $controllers = array('AdminOgoneOrders', 'AdminOgoneTransactions');
        foreach ($controllers as $class_name) {
            if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
                $class_name = $class_name.'14';
            }
            $id = Tab::getIdFromClassName($class_name);
            if (!$id) {
                continue;
            }

            $tab = new Tab($id);
            if (!Validate::isLoadedObject($tab) || !$tab->delete()) {
                $result = false;
            }

        }
        return $result;
    }

    /**
     * Install database tables
     */
    public function installDBTables()
    {
        if (!Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ogone_tl` (
            `id_ogone_tl` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
            `id_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_cart` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_customer` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `payid` varchar(50) NOT NULL DEFAULT "",
            `status` INT(10) NOT NULL DEFAULT 0,
            `response` TEXT NOT NULL  DEFAULT "",
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_ogone_tl`), KEY(`id_cart`), KEY(`id_order`), KEY(`payid`), KEY(`date_add`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ogone_alias` (
            `id_ogone_alias` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
            `id_customer` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `alias` VARCHAR(128) NOT NULL DEFAULT "",
            `active` INT(10) NOT NULL DEFAULT 0,
            `cardno` VARCHAR(64) NOT NULL DEFAULT "",
            `cn` VARCHAR(128) NOT NULL DEFAULT "",
            `brand` VARCHAR(128) NOT NULL DEFAULT "",
            `expiry_date` DATE NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
             is_temporary INT(1) NOT NULL DEFAULT "0",
            PRIMARY KEY (`id_ogone_alias`),
            KEY(`id_customer`),
            KEY(`alias`),
            KEY(`active`),
            KEY(`expiry_date`),
            KEY (is_temporary)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ogone_pm` (
            `id_ogone_pm` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
            `pm` VARCHAR(64) NOT NULL DEFAULT "",
            `brand` VARCHAR(64) NOT NULL DEFAULT "",
            `position` INT(10) NOT NULL DEFAULT 0,
            `active` INT(10) NOT NULL DEFAULT 0,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_ogone_pm`), KEY(`active`), KEY(`position`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ogone_pm_shop` (
            `id_ogone_pm` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_shop_group` VARCHAR(64) NOT NULL DEFAULT "",
            `position` INT(10) NOT NULL DEFAULT 0,
            `active` INT(10) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_ogone_pm`,`id_shop`, `id_shop_group`), KEY(`active`), KEY(`position`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ogone_pm_lang` (
            `id_ogone_pm` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_lang` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `name` VARCHAR(64) NOT NULL DEFAULT "",
            `description`  VARCHAR(64) NOT NULL DEFAULT "",
            PRIMARY KEY (`id_ogone_pm`,  `id_shop`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }
        return true;
    }

    public function uninstallDBTables()
    {
        $result = true;
        foreach (array('ogone_tl', 'ogone_alias', 'ogone_pm', 'ogone_pm_shop', 'ogone_pm_lang') as $table) {
            Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . $table);
        }

        return $result;
    }

    protected function arePaymentModesCreated()
    {
        $query = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ .'ogone_pm';
        return (int)Db::getInstance()->getValue($query) > 0;
    }

    public function addDefaultPaymentModes()
    {
        /* there is something in the table. Leave it alone */
        if ($this->arePaymentModesCreated()) {
            return true;
        }

        if (version_compare(_PS_VERSION_, '1.5', 'ge') && method_exists('Shop', 'getContextListShopID')) {
            $shops = Shop::getContextListShopID();
        } else {
            $shops = array();
        }

        foreach ($this->getDefaultPaymentModes() as $idx => $row) {
            $pm = new OgonePM();
            $pm->pm = $row['pm'];
            $pm->brand = $row['brand'];
            $pm->name = $this->createMultilangArray($row['name']);
            $pm->description = $this->createMultilangArray($row['brand']);
            $pm->position = ++$idx;
            $pm->active = 0;

            $result = $pm->save();

            /*
             * This is not so important, so we are failing silently if there is a problem
             */
            if ($result && Validate::isLoadedObject($pm)) {
                if ($shops) {
                    $pm->associateTo($shops);
                }
                $logo_source =  implode(DIRECTORY_SEPARATOR, array($this->_path,  'views', 'img', $pm->logo));
                if (file_exists($logo_source)) {
                    $logo_target = $this->getPMUserLogoDir() . $pm->id . '.png';
                    copy($logo_source, $logo_target);
                }
            }
        }
        return true;
    }

    protected function createMultilangArray($value)
    {
        $result = array();
        foreach (Language::getLanguages() as $language) {
            $result[(int)$language['id_lang']] = $value;
        }
        return $result;
    }


    /**
     * Adds itermediary statuses. Needs to be public, because it's called by upgrade_module_2_11
     * @return boolean
     */
    public function addStatuses()
    {
        $result = true;

        $select_lang_id = (int) Language::getIdByIso('en');
        if (!$select_lang_id) {
            $select_lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        $lang = new Language($select_lang_id);
        $iso = $lang->iso_code;

        $statuses = $this->getExistingStatuses();
        foreach ($this->new_statuses as $code => $status) {
            if (isset($statuses[$status['names'][$iso]])) {
                if ((int) Configuration::get($code) !== (int) $statuses[$status['names'][$iso]]) {
                    Configuration::updateValue($code, (int) $statuses[$status['names'][$iso]]);
                }

                continue;
            }
            $properties = isset($status['properties']) ? $status['properties'] : array();
            if (!$this->addStatus($code, $status['names'], $properties)) {
                $result = false;
            }

        }
        if (version_compare(_PS_VERSION_, '1.5', 'ge') && is_callable('Cache', 'clean')) {
            Cache::clean('OrderState::getOrderStates*');
        }

        Configuration::updateValue(self::PAYMENT_ACCEPTED, Configuration::get('PS_OS_PAYMENT'), false, false);
        Configuration::updateValue(self::PAYMENT_ERROR, Configuration::get('PS_OS_ERROR'), false, false);
        return $result;
    }

    /**
     * Returns list of existing order statuses
     * @return multitype:number
     */
    protected function getExistingStatuses()
    {
        $statuses = array();
        $select_lang_id = (int) Language::getIdByIso('en');
        if (!$select_lang_id) {
            $select_lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        foreach (OrderState::getOrderStates($select_lang_id) as $status) {
            $statuses[$status['name']] = (int) $status['id_order_state'];
        }

        return $statuses;
    }

    /**
     * Adds new order state on install
     * @param string $code
     * @param array $names
     * @param array $properties
     * @return boolean
     */
    protected function addStatus($code, array $names = array(), array $properties = array())
    {
        $order_state = new OrderState();
        foreach (Language::getLanguages(false) as $language) {
            $iso_code = Tools::strtolower($language['iso_code']);
            $name = isset($names[$iso_code]) ? $names[$iso_code] : $names['en'];
            $order_state->name[(int) $language['id_lang']] = $name;
        }
        foreach ($properties as $property => $value) {
            $order_state->{$property} = $value;
        }

        $order_state->module_name = $this->name;
        $result = $order_state->add() && Validate::isLoadedObject($order_state);
        if ($result) {
            Configuration::updateValue($code, $order_state->id, false, false);
            $source = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.gif';
            if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
                $mb_logo = sprintf('order_state_mini_%d_%d.gif', $order_state->id, Context::getContext()->shop->id);
                $ms_tgt = _PS_TMP_IMG_DIR_ . DIRECTORY_SEPARATOR . $mb_logo;
            } else {
                $ms_tgt = null;
            }

            $targets = array(
                _PS_IMG_DIR_ . DIRECTORY_SEPARATOR . 'os' . DIRECTORY_SEPARATOR . sprintf('%d.gif', $order_state->id),
                _PS_TMP_IMG_DIR_ . DIRECTORY_SEPARATOR . sprintf('order_state_mini_%d.gif', $order_state->id),
            $ms_tgt
            );
            foreach (array_filter($targets) as $target) {
                copy($source, $target);
            }

        }
        return $result;
    }

    /* HOOKS */

    public function hookAdminOrder($params)
    {
        return $this->hookDisplayAdminOrder($params);
    }

    public function hookDisplayAdminOrder($params)
    {

        $id_order = (int) $params['id_order'];
        $order = new Order($id_order);
        if (!$order->module === $this->name) {
            return '';
        }

        list($can_refund, $refund_error) = $this->canRefund($order);
        list($can_capture, $capture_error) = $this->canCapture($order);
        $last = OgoneTransactionLog::getLastByOrderId($order->id);
        $response = ($last && is_array($last) && isset($last['response'])) ? Tools::jsonDecode($last['response'], true) : array();
        $orderid =  ($response && is_array($response) && isset($response['ORDERID'])) ? $response['ORDERID'] : '';
        $payid =  ($response && is_array($response) && isset($response['PAYID'])) ? $response['PAYID'] : '';

        $captured = $this->getCaptureTransactionsAmount($id_order);
        $captured_pending =  $this->getPendingCaptureTransactionsAmount($id_order);
        $capture_max_amount = $this->getCaptureMaxAmount($id_order); // number_format(max(0, $order->total_paid - $order->total_paid_real - $captured_pending), 2),


        $refunded = $this->getRefundTransactionsAmount($id_order);
        $refunded_pending = $this->getPendingRefundTransactionsAmount($id_order);
        $refund_max_amount = $this->getRefundMaxAmount($id_order); // max(0, $order->total_paid_real - $refunded - $refunded_pending)



        $scoring = $this->getFraudScoring($order);
        $currency = new Currency($order->id_currency);
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $controller = 'AdminOgoneOrders14';
            $capture_link = 'index.php?tab='.$controller.'&id_order=' . (int) $id_order . '&token=' . Tools::getAdminTokenLite($controller);
            $return_link = 'index.php?tab=AdminOrders&vieworder&id_order=' . (int) $id_order . '&token=' . Tools::getAdminTokenLite('AdminOrders');
            $refund_link =  'index.php?tab='.$controller.'&id_order=' . (int) $id_order . '&token=' . Tools::getAdminTokenLite($controller);
        } else {
            $controller = 'AdminOgoneOrders';
            $capture_link = 'index.php?controller='.$controller.'&id_order=' . (int) $id_order . '&token=' . Tools::getAdminTokenLite($controller);
            $return_link = 'index.php?controller=AdminOrders&vieworder&id_order=' . (int) $id_order . '&token=' . Tools::getAdminTokenLite('AdminOrders');
            $refund_link = 'index.php?controller='.$controller.'&id_order=' . (int) $id_order . '&token=' . Tools::getAdminTokenLite($controller);
        }


        $tpl_vars = array(
            'orderid' => $orderid,
            'payid' => $payid,
            'scoring' => $scoring,
            'currency_code' => $currency->iso_code,
            'return_link' =>  $return_link,
            'can_use_direct_link' => $this->canUseDirectLink(),
            'can_capture' => $can_capture,
            'cc_title' => $can_capture ? $this->l('Capture') : $capture_error,
            'capture_link' => $can_capture ? $capture_link : '',
            'max_capture_amount' => number_format($capture_max_amount, 2),
            'captured' =>  number_format($captured, 2),
            'captured_pending' =>  number_format($captured_pending, 2),
            'can_refund' => $can_refund,
            'refund_title' => $can_refund ? $this->l('Refund') : $refund_error,
            'refund_link' => $can_refund ? $refund_link : '',
            'max_refund_amount' => number_format($refund_max_amount, 2),
            'refunded' => number_format($refunded, 2),
            'refunded_pending' => number_format($refunded_pending, 2),
        );
        $this->context->smarty->assign($tpl_vars);
        $tpl_name = version_compare(_PS_VERSION_, '1.5', 'ge')  ? 'views/templates/admin/order.tpl' :  'views/templates/admin/order14.tpl';
        return $this->display(__FILE__, $tpl_name);
    }

    public function hookCustomerAccount()
    {
        if ($this->canUseAliases()) {

            $alias_page_link = version_compare(_PS_VERSION_, '1.5', 'ge')  ?
                $this->context->link->getModuleLink('ogone', 'aliases', array(), true) :
                (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
                htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
                __PS_BASE_URI__.'modules/ogone/aliases.php';
            $this->context->smarty->assign('alias_page_link', $alias_page_link);
            return $this->display(__FILE__, 'views/templates/front/my-account.tpl');
        } else {
            return '';
        }
    }

    public function hookHeader()
    {
        $paths = array($this->_path . 'views/css/front.css',
                $this->_path . 'views/css/front' . $this->getPsVersionSqueezed() . '.css');
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $result = array();
            foreach ($paths as $path) {
                $result[] = '<link type="text/css" rel="stylesheet" href="' . $path .'" />';
            }
            return implode(PHP_EOL, $result);
        } else {
            foreach ($paths as $path) {
                $this->context->controller->addCss($path);
            }
        }

    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure', Tools::getValue('module_name')) == $this->name || $this->context->controller->controller_name === 'AdminOrders') {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $result = array();
                $module_css = $this->_path . 'views/css/admin.css';
                $module_js = $this->_path . 'views/js/backoffice.js';

                $fbx_css = __PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css';
                $jquery_fbx = __PS_BASE_URI__ . 'js/jquery/jquery-ui-1.8.10.custom.min.js';
                $jquery_ui = __PS_BASE_URI__ . 'js/jquery/jquery.fancybox-1.3.4.js';

                $result[] = '<link type="text/css" rel="stylesheet" href="'.$module_css.'" />';
                $result[] = '<link type="text/css" rel="stylesheet" href="' .  $fbx_css .'" />';
                $result[] = '<script type="text/javascript" src="' . $jquery_ui .'"></script>';
                $result[] = '<script type="text/javascript" src="' . $jquery_fbx . '"></script>';
                $result[] = '<script type="text/javascript" src="' . $module_js .'">';
                return implode(PHP_EOL, $result);
            } else {
                $this->context->controller->addJquery();
                $this->context->controller->addJQueryPlugin('fancybox');
                $this->context->controller->addJqueryPlugin('colorpicker');
                $this->context->controller->addJqueryUI('ui.sortable');
                $this->context->controller->addJs($this->_path . 'views/js/backoffice.js');
                $this->context->controller->addCss($this->_path . 'views/css/admin.css');
            }
        }

        return '';
    }

    /**
     * hookPayment replacement for compatibility with module eu_legal
     * @param array $params
     * @return string Generated html
     */
    public function hookDisplayPaymentEU($params)
    {
        $this->assignPaymentVars($params);
        return array(
            'cta_text' => $this->l('Ogone'),
            'logo' => $this->_path . 'views/img/ogone.gif',
            'form' => $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/front/ogone_eu.tpl'),
        );
    }

    public function hookPayment($params)
    {
        $result = array();

        // aliases
        if (Configuration::get('OGONE_USE_ALIAS')) {
            $params['pm'] = null;
            foreach (OgoneAlias::getCustomerActiveAliases((int) $params['cart']->id_customer) as $alias) {
                if ($alias['is_temporary'] == 1) {

                    $creation_timestamp =  strtotime($alias['date_add']);
                    if (time()-$creation_timestamp > 7100) {
                        // temporary aliases are stocked for 2 hours
                        continue;
                    }
                }
                $params['alias'] = $alias;
                $params['eci'] = self::INGENICO_ECI_ECOMMERCE;
                $params['immediate_payment'] = false;
                if ($this->canUseAliasesViaDL()) {
                    $params['immediate_payment'] = $this->skipAliasPaymentConfirmation();
                    $result[] = $this->renderPaymentTemplate($params, 'alias-local');
                } else {
                    $result[] = $this->renderPaymentTemplate($params, 'alias');
                }
            }
            unset($params['alias']);
            unset($params['eci']);
            unset($params['immediate_payment']);

            foreach ($this->renderAliasAddTemplates() as $alias_add_tpl) {
                $result[] = $alias_add_tpl;
            }
        }

        // payment modes
        if (Configuration::get('OGONE_USE_PM')) {
            foreach ($this->getPaymentMethodsList() as $pm) {
                if (!$pm->active) {
                    continue;
                }

                $params['pm'] = $pm;
                $result[] = $this->renderPaymentTemplate($params, 'pm');
            }
            $params['pm'] = null;
        }

        // default
        $result[] = $this->renderPaymentTemplate($params);
        return implode(PHP_EOL, $result);

    }

    public function getLocalAliasPaymentLink($params = array())
    {
        if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
            return $this->context->link->getModuleLink(
                $this->name,
                $this->skipAliasPaymentConfirmation() ? 'validation' : 'payment',
                $params,
                true
            );
        } else {
            return(_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
            __PS_BASE_URI__.'modules/ogone/'. ($this->skipAliasPaymentConfirmation() ? 'validate_alias' : 'payment' ).'.php' . ($params ? '?' . http_build_query($params) : '');
        }

    }

    protected function getPsVersionSqueezed()
    {
        return implode('', array_slice(explode('.', _PS_VERSION_), 0, 2));
    }
    protected function renderAliasAddTemplates()
    {
        $customer = $this->context->customer;
        $result = array();
        if ($customer && $customer->id) {
            foreach ($this->getHostedTokenizationPageRegistrationUrls($customer->id, $this->makeImmediateAliasPayment()) as $type => $url) {
                $tpl_vars = array();
                $tpl_vars['type'] = $type;
                $tpl_vars['type_class'] = Tools::strtolower(str_replace(' ', '', $type));
                $tpl_vars['type_name'] = $this->getHTPPaymentMethodName($type);
                $tpl_vars['htp_url'] = $url;
                $tpl_vars['has_aliases'] = (bool)OgoneAlias::getCustomerActiveAliases($customer->id);
                $tpl_vars['immediate_payment'] = $this->makeImmediateAliasPayment();
                if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
                    $tpl_vars['alias_page_url'] = $this->context->link->getModuleLink(
                        $this->name,
                        'aliases',
                        array(),
                        true
                    );
                } else {
                    $tpl_vars['alias_page_url'] = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
                    htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
                    __PS_BASE_URI__.'modules/ogone/aliases.php';
                }
                $this->context->smarty->assign($tpl_vars);
                $result[] = $this->display(__FILE__, 'views/templates/front/htp-iframe-inline.tpl');
            }
        }
        return $result;
    }



    protected function renderAliasAddTemplate()
    {
        $customer = $this->context->customer;
        if ($customer && $customer->id) {
            $tpl_vars = array();
            $tpl_vars['htp_urls'] = $this->getHostedTokenizationPageRegistrationUrls($customer->id, $this->makeImmediateAliasPayment());
            $tpl_vars['has_aliases'] = (bool)OgoneAlias::getCustomerActiveAliases($customer->id);
            $tpl_vars['immediate_payment'] = $this->makeImmediateAliasPayment();
            // @todo adjust redirection url to take step in command tunnel into account

            if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
                $tpl_vars['alias_page_url'] = $this->context->link->getModuleLink(
                    $this->name,
                    'aliases',
                    array(),
                    true
                );
            } else {
                $tpl_vars['alias_page_url'] = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
                htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
                __PS_BASE_URI__.'modules/ogone/aliases.php';
            }
            $this->context->smarty->assign($tpl_vars);
            return $this->display(__FILE__, 'views/templates/front/htp-iframe.tpl');
        }
        return '';
    }

    protected function renderPaymentTemplate($params, $type = '')
    {
        if ($this->assignPaymentVars($params)) {
            return $this->display(__FILE__, $this->getPaymentTemplate($type));
        }

        return '';
    }

    protected function getPaymentTemplate($type = '')
    {
        if (version_compare(_PS_VERSION_, '1.6', 'ge')) {
            $tpl = 'ogone16';
        } else if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $tpl = 'ogone14';
        } else {
            $tpl = 'ogone';
        }
        if ($type) {
            $tpl .= '-' . $type;
        }
        $tpl .= '.tpl';
        return 'views/templates/front/' . $tpl;
    }

    public function hookOrderConfirmation($params)
    {
        if ($params['objOrder']->module != $this->name) {
            return;
        }

        $current_state = (isset($params['objOrder']->current_state) && $params['objOrder']->current_state) ? $params['objOrder']->current_state  : $params['objOrder']->getCurrentState();
        if ($params['objOrder']->valid ||
            (Configuration::get('OGONE_OPERATION') == self::OPERATION_AUTHORISE &&
                (int) $current_state === (int) Configuration::get('OGONE_PAYMENT_AUTHORIZED'))) {
            $this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
        } else {
            $this->context->smarty->assign('status', 'failed');
        }

        $this->context->smarty->assign(
            'operation',
            Configuration::get('OGONE_OPERATION') ? Configuration::get('OGONE_OPERATION') : self::OPERATION_SALE
        );

        $link = method_exists('Link', 'getPageLink') ?
            $this->context->link->getPageLink(version_compare(_PS_VERSION_, '1.5', 'lt') ? 'contact-form.php' : 'contact', true) :
            Tools::getHttpHost(true) . 'contact';
        $this->context->smarty->assign('ogone_link', $link);
        return $this->display(__FILE__, 'views/templates/hook/hookorderconfirmation.tpl');
    }

    /* BACKOFFICE DISPLAY */

    public function getContent()
    {
        $messages = $this->processConfig();

        foreach ($this->checkSettings() as $type => $check_messages) {
            if ($check_messages) {
                foreach ($check_messages as $message) {
                    $messages[$type][] = $message;
                }
            }
        }

        $tpl_vars = array(
            'messages' => $messages,
            'tabs' => $this->getConfigurationTabs(),
            'ps_tracker_url' => $this->getPrestashopTrackerUrl(),
            'selected_tab' => $this->getSelectedTab(),
        );

        $this->context->smarty->assign($tpl_vars);

        return $this->display(__FILE__, 'views/templates/admin/tabs.tpl');
    }

    protected function getSelectedTab()
    {
        if ($this->selected_tab !== null) {
            return $this->selected_tab;
        }

        if (Tools::getValue('selected_tab')) {
            return Tools::getValue('selected_tab');
        }

        if (Configuration::get('OGONE_PSPID')) {
            return 'configuration';
        }

        return 'info';

    }

    protected function processConfig()
    {
        $messages = array(
            'errors' => array(),
            'warnings' => array(),
            'informations' => array(),
            'successes' => array(),
        );

        if (Tools::getValue('action')) {
            $action = Tools::getValue('action');
            if ($action === 'delete_pm') {
                $this->selected_tab = 'pm';
                if ($this->deletePM((int) Tools::getValue('pmid'))) {
                    $messages['successes'][] = $this->l('Payment method deleted');

                } else {
                    $messages['errors'][] = $this->l('Unable to delete payment method');
                }
            }
        }

        if (Tools::isSubmit('submitOgoneDeleteLogo')) {
            $this->selected_tab = 'pm';
            $files_deleted = $this->deleteDefaultOptionLogo();
            if ($files_deleted) {
                $messages['successes'][] = $this->l('Logo deleted');
            } else {
                $messages['errors'][] = $this->l('Unable to delete logo');
            }

        }
        if (Tools::isSubmit('submitOgoneClearLogs')) {
            $this->selected_tab = 'logs';
            $files_deleted = $this->clearLogFiles();
            if ($files_deleted) {
                $messages['successes'][] = sprintf($this->l('%d log files deleted'), $files_deleted);
            } else {
                $messages['errors'][] = $this->l('Unable to delete log files');
            }

        }
        if (Tools::isSubmit('submitOgone')) {
            $this->selected_tab = 'configuration';
            $this->updateConfiguration();
            $messages['successes'][] = $this->l('General configuration updated');
        }
        if (Tools::isSubmit('submitOgoneStatic')) {
            $this->selected_tab = 'static';
            $this->updateStaticConfiguration();
            $messages['successes'][] = $this->l('Static template updated');
        }
        if (Tools::isSubmit('submitOgonePM')) {
            $this->selected_tab = 'pm';
            if ($this->updatePMConfiguration()) {
                $messages['successes'][] = $this->l('Payment methods updated');
            } else {
                $messages['errors'][] = $this->l('Unable to update payment methods');
            }

            list($image_upload, $message) = $this->uploadPaymentLogo(
                'OGONE_DEFAULT_LOGO',
                $this->getDefaultOptionLogoFilename()
            );
            if ($image_upload === true) {
                $messages['successes'][] = $message;
            } elseif ($image_upload === false) {
                $messages['errors'][] = $message;
            }

        }
        if (Tools::isSubmit('submitOgoneAddPM')) {
            $this->selected_tab = 'pm';
            $pm = $this->addPaymentMethod();
            if ($pm) {
                // $messages['successes'][] = $this->l('Payment method was added');
                list($image_upload, $message) = $this->uploadPaymentLogo(
                    'add_pm_logo',
                    $this->getPMUserLogoDir() . $pm->id . '.png'
                );
                if ($image_upload === null) {
                    $messages['successes'][] = $this->l('Payment method was added');
                } elseif ($image_upload === true) {
                    $messages['successes'][] = $this->l('Payment method was added with image');
                } else {
                    $messages['warnings'][] = $this->l('Payment method was added but') . ' ' . lcfirst($message);
                }

            } else {
                $messages['errors'][] = $this->l('Unable to add payment method');
            }

        }
        if (Tools::isSubmit('submitOgoneLog')) {
            $this->selected_tab = 'logs';
            $this->updateLogConfiguration();
            $messages['successes'][] = $this->l('Log configuration updated');
        }
        return $messages;
    }

    protected function deletePM($pmid)
    {
        $pm = new OgonePM((int) $pmid);
        if (Validate::isLoadedObject($pm)) {
            return $pm->delete();
        }

        return false;
    }

    /**
     * Checks all necessary module settings
     */
    protected function checkSettings()
    {
        $errors = array();
        $warnings = array();
        $successes = array();
        $informations = array();
        $can_use_dl = $this->canUseDirectLink();
        if (!function_exists('curl_init')) {
            $errors[] = $this->l('In order to use DirectLink, PHP\'s curl extension is necessary.');
        }

        if (!function_exists('simplexml_load_string')) {
            $errors[] = $this->l('In order to use DirectLink, PHP\'s simplexml extension is necessary.');
        }

        if (!function_exists('mcrypt_encrypt')) {
            $errors[] = $this->l('In order to store aliases securely, PHP\'s mcrypt extension is necessary.');
        }
        $tls_version = $this->getTLSVersion();

        if ($tls_version === null) {
            $warnings[] = $this->l('Unable to verify TLS version. Please contact your hosting provider.');
        } else if (version_compare($tls_version, $this->tls_version_expected, 'lt')) {
            $message = $this->l('TLS version detected is %s, expected version is at least %s.');
            $errors[] = sprintf($message, $tls_version, $this->tls_version_expected);
            $errors[] = $this->l('For security reasons, you should upgrade your server.');
            $errors[] = $this->l('Some functionalities will not work with incorrect TLS version.');
            $errors[] = $this->l('Please contact your hosting provider or server administrator.');
        } else {
            $message = $this->l('TLS version detected is %s, it seems that your server is secure.');
            $successes[] = sprintf($message, $tls_version);
        }

        if (Configuration::get('OGONE_MODE') && !Configuration::get('OGONE_PSPID')) {
            $errors[] = $this->l('You activated Production mode, but PSPID parameter is not defined.');
        }

        if (Configuration::get('OGONE_MODE') && !Configuration::get('OGONE_SHA_IN')) {
            $errors[] = $this->l('You activated Production mode, but SHA-IN parameter is not defined.');
        }

        if (Configuration::get('OGONE_MODE') && !Configuration::get('OGONE_SHA_OUT')) {
            $errors[] = $this->l('You activated Production mode, but SHA-OUT parameter is not defined.');
        }

        if (Configuration::get('OGONE_PSPID') && !Configuration::get('OGONE_SHA_IN')) {
            $errors[] = $this->l('You entered PSPID, but SHA-IN parameter is not defined.');
        }

        if (Configuration::get('OGONE_PSPID') && !Configuration::get('OGONE_SHA_OUT')) {
            $errors[] = $this->l('You entered PSPID, but SHA-OUT parameter is not defined.');
        }

        if (Configuration::get('OGONE_USE_ALIAS') && !Configuration::get('OGONE_ALIAS_SHA_IN')) {
            $errors[] = $this->l('You activated Alias Gateway usage, but alias SHA-IN parameter is not defined.');
        }

        if (Configuration::get('OGONE_USE_DL') && !Configuration::get('OGONE_DL_SHA_IN')) {
            $errors[] = $this->l('You activated Direct Link usage, but DirectLink SHA-IN parameter is not defined.');
        }

        if (Configuration::get('OGONE_USE_DL') && !Configuration::get('OGONE_DL_USER')) {
            $errors[] = $this->l('You activated Direct Link usage, but DirectLink user is not defined.');
        }

        if (Configuration::get('OGONE_USE_D3D') && !$this->canUseDirectLink()) {
            $errors[] = $this->l('You activated 3-D Secure over DirectLink usage, but DirectLink is not configured properly.');
        }

        if (Configuration::get('OGONE_USE_DL') && !Configuration::get('OGONE_DL_PASSWORD')) {
            $errors[] = $this->l('You activated Direct Link usage, but DirectLink password is not defined.');
        }

        if (Configuration::get('OGONE_OPERATION') == self::OPERATION_AUTHORISE && !$can_use_dl) {
            $warnings[] = $this->l('You activated Capture mode, but DirectLink is not configured properly.');
            $warnings[] = $this->l('You will need to capture orders manully via Ingenico BackOffice.');
        }

        if (!file_exists($this->getPMUserLogoDir())) {
            $errors[] = sprintf($this->l('Directory %s do not exisits.'), $this->getPMUserLogoDir());
        }

        if (Configuration::get('OGONE_USE_LOG')) {
            $log_files = count($this->getLogFiles());
            if ($log_files > self::MAX_LOG_FILES_ADVISED) {
                $warnings[] = sprintf($this->l('You activated error logging and you have %d log files.'), $log_files);
                $warnings[] = $this->l('Deactivate this option if it is not necessary or delete unnecessary the files');
            }
            $informations[] = $this->l('Logging is activated');
        }
        $lg = $this->getIngenicoLanguageCode();
        $statuses_to_check = array();
        foreach ($this->new_statuses as $status_var => $definition) {
            $statuses_to_check[$status_var] = isset($definition['names'][$lg]) ?
                $definition['names'][$lg] :
                $definition['names']['en'];
        }

        $statuses_to_check[self::PAYMENT_ACCEPTED] = $this->l('Payment accepted');
        $statuses_to_check[self::PAYMENT_ERROR] = $this->l('Payment error');
        $status_errors = false;
        foreach ($statuses_to_check as $status_var => $status_name) {
            $status_id = (int) Configuration::get($status_var);
            if (!$status_id) {
                $errors[] = sprintf($this->l('Order status "%s" cannot be find.'), $status_name);
                $status_errors = true;
                continue;
            }
            $status = new OrderState($status_id);
            if (!Validate::isLoadedObject($status)) {
                $pattern = $this->l('Order status "%s" (id %d) id cannot be loaded.');
                $errors[] = sprintf($pattern, $status_name, $status_id);
                $status_errors = true;
            }
        }
        if ($status_errors) {
            $errors[] = $this->l('Please try reinstall module or contact support');
        }

        if ($this->areSettingsOverridenByShop()) {
            $warnings[] = $this->l('Some module settings are different depending on shop / shop group context; please verify if you are using the good context');
        }

        if (empty($errors) && empty($warnings)) {
            $successes[] = $this->l('Module is properly configured');
        }

        return array(
            'errors' => $errors,
            'warnings' => $warnings,
            'successes' => $successes,
            'informations' => $informations,
        );
    }

    protected function areSettingsOverridenByShop()
    {
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            return false;
        }
        if (!Shop::isFeatureActive()) {
            return false;
        }
        $query = 'SELECT * FROM ' ._DB_PREFIX_ . 'configuration WHERE name LIKE "OGONE%"';
        $sets = array();
        foreach (Db::getInstance()->executeS($query) as $row) {
            $sets[sprintf('%d-%d', $row['id_shop'], $row['id_shop_group'])][$row['name']] = $row['value'];
        }
        if (count($sets) == 1) {
            return false;
        }
        foreach ($sets as $i => $set1) {
            foreach ($sets as $j => $set2) {
                if ($i == $j) {
                    continue;
                }
                ksort($set1);
                ksort($set2);
                if ($set1 !== $set2) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function getConfigurationTabs()
    {
        $tabs = array();
        $tabs[] = array(
            'id' => 'info',
            'title' => $this->l('Presentation'),
            'content' => $this->getInfoTemplateHtml()
        );
        $tabs[] = array(
            'id' => 'prices',
            'title' => $this->l('Pricing'),
            'content' => $this->getPricingTemplateHtml()
        );
        $tabs[] = array(
            'id' => 'configuration',
            'title' => $this->l('Configuration'),
            'content' => $this->getConfigurationFormHtml()
        );
        $tabs[] = array(
            'id' => 'pm',
            'title' => $this->l('Payment methods'),
            'content' => $this->getPaymentModesFormHtml()
        );
        $tabs[] = array('id' => 'templates',
            'title' => $this->l('Static template'),
            'content' => $this->getStaticTemplateHtml()
        );
        $tabs[] = array('id' => 'logs',
            'title' => $this->l('Debug'),
            'content' => $this->getDebugHtml()
        );
        return $tabs;
    }

    protected function getInfoTemplateHtml()
    {
        $lg_code = $this->getIngenicoLanguageCode();
        $this->context->smarty->assign('lg_code', $lg_code);
        $this->context->smarty->assign($this->getLocalizedContactData($lg_code));
        return $this->display(__FILE__, 'views/templates/admin/info.tpl');
    }

    protected function getPricingTemplateHtml()
    {
        $lg_code = $this->getIngenicoLanguageCode();
        $this->context->smarty->assign('lg_code', $lg_code);
        $this->context->smarty->assign($this->getLocalizedContactData($lg_code));
        return $this->display(__FILE__, 'views/templates/admin/prices.tpl');
    }

    protected function getFormActionUrl()
    {
        $form_url = null;
        $components = parse_url($_SERVER['REQUEST_URI']);
        if ($components) {
            parse_str($components['query'], $query);
            if ($query) {
                unset($query['action'], $query['pmid']);
                $components['query'] = http_build_query($query);
                $form_url = $components['path'] . '?' .  $components['query'];
            }
        }
        return $form_url ? $form_url : $_SERVER['REQUEST_URI'];
    }

    protected function getConfigurationFormHtml()
    {

        $tpl_vars = array(
            'form_action' => $this->getFormActionUrl(),
            'module_url' =>  $this->getModuleUrl(),
            'server_ip' => $this->getServerIp(),
            'direct_link_doc_url' => $this->getDirectLinkDocUrl(),
            'validation_url' => $this->getValidationUrl(),
            'confirmation_url' => $this->getConfirmationUrl(),
        );
        $this->context->smarty->assign($tpl_vars);
        $this->context->smarty->assign($this->getConfigurationVariables());
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    protected function getStaticTemplateHtml()
    {
        $tpl_vars = array(
            'form_action' => $_SERVER['REQUEST_URI'],
            'module_url' => $this->getModuleUrl(),
            'fonts' => $this->static_template_fonts,
        );
        $this->context->smarty->assign($tpl_vars);
        $this->context->smarty->assign($this->getConfigurationVariables());
        return $this->display(__FILE__, 'views/templates/admin/static.tpl');

    }

    public function getCurrentIndex()
    {
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            return $_SERVER['SCRIPT_NAME'].(($controller = Tools::getValue('controller')) ?
                '?controller='.$controller :
                '');
        }
        return AdminController::$currentIndex;
    }

    protected function getModuleUrl()
    {
        $token =  Tools::getAdminTokenLite('AdminModules');
        return $this->getCurrentIndex() . '&configure=' . $this->name . '&token=' . $token;
    }

    protected function addPaymentMethod()
    {
        $brand = Tools::getValue('add_pm_brand');
        $pm = Tools::getValue('add_pm_pm');

        $name = array();
        $description = array();

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];
            $name[$id_lang] = Tools::getValue('add_pm_name_' . $id_lang) ?  Tools::getValue('add_pm_name_' . $id_lang) : Tools::getValue('add_pm_pm');
            $description[$id_lang] = Tools::getValue('add_pm_desc_' . $id_lang);
        }

        if (empty($pm) || empty($brand) || count($name) !== count(array_filter($name))) {
            return false;
        }

        $payment_method = new OgonePM();
        $payment_method->brand = $brand;
        $payment_method->pm = $pm;
        $payment_method->active = 1;
        $payment_method->name = $name;
        $payment_method->description = $description;

        if (!$payment_method->save()) {
            return false;
        }

        return $payment_method;
    }

    public function getPMUserLogoDir()
    {
        return implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'views', 'img', 'pm', ''));
    }

    protected function getPaymentModesFormHtml()
    {
        clearstatcache();
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $default_names = $this->getDefaultOptionNames();
        foreach ($languages as $language) {
            if (!isset($default_names[(int) $language['id_lang']])) {
                $default_names[(int) $language['id_lang']] = '';
            }
        }

        $tpl_vars = array(
            'payment_methods' => $this->getPaymentMethodsList(),
            'default_names' => $default_names,
            'defaultLanguage' => $default_lang,
            'custom_logo_exists' => file_exists($this->getDefaultOptionLogoFilename()),
            'ogone_logo_url' => $this->getDefaultOptionLogoUrl(),
            'languages' => $languages,
            'flags' => $this->displayFlags($languages, $default_lang, 'OGONE_DEFAULT_NAME', 'OGONE_DEFAULT_NAME', true),
            'flags_pm_name' => $this->displayFlags($languages, $default_lang, 'add_pm_name', 'add_pm_name', true),
            'flags_pm_desc' => $this->displayFlags($languages, $default_lang, 'add_pm_desc', 'add_pm_desc', true),
        );

        $this->context->smarty->assign($tpl_vars);
        return $this->display(__FILE__, 'views/templates/admin/pm.tpl');
    }

    protected function getLocalizedContactData($lg_code)
    {
        if (!isset($this->localized_contact_data[$lg_code])) {
            $lg_code = 'en';
        }

        $lg_code2 = ($lg_code == 'en' ? 'int' :  $lg_code);
        $data = $this->localized_contact_data[$lg_code];

        $data['create_test_account_url'] = $this->test_account_url . '?' . $data['test_account_query'];
        $data['integration_guide_url'] = $this->ingenico_server . sprintf($this->int_guide, $lg_code2, $lg_code);
        $data['support_url'] = $this->ingenico_server . sprintf($this->support_url, $lg_code2, $lg_code);

        return $data;
    }

    protected function getIngenicoLanguageCode($code = null, $default = 'en')
    {
        $code = $code ? Tools::strtolower(trim($code)) : Context::getContext()->language->iso_code;
        return in_array($code, $this->documentation_languages) ? $code : $default;
    }

    protected function getPrestashopTrackerUrl()
    {
        $pspid = Configuration::get('OGONE_PSPID');
        if ($pspid) {
            $query = array('pspid' => $pspid, 'mode' => (int) Configuration::get('OGONE_MODE'));
            return 'http://api.prestashop.com/modules/ogone.png?' . http_build_query($query);
        }
        return '';
    }

    protected function getConfigurationVariables()
    {
        $alias_pm = Tools::jsonDecode(Configuration::get('OGONE_ALIAS_PM'), true);
        if (!is_array($alias_pm)) {
            $alias_pm = array(
                'CreditCard' => 0,
                'DirectDebits DE' => 0,
                'DirectDebits NL' => 0,
                'DirectDebits AT' => 0,
            );
            if (Configuration::get('OGONE_ALIAS_PM') && isset($alias_pm[Configuration::get('OGONE_ALIAS_PM')])) {
                $alias_pm[Configuration::get('OGONE_ALIAS_PM')] = 1;
            }
        }

        return array(
            'OGONE_PSPID' => Configuration::get('OGONE_PSPID'),
            'OGONE_SHA_IN' => Configuration::get('OGONE_SHA_IN'),
            'OGONE_SHA_OUT' => Configuration::get('OGONE_SHA_OUT'),
            'OGONE_MODE' => Configuration::get('OGONE_MODE'),
            'OGONE_OPERATION' => Configuration::get('OGONE_OPERATION'),
            'OGONE_DL_USER' => Configuration::get('OGONE_DL_USER'),
            'OGONE_DL_PASSWORD' => Configuration::get('OGONE_DL_PASSWORD'),
            'OGONE_DL_SHA_IN' => Configuration::get('OGONE_DL_SHA_IN'),
            'OGONE_USE_ALIAS' => Configuration::get('OGONE_USE_ALIAS'),
            'OGONE_ALIAS_SHA_IN' => Configuration::get('OGONE_ALIAS_SHA_IN'),
            'OGONE_USE_LOG' => Configuration::get('OGONE_USE_LOG'),
            'OGONE_USE_PM' => Configuration::get('OGONE_USE_PM'),
            'OGONE_USE_TPL' => Configuration::get('OGONE_USE_TPL'),
            'OGONE_TITLE' => Configuration::get('OGONE_TITLE'),
            'OGONE_BGCOLOR' => Configuration::get('OGONE_BGCOLOR'),
            'OGONE_TXTCOLOR' => Configuration::get('OGONE_TXTCOLOR'),
            'OGONE_TBLBGCOLOR' => Configuration::get('OGONE_TBLBGCOLOR'),
            'OGONE_TBLTXTCOLOR' => Configuration::get('OGONE_TBLTXTCOLOR'),
            'OGONE_BUTTONBGCOLOR' => Configuration::get('OGONE_BUTTONBGCOLOR'),
            'OGONE_BUTTONTXTCOLOR' => Configuration::get('OGONE_BUTTONTXTCOLOR'),
            'OGONE_FONTTYPE' => Configuration::get('OGONE_FONTTYPE'),
            'OGONE_LOGO' => Configuration::get('OGONE_LOGO'),
            'OGONE_USE_DL' => Configuration::get('OGONE_USE_DL'),
            'OGONE_ALIAS_PM' => $alias_pm,
            'OGONE_DL_TIMEOUT' => Configuration::get('OGONE_DL_TIMEOUT'),
            'OGONE_USE_KLARNA' => Configuration::get('OGONE_USE_KLARNA'),
            'OGONE_ALIAS_BY_DL' => Configuration::get('OGONE_ALIAS_BY_DL'),
            'OGONE_USE_D3D' => Configuration::get('OGONE_USE_D3D'),
            'OGONE_WIN3DS' => Configuration::get('OGONE_WIN3DS'),
            'OGONE_SKIP_AC' => Configuration::get('OGONE_SKIP_AC'),
            'OGONE_MAKE_IP' => Configuration::get('OGONE_MAKE_IP'),
            'OGONE_DISPLAY_FRAUD_SCORING' => Configuration::get('OGONE_DISPLAY_FRAUD_SCORING'),
            'OGONE_PROPOSE_ALIAS' => Configuration::get('OGONE_PROPOSE_ALIAS'),
            'OGONE_DONT_STORE_ALIAS' => Configuration::get('OGONE_DONT_STORE_ALIAS')
        );
    }

    protected function updatePMConfiguration()
    {

        Configuration::updateValue('OGONE_USE_KLARNA', Tools::getValue('OGONE_USE_KLARNA') ? 1 : 0);
        Configuration::updateValue('OGONE_USE_PM', Tools::getValue('OGONE_USE_PM') ? 1 : 0);
        $languages = Language::getLanguages(false);
        $ogone_default_name = array();
        foreach ($languages as $language) {
            $var_name = 'OGONE_DEFAULT_NAME_' . $language['id_lang'];
            $ogone_default_name[$language['id_lang']] = Tools::getValue($var_name);
        }
        Configuration::updateValue('OGONE_DEFAULT_NAME', Tools::jsonEncode($ogone_default_name));
        $statuses = Tools::getValue('OGONE_PM_STATUS');
        $positions = Tools::getValue('OGONE_PM_POSITION');
        $result = true;
        foreach ($this->getPaymentMethodsList() as $pm) {
            $pm->position = $positions[$pm->id];
            $pm->active = isset($statuses[$pm->id]);
            if (!$pm->update()) {
                $result = false;
            }

        }
        return $result;
    }

    protected function updateLogConfiguration()
    {
        Configuration::updateValue('OGONE_USE_LOG', Tools::getValue('OGONE_USE_LOG') ? 1 : 0);
        return true;
    }

    protected function updateStaticConfiguration()
    {
        Configuration::updateValue('OGONE_BGCOLOR', Tools::getValue('OGONE_BGCOLOR'));
        Configuration::updateValue('OGONE_BUTTONBGCOLOR', Tools::getValue('OGONE_BUTTONBGCOLOR'));
        Configuration::updateValue('OGONE_BUTTONTXTCOLOR', Tools::getValue('OGONE_BUTTONTXTCOLOR'));
        Configuration::updateValue('OGONE_FONTTYPE', Tools::getValue('OGONE_FONTTYPE'));
        Configuration::updateValue('OGONE_LOGO', Tools::getValue('OGONE_LOGO'));
        Configuration::updateValue('OGONE_TBLBGCOLOR', Tools::getValue('OGONE_TBLBGCOLOR'));
        Configuration::updateValue('OGONE_TBLTXTCOLOR', Tools::getValue('OGONE_TBLTXTCOLOR'));
        Configuration::updateValue('OGONE_TITLE', Tools::getValue('OGONE_TITLE'));
        Configuration::updateValue('OGONE_TXTCOLOR', Tools::getValue('OGONE_TXTCOLOR'));
        Configuration::updateValue('OGONE_USE_TPL', Tools::getValue('OGONE_USE_TPL') ? 1 : 0);
        return true;
    }

    protected function updateConfiguration()
    {
        $operation = in_array(Tools::getValue('OGONE_OPERATION'), $this->allowed_operations) ?
            Tools::getValue('OGONE_OPERATION') :
            self::OPERATION_SALE;

        $alias_pm = array(
            'CreditCard' => 0,
            'DirectDebits DE' => 0,
            'DirectDebits NL' => 0,
            'DirectDebits AT' => 0,
        );
        if (is_array(Tools::getValue('OGONE_ALIAS_PM'))) {
            foreach (Tools::getValue('OGONE_ALIAS_PM') as $k) {
                $alias_pm[$k] = 1;
            }
        }

        Configuration::updateValue('OGONE_ALIAS_PM', Tools::jsonEncode($alias_pm));
        Configuration::updateValue('OGONE_ALIAS_SHA_IN', Tools::getValue('OGONE_ALIAS_SHA_IN'));
        Configuration::updateValue('OGONE_DL_PASSWORD', Tools::getValue('OGONE_DL_PASSWORD'));
        Configuration::updateValue('OGONE_DL_SHA_IN', Tools::getValue('OGONE_DL_SHA_IN'));
        Configuration::updateValue('OGONE_DL_TIMEOUT', (int) Tools::getValue('OGONE_DL_TIMEOUT'));
        Configuration::updateValue('OGONE_DL_USER', Tools::getValue('OGONE_DL_USER'));
        Configuration::updateValue('OGONE_MODE', (int) Tools::getValue('OGONE_MODE'));
        Configuration::updateValue('OGONE_OPERATION', $operation);
        Configuration::updateValue('OGONE_PSPID', Tools::getValue('OGONE_PSPID'));
        Configuration::updateValue('OGONE_SHA_IN', Tools::getValue('OGONE_SHA_IN'));
        Configuration::updateValue('OGONE_SHA_OUT', Tools::getValue('OGONE_SHA_OUT'));
        Configuration::updateValue('OGONE_USE_ALIAS', Tools::getValue('OGONE_USE_ALIAS') ? 1 : 0);
        Configuration::updateValue('OGONE_ALIAS_BY_DL', Tools::getValue('OGONE_ALIAS_BY_DL') ? 1 : 0);
        Configuration::updateValue('OGONE_USE_DL', Tools::getValue('OGONE_USE_DL') ? 1 : 0);
        Configuration::updateValue('OGONE_USE_D3D', Tools::getValue('OGONE_USE_D3D') ? 1 : 0);
        Configuration::updateValue('OGONE_WIN3DS', Tools::getValue('OGONE_WIN3DS'));

        Configuration::updateValue('OGONE_SKIP_AC', Tools::getValue('OGONE_SKIP_AC') ? 1 : 0);
        Configuration::updateValue('OGONE_MAKE_IP', Tools::getValue('OGONE_MAKE_IP') ? 1 : 0);
        Configuration::updateValue('OGONE_DISPLAY_FRAUD_SCORING', Tools::getValue('OGONE_DISPLAY_FRAUD_SCORING') ? 1 : 0);
        Configuration::updateValue('OGONE_PROPOSE_ALIAS', Tools::getValue('OGONE_PROPOSE_ALIAS') ? 1 : 0);
        Configuration::updateValue('OGONE_DONT_STORE_ALIAS', Tools::getValue('OGONE_DONT_STORE_ALIAS') ? 1 : 0);


        return true;
    }

    public function getIgnoreKeyList()
    {
        return $this->ignore_key_list;
    }

    public function getNeededKeyList()
    {
        return $this->needed_key_list;
    }

    /**
     * Assigns all vars to smarty
     * @param unknown_type $params
     */
    protected function assignPaymentVars($params)
    {
        $tpl_vars = array();

        $currency = new Currency((int) $params['cart']->id_currency);
        $lang = new Language((int) $params['cart']->id_lang);
        $customer = new Customer((int) $params['cart']->id_customer);
        $address = new Address((int) $params['cart']->id_address_invoice);
        $country = new Country((int) $address->id_country, (int) $params['cart']->id_lang);

        $ogone_params = array();
        $ogone_params['PSPID'] = Configuration::get('OGONE_PSPID');
        $ogone_params['OPERATION'] = (Configuration::get('OGONE_OPERATION') === self::OPERATION_AUTHORISE ?
            self::OPERATION_AUTHORISE :
            self::OPERATION_SALE);

        $ogone_params['ACCEPTURL'] = $this->getConfirmationUrl();
        $ogone_params['DECLINEURL'] = $this->getDeclineUrl();
        $ogone_params['EXCEPTIONURL'] = $this->getExceptionUrl();
        $ogone_params['CANCELURL'] = Tools::getProtocol() . $_SERVER['HTTP_HOST'];
        $ogone_params['BACKURL'] = Tools::getProtocol() . $_SERVER['HTTP_HOST'];

        $total = $params['cart']->getOrderTotal(true, Cart::BOTH);
        $amount = number_format((float) number_format($total, 2, '.', ''), 2, '.', '') * 100;

        $ogone_params['ORDERID'] = $this->generateOrderId($params['cart']->id);
        $ogone_params['AMOUNT'] = $amount;
        $ogone_params['CURRENCY'] = $currency->iso_code;
        $ogone_params['LANGUAGE'] = $lang->iso_code . '_' . Tools::strtoupper($lang->iso_code);
        $ogone_params['CN'] = $customer->lastname;
        $ogone_params['EMAIL'] = $customer->email;
        $ogone_params['OWNERZIP'] = Tools::substr($address->postcode, 0, 10);
        $ogone_params['OWNERADDRESS'] = Tools::substr(trim($address->address1), 0, 35);
        $ogone_params['OWNERCTY'] =  $country->iso_code;
        $ogone_params['OWNERTOWN'] = Tools::substr($address->city, 0, 40);
        $ogone_params['PARAMPLUS'] = 'secure_key=' . $params['cart']->secure_key;

        if (!empty($address->phone)) {
            $ogone_params['OWNERTELNO'] = $address->phone;
        }

        if (Configuration::get('OGONE_USE_PM') && isset($params['pm']) && $params['pm'] instanceof OgonePM) {
            $ogone_params['BRAND'] = $params['pm']->brand;
            $ogone_params['PM'] = $params['pm']->pm;
            $tpl_vars['pm_obj'] = $params['pm'];
        }

        if (Configuration::get('OGONE_USE_TPL')) {
            foreach ($this->tpl_fields as $field) {
                $var_name = 'OGONE_' . $field;
                $value = Configuration::get($var_name);
                if ($value) {
                    $ogone_params[$field] = $value;
                }

            }
            $value = Configuration::get('OGONE_LOGO');
            if ($value) {
                $ogone_params['LOGO'] = $value;
            }

        }

        if (Configuration::get('OGONE_USE_ALIAS') && isset($params['alias'])) {
            $tpl_vars['local_alias_link'] = $this->getLocalAliasPaymentLink();
            $ogone_params['ALIAS'] =  $this->decryptAlias($params['alias']['alias']);
            $ogone_params['ALIASUSAGE'] = $this->l('You can use payment method stored on secure server');
            $ogone_params['BRAND'] = $params['alias']['brand'];
            $ogone_params['PM'] = 'CreditCard';

            $ogone_params['ECI'] = isset($params['eci']) ? $params['eci'] : '';
            $params['alias']['logo'] = $this->getAliasLogoUrl($params['alias'], 'cc_medium.png');

            $tpl_vars['alias_data'] = $params['alias'];
            if (!$this->isDirectDebitBrand($params['alias']['brand'])) {
                $tpl_vars['expiry_date'] = date('m/Y', strtotime($params['alias']['expiry_date']));
            } else {
                $tpl_vars['expiry_date'] = '';
            }
        } else {

            if (Configuration::get('OGONE_PROPOSE_ALIAS') && $this->canUseAliases()) {
                $ogone_params['ALIAS'] = $this->getDirectLinkInstance()->generateAlias($customer->id);
                $ogone_params['ALIASUSAGE'] = $this->l('You can use payment method stored on secure server');
            }


            $default_option_names = $this->getDefaultOptionNames();
            $default_option_name = isset($default_option_names[$lang->id]) ? $default_option_names[$lang->id] : null;
            $tpl_vars['default_option_name'] = $default_option_name;
        }

        $tpl_vars['ogone_logo_url'] = $this->getDefaultOptionLogoUrl();
        $tpl_vars['default_option_logo'] = $this->getDefaultOptionLogoUrl();

        $mode = (Configuration::get('OGONE_MODE') ? 'prod' : 'test');
        $tpl_vars['OGONE_MODE'] = Configuration::get('OGONE_MODE');

        if ($this->isKlarna($ogone_params) && !isset($ogone_params['ALIAS'])) {
            if ($this->canUseKlarna($country)) {
                $klarna_params = $this->getKlarnaVars($params['cart']);
                if (!$klarna_params) {
                    return false;
                }

                foreach ($klarna_params as $key => $value) {
                    $ogone_params[$key] = $value;
                }
                /* to avoid problem with sha signature on utf-8 like string (Klarna wants ISO) */
                foreach ($ogone_params as $key => $value) {
                    $ogone_params[$key] = $this->convertToASCII($value);
                }
                /* ISO */
                $tpl_vars['ogone_url'] = 'https://secure.ogone.com/ncol/' . $mode. '/orderstandard.asp';
                $tpl_vars['form_encoding'] = 'ISO-8859-1';
            } else {
                return false;
            }

        } else {
            $tpl_vars['ogone_url'] = 'https://secure.ogone.com/ncol/' . $mode . '/orderstandard_utf8.asp';
            $tpl_vars['form_encoding'] = 'UTF-8';
        }
        $ogone_params['ORIG'] = Tools::substr('ORPR' . str_replace('.', '', $this->version), 0, 10);
        $ogone_params['SHASign'] = $this->calculateShaSign($ogone_params, Configuration::get('OGONE_SHA_IN'));
        $tpl_vars['ogone_params'] = $ogone_params;
        $tpl_vars['immediate_payment'] = isset($params['immediate_payment']) ? $params['immediate_payment'] : false;

        $this->context->smarty->assign($tpl_vars);
        return true;

    }

    public function isKlarna($ogone_params)
    {
        if (Configuration::get('OGONE_USE_KLARNA')) {
            return true;
        }

        if (!is_array($ogone_params) || !isset($ogone_params['BRAND'])) {
            return false;
        }

        $elements = explode(' ', $ogone_params['BRAND']);
        return (count($elements) == 2 &&
            $elements[0] == 'Installment' &&
            in_array($elements[1], $this->klarna_countries)) ||
            (count($elements) == 3 &&
            $elements[0] == 'Open' &&
            $elements[1] == 'Invoice' &&
            in_array($elements[2], $this->klarna_countries));
    }

    /**
     * @see https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/klarna
     * @param unknown $order
     * @return boolean
     */
    public function canUseKlarna($country)
    {
        return in_array(Tools::strtoupper($country->iso_code), $this->klarna_countries);
    }

    /**
     * @see https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/klarna
     * @param unknown $ogone_params
     * @param unknown $order
     */
    public function getKlarnaVars($cart)
    {
        $customer = new Customer((int) $cart->id_customer);
        $address = new Address((int) $cart->id_address_invoice);
        $country = new Country((int) $address->id_country, $cart->id_lang);
        $carrier = new Carrier((int) $cart->id_carrier);
        $gender = new Gender((int) $customer->id_gender);

        $klarna_params = array();
        $klarna_params['OPERATION'] = 'RES';

        /* required in all countries */

        $klarna_params['ECOM_BILLTO_POSTAL_NAME_FIRST'] = Tools::substr($address->firstname, 0, 50);
        $klarna_params['ECOM_BILLTO_POSTAL_NAME_LAST'] = Tools::substr($address->lastname, 0, 50);
        $owneraddress = Tools::substr(implode(' ', array_filter(array($address->address1, $address->address2))), 0, 35);
        $klarna_params['OWNERADDRESS'] = $owneraddress;
        $klarna_params['OWNERZIP'] = Tools::substr($address->postcode, 0, 10);
        $klarna_params['EMAIL'] = Tools::substr($customer->email, 0, 50);
        $klarna_params['OWNERTOWN'] = Tools::substr($address->city, 0, 25);
        $ownertelno = Tools::substr($address->phone_mobile ? $address->phone_mobile : $address->phone, 0, 20);
        $klarna_params['OWNERTELNO'] = $ownertelno;
        $klarna_params['OWNERCTY'] = $country->iso_code;
        $klarna_params['ORDERSHIPMETH'] =  Tools::substr($carrier->name, 0, 20);

        /* required in scandinavy */
        if (in_array(Tools::strtoupper($country->iso_code), array('SE', 'FI', 'DK', 'NO'))) {
            $nr = Tools::substr($customer->siret ? $customer->siret : ($customer->ape ? $customer->ape : null), 0, 50);
            $klarna_params['CUID'] = $nr;
        }

        /* required in DE and NL*/
        if (in_array(Tools::strtoupper($country->iso_code), array('DE', 'NL'))) {
            $klarna_params['ECOM_CONSUMER_GENDER'] = ($gender->type == 1 ? 'F' : 'M');
            if ($customer->birthday && $customer->birthday != '0000-00-00') {
                $birthday = implode('/', array_reverse(explode('-', $customer->birthday)));
            } else {
                $birthday = date('Y/m/d', 1);
            }

            $klarna_params['ECOM_SHIPTO_DOB'] = $birthday;

            $klarna_params['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = $this->getStreetNumber($address);
        }

        /* if some parameters are empty, this payment cannot be used */
        if (count($klarna_params) !== count(array_filter($klarna_params))) {
            return null;
        }

        /*
         * ORDERSHIPCOST and ORDERSHIPTAXCODE are not required and it's suggested to use items
         */
        $products = $cart->getProducts();
        $total_shipping = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        if ($total_shipping > 0) {
            $products[] = array(
                'reference' => $this->l('Shipping'),
                'name' => $carrier->name,
                'price_with_reduction' => $total_shipping,
                'quantity' => 1,
                'rate' => isset($cart->carrier_tax_rate) ? $cart->carrier_tax_rate : 0,
            );
        }
        $idx = 1;
        foreach ($products as $product) {
            $product_row = array(
                'ITEMID' => $product['reference'],
                'ITEMNAME' => $product['name'],
                'ITEMPRICE' => round($product['quantity'] * (isset($product['price_with_reduction'])  ? $product['price_with_reduction'] : $product['price_wt']), 2) / $product['quantity'],
                'ITEMQUANT' => $product['quantity'],
                'ITEMVATCODE' => $product['rate'] . '%',
                'TAXINCLUDED' => '1',
            );

            foreach ($product_row as $key => $value) {
                $klarna_params[$key . $idx] = $value;
            }

            $idx++;
        }

        return $klarna_params;
    }

    protected function getStreetNumber($address)
    {
        $matches = array();
        $possible_matches = array();
        preg_match('/^(\d+)\s+.*$|^.*\s+(\d+)$/', $address->address1, $matches);
        $possible_matches[] = isset($matches[1]) ? $matches[1] : null;
        preg_match('/^(\d+)\s+.*$|^.*\s+(\d+)$/', $address->address2, $matches);
        $possible_matches[] = isset($matches[1]) ? $matches[1] : null;
        preg_match('/^.*(\d+).*$/', $address->address1, $matches);
        $possible_matches[] = isset($matches[1]) ? $matches[1] : null;
        preg_match('/^.*(\d+).*$/', $address->address2, $matches);
        $possible_matches[] = isset($matches[1]) ? $matches[1] : null;
        $possible_matches[] = 1;
        $possible_matches = array_values(array_filter($possible_matches));
        if ($possible_matches && !empty($possible_matches)) {
            return $possible_matches[0];
        }

        return null;
    }

    public function validate($id_cart, $id_order_state, $amount, $message = '', $secure_key = '')
    {
        $this->currentOrder = null;
        $this->validateOrder(
            (int) $id_cart,
            $id_order_state,
            $amount,
            $this->displayName,
            $message,
            null,
            null,
            true,
            pSQL($secure_key)
        );
        return $this->currentOrder;
    }

    /**
     * Gets translated description of Ogone payment status, based on code. Defaults to "Unknown code: xxx"
     * @param int $code
     * @return string  Translated Ogone payment status description
     */
    public function getCodeDescription($code)
    {
        $code = (int) $code;
        return isset($this->return_codes[$code]) ?
            $this->l($this->return_codes[$code][0]) :
            sprintf('%s %s', $this->l('Unknown code'), $code);
    }

    /**
     * Gets name of Ogone payment status, based on code. Defaults to self::PAYMENT_ERROR
     * @param int $code See Ogone::$return_codes
     * @return string Ogone payment status
     */
    public function getCodePaymentStatus($code)
    {
        return isset($this->return_codes[(int) $code]) ? $this->return_codes[(int) $code][1] : self::PAYMENT_ERROR;
    }

    /**
     * Gets id of Prestashop order status corresponding to Ogone status. Defaults to PS_OS_ERROR
     * @param string $ogone_status name of Ogone return state
     * @return int
     */
    public function getPaymentStatusId($ogone_status)
    {
        $status_id = (int) Configuration::get((string) $ogone_status);
        return ($status_id ? $status_id : (int) Configuration::get('PS_OS_ERROR'));
    }

    /**
     * Adds message to order
     * @param int $id_order
     * @param string $message
     * @return boolean
     */
    public function addMessage($id_order, $message)
    {
        if (!is_int($id_order) || $id_order <= 0) {
            return false;
        }

        if (!Validate::isCleanHtml($message)) {
            return false;
        }

        $message_obj = new Message();
        $message_obj->id_order = $id_order;
        $message_obj->message = $message . date(' (H:i:s)');
        $message_obj->private = true;
        return $message_obj->add();
    }

    public function calculateShaSign($ogone_params, $sha_key)
    {
        uksort($ogone_params, array('DirectLink', 'compareStringAsOgone'));

        $shasign = '';
        foreach ($ogone_params as $key => $value) {
            $shasign .= Tools::strtoupper($key) . '=' . $value . $sha_key;
        }

        $this->log('calculateShaSign');
        $this->log($ogone_params);
        $this->log('KEY: ' . $sha_key);
        $this->log('SIGN: ' . $shasign);
        $this->log('SHASIGN: ' . Tools::strtoupper(sha1($shasign)));
        return Tools::strtoupper(sha1($shasign));
    }

    protected function getPaymentMethodsList()
    {
        $pm_tpl_list = array();
        foreach (OgonePM::getAllIds() as $id) {
            $pm = new OgonePM($id, $this->context->language->id, $this->context->shop->id);
            $pm_tpl_list[] = $pm;
            $logo_path = '/views/img/pm/' . $id . '.png';
            $pm->logo_url = file_exists(dirname(__FILE__) . $logo_path) ?
                _MODULE_DIR_ . $this->name . $logo_path :
                _MODULE_DIR_ . $this->name . '/views/img/cc_medium.png';
        }
        usort($pm_tpl_list, array($this, 'sortByPosition'));
        return $pm_tpl_list;
    }

    protected function sortByPosition($a, $b)
    {
        return $a->position - $b->position;
    }

    protected function getValidationUrl()
    {
        return Tools::getProtocol() . $_SERVER['HTTP_HOST'] . _MODULE_DIR_ . 'ogone/validation.php';
    }

    protected function getConfirmationUrl()
    {
        return (version_compare(_PS_VERSION_, '1.5', 'ge')) ?
            $this->context->link->getModuleLink('ogone', 'confirmation') :
            Tools::getProtocol() . $_SERVER['HTTP_HOST'] . _MODULE_DIR_ . 'ogone/confirmation.php';
    }

    protected function getDeclineUrl()
    {
        return $this->getConfirmationUrl();
    }

    protected function getExceptionUrl()
    {
        return $this->getConfirmationUrl();
    }

    /**
     * Returns directlink doc's url, in function of the current language
     * @return string URL
     */
    protected function getDirectLinkDocUrl()
    {
        $lg_code = $this->getIngenicoLanguageCode();
        $lg_code2 = ($lg_code == 'en' ? 'int' : $lg_code);
        $direct_link_doc_url = $this->ingenico_server . $this->dl_guide;
        return sprintf($direct_link_doc_url, $lg_code2, $lg_code);
    }

    /**
     * Tries to find server's IP
     * @return string|null Ip address, prioritizing IPV4, or null if not found
     */
    protected function getServerIp()
    {
        if (isset($_SERVER) && isset($_SERVER['HTTP_HOST'])) {
            foreach (array(DNS_A, DNS_AAAA) as $flag) {
                $record = dns_get_record($_SERVER['HTTP_HOST'], $flag);
                if ($record && isset($record[0]) && isset($record[0]['ip']) && $record[0]['ip']) {
                    return $record[0]['ip'];
                }

            }
        }
        return null;
    }

    protected function getDefaultPaymentModes()
    {
        $pm_file = implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'data', 'payments.json'));
        $pm_data = (array) Tools::jsonDecode(Tools::file_get_contents($pm_file), true);
        return is_array($pm_data) ? array_values($pm_data) : array();
    }

    /**
     * Logs data to file if OGONE_USE_LOG config var is set
     * @param mixed $data
     */
    public function log($data)
    {
        static $id = null;
        if ($id === null) {
            $id = uniqid();
        }

        if (Configuration::get('OGONE_USE_LOG')) {
            if ($this->log_file === null) {
                $this->log_file = $this->getLogFileDir() . $this->getLogFileName();
                if (!file_exists($this->log_file)) {
                    $this->log(sprintf('PHP: %s; Prestashop: %s; Module: %s', PHP_VERSION, _PS_VERSION_, $this->version));
                }
            }
            $file = isset($_SERVER) && isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : __FILE__;
            $source = isset($_SERVER) && isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : PHP_SAPI;
            $timestamp = date('Y-m-d H:i:s');
            $data = is_string($data) ? $data : var_export($data, true);
            $message = sprintf('[%s] [%s] [%s] [%s] %s %s', $id, $timestamp, $file, $source, $data, PHP_EOL);
            return (bool) file_put_contents($this->log_file, $message, FILE_APPEND);
        }
        return false;
    }

    protected function getLogFileDir()
    {
        return implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'logs', ''));
    }

    protected function getLogFileName()
    {
        return sha1('ogone' . date('Ymd') . Configuration::get('OGONE_PSPID')) . '.ogone.log';
    }

    protected function getLogFiles()
    {
        $pattern = $this->getLogFileDir() . '*.ogone.log';
        return glob($pattern);
    }

    protected function getLogFilesWithUrls()
    {
        $result = array();
        foreach ($this->getLogFiles() as $log_file) {
            $log_name = basename($log_file);
            $file_key = sha1(realpath($log_file) . Configuration::get('OGONE_PSPID'));
            $result[$log_name] = _MODULE_DIR_ . 'ogone/logs.php?filename=' . $log_name . '&key=' . $file_key;
        }
        return $result;
    }

    protected function getLogFilesData()
    {
        $result = array();
        foreach ($this->getLogFiles() as $log_file) {
            $log_name = basename($log_file);
            $file_key = sha1(realpath($log_file) . Configuration::get('OGONE_PSPID'));
            $time = filemtime($log_file);
            $result[$time ? $time : $log_name] = array(
                'name' => $log_name,
                'url' => _MODULE_DIR_ . 'ogone/logs.php?filename=' . $log_name . '&key=' . $file_key,
                'dt' => date('Y-m-d H:i:s', $time),
                'size' => filesize($log_file),
            );
        }

        krsort($result);

        return $result;

    }

    protected function clearLogFiles()
    {
        $deleted_files = 0;
        foreach ($this->getLogFiles() as $log_file) {
            if (unlink($log_file)) {
                $deleted_files++;
            }

        }
        return $deleted_files;
    }

    protected function getDebugHtml()
    {
        $this->context->smarty->assign('log_files', $this->getLogFilesData());
        return $this->display(__FILE__, 'views/templates/admin/logs.tpl');
    }

    protected function uploadPaymentLogo($name, $target, $width = 194, $height = 80)
    {
        if (isset($_FILES[$name]) && isset($_FILES[$name]['tmp_name']) && !empty($_FILES[$name]['tmp_name'])) {
            $ext = Tools::substr($_FILES[$name]['name'], strrpos($_FILES[$name]['name'], '.') + 1);
            if ($ext !== 'png') {
                return array(false, $this->l('Only png images are accepted'));
            }

            if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
                if (ImageManager::validateUpload($_FILES[$name], 400000)) {
                    return array(false, $this->l('Invalid image.'));
                } else {
                    $result = ImageManager::resize($_FILES[$name]['tmp_name'], $target, $width, $height, $ext);
                    return array($result, $result ? $this->l('Image updated.') : $this->l('Error resizing image.'));
                }
            } else {
                if (checkImage($_FILES[$name], 400000)) {
                    return array(false, $this->l('Invalid image.'));
                } else {
                    $result = imageResize($_FILES[$name]['tmp_name'], $target, $width, $height, $ext);
                    return array($result, $result ? $this->l('Image updated.') : $this->l('Error resizing image.'));
                }
            }
        }
        return array(null, $this->l('Image not uploaded.'));
    }

    protected function getDefaultOptionLogoFilename()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views/img/default_user_logo.png';
    }

    protected function deleteDefaultOptionLogo()
    {
        $logo_filename = $this->getDefaultOptionLogoFilename();
        clearstatcache();
        $result = (file_exists($logo_filename) && unlink($logo_filename));
        clearstatcache();
        return $result;
    }

    protected function getDefaultOptionLogo()
    {
        $logo_filename = $this->getDefaultOptionLogoFilename();
        return file_exists($logo_filename) ? basename($logo_filename) : 'ogone.gif';
    }

    protected function getDefaultOptionLogoUrl()
    {
        return _MODULE_DIR_ . 'ogone/views/img/' . $this->getDefaultOptionLogo() . '?r=' . mt_rand();
    }

    protected function getDefaultOptionHtml()
    {
        clearstatcache();
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $default_names = $this->getDefaultOptionNames();
        foreach ($languages as $language) {
            if (!isset($default_names[(int) $language['id_lang']])) {
                $default_names[(int) $language['id_lang']] = '';
            }
        }

        $tpl_vars = array(
            'default_names' => $default_names,
            'defaultLanguage' => $default_lang,
            'custom_logo_exists' => file_exists($this->getDefaultOptionLogoFilename()),
            'logo_url' => $this->getDefaultOptionLogoUrl(),
            'languages' => $languages,
            'flags' => $this->displayFlags($languages, $default_lang, 'OGONE_DEFAULT_NAME', 'OGONE_DEFAULT_NAME', true),
            'flags_pm_desc' => $this->displayFlags($languages, $default_lang, 'add_pm_desc', 'add_pm_desc', true),
            'flags_pm_name' => $this->displayFlags($languages, $default_lang, 'add_pm_name', 'add_pm_name', true),

        );

        $this->context->smarty->assign($tpl_vars);
        return $this->display(__FILE__, 'views/templates/admin/default_option.tpl');

    }

    protected function getDefaultOptionNames()
    {
        $default_names = Tools::jsonDecode(Configuration::get('OGONE_DEFAULT_NAME'), true);
        if (!is_array($default_names)) {
            $default_names = array();
        }

        return $default_names;
    }

    public function getDirectLinkInstance()
    {
        if ($this->direct_link_instance === null) {
            $this->direct_link_instance = $this->createDirectLinkInstance();
        }

        return $this->direct_link_instance;
    }

    public function createDirectLinkInstance()
    {
        $dl = new DirectLink();
        $dl->setPSPId(Configuration::get('OGONE_PSPID'));
        $dl->setUserId(Configuration::get('OGONE_DL_USER'));
        $dl->setPassword(Configuration::get('OGONE_DL_PASSWORD'));
        $dl->setShaInPassphrase(Configuration::get('OGONE_DL_SHA_IN'));
        $dl->setShaOutPassphrase(Configuration::get('OGONE_SHA_OUT'));
        $dl->setUrl((int) Configuration::get('OGONE_MODE') === 1 ? DirectLink::URL_PROD : DirectLink::URL_TEST);
        $dl->setTimeout(Configuration::get('OGONE_DL_TIMEOUT'));

        return $dl;
    }

    public function addTransactionLog($id_cart, $id_order, $id_customer, $response)
    {
        $tl = new OgoneTransactionLog();
        $tl->id_cart = (int) $id_cart;
        $tl->id_order = (int) $id_order;
        $tl->id_customer = (int) $id_customer;
        $tl->payid = isset($response['PAYID']) ? (int) $response['PAYID'] : '';
        $tl->status = isset($response['STATUS']) ? (int) $response['STATUS'] : '';
        $tl->response = OgoneTransactionLog::encodeResponse(is_array($response) ? $response : '');
        return $tl->save();
    }

    /**
     * Checks whether direct link can be used
     * @return boolean true if direct link instance is initalized and can be used
     */
    public function canUseDirectLink()
    {
        $dl = $this->getDirectLinkInstance();
        return $dl && Configuration::get('OGONE_USE_DL') && $dl->checkPrerequisites() && $dl->isInitialized();
    }

    /**
     * @param Order $order
     * @return boolean[]|NULL[]|boolean[]|string[]
     */
    public function canCapture(Order $order)
    {
        if (!Validate::isLoadedObject($order)) {
            return array(false, $this->l('Order capture: Invalid order'));
        }

        if (!$this->active) {
            return array(false, $this->l('To capture orders you need to activate module'));
        }

        if ($order->module !== $this->name) {
            return array(false, $this->l('You can only capture orders paid via Ingenico'));
        }

        /**
         * We are checking if prestashop order state is ok
         */
        /*
        $expected_state_id = (int) Configuration::get(self::PAYMENT_AUTHORIZED);
        $current_state_id = (int) $order->getCurrentState();
        if (!$current_state_id || !$expected_state_id || $expected_state_id !== $current_state_id) {
            $current_state = new OrderState($current_state_id);
            $current_state_name = Validate::isLoadedObject($current_state) ?
                $current_state->name[$this->context->language->id] :
                $current_state_id;
            $expected_state = new OrderState($expected_state_id);
            $expected_state_name = Validate::isLoadedObject($expected_state) ?
                $expected_state->name[$this->context->language->id] :
                $expected_state_id;
            $pattern = $this->l('Invalid order state - "%s" expected, "%s" found');
            return array(false, sprintf($pattern, $expected_state_name, $current_state_name));
        }
        */

        /**
         * We should have at least one transaction
         */
        $last_transaction = OgoneTransactionLog::getLastByCartId($order->id_cart);
        if (!$last_transaction || !is_array($last_transaction)) {
            $pattern = $this->l('Unable to find transaction for order %d - cart %d');
            return array(false, sprintf($pattern, $order->id, $order->id_cart));
        }
/*
        if (!isset($last_transaction['status']) || !(in_array((int)$last_transaction['status'], array(DirectLink::STATUS_AUTHORISED, 0)))) {
            $pattern = $this->l('You can only capture order with status %d, last saved status: %d');
            return array(false, sprintf($pattern, DirectLink::STATUS_AUTHORISED, $last_transaction['status']));
        }
*/
        return array(true, '');
    }


    /**
     * @param Order $order
     * @return boolean[]|NULL[]|boolean[]|string[]
     */
    public function canRefund(Order $order)
    {
        if (!Validate::isLoadedObject($order)) {
            return array(false, $this->l('Order capture: Invalid order'));
        }

        if (!$this->active) {
            return array(false, $this->l('To capture orders you need to activate module'));
        }

        if ($order->module !== $this->name) {
            return array(false, $this->l('You can only capture orders paid via Ingenico'));
        }

        /**
         * We should have at least one transaction
         */
        $last_transaction = OgoneTransactionLog::getLastByCartId($order->id_cart);
        if (!$last_transaction || !is_array($last_transaction)) {
            $pattern = $this->l('Unable to find transaction for order %d - cart %d');
            return array(false, sprintf($pattern, $order->id, $order->id_cart));
        }

        if ($order->total_paid_real == 0) {
            $pattern = $this->l('Nothing to refund for order %d - cart %d');
            return array(false, sprintf($pattern, $order->id, $order->id_cart));
        }
/*
        if (!isset($last_transaction['status']) || !(in_array((int)$last_transaction['status'], array(DirectLink::STATUS_AUTHORISED, 0)))) {
            $pattern = $this->l('You can only capture order with status %d, last saved status: %d');
            return array(false, sprintf($pattern, DirectLink::STATUS_AUTHORISED, $last_transaction['status']));
        }*/

        return array(true, '');
    }

    public function refund($order, $refund_amount = null)
    {
        $result = false;
        $message = '';

        if (!($order instanceof Order) || !Validate::isLoadedObject($order)) {
            $message = $this->l('Invalid order');
        } else {

            $last_transaction = OgoneTransactionLog::getLastByCartId($order->id_cart);
            if (!$last_transaction || !is_array($last_transaction)) {
                $pattern = $this->l('Order refund: unable to find transaction for cart %d');
                return array(false, sprintf($pattern, $order->id_cart));
            }

            $max_refund_amount =  $order->total_paid_real - $this->getRefundTransactionsAmount($order->id);
            if ($refund_amount === null) {
                $refund_amount = $max_refund_amount;
            }
            $refund_amount = max(0, min($max_refund_amount, $refund_amount));

            $data = array(
                'AMOUNT' => (int)round($refund_amount * 100),
                'OPERATION' => DirectLink::REFUND,
            );

            $orderid = OgoneTransactionLog::getOgoneOrderIdFromTransaction($last_transaction);
            if ($orderid) {
                $data['ORDERID'] = $orderid;
            } else {
                $data['PAYID'] = $last_transaction['payid'];
            }

            try {
                $this->addMessage($order->id, 'Making request of refund: ' . $this->convertArrayToReadableString($data, ' ; <br />'));

                $response = $this->getDirectLinkInstance()->maintenance($data);
                $this->addTransactionLog($order->id_cart, $order->id, $order->id_customer, $response);
                if (isset($response['NCERROR']) && !empty($response['NCERROR'])) {
                    $message = isset($response['NCERRORPLUS']) && !empty($response['NCERRORPLUS']) ?
                    $response['NCERRORPLUS'] :
                    sprintf($this->l('Refund error %s', $response['NCERROR']));
                } elseif (isset($response['STATUS'])) {
                    // $response_status = (int) $response['STATUS'];
                    $result = true;
                    $message = 'Refund request sended. Response: ' . $this->convertArrayToReadableString($response, ' ; <br />');
                } else {
                    $message = $this->l('Refund error: invalid response, no status nor error included');
                }

            } catch (Exception $ex) {
                $message = $ex->getMessage();
            }
        }
        if ($order && $order->id) {
            $this->addMessage($order->id, $message);
        }
        return array($result, $message);
    }

    /**
     * Captures order
     * @param Order $order
     */
    public function capture($order, $capture_amount = null)
    {
        $result = false;
        $message = '';

        $accepted_payment_state_id = (int) Configuration::get(self::PAYMENT_ACCEPTED);
        $accepted_payment_state = new OrderState($accepted_payment_state_id);

        $transition_payment_state_id = (int) Configuration::get(self::PAYMENT_IN_PROGRESS);
        $transition_payment_state = new OrderState($transition_payment_state_id);

        $allowed_statuses = array(DirectLink::STATUS_CAPTURED, DirectLink::STATUS_PAYMENT_PROCESSING,
            DirectLink::STATUS_PAYMENT_BEING_PROCESSED);

        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $captured = $this->getCaptureTransactionsAmount($order->id);
            $captured_pending = $this->getPendingCaptureTransactionsAmount($order->id);
            $capture_amount = max(0, min($order->total_paid -  $captured - $captured_pending, $capture_amount ? $capture_amount : $order->total_paid));
        } else {
            $capture_amount = max(0, min($order->total_paid -  $order->total_paid_real, $capture_amount ? $capture_amount : $order->total_paid));
        }

        list($can_capture, $error) = $this->canCapture($order);

        if (!($order instanceof Order) || !Validate::isLoadedObject($order)) {
            $message = $this->l('Invalid order');
        } elseif (!$accepted_payment_state_id || !Validate::isLoadedobject($accepted_payment_state)) {
            $message = sprintf($this->l('Unable to load accepted payment order state %d'), $accepted_payment_state_id);
        } elseif (!$transition_payment_state || !Validate::isLoadedobject($transition_payment_state)) {
            $pattern = $this->l('Unable to load transitional payment order state %d');
            $message = sprintf($pattern, $transition_payment_state_id);
        } elseif (!$can_capture) {
            $message = sprintf($this->l('Unable to capture order %s : %s'), $order->id, $error);
        } elseif (!$this->canUseDirectLink()) {
            $message = $this->l('Unable to use DirectLink. Please check prerequisites and configuration.');
        } else if ($capture_amount !== null && ($capture_amount <=0 || $capture_amount > $order->total_paid)) {
            $message = $this->l('Invalid capture amount.');
        } else {
            $last_transaction = OgoneTransactionLog::getLastByCartId($order->id_cart);
            if (!$last_transaction || !is_array($last_transaction)) {
                $pattern = $this->l('Order capture: unable to find transaction for cart %d');
                return array(false, sprintf($pattern, $order->id_cart));
            }
            $data = array(
                'AMOUNT' => (int)round($capture_amount * 100),
                'OPERATION' => DirectLink::CAPTURE,
            );

            $orderid = OgoneTransactionLog::getOgoneOrderIdFromTransaction($last_transaction);
            if ($orderid) {
                $data['ORDERID'] = $orderid;
            } else {
                $data['PAYID'] = $last_transaction['payid'];
            }

            try {
                $response = $this->getDirectLinkInstance()->maintenance($data);
                $this->addTransactionLog($order->id_cart, $order->id, $order->id_customer, $response);
                $this->log(sprintf('Capture result: %s', Tools::jsonEncode($response)));

                if (isset($response['NCERROR']) && !empty($response['NCERROR'])) {
                    $message = isset($response['NCERRORPLUS']) && !empty($response['NCERRORPLUS']) ?
                        $response['NCERRORPLUS'] :
                        sprintf($this->l('Capture error %s', $response['NCERROR']));

                } elseif (isset($response['STATUS']) && in_array((int) $response['STATUS'], $allowed_statuses)) {
                    $response_status = (int) $response['STATUS'];
                    try {

                        if ($response_status === DirectLink::STATUS_CAPTURED) {

                            $precision = defined('_PS_PRICE_COMPUTE_PRECISION_') ? _PS_PRICE_COMPUTE_PRECISION_ : 6;
                            /**
                             * Order captured directly, we can make update now
                             */
                            $order->total_paid_real = $order->total_paid_real + round($response['AMOUNT'], $precision);
                            if ($order->update()) {
                                $update_history = true;
                                $message = sprintf($this->l('Amount of %s captured', $response['AMOUNT']));

                            } else {
                                $update_history = false;
                                $message = sprintf($this->l('Order capture: unable to update order %d'), $order->id);
                            }
                            $payment_state_id = $accepted_payment_state_id;
                        } else {
                            /**
                             * Order authorisation in progress, final capture will be confirmed via direct request
                             * @var unknown
                             */
                            $update_history = true;
                            $payment_state_id = $transition_payment_state_id;
                            $currency = new Currency($order->id_currency);
                            $message = sprintf($this->l('Capture of %s %s in progress, final capture will be confirmed via direct request'), $response['AMOUNT'], $currency->iso_code);
                        }

                        if ($update_history) {
                            if ((int)$order->current_state === $payment_state_id) {
                                // no need to update history
                                $result = true;
                            } else {
                                $history = new OrderHistory();
                                $history->id_order = (int) $order->id;
                                $history->changeIdOrderState($payment_state_id, (int) $order->id);
                                $result = $history->addWithemail(true, array());
                                if (!$result) {
                                    $message .= $this->l('Error updating message history');
                                }
                            }

                        }
                    } catch (Exception $ex) {
                        $message = sprintf($this->l('Error adding order history - %s', $ex->getMessage()));
                    }

                } elseif (isset($response['STATUS'])) {
                    $pattern = $this->l('Capture error: expecting status %d,  %d sent');
                    $message = sprintf($pattern, DirectLink::STATUS_CAPTURED, (int) $response['STATUS']);
                } else {
                    $message = $this->l('Capture error: invalid response, no status nor error included');
                }
            } catch (Exception $ex) {
                $message = $ex->getMessage();
                $this->addMessage($order->id, $message);

            }
        }

        $this->log($message);
        if (!$result) {
            if (version_compare(_PS_VERSION_, '1.6', 'ge')) {
                $id_order = Validate::isLoadedObject($order) ? $order->id : null;
                $id_employee = isset($this->context->employee) ? (int) $this->context->employee->id : null;
                if (class_exists('PrestaShopLogger')) {
                    PrestaShopLogger::addLog($message, 2, null, 'Order', $id_order, true, $id_employee);
                }
            }
        }
        if ($order && $order->id) {
            $this->addMessage($order->id, $message);
        }

        return array($result, $message);
    }

    /* TABS DISPLAY */

    /**
     * Return list of Ingenico return codes, usable in HelperList
     * @return array
     */
    public function getReturnCodesList()
    {
        $result = array();
        foreach ($this->return_codes as $code => $description) {
            $result[(int) $code] = $this->l($description[0]);
        }

        return $result;
    }

    /**
     * Return colors associated to Ingenico status
     * Background color is taken from associated order status
     * Foreground color is calculated by Tools::getBrightness
     * @param int $status
     * @return array [background color: hex color, foreground color : hex color]
     */
    public function getPaymentStatusColor($status)
    {
        if (!isset($this->return_code_list_colors[(int) $status])) {
            $order_state_id = $this->getPaymentStatusId($this->getCodePaymentStatus($status));
            if ($order_state_id) {
                $order_state = new OrderState($order_state_id);
                $bg_color = $order_state->color ? $order_state->color : '#999999';
            } else {
                $bg_color = '#999';
            }

            $color = Tools::getBrightness($bg_color) < 128 ? '#ffffff' : '#383838';
            $this->return_code_list_colors[$status] = array($bg_color, $color);
        }
        return $this->return_code_list_colors[$status];
    }

    /* ALIAS & HTP */

    public function canUseAliases()
    {
        return Configuration::get('OGONE_USE_ALIAS') && $this->canUseDirectLink();
    }

    public function canUseAliasesViaDL()
    {
        return $this->canUseAliases() && Configuration::get('OGONE_ALIAS_BY_DL');
    }

    public function getHostedTokenizationPageRegistrationUrls($id_customer, $allow_immediate_payment = false)
    {
        $result = array();
        $pms = Tools::jsonDecode(Configuration::get('OGONE_ALIAS_PM'), true);
        if (is_array($pms)) {
            foreach ($pms as $alias_pm => $active) {
                if (!$active) {
                    continue;
                }
                $result[$alias_pm] = $this->getHostedTokenizationPageRegistrationUrl($id_customer, $alias_pm, $allow_immediate_payment);
            }
        }
        return $result;
    }

    /**
     * Gets unique url to hosted tokenization page
     * Generates alias name based on customer id
     * @param integer $id_customer
     */
    public function getHostedTokenizationPageRegistrationUrl($id_customer, $alias_pm, $allow_immediate_payment = false)
    {
        $url = Configuration::get('OGONE_MODE') ? 'https://secure.ogone.com' : 'https://ogone.test.v-psp.com';
        $url .= '/Tokenization/HostedPage';
        $alias = $this->getDirectLinkInstance()->generateAlias($id_customer);
        $params_ok = array('result' => 'ok', 'alias_full' => $alias);
        $params_ko = array('result' => 'ko', 'alias_full' => $alias);

        if ($allow_immediate_payment) {
            $params_ok['aip'] = 1;
        }

        if (version_compare(_PS_VERSION_, '1.5', 'ge')) {
            $accept_url = $this->context->link->getModuleLink(
                $this->name,
                'aliases',
                $params_ok,
                true
            );
            $exception_url = $this->context->link->getModuleLink(
                $this->name,
                'aliases',
                $params_ko,
                true
            );
        } else {
            $base_url = (_PS_SSL_ENABLED_ ? 'https://' : 'http://' ).
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
            __PS_BASE_URI__.'modules/ogone/aliases.php';
            $accept_url = $base_url.'?'.http_build_query($params_ok);
            $exception_url = $base_url.'?'.http_build_query($params_ko);
        }

        $query = array(
            'ACCOUNT.PSPID' => Configuration::get('OGONE_PSPID'),
            'ALIAS.ALIASID' => $alias,
            'CARD.PAYMENTMETHOD' => $alias_pm,
            'PARAMETERS.ACCEPTURL' => $accept_url,
            'PARAMETERS.EXCEPTIONURL' => $exception_url,
            'PARAMETERS.PARAMPLUS' => $alias,
        );

        // for Direct Debits BRAND need to be the same as PAYMENTMETHOD
        if (stristr($alias_pm, 'DirectDebit') !== false) {
            $query['CARD.BRAND'] = str_replace('DirectDebits', 'Direct Debits', $alias_pm);
            $query['CARD.PAYMENTMETHOD'] =str_replace('DirectDebits', 'Direct Debits', $alias_pm);

        }
        if (Configuration::get('OGONE_DONT_STORE_ALIAS') && $allow_immediate_payment) {
            $query['ALIAS.STOREPERMANENTLY'] = 'N';
        }

        $language_code = $this->getLanguageCode();
        if ($language_code) {
            $query['LAYOUT.LANGUAGE'] = $language_code;
        }
        $sha_sign = Configuration::get('OGONE_ALIAS_SHA_IN');
        $query['SHASIGNATURE.SHASIGN'] = $this->getDirectLinkInstance()->getShaSign($query, $sha_sign);
        return $url . '?' . http_build_query($query);
    }

    protected function getLanguageCode()
    {
        $code = $this->context->language->language_code;
        $elements = explode('-', $code);
        if (count($elements) == 2) {
            return Tools::strtolower($elements[0]) . '-' . Tools::strtoupper($elements[1]);
        }

        return null;
    }

    public function createAlias($id_customer, $data, $skip_sha_verification = false)
    {

        $this->log(sprintf('Creating alias for customer %s :  %s', $id_customer, $this->convertArrayToReadableString($data, ' ; ')));

        foreach ($this->expected_alias_return_fields as $name) {
            if (!array_key_exists($name, $data)) {
                return array(false, sprintf($this->l('Unable to save alias - field %s do not exists'), $name));
            }
        }
        if (!$skip_sha_verification && !array_key_exists('SHASIGN', $data)) {
            return array(false, sprintf($this->l('Unable to save alias - field %s do not exists'), 'SHASIGN'));
        }

        if (!$skip_sha_verification) {
            $ogone_params = $data;
            unset($ogone_params['SHASIGN']);

            $passphrase = Configuration::get('OGONE_SHA_OUT');
            $sha_calculated = $this->getDirectLinkInstance()->getShaSign($ogone_params, $passphrase);

            if ($sha_calculated !== $data['SHASIGN']) {
                $pattern = $this->l('Unable to save alias - sha calculated (%s) do not match sha received (%s)');
                return array(false, sprintf($pattern, $sha_calculated, $data['SHASIGN']));
            }

        }

        if (!empty($data['NCERROR'])) {
            return array(false, sprintf($this->l('Unable to save alias - %s'), $data['NCERROR']));
        }

        if (OgoneAlias::getByAlias($this->encryptAlias($data['ALIAS']))) {
            return array(false, sprintf($this->l('Unable to save alias - alias %s exists'), $data['ALIAS']));
        }

        $alias_parts = explode('_', $data['ALIAS']);
        if (count($alias_parts) < 2 ||
            (int) $alias_parts[0] !== (int) $id_customer ||
            !Validate::isLoadedObject(new Customer($id_customer))) {
            return array(false, $this->l('Unable to save alias - invalid customer'));
        }

        if (!$this->isDirectDebitBrand($data['BRAND'])) {
            $expiry_date = DateTime::createFromFormat('my', $data['ED']);
            if (!$expiry_date) {
                return array(false, sprintf($this->l('Unable to save alias - invalid expiry date %s'), $data['ED']));
            }
        } else {
            $expiry_date = null;
        }


        $ogone_alias = new OgoneAlias();
        $ogone_alias->id_customer = $id_customer;
        $ogone_alias->alias = $this->encryptAlias($data['ALIAS']);
        $ogone_alias->cn = $data['CN'];
        $ogone_alias->cardno = $data['CARDNO'];
        $ogone_alias->brand = $data['BRAND'];
        $ogone_alias->active = 1;
        if ($expiry_date) {
            $ogone_alias->expiry_date = $expiry_date->format('Y-m-t 23:59:59'); // last day of month
        }
        $ogone_alias->is_temporary = isset($data['STOREPERMANENTLY']) && $data['STOREPERMANENTLY'] == 'N' ? 1 : 0;

        if (!$ogone_alias->save()) {
            return array(false, $this->l('Unable to save alias'));
        }

        return array(true, $ogone_alias->id);

    }

    protected function isDirectDebitBrand($brand)
    {
        return (Tools::substr(str_replace(' ', '', Tools::strtolower($brand)), 0, 11) == 'directdebit');
    }

    /**
     * Changes shop context if necessary
     * @param Cart $cart
     */
    public function setShopContext($cart)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return false;
        }

        if (!Shop::isFeatureActive()) {
            return false;
        }

        $context = Context::getContext();
        if ($context->shop->id != $cart->id_shop) {
            Cache::clean('*');
            CurrencyCacheCleaner::clean();
            ProductCacheCleaner::clean();
            SpecificPriceCacheCleaner::clean();
            $context->shop = new Shop($cart->id_shop);
            Shop::setContext(Shop::CONTEXT_SHOP, $cart->id_shop);
            Cache::clean('*');
            CurrencyCacheCleaner::clean();
            ProductCacheCleaner::clean();
            SpecificPriceCacheCleaner::clean();
            return true;
        }

        return false;
    }

    /**
     * We cannot assume that iconv / transliterate / mb_convert works as it is needed
     * @param string $str
     */
    protected function convertToASCII($str)
    {
        $decoded = utf8_decode($str);
        $src = 'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ';
        $tgt = 'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy';
        return strtr($decoded, utf8_decode($src), $tgt);
    }

    public function getAliasLogoUrl(array $alias, $default = 'cc_small.png')
    {
        $path =  array('views', 'img', Tools::strtolower(str_replace(' ', '', $alias['brand']). '.png'));

        $logo_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path);
        $base_url = _MODULE_DIR_ . $this->name;
        if (file_exists($logo_path)) {
            return implode('/', array($base_url, implode('/', $path)));
        } else {
            return implode('/', array($base_url, 'views', 'img', $default));
        }
    }

    public function generateOrderId($id_cart)
    {
        return sprintf('%d-%s', $id_cart, base_convert(time(), 10, 36));
    }

    public function extractCartId($id_order)
    {
        $id_cart = stristr($id_order, '-', true);
        return $id_cart ? $id_cart : $id_order;
    }

    public function doDirectLinkAliasPayment(Cart $cart, OgoneAlias $alias)
    {
        $result = false;

        list($check_result, $error) = $this->checkAliasPaymentPrerequisites($cart, $alias);


        if (!$check_result) {
            return array(self::DL_ALIAS_RET_ERROR, $error);
        }

        $data = array(
            'ALIAS'     => $this->decryptAlias($alias->alias),
            'AMOUNT'    => round($cart->getOrderTotal() * 100),
            'ORDERID'   => $this->generateOrderId($cart->id),
            'CURRENCY'  => $this->context->currency->iso_code,
            'ECI'       => self::INGENICO_ECI_DL,	 // ECI value "9" must be sent for reccurring transactions.
            'OPERATION' => Configuration::get('OGONE_OPERATION')
        );

        if ($this->use3DSecureForDL()) {
            $data = $data + $this->getDirectLink3DSData($cart, $alias);
        }

        $transactions = (OgoneTransactionLog::getAllByCartId($cart->id));
        $astatuses = array(DirectLink::STATUS_REFUSED, DirectLink::STATUS_REFUSED,DirectLink::STATUS_WAITING_FOR_IDENTIFICATION);


        foreach ($transactions as $k => $transaction) {
            $this->log($transaction['response']);
            $tr = Tools::jsonDecode($transaction['response'], true);
            if ($tr &&
                isset($tr['ORDERID']) &&
                $tr['ORDERID'] != $data['ORDERID'] &&
                ((isset($tr['STATUS']) && in_array($tr['STATUS'], $astatuses)) ||
                 (time()-strtotime($tr['date_add']) > 360))
                ) {
                $this->log('unset');
                unset($transactions[$k]);
            }

        }

        if ($this->issetDLProcessingToken($cart->id) || !empty($transactions)) {
            if ($this->issetDLProcessingToken($cart->id)) {
                $this->log('PREVIOUS TRANSACTION DETECTED');
            }
            if (!empty($transactions)) {
                $this->log('PROCESSING TOKEN');
            }
            $id_order = Order::getOrderByCartId($cart->id);
            $customer = new Customer($cart->id_customer);

            if ($id_order) {
                $this->log('ORDER DETECTED '. $id_order);

                $redirect = 'index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.
                    $this->id.'&id_order='.$id_order.'&key='.$customer->secure_key.'&why=pt';
            } else {
                $this->log('REDIRECT ');

                $redirect = 'index.php?controller=order&step=3&why=ptno';
            }
            Tools::redirect($redirect);
        }

        $this->setDLProcessingToken($cart->id);

        $this->log($data);

        $response = $this->getDirectLinkInstance()->order($data);

        $this->log($response);

        $this->addTransactionLog($cart->id, null, $alias->id_customer, $response);

        $this->deleteDLProcessingToken($cart->id);

        if ($this->use3DSecureForDL() && $response &&
            isset($response['STATUS']) && (int)$response['STATUS'] === DirectLink::STATUS_WAITING_FOR_IDENTIFICATION) {
            list($result, $message) = $this->processDLAP3DS($response, $cart);
            if ($result && $message) {
                return array(self::DL_ALIAS_RET_INJECT_HTML, $message);
            }
        } else {
            list($result, $message) = $this->processDLAPResponse($response, $cart);
            if ($result) {
                return array(self::DL_ALIAS_RET_PAYMENT_DONE, $message);
            }
        }

        if ($message) {
            $this->log($message);
        }

        return array(self::DL_ALIAS_RET_ERROR, $message ?  $message : $this->l('DirectLink payment error'));
    }

    protected function processDLAP3DS($response, $cart)
    {

        if (!$response || !isset($response['STATUS']) ||
            (int)$response['STATUS'] !== DirectLink::STATUS_WAITING_FOR_IDENTIFICATION) {
            return array(false, $this->l('Status error'));
        }

        if (!isset($response['HTML_ANSWER']) || empty($response['HTML_ANSWER'])) {
            return array(false, $this->l('No expected answer'));
        }

        $html_answer = base64_decode($response['HTML_ANSWER']); /* required to decode API response */

        return ($html_answer ? array(true, $html_answer) : array(false, $this->l('Unable to decode response')));
    }

    protected function processDLAPResponse($response, $cart)
    {

        if (!$response || !is_array($response) || !isset($response['STATUS'])) {
            return array(false, $this->l('Response error'));
        }

        if (!empty($response['NCERROR'])) {
            $message = (isset($response['NCERRORPLUS']) && !empty($response['NCERRORPLUS']) ?
                $response['NCERRORPLUS'] : $response['NCERROR']);
            return array(false, $this->l($message));
        }

        $ogone_return_code = (int) $response['STATUS'];
        $this->log('ogone_return_code : ' . $ogone_return_code);

        $ogone_state = $this->getCodePaymentStatus($ogone_return_code);
        $this->log('ogone_state : ' . $ogone_state);

        $ogone_state_description = $this->getCodeDescription($ogone_return_code);
        $this->log('ogone_state_description : ' . $ogone_state_description);

        $payment_state_id = $this->getPaymentStatusId($ogone_state);
        $this->log('payment_state_id : ' . $payment_state_id);

        $amount_paid = ($ogone_state === Ogone::PAYMENT_ACCEPTED || $ogone_state === Ogone::PAYMENT_AUTHORIZED
            || $ogone_state === Ogone::PAYMENT_IN_PROGRESS  ?
            (float) $response['AMOUNT'] :
            0);

        $this->log('amount_paid : ' . $amount_paid);

        $this->addTransactionLog($cart->id, 0, $cart->id_customer, $response);

        $message = sprintf('%s %s', $ogone_state_description, Tools::safeOutput($ogone_state));
        $this->log($message);
        $this->log('Validating order, state ' . $payment_state_id);
        $result = (bool)$this->validate(
            $cart->id,
            $payment_state_id,
            $amount_paid,
            $message,
            $cart->secure_key
        );

        $this->log($result ? sprintf('Order validated as %s', $this->currentOrder) : 'Order not validated');

        $this->log('Order validate result ' . $this->currentOrder);
        if ($this->currentOrder) {
            $this->addTransactionLog($cart->id, $this->currentOrder, $cart->id_customer, $response);
        }

        return array($result, $result ? '' : $this->l('Unable to validate order') );
    }

    public function use3DSecureForDL()
    {
        return (bool)Configuration::get('OGONE_USE_D3D');
    }

    public function getDirectLink3DSData(Cart $cart, OgoneAlias $alias)
    {
        $data = array(
            'FLAG3D'            => 'Y',
            'HTTP_ACCEPT'       => $_SERVER['HTTP_ACCEPT'],
            'HTTP_USER_AGENT'   => $_SERVER['HTTP_USER_AGENT'],
            'WIN3DS'            => $this->getWin3DSOption(),
            'ACCEPTURL'         => $this->getConfirmationUrl(),// @todo
            'DECLINEURL'        => $this->getDeclineUrl(),// @todo
            'EXCEPTIONURL'      => $this->getExceptionUrl(),// @todo
            'PARAMPLUS'         => '3ds=1&aid='.$alias->id.'&secure_key='.$cart->secure_key . '&dg='.$this->get3DSecureConfirmationDigest($cart, $alias),
            'COMPLUS'           => '',
            'LANGUAGE'          =>  $this->getLanguageCode(),
        );
        return $data;
    }

    public function getWin3DSOption()
    {
        $win3ds = Configuration::get('OGONE_WIN3DS');
        return ($win3ds && in_array($win3ds, array('MAINW', 'POPUP', 'POPIX')) ? $win3ds : 'MAINW');
    }

    protected function get3DSecureConfirmationDigest(Cart $cart, OgoneAlias $alias)
    {
        $data = array(_COOKIE_KEY_, $cart->id, $alias->alias, $alias->id, Configuration::get('OGONE_PSPID'), Configuration::get('OGONE_SHA_IN'), __FILE__);
        return sha1(implode(_COOKIE_KEY_, array_map('sha1', $data)));
    }

    protected function checkAliasPaymentPrerequisites(Cart $cart, OgoneAlias $alias)
    {
        $result = array(false, '');

        if (!$cart->id || !Validate::isLoadedObject($cart)) {
            $result[1] = $this->l('Invalid cart');
            return $result;
        }

        if (!Validate::isLoadedObject($alias)) {
            $result[1] = $this->l('Invalid alias');
            return $result;
        }

        if (!$alias->active) {
            $result[1] = $this->l('Alias is inactive');
            return $result;
        }

        if (!$this->isDirectDebitBrand($alias->brand)) {
            if (!$alias->expiry_date || strtotime($alias->expiry_date) < time()) {
                $result[1] = $this->l('Alias expired!');
                return $result;
            }
        }

        if (!$cart->id_customer || !$alias->id_customer || (int)$cart->id_customer !== (int)$alias->id_customer) {
            $result[1] = $this->l('Invalid customer');
            return $result;
        }

        if (Order::getOrderByCartId($cart->id)) {
            $result[1] = $this->l('Order has been already placed for this cart');
            return $result;
        }
        return array(true, '');
    }

    public function encryptAlias($alias)
    {
        if (Tools::substr($alias, 0, 1) === '!') {
            return $alias;
        }
        return '!' . $this->getCipherTool()->encrypt($alias);

    }

    public function decryptAlias($alias)
    {
        if (Tools::substr($alias, 0, 1) === '!') {
            $encrypted = Tools::substr($alias, 1);
            return $this->getCipherTool()->decrypt($encrypted);
        }
        return $alias;
    }

    protected function initCipherTool()
    {
        if (!Configuration::get('PS_CIPHER_ALGORITHM') || !defined('_RIJNDAEL_KEY_')) {
            return new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
        }
        return new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
    }

    protected function getCipherTool()
    {
        if ($this->cipher_tool === null) {
            $this->cipher_tool = $this->initCipherTool();
        }
        return  $this->cipher_tool;
    }

    protected function getTLSVersion()
    {
        if (function_exists('curl_init') &&  $ch = curl_init()) {
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER =>  false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_URL => $this->check_tls_api,
                CURLOPT_TIMEOUT => 10
            );
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            if ($result) {
                $decoded = Tools::jsonDecode($result, true);
                if ($decoded && is_array($decoded) && isset($decoded['tls_version'])) {
                    return ltrim($decoded['tls_version'], 'A..Z ');
                }
            }
            curl_close($ch);
        }
        return null;
    }

    public function skipAliasPaymentConfirmation()
    {
        return (bool)Configuration::get('OGONE_SKIP_AC');
    }

    public function makeImmediateAliasPayment()
    {
        return (bool)Configuration::get('OGONE_MAKE_IP');
    }

    public function useFraudScoring()
    {
        return (bool)Configuration::get('OGONE_DISPLAY_FRAUD_SCORING');
    }

    public function getFraudScoring($order)
    {
        if (!$this->useFraudScoring()) {
            return null;
        }
        $last_transaction = OgoneTransactionLog::getLastByCartId($order->id_cart);
        if (!$last_transaction || !is_array($last_transaction)) {
            return null;
        }
        try {
            $data =  array('PAYID' => $last_transaction['payid']);
            $result = $this->getDirectLinkInstance()->query($data);
            if ($result && is_array($result) && isset($result['SCO_CATEGORY']) && isset($result['SCORING'])) {
                return array(
                    'category' => $result['SCO_CATEGORY'],
                    'score' => (int)$result['SCORING']
                );
            }
        } catch (Exception $ex) {
            return array(
                    'category' => 'X',
                    'score' =>  $ex->getMessage()
            );
        }
    }

    public function convertArrayToReadableString(array $array, $glue = '<br />', $pattern = '%s : %s')
    {
        $callback = function ($k, $v) use ($pattern) {
            return sprintf($pattern, $k, $v);
        };
        return implode($glue, array_map($callback, array_keys($array), array_values($array)));
    }


    public function getOrderSlipsAmount($id_order)
    {
        $query  = 'SELECT SUM(amount + shipping_cost_amount) FROM `'._DB_PREFIX_.'order_slip` WHERE id_order = ' . (int)$id_order;
        return Db::getInstance()->getValue($query);
    }



    public function getCaptureTransactionsAmount($id_order)
    {
        $amount = 0;
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $order = new Order($id_order);
            $transactions = OgoneTransactionLog::getTransactionsByCartIdAndStatus($order->id_cart, array(DirectLink::STATUS_CAPTURED));
        } else {
            $transactions = OgoneTransactionLog::getTransactionsByOrderIdAndStatus($id_order, array(DirectLink::STATUS_CAPTURED));
        }

        foreach ($transactions as $transaction) {
            $response = $transaction['response'];
            if ($response && $response['AMOUNT'] && empty($response['NCERROR'])) {
                $amount+=(float)$response['AMOUNT'];
            }
        }
        return $amount;
    }

    public function getPendingCaptureTransactionsAmount($id_order)
    {
        $amount = 0;
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $order = new Order($id_order);
            $transactions = OgoneTransactionLog::getTransactionsByCartIdAndStatus($order->id_cart, array(DirectLink::STATUS_PAYMENT_PROCESSING));
        } else {
            $transactions = OgoneTransactionLog::getTransactionsByOrderIdAndStatus($id_order, array(DirectLink::STATUS_PAYMENT_PROCESSING));
        }
        foreach ($transactions as $transaction) {
            $response = $transaction['response'];
            if ($response && $response['AMOUNT'] && empty($response['NCERROR'])) {
                $amount+=(float)$response['AMOUNT'];
            }
        }
        return $transactions ? max($amount - $this->getCaptureTransactionsAmount($id_order), 0) : 0;
    }

    public function getCaptureMaxAmount($id_order)
    {
        $order = new Order($id_order);
        $captured_pending = $this->getPendingCaptureTransactionsAmount($id_order);
        $captured = $this->getCaptureTransactionsAmount($id_order);
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            return max(0, min($order->total_paid, $order->total_paid - $captured - $captured_pending));
        } else {
            return max(0, min($order->total_paid, $order->total_paid - $order->total_paid_real - $captured_pending));
        }
    }

    public function getRefundTransactionsAmount($id_order)
    {
        $amount = 0;
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $order = new Order($id_order);
            $transactions = OgoneTransactionLog::getTransactionsByCartIdAndStatus($order->id_cart, array(DirectLink::STATUS_REFUND));
        } else {
            $transactions = OgoneTransactionLog::getTransactionsByOrderIdAndStatus($id_order, array(DirectLink::STATUS_REFUND));
        }
        foreach ($transactions as $transaction) {
            $response = $transaction['response'];
            if ($response && $response['AMOUNT'] && empty($response['NCERROR'])) {
                $amount+=(float)$response['AMOUNT'] ;
            }
        }
        return $amount;
    }

    public function getPendingRefundTransactionsAmount($id_order)
    {
        $amount = 0;
        if (version_compare(_PS_VERSION_, '1.5', 'lt')) {
            $order = new Order($id_order);
            $transactions = OgoneTransactionLog::getTransactionsByCartIdAndStatus($order->id_cart, array(DirectLink::STATUS_REFUND_PENDING));
        } else {
            $transactions = OgoneTransactionLog::getTransactionsByOrderIdAndStatus($id_order, array(DirectLink::STATUS_REFUND_PENDING));
        }
        foreach ($transactions as $transaction) {
            $response = $transaction['response'];
            if ($response && $response['AMOUNT'] && empty($response['NCERROR'])) {
                $amount+=(float)$response['AMOUNT'] ;
            }
        }        return $transactions ? max($amount - $this->getRefundTransactionsAmount($id_order), 0) : 0;
    }

    public function getRefundMaxAmount($id_order)
    {
        $order = new Order($id_order);
        $refunded = $this->getRefundTransactionsAmount($id_order);
        $refunded_pending = $this->getPendingRefundTransactionsAmount($id_order);
        return max(0, min($order->total_paid_real - $refunded - $refunded_pending, $order->total_paid_real));
    }

    public function getShaOutVariablesFromGet()
    {
        $data = array_change_key_case($_GET, CASE_UPPER); // impossible to use Tools::getValue because exact case can vary
        return array_intersect_key($data, array_combine($this->sha_out_fields, $this->sha_out_fields));
    }

    public function setDLProcessingToken($id_cart)
    {
        $token = new OgoneTransactionLog();
        $token->id_cart = $id_cart;
        $token->response= 'PROCESSING_TOKEN';
        return $token->save();
    }

    public function issetDLProcessingToken($id_cart)
    {
        $query = 'SELECT * FROM `'._DB_PREFIX_.'ogone_tl` WHERE id_cart = ' . (int)$id_cart . ' AND response="PROCESSING_TOKEN"';
        return count(Db::getInstance()->executeS($query));
    }

    public function deleteDLProcessingToken($id_cart)
    {
        $query = 'DELETE FROM `'._DB_PREFIX_.'ogone_tl` WHERE id_cart = ' . (int)$id_cart . ' AND response="PROCESSING_TOKEN"';
        return count(Db::getInstance()->execute($query));
    }

    public function getHTPPaymentMethodName($type)
    {
        switch (Tools::strtolower($type)) {
            case 'creditcard':
                return $this->l('Credit Card');
            case 'directdebits de':
                return $this->l('Direct Debits DE');
            case 'directdebits at':
                return $this->l('Direct Debits AT');
            case 'directdebits nl':
                return $this->l('Direct Debits NL');
            default:
                return '';
        }
    }

    public function verifyShaSignatureFromGet()
    {
        $ogone_params = array();
        $ignore_key_list = $this->getIgnoreKeyList();
        foreach ($_GET as $key => $value) {
            if (Tools::strtoupper($key) != 'SHASIGN' && $value != '' && !in_array($key, $ignore_key_list)) {
                $key = Tools::strtoupper($key);
                if (Tools::substr($key, 0, 5) == 'CARD_' || Tools::substr($key, 0, 6) == 'ALIAS_') {
                    $key = str_replace('_', '.', $key);
                }
                if (!in_array($key, $ignore_key_list)) {
                    $ogone_params[$key] = $value;
                }
            }
        }

        $sha_sign = $this->calculateShaSign($ogone_params, Configuration::get('OGONE_SHA_OUT'));
        return Tools::getValue('SHASign') && $sha_sign && Tools::getValue('SHASign') == $sha_sign;

    }


    public function getAliasReturnVariables()
    {
        $raw_data = $_GET; // cannot use Tools::getValue
        $raw_data = array_change_key_case($raw_data, CASE_UPPER);
        $data = array();
        $data['ALIAS'] = isset($raw_data['ALIAS_ALIASID']) ? $raw_data['ALIAS_ALIASID'] : null;
        $data['CARDNO'] = isset($raw_data['CARD_CARDNUMBER']) ? $raw_data['CARD_CARDNUMBER'] : null;
        $data['CN'] = isset($raw_data['CARD_CARDHOLDERNAME']) ? $raw_data['CARD_CARDHOLDERNAME'] : null;
        $data['ED'] = isset($raw_data['CARD_EXPIRYDATE']) ? $raw_data['CARD_EXPIRYDATE'] : null;
        $data['BRAND'] = isset($raw_data['CARD_BRAND']) ? $raw_data['CARD_BRAND'] : null;
        $data['NCERROR'] = isset($raw_data['ALIAS_NCERROR']) ? $raw_data['ALIAS_NCERROR'] : null;
        $data['STATUS'] = isset($raw_data['ALIAS_STATUS']) ? $raw_data['ALIAS_STATUS'] : null;
        if (isset($raw_data['ALIAS_STOREPERMANENTLY'])) {
            $data['STOREPERMANENTLY'] = $raw_data['ALIAS_STOREPERMANENTLY'];
        }
        $data['SHASIGN'] = isset($raw_data['SHASIGN']) ? $raw_data['SHASIGN'] : null;
        return $data;
    }
}
