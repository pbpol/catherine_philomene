{*
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
*}

{if $etransReason == 'cancel'}
<div class="etrans_error" style="margin-left:15px;">
	{l s='Payment canceled.' mod='etransactions'}
</div>
{/if}

{if $etransReason == 'error'}
<div class="etrans_error" style="margin-left:15px;">
	{l s='Payment refused by PaymentPlatform.' mod='etransactions'}
</div>
{/if}

{if !$etransProduction}
<div class="etrans_error" style="margin-left:15px;">
	{l s='The PaymentPlatform payment is in test mode.' mod='etransactions'}
</div>

{/if}
{* Standard payment *}
{foreach from=$etransCards item=card name=cards}
<p class="payment_module etrans_module">
	<a href="{$card.url|escape:'html'}">
		<img src="{$card.image}" alt="{$card.card}" title="{$card.card}" />
		{l s='Pay by' mod='etransactions'} {$card.label}
	</a>
</p>
{/foreach}

{* Recurring payment *}
{if !empty($etransRecurring)}
<p class="payment_module etrans_module">
	<img src="{$etransImagePath}Paiement_3X.png" />
	{foreach from=$etransRecurring item=card name=cards}
		<a href="{$card.url|escape:'html'}&amp;recurring=1">
			<img src="{$card.image}" alt="{$card.card}" title="{$card.card}" />
		</a>
	{/foreach}
	{l s='Pay by' mod='etransactions'} {l s='card 3 times without fees' mod='etransactions'}
</p>
{/if}
