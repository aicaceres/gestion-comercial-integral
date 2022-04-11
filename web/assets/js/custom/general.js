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

    $('.select2').select2({ width:'style' });
});


let validlogin=false;
// lanza popup para login de ventas
function checkLoginVentas(url_login,referer) {
    jQuery('#popup').html('');
    jQuery('#popup')
        .load(url_login, function () {
            // foco en password
            jQuery('#password').focus();
            const url_check = jQuery('#login').attr('action');
            jQuery('#login').on('submit', function (e) {
                e.preventDefault();
                data = { username: jQuery('#username').val(), password: jQuery('#password').val() };
                // validar usuario y password
                jQuery.getJSON(url_check, data, function (data) {
                    if (data.msg == 'OK') {
                        validlogin = true;
                        jQuery('#popup').dialog('close');
                        if(data.reload) {
                            window.location.reload();
                        }
                        jQuery('#ventasbundle_venta_cliente').focus();
                    } else {
                        jQuery('.loginmsg').html(data.msg);
                        jQuery('.notiflogin').removeClass('hidden');
                        jQuery('#' + data.field).focus();
                        return false;
                    }
                });
            })
        })
        .dialog({
            modal: true, autoOpen: false, title: "INGRESO A VENTAS", width: '450px', minHeight: 380,
            close: function (event, ui) {
                event.preventDefault();
                if (!validlogin) {
                    window.location.href = referer;
                }
            }
        });
    jQuery('#popup').dialog('open');
}
// lanza popup para ver listado de ventas
function partialVentasPorCobrar(url) {
    jQuery('#popup')
        .html('')
        .load(url, function () {
            jQuery('#ventasxcobrar').dataTable({
                        "bSort": false,
                        "sPaginationType": "full_numbers",
                            "oLanguage": {
                                "oPaginate": {
                                    "sFirst": "<<",
                                    "sNext": ">",
                                    "sLast": ">>",
                                    "sPrevious": "<"
                                },
                        "sLengthMenu": "Mostrar _MENU_ registros ",
                        "sZeroRecords": "Sin datos",
                        "sInfo": " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
                        "sInfoEmpty": "Sin coincidencias",
                        "sInfoFiltered": "(filtrado de _MAX_ registros)",
                                    "sSearch": "Buscar:",
                                    "sSelect": "%d seleccionados"
                    }
                });
            jQuery('#ventasxcobrar_filter input').val('#');
            jQuery('#ventasxcobrar_filter input').focus();
            /// hover de nro operacion resalta toda la fila
            jQuery(document).on('focus', '.operacion', function (e) {
                jQuery('#ventasxcobrar tbody tr').removeClass('selectedline');
                jQuery(this).parent().addClass('selectedline');
            })

        })
        .dialog({
            modal: true, autoOpen: false, title: "VENTAS PENDIENTES POR COBRAR", width: "70%",minHeight: 380,
        });
        jQuery('#popup').dialog('open');
}

// lanza popup para apertura de caja
// CAJA=1
function aperturaCajaVentas(url, cobro=false, caja=1 ) {
    let horaRefresh = null;
    data = { 'id': caja };
    jQuery('#popup').html('');
    jQuery('#popup')
        .load(url, data, function () {
            // refresca la hora en un campo fecha-hora
            horaRefresh = setInterval(function () {
                jQuery('.js-hora').html( new Date().toLocaleString().slice(9) );
            }, 1000);
            // foco en monto
            jQuery('#ventasbundle_apertura_montoApertura').focus();
            jQuery('#ir_a_cobro').val(cobro);
        })
    .dialog({
        modal: true, autoOpen: false, title: "APERTURA DE CAJA", width: '450px',
        close: function (event, ui) {
            event.preventDefault();
            clearInterval(horaRefresh);
            if( reload )
                window.location.href = referer;
        }
    });
    jQuery('#popup').dialog('open');
}
// lanza popup para cierre de caja
// CAJA=1
function cierreCajaVentas(url, reload=false, referer = "#",caja=1) {
    let horaRefresh = null;
    data = { 'id': caja };
    jQuery('#popup').html('<div class="loaders" style="width: 100%;text-align: center;margin-top: 10px;">Cargando...</div>');
    jQuery('#popup')
        .load(url, data, function () {
            // refresca la hora en un campo fecha-hora
            horaRefresh = setInterval(function () {
                jQuery('.js-hora').html( new Date().toLocaleString().slice(9) );
            }, 1000);
            // confirm para el cierre
            jQuery('#ventasbundle_cierre').on('submit', function () {
                if (!confirm('CONFIRMA EL CIERRE DE CAJA!\n\n Este movimiento no puede ser modificado!'))
                    return false;
            })
            // foco en monto
            jQuery('#ventasbundle_cierre_montoCierre').focus();
            jQuery('.js-registrar-cierre').on('click', function (e) {
                if (jQuery('#ventasbundle_cierre_montoCierre').val()) {



                    reload = true
                } else {
                    jAlert('Debe indicar la cantidad de dinero en caja.', 'Atención');
                    return false;
                }
            })
        })
    .dialog({
        modal: true, autoOpen: false, title: "CIERRE DE CAJA", width: '450px',
        buttons: [{text: "Registrar el Cierre de Caja", class: 'closePopup additem',
            click: function () {
                if (!jQuery('#ventasbundle_cierre_montoCierre').val()) {
                    jAlert('Debe indicar la cantidad de dinero en caja.', 'Atención',
                        function () {
                            jQuery('#ventasbundle_cierre_montoCierre').focus();
                        });
                    return false;
                }
                url_cierre = jQuery('#ventasbundle_cierre').attr('action');
                data = jQuery('#ventasbundle_cierre').serialize();
                jQuery.post(url_cierre, data)
                    .done(function (data) {
                        if (data == 'ERROR') {
                            jAlert('No se ha podido registrar el cierre. Intente nuevamente.');
                        } else {
                            window.open(data);
                            window.location.reload();
                        }
                    }).fail(function () {
                        alert("No se ha podido registrar el cierre. Intente nuevamente.");
                    });
                jQuery( this ).dialog( "close" )
            }}],
        close: function (event, ui) {
            event.preventDefault();
            clearInterval(horaRefresh);
        }
    });
    jQuery('#popup').dialog('open');
}

function checknumero(obj, dec=3){
    num = obj.val().replace(',','.');
    num = ( isNaN(num) || num==='' )  ? 0 : parseFloat(num).toFixed(dec);
    obj.val( num );
    return num*1;
}
// document ready
jQuery(document).ready(function ($) {

    jQuery('#tabs').tabs();

    jQuery('.select2').select2();

    $('.vuelto').on('DOMSubtreeModified', function () {
        th = $(this).parent();
        val = parseFloat($(this).html());
        th.toggleClass('red', val<0 );
        th.toggleClass('green', val>0 );
    })

// select clientes para index
    let selectClienteIndex = jQuery('#selectClienteIndex');
    let url_selectClienteIndex = selectClienteIndex.attr('url');
    selectClienteIndex.select2({
        ajax: {
        url: url_selectClienteIndex,
        type: "post",
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
            searchTerm: params.term // search term
            };
        },
        processResults: function (response) {
            return {
                results: response
            };
        },
        cache: true
        },
        minimumInputLength: 3
    }).on('change', function(){
        jQuery('#searchform').submit();
    });
// limpia select2
    jQuery('#select2clear').on('click', function (e) {
        objselect = jQuery(this).siblings('select');
        if (objselect.val()) {
            objselect.html('').trigger('change');
        }
    })

/**
CODIGO PARA AGREGAR BANCO EN CHEQUES*/
    selectBanco = $('.selectBanco');
    selectBanco.select2({
        tags: true,
        createTag: function (params) {
            var term = $.trim(params.term).toUpperCase();
            if (term === '') {
                return null;
            }
            return {
            id: term,
            text: term,
            newTag: true // add additional parameters
            }
        }
    });
    jQuery(document).on('select2:selecting', '.selectBanco',function (e) {
        var data = e.params.args.data;
        if (data.newTag) {
            if (confirm('Desea crear el banco ' + data.text + ' ?')) {
                let url = $(this).data('url');
                $.getJSON(url, { 'nombre': data.text }, function (res) {
                    var datos = {
                                id: res.id,
                                text: res.text
                            };
                    var newOption = new Option(datos.text, datos.id, true, true);
                    $(e.target).append(newOption);
                    return true;
                })
            } else {
                $(this).select2('close');
                return false;
            }
        }
    })


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