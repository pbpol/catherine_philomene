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
*  International Registered Trademark & Property of PbesrestaShop SA
*}

<!-- MODULE Block best sellers -->
<div id="best-sellers_block_right" class="lablistproducts2 block products_block">
	<h4 class="title_block">
    	<a href="{$link->getPageLink('best-sales')|escape:'html'}" title="{l s='View a top sellers products' mod='blockbestsellers'}">{l s='Top sellers' mod='blockbestsellers'}</a>
    </h4>
	<div class="block_content">
	{if $best_sellers && $best_sellers|@count > 0}
		<div class="labbest-sellers">
			{foreach from=$best_sellers item=product name=myLoop}
			{if $smarty.foreach.myLoop.index % 3 == 0 || $smarty.foreach.myLoop.first }
				<div class="item-inner" >
			{/if}
			<div class="item wow fadeInUp clearfix" data-wow-delay="{$smarty.foreach.myLoop.iteration}00ms">
				<div class="bestseller-img col-lg-4 col-md-4 col-sm-4 col-xs-4">
					<a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}" class="products-block-image content_img clearfix">
						<img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html'}" alt="{$product.legend|escape:'html':'UTF-8'}" />
					</a>
				</div>
				<div class="bestseller-content col-lg-8 col-md-8 col-sm-8 col-xs-8">	
					<div class="product-content">
	                	<h5>
	                    	<a class="product-name" href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}">
	                            {$product.name|strip_tags:'UTF-8'|escape:'html':'UTF-8'}
	                        </a>
	                    </h5>
	                    {hook h='displayProductListReviews' product=$product}
	                    {if !$PS_CATALOG_MODE}
	                        <div class="price-box">
	                            <span class="price">{$product.price}</span>
	                        </div>
	                    {/if}
	                </div>
	            </div>    
			</div>
			{if $smarty.foreach.myLoop.iteration % 3 == 0 || $smarty.foreach.myLoop.last  }
				</div>
			{/if}
		{/foreach}
		</div>
		<div class="labnextprev">
			<a class="prevbest"><i class="icon-angle-left"></i></a>
			<a class="nextbest"><i class="icon-angle-right"></i></a>
		</div>
		<div class="box-info-product">
        	<a href="{$link->getPageLink('best-sales')|escape:'html'}" title="{l s='All best sellers' mod='blockbestsellers'}"  class="exclusive"><span>{l s='All best sellers' mod='blockbestsellers'}</span></a>
        </div>
	{else}
		<p>{l s='No best sellers at this time' mod='blockbestsellers'}</p>
	{/if}
	</div>
</div>
<script>
$(document).ready(function() {
var owl = $(".labbest-sellers");
	owl.owlCarousel({
		autoPlay : false,
		items :1,
		itemsDesktop : [1200,1],
		itemsDesktopSmall : [991,1],
		itemsTablet: [767,1],
		itemsMobile : [480,1],
	});
	$(".nextbest").click(function(){
	owl.trigger('owl.next');
	})
	$(".prevbest").click(function(){	
	owl.trigger('owl.prev');
	})
});
</script>
<!-- /MODULE Block best sellers -->