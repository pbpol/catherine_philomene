<div class="lablistproducts  labnewsmartblog row ">
    <div class='title_block'>
		<h4>
			<span>{l s='latest from blogs' mod='smartbloghomelatestnews'}</span>
		</h4>
	</div>
	
    <div class="smartblog">
	    <div class="sdsblog-box-content">
	        {if isset($view_data) AND !empty($view_data)}
	            {assign var='i' value=1}
	            {foreach from=$view_data item=post}
	                    {assign var="options" value=null}
	                    {$options.id_post = $post.id}
	                    {$options.slug = $post.link_rewrite}
	                    <div class="blog-item-inner">
							<div class="item-i">
								<span class="news_module_image_holder">
									 <a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}"><img alt="{$post.title}" class="feat_img_small" src="{$modules_dir}smartblog/images/{$post.post_img}-home-default.jpg"></a>
								</span>
								<h2 class="labname"><a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}">{$post.title}</a></h2>
								<p class="short_description">
									{$post.short_description|truncate:140:'...'|escape:'htmlall':'UTF-8'}
									<a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}" title="{l s='See more ...' mod='smartbloghomelatestnews'}"  class="r_more">{l s='{...}' mod='smartbloghomelatestnews'}</a>
								</p>

							</div>
	                    </div>
	                
	                {$i=$i+1}
	            {/foreach}
	        {/if}
	    </div>
			 <div class="lab_boxnp">
				<a class="prev labnewblogprev"><i class="icon-angle-left"></i></a>
				<a class="next labnewblognext"><i class="icon-angle-right"></i></a>
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
	var owl = $(".sdsblog-box-content");
    owl.owlCarousel({
		autoPlay : false,
		items :3,
		
		itemsDesktop : [1200,3],
		itemsDesktopSmall : [991,3],
		itemsTablet: [767,2],
		itemsMobile : [480,1],
	});
	$(".labnewblognext").click(function(){
	owl.trigger('owl.next');
	})
	$(".labnewblogprev").click(function(){
	owl.trigger('owl.prev');
	})
    });
</script>