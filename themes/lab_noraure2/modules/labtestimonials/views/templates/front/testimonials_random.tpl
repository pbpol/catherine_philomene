<div class="lab_testimonials">
  <h4 class="lab_title wow fadeInDown" data-wow-delay="200ms">{l s='what’s client say?' mod='labtestimonials'}</h4>
  <div id="wrapper">
    {if $testimonials}
        <div class="testimonials">
          {foreach from=$testimonials key=test item=testimonial}
            {if $testimonial.active == 1}
              <div class="item-inner wow fadeInUp " data-wow-delay="200ms" >
				<div class="item">

                <div class="content_test">
                  <p class="des_testimonial">“{$testimonial.content|truncate:350}" </p>

                    <p class="media-content">
                        {if $testimonial.media}
                            {if in_array($testimonial.media_type,$arr_img_type)}
                                <a class="fancybox-media" href="{$mediaUrl}{$testimonial.media}?id={$testimonial.id_labtestimonial}">
                                    <img src="{$mediaUrl}{$testimonial.media}" alt="Image Testimonial">
                                </a>

                            {/if}
                            {if in_array($testimonial.media_type,$video_types) }
                                <video width="260" height="240" controls>
                                    <source src="{$mediaUrl}{$testimonial.media}" type="video/mp4" />
                                </video>
                            {/if}
                        {else}
                            <img src="{$module_dir}assets/front/img/demo1.jpg" alt="Image Testimonial">
                        {/if}
                        {if $testimonial.media_type == 'youtube'}
                            <a class="fancybox-media" href="{$testimonial.media_link}"><img src="{$video_youtube}" alt="{l s='Youtube Video' mod='labtestimonials'}"></a>
                        {elseif $testimonial.media_type == 'vimeo'}
                            <a class="fancybox-media" href="{$testimonial.media_link}"><img src="{$video_vimeo}" alt="{l s='Vimeo Video' mod='labtestimonials'}"></a>
                        {/if}
                    </p>
                  <div class="termi-info-txt">
                    <p class="des_namepost"><span>{$testimonial.name_post}</span></p>
                    <p class="des_company">{$testimonial.company}</p>
                  </div>
                </div>
              </div>
              </div>
            {/if}

          {/foreach}
        </div>
    {/if}
      <!-- <div class="button_testimonial">
          <div class="view_all"><a class="btn btn-default button button-small" href="{$link->getModuleLink('labtestimonials','views',['process' => 'view'])}">{l s='View All' mod='labtestimonials'}</a></div>
          <div class="submit_link"><a class="btn btn-default button button-small" href="{$link->getModuleLink('labtestimonials','views',['process' => 'form_submit'])}"> {l s='Submit Testimonial' mod='labtestimonials'}</a></div>
      </div> -->
  </div>
</div>
<script>
	$(document).ready(function() {
	var owl = $(".testimonials");
	owl.owlCarousel({
		autoPlay : false,
		items :1,
		itemsDesktop : [1200,1],
		itemsDesktopSmall : [991,1],
		itemsTablet: [767,1],
		itemsMobile : [480,1],
	});
   $('.media-content .fancybox-media').fancybox({
          closeEffect : 'none',
          prevEffect : 'none',
          nextEffect : 'none',
          openEffect  : 'elastic',

        });
	});
</script>