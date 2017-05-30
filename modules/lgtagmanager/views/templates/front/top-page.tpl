{*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*}

<script type="text/javascript">
	{if $isOrder}
	dataLayer = [{
		'transactionId': '{$trans.id|escape:'javascript':'UTF-8'}',
		'transactionAffiliation': '{$trans.store|escape:'javascript':'UTF-8'}',
		'transactionTotal': {$trans.total|escape:'javascript':'UTF-8'},
		'transactionTax': {$trans.tax|escape:'javascript':'UTF-8'},
		'transactionShipping': {$trans.shipping|escape:'javascript':'UTF-8'},
		{if isset($items) && count($items) > 0}
		'transactionProducts': [{foreach from=$items item=item name=products}{ldelim}
			'sku': '{$item.SKU|escape:'javascript':'UTF-8'}',
			'name': '{$item.Product|escape:'javascript':'UTF-8'}',
			'category': '{$item.Category|escape:'javascript':'UTF-8'}',
			'price': {$item.Price|escape:'javascript':'UTF-8'},
			'quantity': {$item.Quantity|escape:'javascript':'UTF-8'}
			{rdelim}{if !$smarty.foreach.productos.last},{/if}{/foreach}]
		{/if}
	}];
	{else}
	dataLayer = [{}];
	{/if}
</script>
