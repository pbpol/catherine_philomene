{**
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<h1>{l s='Easy seo for Prestashop' mod='oparteasyseoforprestashop'}</h1>
{if count($success)>0}
<ps-alert-success>
    {foreach $success as $msg}
        {$msg} {* need html *}
    {/foreach}
</ps-alert-success>
{/if}
<ps-tabs>
    <ps-tab-nav>
        <li riot-tag="ps-tab-nav-item" target="tabCategory" {if $selected_element == 1}class="active"{/if} onClick="oesfpLoadTab(1)">{l s='Category' mod='oparteasyseoforprestashop'}</li>
        <li riot-tag="ps-tab-nav-item" target="tabProducts" {if $selected_element == 2}class="active"{/if} onClick="oesfpLoadTab(2)">{l s='Products' mod='oparteasyseoforprestashop'}</li>
        <li riot-tag="ps-tab-nav-item" target="tabSuppliers" {if $selected_element == 3}class="active"{/if} onClick="oesfpLoadTab(3)">{l s='Suppliers' mod='oparteasyseoforprestashop'}</li>    
        <li riot-tag="ps-tab-nav-item" target="tabCms" {if $selected_element == 4}class="active"{/if} onClick="oesfpLoadTab(4)">{l s='Cms' mod='oparteasyseoforprestashop'}</li> 
        <li riot-tag="ps-tab-nav-item" target="tabManufacturers" {if $selected_element == 5}class="active"{/if} onClick="oesfpLoadTab(5)">{l s='Manufacturers' mod='oparteasyseoforprestashop'}</li>    
        <li riot-tag="ps-tab-nav-item" target="tabImages" {if $selected_element == 6}class="active"{/if} onClick="oesfpLoadTab(6)">{l s='Images' mod='oparteasyseoforprestashop'}</li>    
         
        <li riot-tag="ps-tab-nav-item" target="tabOthers" {if $selected_element == 9}class="active"{/if} onClick="oesfpLoadTab(9)">{l s='Others' mod='oparteasyseoforprestashop'}</li>   
        <li riot-tag="ps-tab-nav-item" target="tabHelp" {if $selected_element == 10}class="active"{/if} onClick="oesfpLoadTab(10)">{l s='Help' mod='oparteasyseoforprestashop'}</li>    
    
        {*<li riot-tag="ps-tab-nav-item" target="tabMAnufacturers" {if $selected_element == 4}class="active"{/if} onClick="oesfpLoadTab(3)">{l s='Manufacturers' mod='oparteasyseoforprestashop'}</li> *}
    </ps-tab-nav>
    <ps-tab-content>
        <div id="tabCategory" class="tab-pane{if $selected_element == 1} active{/if}">{if $selected_element == 1}{include file="$module_local_path/views/templates/admin/category.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>
        <div id="tabProducts" class="tab-pane{if $selected_element == 2} active{/if}">{if $selected_element == 2}{include file="$module_local_path/views/templates/admin/product.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>
        <div id="tabSuppliers" class="tab-pane{if $selected_element == 3} active{/if}">{if $selected_element == 3}{include file="$module_local_path/views/templates/admin/suppliers.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>        
        <div id="tabCms" class="tab-pane{if $selected_element == 4} active{/if}">{if $selected_element == 4}{include file="$module_local_path/views/templates/admin/cms.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>        
        <div id="tabManufacturers" class="tab-pane{if $selected_element == 5} active{/if}">{if $selected_element == 5}{include file="$module_local_path/views/templates/admin/manufacturers.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>        
        <div id="tabImages" class="tab-pane{if $selected_element == 6} active{/if}">{if $selected_element == 6}{include file="$module_local_path/views/templates/admin/images.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>        
    
        <div id="tabOthers" class="tab-pane{if $selected_element == 9} active{/if}">{if $selected_element == 9}{include file="$module_local_path/views/templates/admin/others.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>        
        <div id="tabOthers" class="tab-pane{if $selected_element == 10} active{/if}">{if $selected_element == 10}{include file="$module_local_path/views/templates/admin/tab_help.tpl"}{else}<img src='{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif' alt='' />{/if}</div>        
    
        {*<div id="tabManufacturers" class="tab-pane{if $selected_element == 3} active{/if}">{if $selected_element == 3}{include file="$module_local_path/views/templates/admin/manufacturers.tpl"}{/if}</div>        *}
        </ps-tab-content>
</ps-tabs>
<script type="text/javascript">
    var admin_module_url = '{$admin_module_url|escape:'javascript':'UTF-8'}';
    var baseDir = '{$baseDir|escape:'htmlall':'UTF-8'}';
</script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/back.js"></script>
