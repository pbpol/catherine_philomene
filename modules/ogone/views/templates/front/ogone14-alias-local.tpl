{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
    <p class="payment_module local-alias" onclick="document.forms['ogone_form_a{$alias_data.id_ogone_alias|escape:'htmlall':'UTF-8'}'].submit(); return false;" >
      <a class="ogone alias"  href="#" title="{l s='Pay with Ingenico ePayments' mod='ogone'}">
        <img src="{$alias_data.logo|escape:'htmlall':'UTF-8'}"/>
          <span class="card-item">
            <!-- img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/visa.png" //-->
            <span class="left-card">
              <span class="card-name">{$alias_data.cn|escape:'htmlall':'UTF-8'}</span>
              <span class="card-number">{$alias_data.cardno|escape:'htmlall':'UTF-8'}</span>
            </span>
            <span class="right-card">
              <span class="card-expiration">{l s='Expiration date' mod='ogone'}</span>
              <span class="card-date">{$expiry_date|escape:'htmlall':'UTF-8'}</span>
            </span>
          </span>
          {if isset($immediate_payment) && $immediate_payment}
             <span class="ogone_immediate_payment"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/info_ogone.png"/> {l s='Immediate payment' mod='ogone' }</span>
          {/if}
      </a>
    </p>

  <form name="ogone_form_a{$alias_data.id_ogone_alias|escape:'htmlall':'UTF-8'}" action="{$local_alias_link|escape:'html'}" method="post">
    <input type="hidden" name="id_alias" value="{$alias_data.id_ogone_alias|escape:'htmlall':'UTF-8'}" />
  </form>
