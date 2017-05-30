{**
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
{include file="$module_local_path/views/templates/admin/tab_header.tpl"}

    <ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{l s='Variable settings' mod='oparteasyseoforprestashop'}">
        <ps-form-group name="my_select" label="{l s='Category' mod='oparteasyseoforprestashop'}" help="{l s='You can apply this settings for products included in selected categories only' mod='oparteasyseoforprestashop'}">
            <select name="oesfp_selected_category[]" multiple="multiple" class="oesfp_selected_category">{$select_category_options} {*need html*}</select>
        </ps-form-group>
        <ps-input-text ps_id="oesfp_prod_title" name="oesfp_title" label="Meta title" help="{l s='You can use link below to add dynamique data' mod='oparteasyseoforprestashop'}"  value="{if isset($meta_data)}{$meta_data['meta_title']|escape:'htmlall':'UTF-8'}{/if}" required="true"></ps-input-text>
        <ps-form-group>
           <a href="#oesfp_prod_title" class="addTaglink">[NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[DESCRIPTION_SHORT]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[DESCRIPTION]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[ID_PRODUCT]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[REFERENCE]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[PRICE_HT]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[PRICE_TTC]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[EAN13]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[MANUFACTURER_NAME]</a> &nbsp;  &nbsp;            
           <a href="#oesfp_prod_title" class="addTaglink">[SUPPLIER_NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[CATEGORY_NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[CATEGORY_PARENT_NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_title" class="addTaglink">[SHOP_NAME]</a>
        </ps-form-group>
        <ps-input-text ps_id="oesfp_prod_desc" name="oesfp_desc" label="Meta description" help="{l s='You can use link below to add dynamique data' mod='oparteasyseoforprestashop'}"  value="{if isset($meta_data)}{$meta_data['meta_desc']|escape:'htmlall':'UTF-8'}{/if}" required="true"></ps-input-text>
        <ps-form-group>
           <a href="#oesfp_prod_desc" class="addTaglink">[NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[DESCRIPTION_SHORT]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[DESCRIPTION]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[ID_PRODUCT]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[REFERENCE]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[PRICE_HT]</a> &nbsp;  &nbsp;  
           <a href="#oesfp_prod_desc" class="addTaglink">[PRICE_TTC]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[EAN13]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[MANUFACTURER_NAME]</a> &nbsp;  &nbsp;            
           <a href="#oesfp_prod_desc" class="addTaglink">[SUPPLIER_NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[CATEGORY_NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[CATEGORY_PARENT_NAME]</a> &nbsp;  &nbsp; 
           <a href="#oesfp_prod_desc" class="addTaglink">[SHOP_NAME]</a>
        </ps-form-group>
        <ps-switch label="{l s='Override product meta that are already filled ?' mod='oparteasyseoforprestashop'}" help="{l s='If you checked yes to this field, pages with meta data already filled will be overrited. Go back will not be possible.' mod='oparteasyseoforprestashop'}" name="oesfp_override" yes="yes" no="no" active="{if isset($meta_data.override_meta) && $meta_data.override_meta}true{else}false{/if}"></ps-switch>      
        <ps-switch label="{l s='Automatic update is allowed ?' mod='oparteasyseoforprestashop'}" help="{l s='If you checked yes to this field, this settings will automatically applied.'  mod='oparteasyseoforprestashop'} <br /> <a href='#tabHelp' onclick='oesfpLoadTab(10)'>{l s='Read the help section to learn how create an automatic update' mod='oparteasyseoforprestashop'}</a>" name="oesfp_automaticupdate" yes="yes" no="no" active="{if isset($meta_data.automatic_update) && $meta_data.automatic_update}true{else}false{/if}"></ps-switch>      
        
        {include file="$module_local_path/views/templates/admin/tab_footer.tpl"}

