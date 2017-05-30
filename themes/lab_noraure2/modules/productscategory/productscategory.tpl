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
{if count($categoryProducts) > 0 && $categoryProducts !== false}
<section class="page-product-box blockproductscategory lablistproducts">
	<div class="title_block">
		<h4>
			<span>{l s='Related Products' mod='productscategory'}</span>
		</h4>
	</div>
	<div class="block_content row">
	<div id="productscategory_list">
		{foreach from=$categoryProducts item='product' name=myLoop}
			{if $smarty.foreach.myLoop.index % 1 == 0 || $smarty.foreach.myLoop.first }
				<div class="item-inner wow fadeInUp " data-wow-delay="{$smarty.foreach.myLoop.iteration}00ms" >
				{/if}
					<div class="item">
						<div class="topItem">
							<div class="lab-img">
								{if Hook::exec('rotatorImg')}
									{hook h ='rotatorImg' product=$product}
								{else}
									<a class = "product_image" href="{$product.link|escape:'html'}" title="{$product.name|escape:html:'UTF-8'}">
										<img class="img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="{$product.name|escape:html:'UTF-8'}" />							
									</a>
								{/if}
								{if isset($product.new) && $product.new == 1}
								<a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
									<span class="new-label">{l s='New' mod='labhomefeatured'}</span>
								</a>
								{/if}
								{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
									<a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
										<span class="sale-label">{l s='Sale!' mod='labhomefeatured'}</span>
									</a>
								{/if}
							</div>
						</div>	
							<!-- <div class="actions">
								<ul class="add-to-links">				
									<li class="lab-Wishlist">
										<a onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', $('#idCombination').val(), 1,'tabproduct'); return false;" class="add-wishlist wishlist_button" href="#"
										data-id-product="{$product.id_product|intval}"
										title="{l s='Add to Wishlist' mod='labhomefeatured'}">
										<i class="icon-heart"></i></a>
									</li>
									{if isset($comparator_max_item) && $comparator_max_item}
										<li class="lab-compare">	
											<a class="add_to_compare" 
												href="{$product.link|escape:'html':'UTF-8'}" 
												data-product-name="{$product.name|escape:'htmlall':'UTF-8'}"
												data-product-cover="{$link->getImageLink($product.link_rewrite, $product.id_image, 'cart_default')|escape:'html'}"
												data-id-product="{$product.id_product}"
												title="{l s='Add to Compare' mod='labhomefeatured'}">
												<i class="icon-retweet"></i>
											</a>
										</li>
									{/if}
									{if isset($quick_view) && $quick_view}
										<li class="lab-quick-view">
											<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}"
												data-id-product="{$product.id_product|intval}"
												title="{l s='Quick view' mod='labhomefeatured'}">
												<i class="icon-eye-open"></i>
											</a>
										</li>
									{/if}
								</ul>
							</div> -->
						
						<div class="bottomItem">
							<h5 class="h5product-name">
								<a class="product-name" href="{$product.link|escape:'html'}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h5>
							{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
								<div class="lab-price">
									<span class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>
									<meta itemprop="priceCurrency" content="{$priceDisplay}" />
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
										<span class="old-price product-price">
											{displayWtPrice p=$product.price_without_reduction}
										</span>
									{/if}
								</div>
							{/if}
							{hook h='displayProductListReviews' product=$product}
							<div class="lab-cart">
								
								<div class="lab-Wishlist">
									<a onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', $('#idCombination').val(), 1,'tabproduct'); return false;" class="add-wishlist wishlist_button" href="#"
									   data-id-product="{$product.id_product|intval}"
									   title="{l s='Add to Wishlist' mod='land_tabproductslider'}">
										<i class="icon-heart"></i></a>
								</div>

										{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
											{if ($product.allow_oosp || $product.quantity > 0)}
												{if isset($static_token)}
													<a class="lab-cart-i button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"
													data-id-product="{$product.id_product|intval}"
													title="{l s='Add to cart' mod='labhomefeatured'}" >
														<!--<span>{l s='Add to cart' mod='labhomefeatured'}</span>-->
														<span>Ajouter au panier</span>
													</a>
												{else}
													<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"
													data-id-product="{$product.id_product|intval}"
													title="{l s='Add to cart' mod='labhomefeatured'}">
														<!--<span>{l s='Add to cart' mod='labhomefeatured'}</span>-->
														<span>Ajouter au panier</span>
													</a>
												{/if}						
											{else}
												<span class="button ajax_add_to_cart_button btn btn-default disabled">
													<!--<span>{l s='Add to cart' mod='labhomefeatured'}</span>-->
													<span>Ajouter au panier</span>
												</span>
											{/if}
										{/if}

								<!--  compare -->
									{if isset($comparator_max_item) && $comparator_max_item}
									<div class="lab-compare">
										<a class="add_to_compare"
										   href="{$product.link|escape:'html':'UTF-8'}"
										   data-product-name="{$product.name|escape:'htmlall':'UTF-8'}"
										   data-product-cover="{$link->getImageLink($product.link_rewrite, $product.id_image, 'cart_default')|escape:'html'}"
										   data-id-product="{$product.id_product}"
										   title="{l s='Add to Compare' mod='land_tabproductslider'}">
											<i class="icon-retweet"></i>
										</a>
									</div>
								{/if}
								</div>
							
						</div>
					</div>
				{if $smarty.foreach.myLoop.iteration % 1 == 0 || $smarty.foreach.myLoop.last  }
					</div>
				{/if}
		{/foreach}

	</div>
		<div class="lab_boxnp">
					<a class="prev prevproductTab"><i class="icon-angle-left"></i></a>
					<a class="next nextproductTab"><i class="icon-angle-right"></i></a>
				</div>
	</div>
</section>
		<script>
			$(document).ready(function() {
			var owl = $("#productscategory_list");
			owl.owlCarousel({
				autoPlay : false,
				items :3,
				itemsDesktop : [1200,3],
				itemsDesktopSmall : [991,3],
				itemsTablet: [768,2],
				itemsMobile : [480,1],
			});
				$(".nextproductTab").click(function(){
				owl.trigger('owl.next');
			})
			$(".prevproductTab").click(function(){
				owl.trigger('owl.prev');
			})
			});
		</script>
{/if}
