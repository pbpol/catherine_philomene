{**
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}


<ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{l s='Automatic update' mod='oparteasyseoforprestashop'}">
    {l s='To create an automatic update you have to configure a cron using this url' mod='oparteasyseoforprestashop'}:<br />
    <strong>{$cronUrl|escape:'htmlall':'UTF-8'}</strong><br />
    {l s='If you don\'t know what a cron is, please read the documentation' mod='oparteasyseoforprestashop'} 
</ps-panel>

<ps-panel icon="icon-cogs" img="../img/t/AdminBackup.gif" header="{l s='Help' mod='oparteasyseoforprestashop'}">
    {l s='Documentation' mod='oparteasyseoforprestashop'} <a href="{$moduledir|escape:'htmlall':'UTF-8'}readme_fr.pdf" target="blank">{l s='in french' mod='oparteasyseoforprestashop'}</a>, <a href="{$moduledir|escape:'htmlall':'UTF-8'}readme_en.pdf" target="blank">{l s='in english' mod='oparteasyseoforprestashop'}</a>
</ps-panel>