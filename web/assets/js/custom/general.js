jQuery.noConflict();
jQuery(function($){
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '&#x3c;Ant',
        nextText: 'Sig&#x3e;',
        currentText: 'Hoy',
        monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
        'Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
        dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
        weekHeader: 'Sm',
        dateFormat: 'dd-mm-yy',
        firstDay: 0,
        isRTL: false,
        changeMonth: true,
        changeYear: true,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);
});

function checknumero(obj){    
    num = obj.val().replace(',','.');
    num = ( isNaN(num) || num==='' )  ? 0 : parseFloat(num).toFixed(3);
    obj.val( num );
    return num*1;
}
jQuery(document).ready(function(){   
   jQuery('#tabs').tabs();

   jQuery('.select2').select2();;
   
    ///// CHANGE THEME /////
    jQuery('.changetheme a').bind('mousedown', function(e) {
       // var ck = jQuery.cookie("addonstyle");
        if(e.altKey && e.ctrlKey){
            e.preventDefault();
          //  var css;
            jQuery.ajax({
                    url: 'http://colchonesmisiones/changeTheme',
                    type: 'POST',
                    async: false,
                    success: function(data) {
                      //  c = data;
                      //  css = window.link.split('.', 1) + '.' + c + '.css';
                       // jQuery('head').append('<link id="addonstyle" rel="stylesheet" href="' + css + '" type="text/css" />');
                        if(data=='default'){
                          //  jQuery('.changetheme a').removeClass('hide');
                         //   jQuery('.changetheme a.contrast').addClass('hide');
                            window.location.href='http://colchonesmisiones/';
                        }else{
                         //   jQuery('.changetheme a').addClass('hide');
                         //   jQuery('.changetheme a.contrast').removeClass('hide');
                            alert('Utilizará el tema alternativo');
                            window.location.href='http://colchonesmisiones/especial';
                        }
                        // jQuery.cookie("addonstyle", c, {path: '/'});
                    },
                    error: function() {
                        alert('Imposible cambiar de tema en este momento.');
                    }
                });
        }
    });
    
	///// SHOW/HIDE USERDATA WHEN USERINFO IS CLICKED /////
	jQuery('.userinfo').click(function(){
		if(!jQuery(this).hasClass('active')) {
			jQuery('.userinfodrop').show();
			jQuery(this).addClass('active');
		} else {
			jQuery('.userinfodrop').hide();
			jQuery(this).removeClass('active');
		}
		//remove notification box if visible
		jQuery('.notification').removeClass('active');
		jQuery('.noticontent').remove();
		
		return false;
	});

	///// SHOW/HIDE NOTIFICATION /////
	jQuery('.notification a').click(function(){
		var t = jQuery(this);
		var url = t.attr('href');
		if(!jQuery('.noticontent').is(':visible')) {
			jQuery.post(url,function(data){
				t.parent().append('<div class="noticontent">'+data+'</div>');
			});
			//this will hide user info drop down when visible
			jQuery('.userinfo').removeClass('active');
			jQuery('.userinfodrop').hide();
		} else {
			t.parent().removeClass('active');
			jQuery('.noticontent').hide();
		}
		return false;
	});

	///// SHOW/HIDE BOTH NOTIFICATION & USERINFO WHEN CLICKED OUTSIDE OF THIS ELEMENT /////
	jQuery(document).click(function(event) {
		var ud = jQuery('.userinfodrop');
		var nb = jQuery('.noticontent');
		//hide user drop menu when clicked outside of this element
		if(!jQuery(event.target).is('.userinfodrop') 
			&& !jQuery(event.target).is('.userdata') 
			&& ud.is(':visible')) {
				ud.hide();
				jQuery('.userinfo').removeClass('active');
		}
		//hide notification box when clicked outside of this element
		if(!jQuery(event.target).is('.noticontent') && nb.is(':visible')) {
			nb.remove();
			jQuery('.notification').removeClass('active');
		}
	});
	
	///// NOTIFICATION CONTENT /////
	jQuery('.notitab a').live('click', function(){
		var id = jQuery(this).attr('href');
		jQuery('.notitab li').removeClass('current'); //reset current 
		jQuery(this).parent().addClass('current');
		if(id == '#messages')   jQuery('#activities').hide();
		else                    jQuery('#messages').hide();		
		jQuery(id).show();
		return false;
	});
	
	///// SHOW/HIDE VERTICAL SUB MENU /////	
	jQuery('.vernav > ul li a, .vernav2 > ul li a').each(function(){
		var url = jQuery(this).attr('href');
		jQuery(this).click(function(){
			if(jQuery(url).length > 0) {
				if(jQuery(url).is(':visible')) {
					if(!jQuery(this).parents('div').hasClass('menucoll') &&
					   !jQuery(this).parents('div').hasClass('menucoll2'))
							jQuery(url).slideUp();
				} else {
					jQuery('.vernav ul ul, .vernav2 ul ul').each(function(){
							jQuery(this).slideUp();
					});
					if(!jQuery(this).parents('div').hasClass('menucoll') &&
					   !jQuery(this).parents('div').hasClass('menucoll2'))
							jQuery(url).slideDown();
				}
				return false;	
			}
		});
	});
	
	///// SHOW/HIDE SUB MENU WHEN MENU COLLAPSED /////
	jQuery('.menucoll > ul > li, .menucoll2 > ul > li').live('mouseenter mouseleave',function(e){
		if(e.type == 'mouseenter') {
			jQuery(this).addClass('hover');
			jQuery(this).find('ul').show();	
		} else {
			jQuery(this).removeClass('hover').find('ul').hide();	
		}
	});
	
	///// HORIZONTAL NAVIGATION (AJAX/INLINE DATA) /////	
	jQuery('.hornav a').click(function(){
		//this is only applicable when window size below 450px
		if(jQuery(this).parents('.more').length == 0)
			jQuery('.hornav li.more ul').hide();
		//remove current menu
		jQuery('.hornav li').each(function(){
			jQuery(this).removeClass('current');
		});
		jQuery(this).parent().addClass('current');	// set as current menu
		
		var url = jQuery(this).attr('href');
		if(jQuery(url).length > 0) {
			jQuery('.contentwrapper .subcontent').hide();
			jQuery(url).show();
		} else {
			jQuery.post(url, function(data){
				jQuery('#contentwrapper').html(data);
				//jQuery('.stdtable input:checkbox').uniform();	//restyling checkbox
			});
		}
		return false;
	});
	
	///// NOTIFICATION CLOSE BUTTON /////
	jQuery('.notibar .close,.notifmsg .close').click(function(){
		jQuery(this).parent().fadeOut(function(){
			jQuery(this).remove();
		});
	});
	
	///// COLLAPSED/EXPAND LEFT MENU /////
	jQuery('.togglemenu').click(function(){
		if(!jQuery(this).hasClass('togglemenu_collapsed')) {
			
			//if(jQuery('.iconmenu').hasClass('vernav')) {
			if(jQuery('.vernav').length > 0) {
				if(jQuery('.vernav').hasClass('iconmenu')) {
					jQuery('body').addClass('withmenucoll');
					jQuery('.iconmenu').addClass('menucoll');
				} else {
					jQuery('body').addClass('withmenucoll');
					jQuery('.vernav').addClass('menucoll').find('ul').hide();
				}
			} else if(jQuery('.vernav2').length > 0) {
			//} else {
				jQuery('body').addClass('withmenucoll2');
				jQuery('.iconmenu').addClass('menucoll2');
			}
			
			jQuery(this).addClass('togglemenu_collapsed');
			
			jQuery('.iconmenu > ul > li > a').each(function(){
				var label = jQuery(this).text();
				jQuery('<li><span>'+label+'</span></li>')
					.insertBefore(jQuery(this).parent().find('ul li:first-child'));
			});
		} else {
			
			//if(jQuery('.iconmenu').hasClass('vernav')) {
			if(jQuery('.vernav').length > 0) {
				if(jQuery('.vernav').hasClass('iconmenu')) {
					jQuery('body').removeClass('withmenucoll');
					jQuery('.iconmenu').removeClass('menucoll');
				} else {
					jQuery('body').removeClass('withmenucoll');
					jQuery('.vernav').removeClass('menucoll').find('ul').show();
				}
			} else if(jQuery('.vernav2').length > 0) {	
			//} else {
				jQuery('body').removeClass('withmenucoll2');
				jQuery('.iconmenu').removeClass('menucoll2');
			}
			jQuery(this).removeClass('togglemenu_collapsed');	
			
			jQuery('.iconmenu ul ul li:first-child').remove();
		}
	});

	///// RESPONSIVE /////
	if(jQuery(document).width() < 640) {
		jQuery('.togglemenu').addClass('togglemenu_collapsed');
		if(jQuery('.vernav').length > 0) {
			
			jQuery('.iconmenu').addClass('menucoll');
			jQuery('body').addClass('withmenucoll');
			jQuery('.centercontent').css({marginLeft: '56px'});
			if(jQuery('.iconmenu').length == 0) {
				jQuery('.togglemenu').removeClass('togglemenu_collapsed');
			} else {
				jQuery('.iconmenu > ul > li > a').each(function(){
					var label = jQuery(this).text();
					jQuery('<li><span>'+label+'</span></li>')
						.insertBefore(jQuery(this).parent().find('ul li:first-child'));
				});		
			}

		} else {
			
			jQuery('.iconmenu').addClass('menucoll2');
			jQuery('body').addClass('withmenucoll2');
			jQuery('.centercontent').css({marginLeft: '36px'});
			
			jQuery('.iconmenu > ul > li > a').each(function(){
				var label = jQuery(this).text();
				jQuery('<li><span>'+label+'</span></li>')
					.insertBefore(jQuery(this).parent().find('ul li:first-child'));
			});		
		}
	}
	
	
	jQuery('.searchicon').live('click',function(){
		jQuery('.searchinner').show();
	});
	
	jQuery('.searchcancel').live('click',function(){
		jQuery('.searchinner').hide();
	});
        // impresion de formularios y listados
	jQuery('.btn_print').click(function(){
            var url = jQuery(this).attr('url');
           // myWindow=window.open(url,"","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=1, resizable=no, titlebar=no,copyhistory=no, width=800")
            myWindow=window.open(url);
            myWindow.focus();
        });
        // impresion de facturas de venta
	jQuery('.btn_printcomp').click(function(){
            var url = jQuery(this).attr('url');
            LeftPosition = (screen.width) ? (screen.width - 500) / 2 : 0;
            TopPosition = (screen.height) ? (screen.height - 150) / 2 : 0;
            settings = 'height=150,width=500,top=' + TopPosition + ',left=' + LeftPosition + ',scrollbars=no,resizable=no'
            win = window.open(url, "", settings);
            win.focus();
        });
	///// ON RESIZE WINDOW /////
	jQuery(window).resize(function(){
		if(jQuery(window).width() > 640)
			jQuery('.centercontent').removeAttr('style');  
		//reposSearch();
	});        
        
    // BOTON DE ELIMINAR EN FORMULARIOS DE EDICION
    jQuery('.delete').on('click',function(){
        jConfirm('Está seguro de eliminar este registro?', 'Borrar registro', function(r) {
            if (r) {
               jQuery("form input[value='DELETE']").parent('form').submit();
                return true; 
            }
            return false;
        });
    });
        
        
 /// Cancelar edición
  /*  jQuery(document).keyup(function(e) {
        if (e.which === 27) {
            url=jQuery('a.cancelar').attr('href');
            if(url)  location.href=url;
        }  // esc   
        
    });*/
    
});