
<div id="data_biz_{$tabId}" class="grid_content">
	<div class="productGridContent productGridContent_{$tabId} product_list productContent">
	{foreach from=$productlists item=product name=myLoop}
		{if $smarty.foreach.myLoop.index % 2 == 0 || $smarty.foreach.myLoop.first }
		<div class="item-inner clearfix">
		{/if}
			<div class="item">
				<div class="topItem">
					<div class="lab-img">
					{if Hook::exec('rotatorImg')}
						<!-- {hook h ='rotatorImg' product=$product} -->
						<a class = "product_image" href="{$product.link|escape:'html'}" title="{$product.name|escape:html:'UTF-8'}">
							<img class="img-responsive" src="{$product.imageData}" alt="{$product.name|escape:html:'UTF-8'}" />							
						</a>
					{else}
						<a class = "product_image" href="{$product.link|escape:'html'}" title="{$product.name|escape:html:'UTF-8'}">
							<img class="img-responsive" src="{$product.imageData}" alt="{$product.name|escape:html:'UTF-8'}" />							
						</a>
					{/if}

					{if isset($quick_view) && $quick_view}
												<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}"
												   data-id-product="{$product.id_product|intval}"
												   title="{l s='Quick view' mod='land_gridproduct'}">
												</a>
										{/if}
					</div>
				</div>
				<div class="bottomItem">
					<h5 class="h5product-name">
						<a class="product-name" href="{$product.link|escape:'html'}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h5>
					{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
						<div class="lab-price">
								<span class="old-price product-price">
									{displayWtPrice p=$product.price_without_reduction}
								</span>
							<span class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>
							<meta itemprop="priceCurrency" content="{$priceDisplay}" />
							{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
								
							{/if}
						</div>
					{/if}
					<div class="lab-cart">
						{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
							{if ($product.allow_oosp || $product.quantity > 0)}
								{if isset($static_token)}
									<a class=" lab-cart-i button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"
									   data-id-product="{$product.id_product|intval}"
									   title="{l s='Add to cart' mod='land_tabproductslider'}" >
										<!--<span>{l s='Add to cart' mod='land_tabproductslider'}</span>-->
										<span>Ajouter au panier</span>
									</a>
								{else}
									<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"
									   data-id-product="{$product.id_product|intval}"
									   title="{l s='Add to cart' mod='land_tabproductslider'}">
										<span><i class="fa fa-shopping-cart"></i></span>
									</a>
								{/if}
							{else}
								<span class="button ajax_add_to_cart_button btn btn-default disabled">
											<span><i class="fa fa-shopping-cart"></i></span>
								</span>
							{/if}
						{/if}

						<div class="lab-Wishlist">
							<a onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', $('#idCombination').val(), 1,'tabproduct'); return false;" class="add-wishlist wishlist_button" href="#"
							   data-id-product="{$product.id_product|intval}"
							   title="{l s='Add to Wishlist' mod='land_tabproductslider'}">
								<i class="icon-heart"></i></a>
						</div>

				


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
		{if $smarty.foreach.myLoop.iteration % 2 == 0 || $smarty.foreach.myLoop.last  }
		</div>
		{/if}

	{/foreach}
	</div>



	<div class="lab_boxnp">
			<a class="prev prevproductGridContent_{$tabId}"><i class="icon-angle-left"></i></a>
			<a class="next nextproductGridContent_{$tabId}"><i class="icon-angle-right"></i></a> 
		</div>	
	<script>
		$(document).ready(function() {
		var owl = $(".productGridContent_{$tabId}");
		owl.owlCarousel({
				autoPlay : false,
				singleItem:true,
				items :1,
				itemsDesktop : [1200,1],
				itemsDesktopSmall : [991,1],
				itemsTablet: [767,1],
				itemsMobile : [360,1],
			});	
		$(".nextproductGridContent_{$tabId}").click(function(){
				owl.trigger('owl.next');
				})
		$(".prevproductGridContent_{$tabId}").click(function(){
				owl.trigger('owl.prev');
			})		
		});
	</script>

</div>

