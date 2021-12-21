var $collectionHolder;
jQuery(document).ready(function() {
    jQuery('.datepicker').datepicker({dateFormat: 'dd-mm-yy'});
    // Get the container who holds the collection
    $collectionHolder = jQuery('table.stdtable tbody');
    $collectionHolder.find('.delTd').each(function() {
        addItemFormDeleteLink(jQuery(this));
    });
    $collectionHolder.find('.ordTd').each(function(index) {
        jQuery(this).html(index + 1);
    });
    $collectionHolder.data('index', $collectionHolder.find('tr.item').length);
    jQuery('#linkAdd').on('click', function(e) {
        e.preventDefault();
        addItemForm();
    });
    jQuery(document).keypress(function(e) {
        if (e.which == 43) { 
            e.preventDefault();
            if( jQuery("#popup").parents(".ui-dialog").is(":visible")===false )
                jQuery('#linkAdd').click();
        }
    });
    jQuery('.guardar').click(function() {
        if (!confirm('Confirma la registración?')) {
            return false;
        }
    });
    
   
});
function addItemForm() {
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype.replace(/items/g, index);
    jQuery('#popup').dialog({
        modal: true, autoOpen: false, title: 'Agregar Nuevo Item', width: 500,
        buttons: [{text: "Agregar", class: 'closePopup',
                click: function() { 
                    if( jQuery(this).find('[name*="cantidad"]').val()>0  )
                        jQuery(this).dialog("close"); 
                    else { alert( 'Ingrese una cantidad' ) }
                }}],
        open: function(event,ui){  
            jQuery(".chzn-select").chosen({no_results_text: "Sin resultados",search_contains: true});
            jQuery(".chzn-select").change( function(){
                jQuery.get($rutaProductoPrecio,
                    { prod: jQuery(this).val(), lista: jQuery('[name*="precioLista"]').val(), tipo: 'costo' } ,
                function( data ) {
                    if(data>0){
                        jQuery('#popup').find('[name*="precio"]').val(data);
                        jQuery('#popup').find('[name*="cantidad"]').focus(); 
                    }else{ jQuery('#popup').find('[name*="precio"]').focus(); }
                });
                
            });
            jQuery('.itemForm > div').last().find('input').keypress(function(e){
                if (e.keyCode == 13) { jQuery('.closePopup').click(); }
            });            
            jQuery('.chzn-container').mousedown();
  },
        close: function(event, ui) { addNewItem(jQuery(this));
        }
    });
    jQuery('#popup').html('');
    jQuery('#popup').html(newForm);
    jQuery('#popup').find('[name*="cantidad"]').val(1);
    jQuery('#popup').find('[name*="iva"]').val(21);
    jQuery('#popup').find('[name*="descuento"]').val(0);
    jQuery('#popup').find('[name*="recargo"]').val(0);
    jQuery('#popup').dialog('open');
}
function addNewItem(itemForm) {
    if(itemForm.find('[name*="cantidad"]').val()>0){
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype.replace(/items/g, index);
    var newId = 'item_' + index;
    jQuery('#divItems').append('<div id="' + newId + '" style="display:none;" >' + newForm + '</div>');

    jQuery('#' + newId).children().find('[name*="cm_ventasbundle_"]').each(function() {
        valor = itemForm.find('[name*="' + jQuery(this).attr('name') + '"]').val();
        jQuery(this).val(valor);
        jQuery(this).trigger('liszt:updated');
    });
    // crear td y ord
    var orden = $collectionHolder.find('.ordTd').last().html();    
    addNewTd(jQuery('#' + newId));
    // controla si se inserto tr
    if (!jQuery.isEmptyObject($collectionHolder.find('[divdata="' + newId + '"]').html())) {
        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);
        // agregar ord y del
        addItemFormDeleteLink($collectionHolder.find('.delTd').last());
        $collectionHolder.find('.ordTd').last().html(orden * 1 + 1);
        actualizaTotales();
    } else {
        jQuery('#' + newId).remove();
    }
    }
}
function addItemFormDeleteLink($itemFormTd) {
    var $removeFormA = jQuery('<a href="#" title="Quitar" tabIndex="-1"><span class="minus"></span></a>');
    $itemFormTd.append($removeFormA);
    $removeFormA.on('click', function(e) {
        var res = true;
        if ($itemFormTd.parent().find(".cantTd input").val() > 0)
            res = confirm('Desea eliminar este item?');
        if (res) {
            e.preventDefault();
            jQuery('#' + $itemFormTd.parent().attr('divdata')).remove();
            $itemFormTd.parent().remove();
            actualizaTotales();
        }
    });
}