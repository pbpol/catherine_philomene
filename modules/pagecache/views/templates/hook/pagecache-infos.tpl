{*
* Page Cache powered by Jpresta (jpresta . com)
*
*    @author    Jpresta
*    @copyright Jpresta
*    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
*               is permitted for one Prestashop instance only but you can install it on your test instances.
*}
<table id="pagecache_stats">
	<caption>
		<img
			style="float: left; display: inline; width: 24px; height: 24px; margin-right: 5px" src="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'html':'UTF-8'}{else}{$base_dir|escape:'html':'UTF-8'}{/if}/modules/pagecache/logo.png"
			alt="" width="24" height="24"/>
		{l s='PAGE CACHE - INFOS' mod='pagecache'}
		<div class="actions">
			<a href="{$url_on_off|escape:'htmlall':'UTF-8'}" class="pagecache" title="{if $dbgpagecache eq '1'}{l s='Click to disable cache' mod='pagecache'}{else}{l s='Click to enable cache' mod='pagecache'}{/if}"><img src="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'html':'UTF-8'}{else}{$base_dir|escape:'html':'UTF-8'}{/if}/modules/pagecache/views/img/{if $dbgpagecache eq '1'}on{else}off{/if}.png" alt="" width="32" height="32/"></a>
			<a href="{$url_reload|escape:'htmlall':'UTF-8'}" class="pagecache" title="{l s='Reload this page, use browser cache if any' mod='pagecache'}"><img src="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'html':'UTF-8'}{else}{$base_dir|escape:'html':'UTF-8'}{/if}/modules/pagecache/views/img/reload.png" alt="" width="32" height="32/"></a>
			<a href="{$url_del|escape:'htmlall':'UTF-8'}" class="pagecache" title="{l s='Force this page to be generated (refresh cache)' mod='pagecache'}"><img src="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'html':'UTF-8'}{else}{$base_dir|escape:'html':'UTF-8'}{/if}/modules/pagecache/views/img/trash.png" alt="" width="32" height="32/"></a>
			<a href="#" class="pagecache" title="{l s='Display more technical informations' mod='pagecache'}" onclick="$('.adv').show();$('#pagecache_stats').css('width', 'inherit');return false;"><img src="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'html':'UTF-8'}{else}{$base_dir|escape:'html':'UTF-8'}{/if}/modules/pagecache/views/img/plus.png" alt="" width="32" height="32/"></a>
			<a href="{$url_close|escape:'htmlall':'UTF-8'}" class="pagecache" title="{l s='Close this infos box' mod='pagecache'}"><img src="{if isset($force_ssl) && $force_ssl}{$base_dir_ssl|escape:'html':'UTF-8'}{else}{$base_dir|escape:'html':'UTF-8'}{/if}/modules/pagecache/views/img/close.png" alt="" width="32" height="32/"></a>
		</div>
	</caption>
	<tbody>
		<tr>
			<td>{l s='Page generation' mod='pagecache'}</td>
			<td id="pc_speed">
				<span class="pctype0 pctype1 pctype3" style="display: none">{$speed|escape:'html':'UTF-8'}</span>
				<span class="pctype2" style="display: none">0 ms</span>
			</td>
		</tr>
		<tr>
			<td>{l s='Cache type' mod='pagecache'}</td>
			<td id="pc_type">
				<b class="pctype0" style="display: none">{l s='no cache available :-(' mod='pagecache'}</b>
				<b class="pctype1" style="display: none">{l s='server cache :-)' mod='pagecache'}</b>
				<b class="pctype2" style="display: none">{l s='browser cache :-D (press F5 to refresh)' mod='pagecache'}</b>
				<b class="pctype3" style="display: none">{l s='cache disabled' mod='pagecache'}</b>
			</td>
		</tr>
		<tr>
			<td>{l s='Can be cached' mod='pagecache'}</td>
			<td id="pc_cacheable">{$cacheable|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="pctype1" style="display: none">
			<td>{l s='Cache age' mod='pagecache'}</td>
			<td id="pc_age">{$age|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Server cache timeout' mod='pagecache'}</td>
			<td id="pc_timeout_server">{$timeout_server|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Browser cache duration' mod='pagecache'}</td>
			<td id="pc_timeout_browser">{$timeout_browser|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Controller' mod='pagecache'}</td>
			<td id="pc_controller">{$controller|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Currency' mod='pagecache'}</td>
			<td id="pc_cur">{$currency|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Country' mod='pagecache'}</td>
			<td id="pc_country">{$country|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Country' mod='pagecache'}</td>
			<td id="pc_country">{$country2|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='Cache file' mod='pagecache'}</td>
			<td id="pc_file">{$file|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='Pseudo URL' mod='pagecache'}</td>
			<td id="pc_pseudo_url">{$pseudo_url|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='Pseudo URL (after)' mod='pagecache'}</td>
			<td id="pc_pseudo_url_after">{$pseudo_url_after|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='Cache exists' mod='pagecache'}</td>
			<td id="pc_exists">{$exists|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Groups' mod='pagecache'}</td>
			<td id="pc_groups">{$groups|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='Groups in cookie' mod='pagecache'}</td>
			<td id="pc_groups">{$cookie_groups|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='Default group in cookie' mod='pagecache'}</td>
			<td id="pc_default_group">{$cookie_group_default|escape:'html':'UTF-8'}</td>
		</tr>
		<tr class="adv">
			<td>{l s='From cache' mod='pagecache'}</td>
			<td id="pc_from_cache">{$from_cache|escape:'html':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Performances' mod='pagecache'}</td>
			<td id="pc_perfs">{l s='Server cache used' mod='pagecache'} <b style="color: green">{$hit|escape:'html':'UTF-8'}</b> {l s='times and built' mod='pagecache'} <b style="color: red">{$missed|escape:'html':'UTF-8'}</b> {l s='times' mod='pagecache'} =&gt; {$perfs|escape:'html':'UTF-8'}</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
$(document).ready(function() {
    try {
        if (false || isNaN(parseInt($.cookie("pc_type_{$pagehash|escape:'html':'UTF-8'}")))) {
            pctype = 0;
        } else if (typeof $.cookie == 'undefined') {
            pctype = 1;
        } else {
            pctype = $.cookie("pc_type_{$pagehash|escape:'html':'UTF-8'}");
        }
        if (typeof pctype == 'undefined' || pctype == null) {
            pctype = 2;
        }
        $('.pctype' + pctype).show();
        var date = new Date();
        var minutes = 60;
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        if (pctype != 2) $.cookie("pc_type_{$pagehash|escape:'html':'UTF-8'}", 2, {
            expires: date,
            path: '/'
        });
    } catch (err) {
        console.warn("Cannot treat PageCache infos box: " + err.message, err);
    }
});
</script>
