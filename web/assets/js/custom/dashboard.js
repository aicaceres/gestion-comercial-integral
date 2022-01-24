jQuery(document).ready(function(){
	
		jQuery('#overviewselect,select.uniformselect, input:checkbox').uniform();
		
		///// DATE PICKER /////
		jQuery( "#datepickfrom, #datepickto", ".datepicker" ).datepicker();
		
		///// SLIM SCROLL /////
		jQuery('#scroll1').slimscroll({
			color: '#666',
			size: '10px',
			width: 'auto',
			height: '175px'                  
		});
		
		///// ACCORDION /////
		jQuery('#accordion').accordion({autoHeight:  false});
                
	//// CONTROL DE MENU ///
        var route = jQuery("#route").val();
        var subroute = route.split('_',3);           
        jQuery("#"+subroute[0]).addClass("current");
      /* for (var i=0; i < subroute.length; i++){
            jQuery("#"+subroute[i]).addClass("current");
        }*/
        for (var i=(subroute.length-1); i > 0 ; i--){
            var item = jQuery("#"+subroute[i]);            
            if(item.length) {                
                jQuery("#"+subroute[i]).addClass("current");
                break;
            }
        }
   
        if(subroute.length>1){
            var ul = "#"+subroute[1];
            if(route.search("extra")>=0){
                ul = "#"+subroute[2];                
            } 
            // si es parametro se usa el slug
            if(subroute[1]==='parametro'){
                var slug = jQuery("#slug").val();
                jQuery("#"+slug).addClass("current");
                ul = "#"+slug;
            } 
            //submenu
            superior = jQuery(ul).closest("ul");
            superior.parent().addClass("current");
        }

        function checkTheme(theme){
            console.log(theme);
        }
        // redireccionar cualquier ruta de parametros 
        //if( route.indexOf("parametro")>=0 ){  route='parametro';  }
        //if($("#"+route).hasClass('hidden'))  $("#"+route).removeClass('hidden');
		
	///// SWITCHING LIST FROM 3 COLUMNS TO 2 COLUMN LIST /////
	function rearrangeShortcuts() {
		if(jQuery(window).width() < 430) {
			if(jQuery('.shortcuts li.one_half').length == 0) {
				var count = 0;
				jQuery('.shortcuts li').removeAttr('class');
				jQuery('.shortcuts li').each(function(){
					jQuery(this).addClass('one_half');
					if(count%2 != 0) jQuery(this).addClass('last');
					count++;
				});	
			}
		} else {
			if(jQuery('.shortcuts li.one_half').length > 0) {
				jQuery('.shortcuts li').removeAttr('class');
			}
		}
	}
	
	rearrangeShortcuts();
	
	///// ON RESIZE WINDOW /////
	jQuery(window).resize(function(){
		rearrangeShortcuts();
	});
});
