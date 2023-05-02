/*
 * 	Additional function for tables.html
 *	Written by ThemePixels
 *	http://themepixels.com/
 *
 *	Copyright (c) 2012 ThemePixels (http://themepixels.com)
 *
 *	Built for Amanda Premium Responsive Admin Template
 *  http://themeforest.net/category/site-templates/admin-templates
 */

jQuery(document).ready(function($){

	jQuery('.stdtablecb .checkall').click(function(){
		var parentTable = jQuery(this).parents('table');
		var ch = parentTable.find('tbody input[type=checkbox]');
		if(jQuery(this).is(':checked')) {

			//check all rows in table
			ch.each(function(){
				jQuery(this).attr('checked',true);
				jQuery(this).parent().addClass('checked');	//used for the custom checkbox style
				jQuery(this).parents('tr').addClass('selected');
			});

			//check both table header and footer
			parentTable.find('.checkall').each(function(){ jQuery(this).attr('checked',true); });

		} else {

			//uncheck all rows in table
			ch.each(function(){
				jQuery(this).attr('checked',false);
				jQuery(this).parent().removeClass('checked');	//used for the custom checkbox style
				jQuery(this).parents('tr').removeClass('selected');
			});

			//uncheck both table header and footer
			parentTable.find('.checkall').each(function(){ jQuery(this).attr('checked',false); });
		}
	});


	///// PERFORMS CHECK/UNCHECK BOX /////
	jQuery('.stdtablecb tbody input[type=checkbox]').click(function(){
		if(jQuery(this).is(':checked')) {
			jQuery(this).parents('tr').addClass('selected');
		} else {
			jQuery(this).parents('tr').removeClass('selected');
		}
	});

	///// DELETE SELECTED ROW IN A TABLE /////
	jQuery('.deletebutton').click(function(){
		var tb = jQuery(this).attr('title');							// get target id of table
		var sel = false;												//initialize to false as no selected row
		var ch = jQuery('#'+tb).find('tbody input[type=checkbox]');		//get each checkbox in a table

		//check if there is/are selected row in table
		ch.each(function(){
			if(jQuery(this).is(':checked')) {
				sel = true;												//set to true if there is/are selected row
				jQuery(this).parents('tr').fadeOut(function(){
					jQuery(this).remove();								//remove row when animation is finished
				});
			}
		});

		if(!sel) alert('No data selected');								//alert to no data selected
	});

	///// DELETE INDIVIDUAL ROW IN A TABLE /////
	/*jQuery('.stdtable a. ').click(function(){
		var c = confirm('Continue delete?');
		if(c) jQuery(this).parents('tr').fadeOut(function(){
			jQuery(this).remove();
		});
		return false;
	});*/

    jQuery('.stdtable .delete').live('click', function() {
        var p = jQuery(this).parents('tr');
        var url = jQuery(this).attr('url');
        jConfirm('Está seguro de eliminar este registro?', 'Borrar registro', function(r) {
            if (r) {
                jQuery.ajax({
                    url: url,
                    async:true,
                    type: "POST",
                    success: function(data) {
                        if (data==='"OK"') {
                            if (p.next().hasClass('togglerow'))
                                p.next().remove();
                            p.fadeOut(function() {
                                jQuery(this).remove();
                            });
                            window.location.reload();
                        }else alert(data);
                    }, error: function() {
                        alert('No se puede realizar la operación en este momento');
                    }
                });
            }
        });
        return false;
    });

   /* jQuery('#editform').live('submit',function(e){
            e.preventDefault();
            var url = jQuery(this).attr("action");
            jQuery.ajax({
                type: "POST",
                url: url,
                async:true,
                data: jQuery(this).serialize(),
                dataType: "json",
                success: function(data){
                    alert(data);
                },error: function() {
                        alert('No se puede realizar la operación en este momento');
                        //loading.hide();
                    }
            });

*
        }); */

        ///// QUICK VIEW UPDATE BUTTON /////
	/*jQuery('form.editar').live('submit',function(){
		var loading = jQuery(this).parent().find('.loading');
                loading.show();
                jQuery.ajax({
                    url: jQuery(this).attr('url'),
                    dataType: 'json',
                    type: 'POST',
                    data: jQuery('.quickform').serialize(),
                    success: function(data){
                        alert(data);
                    },
                    error: function() {
                        alert('No se puede realizar la operación en este momento');
                        loading.hide();
                    }
                });

	});*/

	///// GET DATA FROM THE SERVER AND INJECT IT RIGHT NEXT TO THE ROW SELECTED /////
	jQuery('.stdtable a.toggle').click(function(){
		//this is to hide current open quick view in a table
		jQuery(this).parents('table').find('tr').each(function(){
			jQuery(this).removeClass('hiderow');
			if(jQuery(this).hasClass('togglerow'))
				jQuery(this).remove();
		});

		var parentRow = jQuery(this).parents('tr');
		var numcols = parentRow.find('td').length + 1;				//get the number of columns in a table. Added 1 for new row to be inserted
		var url = jQuery(this).attr('href');

		//this will insert a new row next to this element's row parent
		parentRow.after('<tr class="togglerow"><td colspan="'+numcols+'"><div class="toggledata"></div></td></tr>');

		var toggleData = parentRow.next().find('.toggledata');

		parentRow.next().hide();

		//get data from server
		jQuery.post(url,function(data){
			toggleData.append(data);						//inject data read from server
			parentRow.next().fadeIn();						//show inserted new row
			parentRow.addClass('hiderow');					//hide this row to look like replacing the newly inserted row
			jQuery('input,select').uniform();
		});

		return false;
	});

	///// REMOVE TOGGLED QUICK VIEW WHEN CLICKING SUBMIT/CANCEL BUTTON /////
	jQuery('.toggledata button.cancel, .toggledata button.submit').live('click',function(){
		jQuery(this).parents('.toggledata').animate({height: 0},200, function(){
			jQuery(this).parents('tr').prev().removeClass('hiderow');
			jQuery(this).parents('tr').remove();
		});
		return false;
	});
        jQuery('#pedidos').dataTable({
            "bPaginate": false,
            "bInfo": false,
            "bSearchable": true,
            "oLanguage": {"sZeroRecords": "Sin datos",
                "sInfoEmpty": "Sin coincidencias",
                "sSearch": "Buscar:"
            }
        }); 

	jQuery('#dyntable').dataTable({
		"sPaginationType": "full_numbers"
	});

	jQuery('#dyntable2,#dyntable3').dataTable({
                "bAutoWidth": false,
                "bRetrieve" : true,
                "columnDefs": [ {
                    "targets"  : 'nosort',
                    "orderable": false
                  }],
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

	///// TRANSFORM CHECKBOX AND RADIO BOX USING UNIFORM PLUGIN /////
	//jQuery('input:checkbox,input:radio').uniform();


});