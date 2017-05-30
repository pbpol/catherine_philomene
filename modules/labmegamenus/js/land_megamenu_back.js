var VNLAB = {
	init:function(){
		VNLAB.AccTabTheme();
	},
	AccTabTheme:function(){
		jQuery(".square-button").click(function(){
			var _this=this;
			var objID = jQuery(this).closest("div.tool-class-admin").attr("id");
			  jQuery( "#"+objID+" .box_lab" ).slideToggle( "fast", function() {
			    if(jQuery(this).is(":visible"))
				  {
					var itext = '<i class="fa fa-minus-square"></i>';
					jQuery(_this).find('i').remove();
				  }     
				else
				  {
					var itext = '<i class="fa fa-plus-square"></i>';
					jQuery(_this).find('i').remove();
				  }
				jQuery(_this).html(itext);
			  });
		})
	}
}

var MEGAMENU = {
  init:function(){
    MEGAMENU.AdminForm();
    MEGAMENU.AddIconArrow();
  },
  AdminForm:function(){
    $('#menuOrderUp').click(function(e){
        e.preventDefault();
        MEGAMENU.AdminMove(true);
    });
    $('#menuOrderDown').click(function(e){
        e.preventDefault();
        MEGAMENU.AdminMove();
    });
    $("#items").closest('form').on('submit', function(e) {
        $("#items option").prop('selected', true);
    });
    $("#addItem").click(function(e){
    	e.preventDefault();
    	MEGAMENU.AddminAdd()
    });
    $("#availableItems").dblclick(function(e){
    	e.preventDefault();
    	MEGAMENU.AddminAdd()
    });
    $("#removeItem").click(function(e){
    	e.preventDefault();
    	MEGAMENU.AdminRemove()
    });
    $("#items").dblclick(function(e){
    	e.preventDefault();
    	MEGAMENU.AdminRemove()
    });
  },

  AddminAdd:function()
    {
        $("#availableItems option:selected").each(function(i){
            var val = $(this).val();
            var text = $(this).text();
            text = text.replace(/(^\s*)|(\s*$)/gi,"");
            if (val == "PRODUCT")
            {
                val = prompt('Indicate the ID number for the product');
                if (val == null || val == "" || isNaN(val))
                    return;
                text = 'Product ID #'+val;
                val = "PRD"+val;
            }

            $("#items").append('<option value="'+val+'" selected="selected">'+text+'</option>');
        });
        MEGAMENU.AdminSerialize();
        return false;
    },
    AdminRemove:function()
    {
        $("#items option:selected").each(function(i){
            $(this).remove();
        });
        MEGAMENU.AdminSerialize();
        return false;
    },
    AdminSerialize:function ()
    {
        var options = "";
        $("#items option").each(function(i){
            options += $(this).val()+",";
        });
        $("#itemsInput").val(options.substr(0, options.length - 1));
    },
    AdminMove:function(up)
    {
        var tomove = $('#items option:selected');
        if (tomove.length >1)
        {
            alert('Please select just one item');
            return false;
        }
        if (up)
            tomove.prev().insertAfter(tomove);
        else
            tomove.next().insertBefore(tomove);
        MEGAMENU.AdminSerialize();
        return false;
    },
    AddIconArrow:function(){
    	var square = '<a href="#" class="square-button-module"><i class="fa fa-minus-square"></i></a>';
    	var squareClsoe = '<i class="fa fa-minus-square"></i>';
    	var squareOpen = '<i class="fa fa-plus-square"></i>';
    	jQuery(".defaultForm .panel-heading").append(square);
    	jQuery(".square-button-module").click(function(e){
    		e.preventDefault();
    		var objID = jQuery(this).closest("div.panel").attr("id");
    		jQuery( "#"+objID+" > .form-wrapper" ).slideToggle( "fast", function() {
    			jQuery("#"+objID+" > .panel-heading").find('.square-button-module > i').remove();
			    if(jQuery(this).is(":visible"))
				  {
				  	jQuery("#"+objID+" > .panel-heading > .square-button-module").append(squareClsoe);
				  	jQuery("#"+objID+" .panel-footer").show('fast');
				  }     
				else
				  {
				  	jQuery("#"+objID+" > .panel-heading > .square-button-module").append(squareOpen);
				  	jQuery("#"+objID+" .panel-footer").hide('fast');
				  }
			});
    	});
    }
};


jQuery(document).ready(function(){
	VNLAB.init();
	MEGAMENU.init();
});