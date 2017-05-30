<?php
/**
 * Page Cache powered by Jpresta (jpresta . com)
 *
 *    @author    Jpresta
 *    @copyright Jpresta
 *    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
 *               is permitted for one Prestashop instance only but you can install it on your test instances.
 */

/*
*
*/
function upgrade_module_3_00($module)
{
    // By default upgraded stores are installed and up and running
    Configuration::updateValue('pagecache_install_step', 6);

    // By default, disallow infos box
    Configuration::updateValue('pagecache_allow_infosbox', 0);

    if (Configuration::get('pagecache_debug') || Configuration::get('pagecache_show_stats')) {
        // Except if debug mode is enabled or if infos box is displayed, in this case we are in step 2
        Configuration::updateValue('pagecache_install_step', 2);

        // In this case, allow infos box
        Configuration::updateValue('pagecache_allow_infosbox', 1);
    }

    // If no Javascript is defined then set default one
    $js = trim(Configuration::get('pagecache_cfgadvancedjs'));
    if (empty($js)) {
        $v2DefaultValue = <<<EOT
$.ajax({ type: 'POST', headers: { "cache-control": "no-cache"}, url: baseUri + '?rand=' + new Date().getTime(), async: true, cache: false, dataType: "json", data: 'controller=cart&ajax=true&token=' + static_token,
	success: function (jsonData) { ajaxCart.updateCart(jsonData);}
});
var cart_block = new HoverWatcher('#cart_block');
var shopping_cart = new HoverWatcher('#shopping_cart');
$("#shopping_cart a:first").hover(
	function() {
		$(this).css('border-radius', '3px 3px 0px 0px');
		if (ajaxCart.nb_total_products > 0)
			$("#cart_block").stop(true, true).slideDown(450);
	},
	function() {
		$('#shopping_cart a').css('border-radius', '3px');
		setTimeout(function() {
			if (!shopping_cart.isHoveringOver() && !cart_block.isHoveringOver())
				$("#cart_block").stop(true, true).slideUp(450);
		}, 200);
	}
);
EOT;
        Configuration::updateValue('pagecache_cfgadvancedjs', $v2DefaultValue);
    }

    // Delete old variables
    Configuration::deleteByName('pagecache_show_stats');

    // Be sure new javascript and CSS are read
    if (method_exists('Media','clearCache')) {
        Media::clearCache();
    }

    // Clear cache because templates have changed
    $module->clearCache();

    return true;
}
