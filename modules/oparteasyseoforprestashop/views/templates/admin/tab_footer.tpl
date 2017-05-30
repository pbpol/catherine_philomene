{**
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**}
<ps-panel-footer>
            <ps-panel-footer-submit title="{l s='save settings' mod='oparteasyseoforprestashop'}" direction="right" name="saveSetting" icon="save" ps_value="1" ps_id="saveSetting"></ps-panel-footer-submit>
            <ps-panel-footer-submit title="{l s='Apply' mod='oparteasyseoforprestashop'}" direction="right" name="applySetting" icon="save" ps_value="1" ps_id="applySetting"></ps-panel-footer-submit>
        </ps-panel-footer>    
    </ps-panel>
</form>

{* js text *}
<script type="text/javascript">
    var oesfpConfirmTitle = "{l s='You will overwrite your existing meta' mod='oparteasyseoforprestashop'}";
    var oesfpConfirmQuestion = "{l s='By clicking on the continu button you will overwrite meta that are already filled' mod='oparteasyseoforprestashop'}";
    var oesfpConfirmOkBtn = "{l s='Continu' mod='oparteasyseoforprestashop'}";
    var oesfpConfirmCancelBtn = "{l s='Cancel' mod='oparteasyseoforprestashop'}";
</script>