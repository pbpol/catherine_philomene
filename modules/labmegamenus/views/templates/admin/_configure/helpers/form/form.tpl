{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}


{block name="input"}
    {if $input.type == 'link_choice'}
	    <div class="row">
	    	<div class="col-lg-1">
	    		<h4 style="margin-top:5px;">{l s='Change position' mod='labmegamenus'}</h4>
                <a href="#" id="menuOrderUp" class="btn btn-default" style="font-size:20px;display:block;"><i class="icon-chevron-up"></i></a><br/>
                <a href="#" id="menuOrderDown" class="btn btn-default" style="font-size:20px;display:block;"><i class="icon-chevron-down"></i></a><br/>
	    	</div>
	    	<div class="col-lg-4">
	    		<h4 style="margin-top:5px;">{l s='Selected items' mod='labmegamenus'}</h4>
	    		{$selected_links}
	    	</div>
	    	<div class="col-lg-4">
	    		<h4 style="margin-top:5px;">{l s='Available items' mod='labmegamenus'}</h4>
	    		{$choices}
	    	</div>
	    	
	    </div>
	    <br/>
	    <div class="row">
	    	<div class="col-lg-1"></div>
	    	<div class="col-lg-4"><a href="#" id="removeItem" class="btn btn-default"><i class="icon-arrow-right"></i> {l s='Remove' mod='labmegamenus'}</a></div>
	    	<div class="col-lg-4"><a href="#" id="addItem" class="btn btn-default"><i class="icon-arrow-left"></i> {l s='Add' mod='labmegamenus'}</a></div>
	    </div>
    {/if}
    {if $input.type == 'listhost'}
        <div class="row">
            <div class="col-lg-6">
                <select id="list_cate" class=" fixed-width-xl" multiple="multiple" name ="cate_hot[]">
                    {$cate_hot}
                </select>
            </div>
        </div>
    {/if}
	{if $input.type =='listnew' }
        <div class="row">
            <div class="col-lg-6">
                <select id="list_cate" class=" fixed-width-xl" multiple="multiple" name ="cate_new[]">
                    {$cate_new}
                </select>
            </div>
        </div>

    {/if}
		{$smarty.block.parent}
{/block}
