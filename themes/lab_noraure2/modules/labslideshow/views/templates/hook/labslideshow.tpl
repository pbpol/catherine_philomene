
{if $page_name =='index'}
	{if isset($labslideshow_slides)}
		<div class="lab-nivoSlideshow">
			<div class="lab-loading"></div>
			<div id="lab-slideshow" class="flexslider">
				<ul class="slides">
					{foreach from=$labslideshow_slides item=slide}
						<li>
							{if $slide.active}
								<img data-thumbnail="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`labslideshow/images/`$slide.image|escape:'htmlall':'UTF-8'`")}"
										src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`labslideshow/images/`$slide.image|escape:'htmlall':'UTF-8'`")}"
										alt="{$slide.legend|escape:'htmlall':'UTF-8'}"
										title="{$slide.title}" />
								{if $slide.active}
									<div class=" nivo-html-caption nivo-caption">
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
										<div class="slide{$slide.id_slide}">
											<div class="lab_description  {$slide.margin}">
												{if $labslideshow.LAB_TITLE =='true'}
													<div class="title a1 animated">
														{$slide.title}
													</div>
												{/if}
												<div class="description a2 animated">
													{$slide.description}
												</div>
												{if $slide.url}
													<div class="shopnow a3 animated ">
														<a href="{$slide.url}">{l s='Shop Now' mod='labslideshow_slides'}</a>
													</div>
												{/if}
											</div>
										</div>
									</div>
								{/if}
							{/if}
						</li>
					{/foreach}
				</ul>
			</div>
		</div>
	{/if}
	<!-- /Module labslideshow -->
	<script>
		$(function () {
			$('#lab-slideshow').flexslider({
				slideshow: {if $labslideshow.LAB_TITLE==1} true {else} false{/if},
				slideshowSpeed: {if $labslideshow.LAB_PAUSE} {$labslideshow.LAB_PAUSE}{else} 5000{/if}, // pause
				animation: "fade",
				animationLoop: true,
				controlNav: {if $labslideshow.LAB_E_N_P==1} true {else} false{/if},               //Boolean: Create navigation for paging control of each clide? Note: Leave true for manualControls usage
				directionNav: true,
				start: renderPreview,	//render preview on start
				before: renderPreview //render preview before moving to the next slide
			});
			function renderPreview(slider) {
				var sl = $(slider);
				var prevWrapper = sl.find('.flex-prev');
				var nextWrapper = sl.find('.flex-next');
				//calculate the prev and curr slide based on current slide
				var curr = slider.animatingTo;
				var prev = (curr == 0) ? slider.count - 1 : curr - 1;
				var next = (curr == slider.count - 1) ? 0 : curr + 1;
				//add prev and next slide details into the directional nav
				prevWrapper.find('.preview, .arrow').remove();
				nextWrapper.find('.preview, .arrow').remove();
				prevWrapper.append(grabContent(sl.find('li:eq(' + prev + ') img')));
				nextWrapper.append(grabContent(sl.find('li:eq(' + next + ') img')));
			}
			// grab the data and render in HTML
			function grabContent(img) {
				var tn = img.data('thumbnail');
				var alt = img.prop('alt');
				var html = '';
				//you can edit this markup to your own needs, but make sure you style it up accordingly
				html = '<div  class="arrow "> </div><div class=" preview"><img src="' + tn + '" alt="" /> </div>';
				return html;
			}
		});

	</script>
{/if}