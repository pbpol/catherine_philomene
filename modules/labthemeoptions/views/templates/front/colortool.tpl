
     {if $show_labskin !=0 }

        <div class="lab-wrap">
		<h2 class="lab-title">Theme Options</h2>
		<div class="lab-option">
			<div class="cl-table">
				<div class="cl-tr cl-tr-mode-label">
					<div class="cl-tr cl-tr-style-label">{l s='Mode Layout'}</div>
				</div>
				<div class="cl-tr cl-tr-mode">
					<div class="pull-left">
						<input class="mode_theme" id="labbox" type="radio" value="box" name="mode_css">{l s='Box'}
					</div>
					<div class="pull-right">
						<input class="mode_theme" id="labwide" type="radio" value="wide" name="mode_css" checked="checked">{l s='Wide'}
					</div>
				</div>
			</div>
			
			
			<div class="cl-wrapper">
				<div class="cl-container">
					<div class="cl-tr cl-tr-mode-label">
						<div class="cl-tr cl-tr-style-label">{l s='Theme color'}</div>
					</div>
					<div class="cl-table">
                        <div class="cl-tr cl-tr-style box-layout">
                            <div class="cl-td-l cl-td-layout cl-td-layout1"><a href="javascript:void(0)" id="color1" title="{l s='color1'}"><span class="cl1"></span></a></div>
                            <div class="cl-td-l cl-td-layout cl-td-layout2"><a href="javascript:void(0)" id="color2" title="{l s='color2'}"><span class="cl1"></span></a></div>
                        </div>
                        <div class="cl-tr cl-tr-style box-layout">
                            <div class="cl-td-l cl-td-layout cl-td-layout3"><a href="javascript:void(0)" id="color3" title="{l s='color3'}"><span class="cl1"></span></a></div>
                            <div class="cl-td-l cl-td-layout cl-td-layout4"><a href="javascript:void(0)" id="color4" title="{l s='color4'}"><span class="cl1"></span></a></div>
                        </div>
                        <div class="cl-tr cl-tr-style box-layout">
                            <div class="cl-td-l cl-td-layout cl-td-layout5"><a href="javascript:void(0)" id="color5" title="{l s='color5'}"><span class="cl1"></span></a></div>
                            <div class="cl-td-l cl-td-layout cl-td-layout6"><a href="javascript:void(0)" id="color6" title="{l s='color6'}"><span class="cl1"></span></a></div>
                        </div>
						<div class="cl-tr cl-tr-style box-layout">
                            <div class="cl-td-l cl-td-layout cl-td-layout7"><a href="javascript:void(0)" id="color7" title="{l s='color7'}"><span class="cl1"></span></a></div>
                            <div class="cl-td-l cl-td-layout cl-td-layout8"><a href="javascript:void(0)" id="color8" title="{l s='color8'}"><span class="cl1"></span></a></div>
                        </div>
                    </div>
					<div class="cl-table">	
                        <div class="cl-tr">
                            <div class="cl-tr cl-tr-style-label">{l s='Background Image:'}</div>
                            <div class="cl-td-bg">
                                <div class="cl-pattern">
                                    {for $id=1 to 30}
                                        <div class="cl-image pattern{$id}" id="pattern{$id}"></div>
                                    {/for}
                                </div>
                            </div>
                        </div>
                        <div class="cl-tr cl-row-reset">
                            <span class="cl-reset"></span>
                        </div>
					</div>
                </div>
			</div>
		</div>
		<div class="control inactive"><a href="javascript:void(0)"><i class="icon-cog icon-spin"></i></a></div>
		<script type="text/javascript">
//			$(document).ready( function(){ldelim}
				$('.control').click(function(){ldelim}
					if($(this).hasClass('inactive')) {ldelim}
						$(this).removeClass('inactive');
						$(this).addClass('active');
						$('.lab-wrap').animate({ldelim}left:'0'{rdelim}, 500);
					{rdelim} else {ldelim}
						$(this).addClass('inactive');
						$(this).removeClass('active');
						$('.lab-wrap').animate({ldelim}left:'-228px'{rdelim}, 500);
					{rdelim}
				{rdelim});
//			{rdelim});
		</script>
	</div>
     {/if}
<script type="text/javascript">

var date = new Date();
date.setTime(date.getTime() + (1440 * 60 * 1000));
// set default background image
    $(document).ready(function(){
      {for $id=1 to 30}
        $('#pattern{$id}').click(function(){
            $('.cl-image').removeClass('active');
            $(this).addClass('active');
            $("body").css('background-image','url("{$modules_dir}labthemeoptions/views/templates/front/colortool/images/pattern/pattern{$id}.png")');
            $.cookie('pattern', 'labthemeoptions/views/templates/front/colortool/images/pattern/pattern{$id}.png' , {ldelim} expires: date {rdelim});
            $.cookie('id','{$id}');
        });
   {/for}
        if ($.cookie('theme_skin_cookie')!= '' && $.cookie('theme_skin_cookie') != null ){
            $('<link rel="stylesheet" type="text/css" href="'+$.cookie('theme_skin_cookie')+'" />').appendTo('head');
        }     //set theme color cookie
        $('.cl-td-layout a').click(function(){ldelim}
            $('<link rel="stylesheet" type="text/css" href="{$PS_BASE_URL}{$PS_BASE_URI}themes/{$LAB_THEMENAME}/css/color/global_'+this.id+'.css" />').appendTo('head');
            $.cookie('theme_skin_cookie', '{$PS_BASE_URL}{$PS_BASE_URI}themes/{$LAB_THEMENAME}/css/color/global_'+this.id+'.css', {ldelim} expires: date {rdelim});
            {rdelim});
     /*   $(' body > input:checkbox').click(function(){
            if($('#wide').is(":checked")) {
                $('body').addClass("checked");
            } else  if($('#box').is(":checked")) {
                $('body').removeClass("checked");
            }
        });*/
        $(document).ready(function () {
            $('.mode_theme').click(function () {
            $id = $(this).attr('id');
                $('#page').removeAttr('class');
                $('#page').addClass($id);
            });
            });

    $('.cl-reset').click(function(){ldelim}
		$.cookie('font_cookie','');
		$.cookie('pattern','');
        $.cookie('backgroundimage_cookie','');
        $.cookie('theme_skin_cookie','');
       //reset main
       location.reload();
    });
    {rdelim});
</script>

