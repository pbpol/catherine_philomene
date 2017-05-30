
<div class="col-lg-12">
	<div class="row">
		<!-- <div class="col-lg-12">
			<h2 class="title"><i class="icon-AdminTools"></i> Lab Theme Option</h2>
		</div> -->
		<div class="boxthemeoptionTab">
		<div class="themeoptionTab col-lg-2">
			<div class="list-group">
				<ul id="myTab" class="nav nav-tabs">
					<li class="active"><a href="#themeSkin" data-toggle="tab"><span> Theme skin</span></a></li>
					<li class=""><a href="#headercolor" data-toggle="tab"><span> Header</span></a></li>
					<li class=""><a href="#megamenu" data-toggle="tab"><span> Mega Menu</span></a></li>
					<li class=""><a href="#maincontent" data-toggle="tab"><span> Main Content</span></a></li>
					<li class=""><a href="#footercolor" data-toggle="tab"><span> Footer</span></a></li>
					<!-- <li class=""><a href="#Stfotatic-block" data-toggle="tab"><i class="icon-file-code-o"></i> Static block</a></li>
					<li class=""><a href="#footer-block" data-toggle="tab"><i class="icon-life-ring"></i> footer block</a></li> -->
				</ul>
			</div>
		</div>

		<div class="col-lg-10">
		<div id="myTabContent" class="tab-content">
        <!----   themes skin -->
			<div class="tab-pane active in" id="themeSkin">
				<div class="tab-content-ii">
					<form class="form-horizontal" action = "{$action}"  enctype="multipart/form-data" method="post">
                        <h4 class="name-tab"> <i class="icon-info"></i> Theme Skin</h4>
                        <div class="box_lab">
                            <div class="form-group">
                                <div class="col-lg-3"> Theme color</div>
                                <div class="col-lg-9">
                                    <div class="cl-tr cl-tr-style">
                                        <div class="cl-td-l cl-td-layout cl-td-layout1{if $labthemecolor=='color1' } active {/if} "id="color1"><a href="javascript:void(0)"  title=""><span class="cl2"></span><span class="cl1"></span>{l s='color1'}</a></div>
                                        <div class="cl-td-l cl-td-layout cl-td-layout2{if $labthemecolor=='color2' } active {/if}" id="color2"><a href="javascript:void(0)"  title=""><span class="cl2"></span><span class="cl1"></span>{l s='color2'}</a></div>
                                    </div>
                                    <div class="cl-tr cl-tr-style">
                                        <div class="cl-td-l cl-td-layout cl-td-layout3 {if $labthemecolor=='color3' } active {/if}" id="color3"><a href="javascript:void(0)"  title=""><span class="cl2"></span><span class="cl1"></span>{l s='color3'}</a></div>
                                        <div class="cl-td-l cl-td-layout cl-td-layout4 {if $labthemecolor=='color4' } active {/if}" id="color4"><a href="javascript:void(0)"  title=""><span class="cl2"></span><span class="cl1"></span>{l s='color4'}</a></div>
                                    </div>
                                    <div class="cl-tr cl-tr-style">
                                        <div class="cl-td-l cl-td-layout cl-td-layout5 {if $labthemecolor=='color5' } active {/if}" id="color5"><a href="javascript:void(0)"  title=""><span class="cl2"></span><span class="cl1"></span>{l s='color5'}</a></div>
                                        <div class="cl-td-l cl-td-layout cl-td-layout6 {if $labthemecolor=='color6' } active {/if}"  id="color6"><a href="javascript:void(0)" title=""><span class="cl2"></span><span class="cl1"></span>{l s='color6'}</a></div>
                                    </div>
									<div class="cl-tr cl-tr-style">
                                        <div class="cl-td-l cl-td-layout cl-td-layout7 {if $labthemecolor=='color7' } active {/if}" id="color7"><a href="javascript:void(0)"  title=""><span class="cl2"></span><span class="cl1"></span>{l s='color7'}</a></div>
                                        <div class="cl-td-l cl-td-layout cl-td-layout8 {if $labthemecolor=='color8' } active {/if}"  id="color8"><a href="javascript:void(0)" title=""><span class="cl2"></span><span class="cl1"></span>{l s='color8'}</a></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group themeSkin">
                                <div class="col-lg-3">
                                    <div class="cl-tr">
                                        <div class="cl-td-l">Background Image:</div>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                            <div class="cl-td-bg ">
                                                <div class="cl-pattern">
                                                    {for $id=1 to 30}
                                                        <div class="cl-image pattern{$id} {if $labskin|substr:7 == $id } active {/if}" id="pattern{$id}"  ></div>
                                                    {/for}
                                                </div>
                                            </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Show Tool Themeoption Fontend:'}
                                </label>
                                <div class="col-lg-9 ">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" value="1" id="show_labskin_on" {if $labshowskin ==1} checked="checked" {/if} name="show_labskin">
                                            <label for="show_labskin_on">{l s='Yes'}</label>
                                                <input type="radio" {if $labshowskin ==0} checked="checked" {/if} value="0" id="show_labskin_off" name="show_labskin">
                                            <label for="show_labskin_off">{l s='No'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    <div class="help-block">If you choose yes ,fontend  will appear  select box for you to customize themes
                                    </div>
                                </div>

                            </div>


                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Show Theme Color Fontend:'}
                                </label>
                                <div class="col-lg-9 ">
                                     <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" value="1" id="labshowthemecolor_on" {if $labshowthemecolor ==1} checked="checked" {/if} name="labshowthemecolor">
                                            <label for="labshowthemecolor_on">{l s='Yes'}</label>
                                                <input type="radio" {if $labshowthemecolor ==0} checked="checked" {/if} value="0" id="labshowthemecolor_off" name="labshowthemecolor">
                                            <label for="labshowthemecolor_off">{l s='No'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    <div class="help-block">If you choose yes , it  will appear Background Image and color themes that you selected in the admin
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Show Mode themes:'}
                                </label>
                                <div class="col-lg-9 ">
                                    <select id="lab_mode_theme" class=" fixed-width-xl" name="lab_mode_theme">
                                        <option {if $lab_mode_theme =='labbox' } selected="box" {/if} value="labbox">{l s='box'}</option>
                                        <option {if $lab_mode_theme =='labwide' } selected="wide" {/if} value="labwide">{l s='wide'}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Show Color Option Fontend:'}
                                </label>
                                <div class="col-lg-9 ">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" value="1" {if $labshowcolor ==1}  checked="checked" {/if} id="show_color_on" name="show_color">
                                            <label for="show_color_on">{l s='Yes'}</label>
                                                <input type="radio" {if $labshowcolor ==0}  checked="checked" {/if} value="0" id="show_color_off" name="show_color">
                                            <label for="show_color_off">{l s='No'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    <div class="help-block">If you choose to have it all on your color choice in the header, MainContent, megamenu, outside fontend footer will change like the options in your admin if you select no, the color of the admin will not work
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer-i">
                                <button class="btn btn-default pull-right" name="submitUpdateThemeskin" type="submit"><i class="process-icon-save"></i> Save</button>
                            </div>
                        </div>
                        <script type="text/javascript">
                            $(document).ready(function(){
                                {for $id=1 to 30}
                                $('#pattern{$id}').click(function(){
                                    var val = $(this).attr('id');
                                    $("#bglabskin").remove();
                                    $(".cl-pattern").append('<input type=hidden id="bglabskin" name="labskin" value="'+val+'">');
                                    $('.cl-image').removeClass('active');
                                    $(this).addClass('active');
                                });
                                {/for}
                                $('.cl-td-layout').click(function(){ldelim}
                                    var val = $(this).attr('id');
                                    $("#labthemecolor").remove();
                                    $(".cl-pattern").append('<input type=hidden id="labthemecolor" name="labthemecolor" value="'+val+'">');
                                    $('.cl-td-layout').removeClass('active');
                                    $(this).addClass('active');
                                    {rdelim});
                            });
                        </script>
                    </form>
				</div>
			</div><!-- end tab pane review -->
            <!--- end themeskin -->
			<div class="tab-pane fade" id="configcolor">
				<div class="tab-content-ii">
                    <form class="form-horizontal" action = "{$action}"  enctype="multipart/form-data" method="post">
                    <h4 class="name-tab"> <i class="icon-info"></i> Unlimited color</h4>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Show fontend:'}
                        </label>
                        <div class="col-lg-9 ">
						<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" value="1" id="show_fontend_on" name="show_fontend">
							<label for="show_fontend_on">{l s='Yes'}</label>
								<input type="radio" checked="checked" value="0" id="show_fontend_off" name="show_fontend">
							<label for="show_fontend_off">{l s='No'}</label>
							<a class="slide-button btn"></a>
						</span>
                        </div>
						 <div class="panel-footer-i">
							<button class="btn btn-default pull-right" name="submitUpdate" type="submit"><i class="process-icon-save"></i> Save</button>
						</div>
                    </div>
                   
                    </form>
				</div>
			</div><!-- col-tab pane -->
        <!----   header color -->
            <div class="tab-pane fade" id="headercolor">
                    <div class="tab-content-ii">
                        <form class="form-horizontal" action = "{$action}"  enctype="multipart/form-data" method="post">
                            <h4 class="name-tab"> <i class="icon-info"></i> Unlimited color</h4>
                            <div class="box_lab">
                    <h4 class="col-md-offset-3"><i class="icon-dot-circle-o"></i> {l s='Header'}</h4>
                    <div class="form-group row">
                        <label class="control-label col-lg-3">
                            {l s='background color nav'}
                        </label>
                        <div class="col-lg-9 ">
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="input-group">
                                        <input type="text" value="{$lab_hbgnav}" name="lab_hbgnav" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_0" style="background-color:{$lab_hbgnav};color: black;">
                                            <span class="mColorPickerTrigger input-group-addon" id="icp_color_0" style="cursor:pointer;" data-mcolorpicker="true">
                                                <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-lg-3">
                            {l s='Text nav'}
                        </label>
                        <div class="col-lg-9 ">
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="input-group">
                                        <input type="text" value="{$lab_htextnav}" name="lab_htextnav" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_1" style="background-color:{$lab_htextnav};color: black;">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_1" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="control-label col-lg-3">
                            {l s='Text nav hover'}
                        </label>
                        <div class="col-lg-9 ">
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="input-group">
                                        <input type="text" value="{$lab_hhovertextnav}" name="lab_hhovertextnav" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_2" style="background-color:{$lab_hhovertextnav};color: black;">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_2" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="form-group row">
                        <label class="control-label col-lg-3">
                            {l s='background header color'}
                        </label>
                        <div class="col-lg-9 ">
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="input-group">
                                        <input type="text" value="{$lab_hbgheadercolor}" name="lab_hbgheadercolor" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_3" style="background-color:{$lab_hbgheadercolor};color: black;">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_3" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="form-group row">
                        <label class="control-label col-lg-3">
                            {l s='Text header'}
                        </label>
                        <div class="col-lg-9 ">
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="input-group">
                                        <input type="text" value="{$lab_htextheader}" name="lab_htextheader" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_4" style="background-color:{$lab_htextheader};color: black;">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_4" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="form-group row">
                        <label class="control-label col-lg-3">
                            {l s='Text hover'}
                        </label>
                        <div class="col-lg-9 ">
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="input-group">
                                        <input type="text" value="{$lab_hhovertext}" name="lab_hhovertext" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_5" style="background-color:{$lab_hhovertext};color: black;">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_5" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
					<div class="panel-footer-i">
						<button class="btn btn-default pull-right" name="submitUpdateheader" type="submit"><i class="process-icon-save"></i> Save</button>
					</div>
                </div>

                            

                         </form>
                        </div>
                    </div>
                <!-- end header -->
        <!----   megamenu  color -->
            <div class="tab-pane fade" id="megamenu">
                    <div class="tab-content-ii">
                        <form class="form-horizontal" action = "{$action}"  enctype="multipart/form-data" method="post">
                            <h4 class="name-tab"> <i class="icon-info"></i> Unlimited color</h4>
                            <div class="box_lab">
                                <h4 class="col-md-offset-3"><i class="icon-dot-circle-o"></i> {l s='Mega menu'}</h4>
                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='background color:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mbgcolor}" name="lab_mbgcolor" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_01" style="color: black;background-color:{$lab_mbgcolor}">
                                            <span class="mColorPickerTrigger input-group-addon" id="icp_color_01" style="cursor:pointer;" data-mcolorpicker="true">
                                                <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='background hover:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mbghover}" name="lab_mbghover" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_11" style="color: black;background-color: {$lab_mbghover}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_11" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='Text:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mtext}" name="lab_mtext" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_21" style="color: black;background-color: {$lab_mtext}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_21" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='Hover text:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mtexthover}" name="lab_mtexthover" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_31" style="color: black;background-color: {$lab_mtexthover}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_31" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='background sub menu:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mbgsubmenu}" name="lab_mbgsubmenu" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_41" style="color: black;background-color: {$lab_mbgsubmenu}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_41" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='Text sub:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mtextsub}" name="lab_mtextsub" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_51" style="color: black;background-color: {$lab_mtextsub}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_51" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group row">
                                    <label class="control-label col-lg-3">
                                        {l s='Hover Text sub:'}
                                    </label>
                                    <div class="col-lg-9 ">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="input-group">
                                                    <input type="text" value="{$lab_mhovertextsub}" name="lab_mhovertextsub" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_61" style="color: black;background-color: {$lab_mhovertextsub}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_61" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
								<div class="panel-footer-i">
									<button class="btn btn-default pull-right" name="submitUpdateMegamenu" type="submit"><i class="process-icon-save"></i> Save</button>
								</div>
                            </div>
                            <!-- end mega menu -->

                            


                         </form>
                        </div>
                    </div>
                <!-- end megamenu -->


        <!-- Maincontent -->
        <div class="tab-pane fade" id="maincontent">
            <div class="tab-content-ii">
                <form class="form-horizontal" action = "{$action}"  enctype="multipart/form-data" method="post">
                    <h4 class="name-tab"> <i class="icon-info"></i> Maincontent color</h4>


                    <div class="box_lab">
            <h4 class="col-md-offset-3"><i class="icon-dot-circle-o"></i> {l s='Main content'}</h4>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='background color:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_cbgcolor}" name="lab_cbgcolor" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_02" style="color: black;background-color: {$lab_cbgcolor}">
                                            <span class="mColorPickerTrigger input-group-addon" id="icp_color_02" style="cursor:pointer;" data-mcolorpicker="true">
                                                <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='color link:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ccolorlink}" name="lab_ccolorlink" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_12" style="color: black;background-color: {$lab_ccolorlink}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_12" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='color hover link:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_chovercolorlink}" name="lab_chovercolorlink" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_22" style="color: black;background-color: {$lab_chovercolorlink}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_22" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='Color text:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ccolortext}" name="lab_ccolortext" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_32" style="color: black;background-color: {$lab_ccolortext}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_32" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='Color price:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ccolorprice}" name="lab_ccolorprice" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_42" style="color: black;background-color: {$lab_ccolorprice}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_42" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='color old-price:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ccoloroldprice}" name="lab_ccoloroldprice" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_52" style="color: black;background-color: {$lab_ccoloroldprice}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_52" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='icon color:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ciconcolor}" name="lab_ciconcolor" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_62" style="color: black;background-color: {$lab_ciconcolor}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_62" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='background button:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_cbgbuttom}" name="lab_cbgbuttom" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_72" style="color: black;background-color:{$lab_cbgbuttom}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_72" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='text button:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ctextbuttom}" name="lab_ctextbuttom" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_82" style="color: black;background-color:{$lab_ctextbuttom}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_82" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='background button hover:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_cbgbuttomhover}" name="lab_cbgbuttomhover" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_92" style="color: black;background-color: {$lab_cbgbuttomhover}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_92" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='Text button hover:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_ctextbuttomhover}" name="lab_ctextbuttomhover" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_44" style="color: black;background-color: {$lab_ctextbuttomhover}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_44" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			 <div class="panel-footer-i">
                        <button class="btn btn-default pull-right" name="submitUpdateMaincontent" type="submit"><i class="process-icon-save"></i> Save</button>
                    </div>
        </div>

                   

                    </form>
                </div>
            </div>
        <!-- end main content -->


        <!-- footer -->
        <div class="tab-pane fade" id="footercolor">
            <div class="tab-content-ii">
                <form class="form-horizontal" action = "{$action}"  enctype="multipart/form-data" method="post">
                    <h4 class="name-tab"> <i class="icon-info"></i> Unlimited color</h4>
        <div class="box_lab">
            <h4 class="col-md-offset-3"><i class="icon-dot-circle-o"></i> {l s='Footer'}</h4>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='background color:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_fbgcolor}" name="lab_fbgcolor" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_03" style="color: black;background-color: {$lab_fbgcolor}">
                                            <span class="mColorPickerTrigger input-group-addon" id="icp_color_03" style="cursor:pointer;" data-mcolorpicker="true">
                                                <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='Color Text:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_fcolortext}" name="lab_fcolortext" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_13" style="color: black;background-color: {$lab_fcolortext}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_13" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='Link color:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_flinkcolor}" name="lab_flinkcolor" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_23" style="color: black;background-color:{$lab_flinkcolor}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_23" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-3">
                    {l s='link hover:'}
                </label>
                <div class="col-lg-9 ">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="input-group">
                                <input type="text" value="{$lab_flinkcolorhover}" name="lab_flinkcolorhover" class="color mColorPickerInput mColorPicker" data-hex="true" id="color_33" style="color: black;background-color:{$lab_flinkcolorhover}">
                                        <span class="mColorPickerTrigger input-group-addon" id="icp_color_33" style="cursor:pointer;" data-mcolorpicker="true">
                                            <img align="absmiddle" style="border:0;margin:0 0 0 3px" src="../img/admin/color.png"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<div class="panel-footer-i">
                        <button class="btn btn-default pull-right" name="submitUpdateFooter" type="submit"><i class="process-icon-save"></i> Save</button>
                    </div>
        </div>
                    
        </form>
                </div></div>
        <!--end footer -->
            </div>
		</div><!-- end  tab content -->
		</div>
	</div>
	</div>
</div>

