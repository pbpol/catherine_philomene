

'<div class="product-tabs-slider lablistproducts laberthemes">
	<div class="lab_tabs">
		<ul class="tabs">
			{$count=0}
			{foreach from=$productTabslider item=productTab name=posTabProduct}
				<li class="{if $smarty.foreach.posTabProduct.first}first_item{elseif $smarty.foreach.posTabProduct.last}last_item{else}{/if} {if $count==0} active {/if}" rel="tab_{$productTab.id}"  >
					{$productTab.name}
				</li>
				{$count= $count+1}
			{/foreach}
		</ul>
		
	</div>
	{if Hook::exec('land_tabproductslider')}
		<div class="static-block">
			{hook h="land_tabproductslider"}
		</div>
	{/if}
	<div class="producttab row">
		<div class="tab_container">
			{foreach from=$productTabslider item=productTab name=labTabProduct}
				<div id="tab_{$productTab.id}" class="tab_content">
					<div class="productTabContent productTabContent_{$productTab.id} product_list productContent">
						{foreach from=$productTab.productInfo item=product name=myLoop}

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
												<span class="new-label">{l s='New' mod='land_tabproductslider'}</span>
											</a>
										{/if}
										{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
											<a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
												<span class="sale-label">{l s='Sale!' mod='land_tabproductslider'}</span>
											</a>
										{/if}
										{if isset($quick_view) && $quick_view}
												<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}"
												   data-id-product="{$product.id_product|intval}"
												   title="{l s='Quick view' mod='land_tabproductslider'}">
												</a>
										{/if}
									</div>

								</div>
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
															<!--<span>{l s='Add to cart' mod='land_tabproductslider'}</span>-->
															<span>Ajouter au panier</span>
														</a>
													{/if}
												{else}
													<span class=" lab-cart-i button ajax_add_to_cart_button btn btn-default disabled">
													<!--<span>{l s='Add to cart' mod='land_tabproductslider'}</span>-->
													<span>Ajouter au panier</span>
												</span>
												{/if}
											{/if}
										

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
									<a class="prev labprotabprev"><i class="icon-angle-left"></i></a>
									<a class="next labprotabnext"><i class="icon-angle-right"></i></a>
					</div>					
				</div>
				{foreach from=$languages key=k item=language name="languages"}
					{if $language.iso_code == $lang_iso}
						{assign var='rtl' value=$language.is_rtl}
					{/if}
				{/foreach}
				<script>

					$(document).ready(function() {
						var owl = $(".productTabContent_{$productTab.id}");
						owl.owlCarousel({
							autoPlay : false,
							items :4,
							itemsDesktop : [1200,3],
							itemsDesktopSmall : [991,3],
							itemsTablet: [767,2],
							itemsMobile : [480,1],
						});
						$(".labprotabnext").click(function(){
							owl.trigger('owl.next');
						})
						$(".labprotabprev").click(function(){
							owl.trigger('owl.prev');
						})
					});
				</script>

			{/foreach}

		</div> <!-- .tab_container -->
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(".tab_content").hide();
		$(".tab_content:first").show();
		$("ul.tabs li").click(function() {
			$("ul.tabs li").removeClass("active");
			$(this).addClass("active");
			$(".tab_content").hide();
			$(".tab_content").removeClass("animate1 {$tab_effect}");
			var activeTab = $(this).attr("rel");
			$("#"+activeTab) .addClass("animate1 {$tab_effect}");
			$("#"+activeTab).fadeIn();
		});
	});
</script>