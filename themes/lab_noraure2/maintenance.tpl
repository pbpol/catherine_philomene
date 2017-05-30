{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!DOCTYPE html>
<html lang="{$language_code|escape:'html':'UTF-8'}">
<head>
	<meta charset="utf-8">
	<title>{$meta_title|escape:'html':'UTF-8'}</title>
{if isset($meta_description)}
	<meta name="description" content="{$meta_description|escape:'html':'UTF-8'}">
{/if}
{if isset($meta_keywords)}
	<meta name="keywords" content="{$meta_keywords|escape:'html':'UTF-8'}">
{/if}
	<meta name="robots" content="{if isset($nobots)}no{/if}index,follow">
	<link rel="shortcut icon" href="{$favicon_url}">
       	<link href="{$css_dir}maintenance.css" rel="stylesheet">
       	<link href='//fonts.googleapis.com/css?family=Open+Sans:600' rel='stylesheet'>
	{literal}
		<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		ga('create', 'UA-79559862-1', 'auto');
		ga('send', 'pageview');
		
		</script>
	{/literal}
</head>
<body>
    	<div class="container">
			<div id="maintenance">
				<div class="logo"><img src="{$logo_url}" {if $logo_image_width}width="{$logo_image_width}"{/if} {if $logo_image_height}height="{$logo_image_height}"{/if} alt="Catherine-Philomene.com : Bijoux Anciens et Vintage" /></div>
	        		{$HOOK_MAINTENANCE}
	        		<div id="message">
	             			<h1 class="maintenance-heading">{l s='We\'ll be back soon.'}</h1>
							{l s='We are currently updating our shop and will be back really soon.'}
							<br /><br />
							{l s='text 1'}
							<br /><br />
							{l s='text 2'}
							<br /><br />
							{l s='text 3'}
							<br /><br />
							{l s='text 4'}
							<br /><br />
							{l s='text 5'}
							<br /><br />
							{l s='text 6'}
							<br /><br />
							{l s='text 7'}
							<br /><br />
							{l s='text 8'}
							<br /><br />
							{l s='text 9'}
							<br /><br />
							{l s='text 10'}
							<br /><br />
							{l s='text 11'}
							<br /><br />
							{l s='text 12'}
							<br /><br />
							{l s='text 13'}
							<br /><br />
							{l s='Thanks for your patience.'}
					</div>
				</div>
	        </div>
		</div>
</body>
</html>
