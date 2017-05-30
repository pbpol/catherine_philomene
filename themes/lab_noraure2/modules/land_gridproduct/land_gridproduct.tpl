<div class="product-biz list-products">
	<div class="row">
		<div class="land-biz-product ">
			<div class="biz" data-ajaxurl="{$base_dir}modules/land_gridproduct/gridproduct-ajax.php">
			<ul class="tab-biz-control hidden-lg hidden-sm hidden-md hidden-xs col-sm-12 col-sx-12">
				{$i=1}
				{foreach from=$productTabslider item=bizTab name=posTabProduct}
				<li><a href="#bizData-{$bizTab.id}" class="land-underline-from-center {if $i==1}active{/if}">{$bizTab.name}</a></li>
				{$i= $i+1}
				{/foreach}
			</ul>
				{$count=1}
				{foreach from=$productTabslider item=productTab name=posTabProduct}
				<div id="bizData-{$productTab.id}" class="biz-group col-lg-4 col-sm-4 col-sx-4 col-md-4">

						<div class="block-title">
        					<strong>	<span>{$productTab.name}</span></strong>
    					</div>
						<div id="bizTab-{$productTab.id}" class="biz-group-content">
							
						</div>
						<!-- <div class="view-more-cat-link clearfix"><a href="{$link->getPageLink({$productTab.link})|escape:'html'}">View more<i class="fa fa-long-arrow-right"></i></a></div> -->
					</div>
				{$count= $count+1}
				{/foreach}	
			</div>
		</div>	
	</div>
</div>
{foreach from=$languages key=k item=language name="languages"}
	{if $language.iso_code == $lang_iso}
		{assign var='rtl' value=$language.is_rtl}
	{/if}
{/foreach}
<script type="text/javascript"> 
	$(document).ready(function() {
		var urlAjax = jQuery(".land-biz-product > .biz").attr("data-ajaxurl");
		jQuery(".biz-group-content").each(function(){
			var idBiz = jQuery(this).attr("id").replace("bizTab-","");
			var params = {}
			params.tab = idBiz;
			jQuery.ajax({
	            url: urlAjax,
	            data:params,
	            type:"POST",
	            success:function(data){
	            	var results = JSON.parse(data);
	            	jQuery("#bizTab-"+idBiz).html(results);
	                return false;
	            }
	        });
		});

		jQuery(".tab-biz-control li a").click(function(e){
			e.preventDefault();
			var ibObjTab = jQuery(this).attr("href");
			jQuery(".tab-biz-control li a").removeClass("active");
			jQuery(this).addClass("active");
			jQuery(".biz-group").hide();
			jQuery(ibObjTab).show();
		});
		
	});
</script>