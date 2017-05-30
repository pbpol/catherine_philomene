{*
* Page Cache powered by Jpresta (jpresta . com)
*
*    @author    Jpresta
*    @copyright Jpresta
*    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
*               is permitted for one Prestashop instance only but you can install it on your test instances.
*}
<script type="text/javascript">
	processDynamicModules = function(dyndatas) {
		for (var key in dyndatas) {
			var tokens = key.split('|');
			if (tokens>1) {
				var domNode = $(dyndatas[tokens[0]]).filter('#'+tokens[1]);
				if (domNode.length) {
					$('#pc_'+tokens[0]+' #'+tokens[1]).replaceWith(domNode.html());
				}
				else {
					$('#pc_'+tokens[0]).replaceWith(dyndatas[tokens[0]]);
				}
			}
			else if (key=='js') {
				$('body').append(dyndatas[key]);
			}
			else {
				$('#pc_'+key).replaceWith(dyndatas[key]);
			}
		}
		{if isset($cfgadvancedjs)}
			{$cfgadvancedjs|escape:'javascript_that_cannot_be_escaped':'UTF-8'}
		{/if}
	};
</script>
