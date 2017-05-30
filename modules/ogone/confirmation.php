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
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/ogone.php';

/* PrestaShop < 1.5 */
if (_PS_VERSION_ < '1.5') {
    include dirname(__FILE__) . '/../../header.php';

    $ogone = new Ogone();

    $ogone->log('confirmation.php called');

    $id_module = $ogone->id;
    $id_cart = $ogone->extractCartId(Tools::getValue('orderID'));
    $key = Db::getInstance()->getValue(
        'SELECT secure_key FROM ' . _DB_PREFIX_ . 'customer WHERE id_customer = ' .
        (int) Context::getContext()->cookie->id_customer
    );
    $operation = Configuration::get('OGONE_OPERATION') ? Configuration::get('OGONE_OPERATION') : Ogone::OPERATION_SALE;
    $tpl_vars = array(
        'id_module' => $id_module,
        'id_cart' => $id_cart,
        'key' => $key,
        'ogone_link' => __PS_BASE_URI__ . 'order-confirmation.php',
        'operation' => $operation,
        'order_id' => Tools::getValue('orderID'),
    );

    $ogone->log($tpl_vars);

    Context::getContext()->smarty->assign($tpl_vars);

    echo $ogone->display(dirname(__FILE__), '/views/templates/front/waiting.tpl');

    include dirname(__FILE__) . '/../../footer.php';
} else {

    $url = __PS_BASE_URI__ . 'index.php?fc=module&module=ogone&controller=confirmation&';

    Tools::redirect($url . http_build_query($_GET));
}
