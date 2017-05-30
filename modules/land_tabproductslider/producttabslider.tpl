<div class="product-tabs-slider list-products">
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
	<div class="tab_container fx-fadeInUp lablistproducts"> 
	{foreach from=$productTabslider item=productTab name=labTabProduct}
		<div id="tab_{$productTab.id}" class="tab_content">
			<div class="productTabContent productTabContent_{$productTab.id} product_list productContent">
			{foreach from=$productTab.productInfo item=product name=posFeatureProducts}
				
				{if $smarty.foreach.myLoop.index % 1 == 0 || $smarty.foreach.myLoop.first }
				<div class="item-inner">
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
						{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
							<span class="sale-label">{l s='Sale!' mod='labnewproducts'}</span>
						{else}
							{if isset($product.new) && $product.new == 1}
								<span class="new-label">{l s='New' mod='labnewproducts'}</span>
							{/if}
						{/if}
						</div>
						<div class="countdown">
                                        {if $product.specific_prices && $product.reduction}
                                            {hook h='timecountdown' product=$product tabid = $productTab.id}
                                            <span id="future_date_{$product.id_category_default}_{$product.id_product}_{$productTab.id}">
											</span>
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
						<div class="actions">
							<ul class="add-to-links">
								<li class="lab-cart">
									{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
										{if ($product.allow_oosp || $product.quantity > 0)}
											{if isset($static_token)}
												<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"
												data-toggle="tooltip" 
												data-placement="top" 
												data-id-product="{$product.id_product|intval}"
												data-original-title="{l s='Add to cart' mod='labtabproductslider'}" >
													<i data-icon="&#xe013;" aria-hidden="true" class="fs1"></i>
												</a>
												
											{else}
												<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"
												data-toggle="tooltip" 
												data-placement="top"
												data-id-product="{$product.id_product|intval}"
												data-original-title="{l s='Add to cart' mod='labtabproductslider'}">
													<i data-icon="&#xe013;" aria-hidden="true" class="fs1"></i>
												</a>
											{/if}						
										{else}
											<span class="button ajax_add_to_cart_button btn btn-default disabled">
												<i data-icon="&#xe013;" aria-hidden="true" class="fs1"></i>
											</span>
										{/if}
									{/if}
								</li>
															
								<li class="lab-Wishlist">
									<a onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', $('#idCombination').val(), 1,'tabproduct'); return false;" class="add-wishlist wishlist_button" href="#"
									data-toggle="tooltip" 
									data-placement="top" 
									data-id-product="{$product.id_product|intval}"
									data-original-title="{l s='Wishlist' mod='labtabproductslider'}">
									<i class="icon-heart"></i></a>
								</li>
								{if isset($comparator_max_item) && $comparator_max_item}
									<li class="lab-compare">	
										<a class="add_to_compare" 
											href="{$product.link|escape:'html':'UTF-8'}" 
											data-product-name="{$product.name|escape:'htmlall':'UTF-8'}"
											data-product-cover="{$link->getImageLink($product.link_rewrite, $product.id_image, 'cart_default')|escape:'html'}"
											data-id-product="{$product.id_product}"
											data-toggle="tooltip" 
											data-placement="top" 
											data-original-title="{l s='Compare' mod='labtabproductslider'}">
											<i class="icon-refresh"></i>
										</a>
									</li>
								{/if}
								{if isset($quick_view) && $quick_view}
									<li class="lab-quick-view">
										<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}"
										data-toggle="tooltip" 
										data-placement="top" 
										data-id-product="{$product.id_product|intval}"
										data-original-title="{l s='View' mod='labtabproductslider'}">
											<i class="icon-eye-open"></i>
										</a>
									</li>
								{/if}
							</ul>
							{hook h='displayProductListReviews' product=$product}
						</div>

					</div>
				</div>
				{if $smarty.foreach.myLoop.iteration % 1 == 0 || $smarty.foreach.myLoop.last  }
				</div>
				{/if}



			{/foreach}
				<div class="lab_boxnp">
					<a class="prev prevproductTab"><i class="icon-angle-left"></i></a>
					<a class="next nextproductTab"><i class="icon-angle-right"></i></a>
				</div>

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
				items :5,
				itemsDesktop : [1200,3],
				itemsDesktopSmall : [991,3],
				itemsTablet: [767,2],
				itemsMobile : [360,1],
			});	
			$(".nextproductTab").click(function(){
			owl.trigger('owl.next');
			})
			$(".prevproductTab").click(function(){
			owl.trigger('owl.prev');
			})		
		});
	</script>
	
	{/foreach}

		<div class="lab_boxnp">
			<a class="prev prevproductTab"><i class="icon-angle-left"></i></a>
			<a class="next nextproductTab"><i class="icon-angle-right"></i></a>
		</div>

</div> <!-- .tab_container -->


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