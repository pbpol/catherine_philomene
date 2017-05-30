{**
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<form class="form-horizontal" action="{$admin_module_url|escape:'htmlall':'UTF-8'}&oesfp_element_type={$selected_element|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
          <ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{l s='Choose language' mod='oparteasyseoforprestashop'}">   
            <ps-form-group name="my_select" label="{l s='Choose language' mod='oparteasyseoforprestashop'}">
                <select name="oesfp_img_lang" id="oesfp_img_lang" class="oesfp_select_lang oesfpSelectReloadPage">
                    {foreach $languages as $language}
                        <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang == $id_lang}selected='selected'{/if}>{$language.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </ps-form-group>
            <ps-form-group name="my_select_nb_prod" label="{l s='Images by page' mod='oparteasyseoforprestashop'}">
                <select name="oesfp_max_img_by_page" id="oesfp_max_img_by_page" class="oesfp_select_lang oesfpSelectReloadPage">
                    {foreach $max_by_page_options as $max_by_page_option}
                        <option value="{$max_by_page_option|escape:'htmlall':'UTF-8'}" {if $max_img_by_page == $max_by_page_option}selected='selected'{/if}>{$max_by_page_option|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </ps-form-group>
            <ps-form-group name="my_select_page_number" label="{l s='Choose a page' mod='oparteasyseoforprestashop'}">
                <select name="oesfp_page_number" id="oesfp_page_number" class="oesfp_select_lang oesfpSelectReloadPage">
                    {section name=foo start=1 loop=$img_nb_page+1}
                        <option value="{$smarty.section.foo.index|escape:'htmlall':'UTF-8'}" {if $page_number == $smarty.section.foo.index}selected='selected'{/if}>{$smarty.section.foo.index|escape:'htmlall':'UTF-8'}</option>
                    {/section}
                </select>
            </ps-form-group>
            <ps-form-group name="oesfp_empty_legend" label="{l s='Empty legend only' mod='oparteasyseoforprestashop'}">
                <select name="oesfp_empty_legend" id="oesfp_empty_legend" class="oesfp_select_lang oesfpSelectReloadPage">
                    <option value="0" {if $empty_legend == 0}selected='selected'{/if}>{l s='no' mod='oparteasyseoforprestashop'}</option>
                    <option value="1" {if $empty_legend == 1}selected='selected'{/if}>{l s='yes' mod='oparteasyseoforprestashop'}</option>
                </select>
            </ps-form-group>
    </ps-panel>
      {assign var=tabIndex value=0}          
      {foreach $meta_images as $meta_image}          
          <ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{$meta_image[0]['name']|escape:'htmlall':'UTF-8'}" class="oesfpPanelImg">
          {foreach $meta_image as $img}              
               {assign var=tabIndex value=$tabIndex+1}
              <div class="oesfpConteneurImg">
                  <a href="http://{$img['src']|escape:'htmlall':'UTF-8'}" class="oesfpMiniImg"><img src="http://{$img['src']|escape:'htmlall':'UTF-8'}" alt="" width="180"/></a>
                  <span class="oesfpSavedSpan" id="oesfpSavedSpan_{$img['id_image']|escape:'htmlall':'UTF-8'}"></span>
                  <input tabindex="{$tabIndex|escape:'htmlall':'UTF-8'}" id="imgLegend_{$img['id_image']|escape:'htmlall':'UTF-8'}" type="text" value="{$img['legend']|escape:'htmlall':'UTF-8'}" class="oesfpInputImgLegend"/>                 
              </div>
          {/foreach}
          </ps-panel>
      {/foreach}
</form>
<div id="oesfpBigImg"></div>