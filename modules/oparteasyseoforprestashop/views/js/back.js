/*
* @category Prestashop
* @category Module
* @author Olivier CLEMENCE <manit4c@gmail.com>
* @copyright  Op'art
* @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
**/
$('document').ready(function () {
    $('.addTaglink').click(function (e) {
        e.preventDefault();
        var target = $($(this).prop("hash"));
        var inputVal = target.val() + ' ' + $(this).text();
        target.val(inputVal);
    });
    $('.oesfp_select_settings').change(function() {
        var selected_element = $(this).attr('data-element');
        var url = admin_module_url+'&oesfp_element_type='+selected_element+'&oesfp_select_setting='+$(this).val();
        window.location.href = url;
    });
    $('.oesfpMiniImg').fancybox();
    
    $('.oesfpSelectReloadPage').change(function() {
        oesfpLoadImgPage();        
    })
    
    $('#applySetting').click(function() {
        if($('input[name=oesfp_override]:checked','#oesfp_form').val() == '1') {
                var buttons = {};
                buttons[oesfpConfirmCancelBtn] = "";
                buttons[oesfpConfirmOkBtn] = "oesfpApplySettings";
                fancyChooseBox(oesfpConfirmQuestion,oesfpConfirmTitle, buttons );
                return false;
        }
        else
            oesfpApplySettings();
        return false;
    });

    function oesfpLoadImgPage() {
        var max_img_by_page = $('#oesfp_max_img_by_page').val();
        var id_lang = $('#oesfp_img_lang').val();
        var page_number = $('#oesfp_page_number').val();
        var empty_legend = $('#oesfp_empty_legend').val();
        var url = admin_module_url+'&oesfp_element_type=6&oesfp_id_lang='+id_lang+'&oesfp_max_img_by_page='+max_img_by_page+'&oesfp_page_number='+page_number+'&oesfp_empty_legend='+empty_legend;        
        window.location.href = url;
    }
    
    $('.oesfpInputImgLegend').focusout(function() {
        if(oesfpLastInputVal == $(this).val())
            return false;
        var id_lang = $('#oesfp_img_lang').val();
        var legend = encodeURIComponent($(this).val());
        var id_image = parseInt($(this).attr('id').replace('imgLegend_',''));
        var target = $('#oesfpSavedSpan_'+id_image);
        $.ajax({ 
            type : 'POST', 
            url : admin_module_url,
            data: 'ajax=1&id_lang='+id_lang+'&legend='+legend+'&id_image='+id_image+'&action=saveLegend',
            success : function(data){
               target.html(data); 
               target.show(250).delay(1000).hide(100);
               //console.log(data);
            }, 
            error : function(XMLHttpRequest, textStatus, errorThrown) { 
                console.log(textStatus); 
                console.log(XMLHttpRequest); console.log(errorThrown);
            }
        })
    })
    $('.oesfpInputImgLegend').focusin(function() {
        oesfpLastInputVal = $(this).val();
    })
})

function oesfpApplySettings() {
    console.log('apply');
    var btn_submit = $('#applySetting');
    btn_submit.before('<input type="hidden" name="' + btn_submit.attr("name") + '" value="1" />');
    $('#oesfp_form').submit();
}

function oesfpLoadTab( element_type) {
    var url = admin_module_url+'&oesfp_element_type='+element_type;
    window.location.href = url;
}