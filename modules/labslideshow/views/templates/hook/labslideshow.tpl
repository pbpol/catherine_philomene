
{if $page_name =='index'}

    {if isset($labslideshow_slides)}

		<div class="lab-nivoSlideshow">
		<div class="lab-loading"></div>
        <div id="lab-slideshow" class="flexslider">
			<ul class="slides">
                {foreach from=$labslideshow_slides item=slide}
				<li>
                    {if $slide.active}
                                <img
									data-thumbnail="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`labslideshow/images/`$slide.image|escape:'htmlall':'UTF-8'`")}"
									src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`labslideshow/images/`$slide.image|escape:'htmlall':'UTF-8'`")}"
                                     alt="{$slide.legend|escape:'htmlall':'UTF-8'}"
									 title="#htmlcaption{$slide.id_slide}" />
                    {/if}
				</li>
                {/foreach}
        </div>
		
		{foreach from=$labslideshow_slides item=slide}
        {if $slide.active}
		<div id="htmlcaption{$slide.id_slide}" class=" nivo-html-caption nivo-caption">
			<div class="timeline" style=" 
								position:absolute;
								top:0;
								left:0;
								background-color: rgba(49, 56, 72, 0.298);
								height:5px;
								-webkit-animation: myfirst {$labslideshow.LAB_PAUSE}ms ease-in-out;
								-moz-animation: myfirst {$labslideshow.LAB_PAUSE}ms ease-in-out;
								-ms-animation: myfirst {$labslideshow.LAB_PAUSE}ms ease-in-out;
								animation: myfirst {$labslideshow.LAB_PAUSE}ms ease-in-out;
							">
			</div>
			<div class="lab_description {$slide.margin}">
			{if $labslideshow.LAB_TITLE =='true'}
			<div class="title animated a1">
				{$slide.title}
			</div>
			{/if}
			<div class="description animated a2">
				{$slide.description}
			</div>
			{if $slide.url}
			<div class="shopnow animated a3">
				<a href="{$slide.url}">{l s='Shop Now' mod='labslideshow_slides'}</a>
			</div>
			{/if}
			</div>
		</div>
		{/if}
        {/foreach}
	</div>
    {/if}
    <!-- /Module labslideshow -->
	
{/if}