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
<!DOCTYPE HTML>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8 ie7"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9 ie8"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<!--[if gt IE 8]> <html class="no-js ie9"{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}><![endif]-->
<html{if isset($language_code) && $language_code} lang="{$language_code|escape:'html':'UTF-8'}"{/if}>
	<head>
		<meta charset="utf-8" />
		<title>{$meta_title|replace:' - Catherine-Philomene.com':''|escape:'html':'UTF-8'}</title>
		{if isset($meta_description) AND $meta_description}
			<meta name="description" content="{$meta_description|escape:'html':'UTF-8'}" />
		{/if}
		{if isset($meta_keywords) AND $meta_keywords}
			<meta name="keywords" content="{$meta_keywords|escape:'html':'UTF-8'}" />
		{/if}
		<meta name="generator" content="PrestaShop" />
		<meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />
		<meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=1.6, initial-scale=1.0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="{$favicon_url}?{$img_update_time}" />
		<link rel="shortcut icon" type="image/x-icon" href="{$favicon_url}?{$img_update_time}" />
		<link href='https://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Roboto:400,400italic,500,700,500italic' rel='stylesheet' type='text/css'>
		<link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700">
		<link href='{$css_dir}font-awesome/font-awesome.css' rel='stylesheet' type='text/css'>
		<link href='{$css_dir}font-awesome/font-awesome-ie7.css' rel='stylesheet' type='text/css'>
		{if isset($css_files)}
			{foreach from=$css_files key=css_uri item=media}
				<link rel="stylesheet" href="{$css_uri|escape:'html':'UTF-8'}" type="text/css" media="{$media|escape:'html':'UTF-8'}" />
			{/foreach}
		{/if}

		{if isset($js_defer) && !$js_defer && isset($js_files) && isset($js_def)}
			{$js_def}
			{foreach from=$js_files item=js_uri}
				<script type="text/javascript" src="{$js_uri|escape:'html':'UTF-8'}"></script>

			{/foreach}
		{/if}
		{$HOOK_HEADER}
		<link href="{$tpl_uri|escape:'html':'UTF-8'}css/responsive.css" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" href="http{if Tools::usingSecureMode()}s{/if}://fonts.googleapis.com/css?family=Open+Sans:300,600&amp;subset=latin,latin-ext" type="text/css" media="all" />
		<!--[if IE 8]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
		{literal}

		<!--
		<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		ga('create', 'UA-79559862-1', 'auto');
		ga('send', 'pageview');
		
		</script>-->
		{/literal}
	</head>
	<body{if isset($page_name)} id="{$page_name|escape:'html':'UTF-8'}"{/if} class="{if isset($page_name)}{$page_name|escape:'html':'UTF-8'}{/if}{if isset($body_classes) && $body_classes|@count} {implode value=$body_classes separator=' '}{/if}{if $hide_left_column} hide-left-column{else} show-left-column{/if}{if $hide_right_column} hide-right-column{else} hide-right-column{/if}{if isset($content_only) && $content_only} content_only{/if} lang_{$lang_iso}" itemtype="http://schema.org/WebPage" itemscope="">
	{hook h ='displayAfterBody'}
	{if !isset($content_only) || !$content_only}
		{if isset($restricted_country_mode) && $restricted_country_mode}
			<div id="restricted-country">
				<p>{l s='You cannot place a new order from your country.'}{if isset($geolocation_country) && $geolocation_country} <span class="bold">{$geolocation_country|escape:'html':'UTF-8'}</span>{/if}</p>
			</div>
		{/if}
		<div id="page">
			<div class="header-container">
				<header id="header">
						<div class="labheader">
							<div class="container">
								<div class="lab_logo">
									<h1 class="logo_container">
										<a href="{if $force_ssl}{$base_dir_ssl}{else}{$base_dir}{/if}" title="{$shop_name|escape:'html':'UTF-8'}">
											<img class="logo" src="{$logo_url}" alt="Catherine-Philomene.com : Bijoux Anciens et Vintage"{if isset($logo_image_width) && $logo_image_width} width="{$logo_image_width}"{/if}{if isset($logo_image_height) && $logo_image_height} height="{$logo_image_height}"{/if}/>
										</a>
									</h1>
								</div>
								<div class="header-right">
									
									<div class="header-right-bot">
										<div class="labmegamenu ">
											<div class="container">
												<div class="menu-wrapper row">
													{hook h="megamenu"}
												</div>
											</div>
										</div>
										<div class="lab_right">
											{if isset($HOOK_TOP)}{$HOOK_TOP}{/if}
										</div>
									</div>
								</div>
						</div>

					</div>
				</header>
			</div>
		
			{if $page_name == 'index'}
				{if Hook::exec('bannerSlide')}
					<div class="lab_bannerSlide">
						{hook h="bannerSlide"}
					</div>
				{/if}
			{/if}
			{if $page_name == 'index'}
				{if Hook::exec('blockPosition1')}
					<div onclick="location.href='https://catherine-philomene.com/bagues-anciennes.html'" class="blockPosition1 blockPosition">
						<div class="container">
							{hook h="blockPosition1"}
						</div>
					</div>
				{/if}
			{/if}
			{if $page_name == 'index'}
				{if Hook::exec('blockPosition2')}
					<div class="blockPosition2 blockPosition">
							{hook h="blockPosition2"}
					</div>
				{/if}
			{/if}


			{if $page_name == 'index'}
				{if Hook::exec('blockProducttab')}
					<div class="blockPosition2 blockPosition">
						<div class="container">
							{hook h="blockProducttab"}
						</div>
					</div>
				{/if}
			{/if}
			{if $page_name == 'index'}
				{if Hook::exec(smartBlognew)}
					<div class="smartBlog">
						<div class="container">
							{hook h="smartBlognew"}
						</div>
					</div>
				{/if}
			{/if}
		{if $page_name == 'index'}
					{if Hook::exec('blockPosition3')}
						<div class="blockPosition3 blockPosition">
							<div class="container">
								{hook h="blockPosition3"}
							</div>
						</div>
					{/if}
				{/if}
			{if $page_name == 'index'}
				{if Hook::exec('blockPosition4')}
					<div class="blockPosition4 blockPosition">
						<div class="container">
							{hook h="blockPosition4"}
						</div>
					</div>
				{/if}
			{/if}

		


{if $page_name =='category' || $page_name =='product'}
				{if $category->id_image}
					<div class="content_scene_cat">
						<!-- Category image -->
						<div class="content_scene_cat_bg"{if $category->id_image} style="background:url({$link->getCatImageLink($category->link_rewrite, $category->id_image, 'category_default')|escape:'html':'UTF-8'}) no-repeat scroll center center/cover;"{/if}>
							{if $category->name}
								<h4 class="category-name">
									<span>
										{strip}
											{$category->name|escape:'html':'UTF-8'}
											{if isset($categoryNameComplement)}
												{$categoryNameComplement|escape:'html':'UTF-8'}
											{/if}
										{/strip}
									</span>
								</h4>
							{/if}
							{if $page_name !='index' && $page_name !='pagenotfound'}
								<div class="lab_breadcrumb">
									{include file="$tpl_dir./breadcrumb.tpl"}
								</div>
							{/if}
						 </div>
					</div>
					{if $category->description}
						<div class="category-description">
							{strip}
								{$category->description}
							{/strip}
						</div>
					{/if}
				{else}
					<div class="content_scene_cat">
						<!-- Category image -->
						<div class="content_scene_cat_bg content_image_cat">
							{if $category->name}
								<h4 class="category-name">
									<span>
										{strip}
											{$category->name|escape:'html':'UTF-8'}
											{if isset($categoryNameComplement)}
												{$categoryNameComplement|escape:'html':'UTF-8'}
											{/if}
										{/strip}
									</span>
								</h4>
							{/if}
							{if $page_name !='index' && $page_name !='pagenotfound'}
								<div class="lab_breadcrumb">
									{include file="$tpl_dir./breadcrumb.tpl"}
								</div>
							{/if}
						</div>
					</div>
					{if $category->description}
						<div class="category-description">
							{strip}
								{$category->description}
							{/strip}
						</div>
					{/if}
				{/if}
			{else}
			
				{if $page_name !='index' && $page_name !='pagenotfound'}
				<!--<div class="wrap_breadcrumb" {if $page_name =='cms'}style="color:{$cms->id};"{/if}>-->
				<div class="wrap_breadcrumb">
					<div class="labBreadcrumb">
						<div class="container">
							{include file="$tpl_dir./breadcrumb.tpl"}
						</div>
					</div>
				</div>
				{/if}
				
			{/if}
			<div class="columns-container">
				<div id="columns" class="container">
					<div class="row">
						{if isset($left_column_size) && !empty($left_column_size)}
							<div id="left_column" class="column col-xs-12 col-sm-{$left_column_size|intval}">{$HOOK_LEFT_COLUMN}</div>
						{/if}
						{if isset($left_column_size) && isset($right_column_size)}{assign var='cols' value=(12 - $left_column_size - $right_column_size)}{else}{assign var='cols' value=12}{/if}
						<div id="center_column" class="center_column col-xs-12 col-sm-{$cols|intval}">
	{/if}
