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
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !isset($content_only) || !$content_only}
					</div><!-- #center_column -->
					{if isset($right_column_size) && !empty($right_column_size)}
						<div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
					{/if}
					</div><!-- .row -->
				</div><!-- #columns -->
			</div><!-- .columns-container -->
			
			{if Hook::exec(manufacturer)}
				<div class="labmanufacturer blockPosition">
					<div class="container">
						{hook h="manufacturer"}
					</div>
				</div>
			{/if}
			{if isset($HOOK_FOOTER)}
				<!-- Footer -->
					<div class="footer-container">
						<footer id="footer">
							<div class="topFooter">
								<div class="container">
									<div class="row">
										{if Hook::exec('blockFooter1')}
											{hook h="blockFooter1"}
										{/if}
										{if Hook::exec('blockFooter2')}
											<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
												{hook h="blockFooter2"}
											</div>
										{/if}
										
									</div>
								</div>
							</div>
							{if Hook::exec('blockFooter3')}
								<div class="bottomFooter">
									<div class="container">
										<div class="row">
											{hook h="blockFooter3"}
										</div>
									</div>
								</div>
							{/if}
							
						</footer>
				</div><!-- #footer -->
			{/if}
		 </div> <!-- #page -->
{/if}
{hook h='labscroll'}
{hook h='labpopup'}

{if $comparator_max_item}
    <div id="compare_message" class="dialog_message" style="display:none;">
        <div class="clearfix mar_b10">
            <div id="compare_pro_img" class="fl dialog_pro_img">
            </div>
            <div id="compare_pro_info" class="dialog_pro_info">
                <div id="compare_pro_title" class="dialog_pro_title"></div>
            </div>
        </div>
        <p id="compare_add_success" class="success hidden">{l s='has been added to compare.'}</p>
        <p id="compare_remove_success" class="success hidden">{l s='has been removed from compare.'}</p>
        <p id="compare_error" class="warning hidden"></p>
        <div class="dialog_action clearfix">
            <a id="compare_continue" class="fl button" href="javascript:;" rel="nofollow">{l s='Continue shopping'}</a>
            <a class="fr button" href="{$link->getPageLink('products-comparison')|escape:'htmlall':'UTF-8'}" title="{l s='Compare'}" rel="nofollow">{l s='Compare'}</a>
        </div>
    </div>
    <script type="text/javascript">
        // <![CDATA[
        var st_compare_min_item = '{l s='Please select at least one product' js=1}';
        var st_compare_max_item = "{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}";
        //]]>
    </script>
{/if}
{include file="$tpl_dir./global.tpl"}
	</body>
{if $page_name =='index' || $page_name =='product'}
	{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
	{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
	{addJsDef comparator_max_item=$comparator_max_item}
	{addJsDef comparedProductsIds=$compared_products}
{/if}
<script type="text/javascript">
	$(document).ready(function(){
		$(function () {
			$("[data-toggle='tooltip']").tooltip();
		});
		$(window).scroll(function() {    
			var scroll = $(window).scrollTop();
			if (scroll < 200) {
			 $(".labmegamenu").removeClass("scroll-menu");
			}else{
			 $(".labmegamenu").addClass("scroll-menu");
			}
		});
	});
</script>

</html>