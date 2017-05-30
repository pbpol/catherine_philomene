{**
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<form class="form-horizontal" action="{$admin_module_url|escape:'htmlall':'UTF-8'}&oesfp_element_type={$selected_element|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" name="oesfp_form" id="oesfp_form">
<ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{l s='Load settings' mod='oparteasyseoforprestashop'}">   
            <ps-form-group name="my_select" label="{l s='Load settings' mod='oparteasyseoforprestashop'}">
                <select name="oesfp_select_settings" data-element="{$selected_element|escape:'htmlall':'UTF-8'}" class="oesfp_select_settings" label="language" >
                    <option value="0">{l s='new' mod='oparteasyseoforprestashop'}</option>
                    {foreach $saved_settings as $setting}
                        <option value="{$setting.id_oparteasyseoforprestashop_settings|escape:'htmlall':'UTF-8'}" {if isset($meta_data) && $setting.id_oparteasyseoforprestashop_settings == $meta_data['id_oparteasyseoforprestashop_settings']}selected='selected'{/if}>{$setting.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                {if isset($meta_data)}<a href="{$admin_module_url|escape:'htmlall':'UTF-8'}&oesfp_element_type={$selected_element|escape:'htmlall':'UTF-8'}&oesfp_delete_setting={$meta_data['id_oparteasyseoforprestashop_settings']|escape:'htmlall':'UTF-8'}"><img src="{$baseDir|escape:'htmlall':'UTF-8'}img/admin/delete.gif" alt="" />&nbsp; {l s='Delete this setting' mod='oparteasyseoforprestashop'}</a>{/if}
            </ps-form-group>
            <ps-input-text ps_id="oesfp_setting_name" name="oesfp_setting_name" label="{l s='Setting name' mod='oparteasyseoforprestashop'}" value="{if isset($meta_data)}{$meta_data['name']|escape:'htmlall':'UTF-8'}{/if}" required="true" hint="{l s='Choose a name for easily retrieve this setting' mod='oparteasyseoforprestashop'}"></ps-input-text>
    </ps-panel>

    <ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{l s='Choose language' mod='oparteasyseoforprestashop'}">   
            <ps-form-group name="my_select" label="{l s='Choose language' mod='oparteasyseoforprestashop'}">
                <select name="oesfp_lang" data-element="1" class="oesfp_select_lang">
                    {foreach $languages as $language}
                        <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang == $id_lang}selected='selected'{/if}>{$language.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </ps-form-group>
    </ps-panel>