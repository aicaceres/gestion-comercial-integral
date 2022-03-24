jQuery(function ($) {
    const esPresupuesto = $(location).attr('pathname').includes('presupuesto');
    $(window).on('load', function () {
        // si la pantalla es chica expandir
        if ( $('#contentwrapper').width() < 1000) {
            $('.togglemenu').click();
        }
        // refresca la hora en un campo fecha-hora
        horaRefresh = setInterval(function () {
            $('.js-hora').html( new Date().toLocaleString().slice(9) );
        }, 1000);

        $('.select2').select2({ width:'style' });

        $(document).on('keydown', (e) => { detectarControles(e); })

        cliente = $('.form-horizontal').find('[id*="_cliente"]');
        url_cliente_autocomplete = cliente.attr('url_autocomplete')
        cliente.select2({
            ajax: {
            url: url_cliente_autocomplete,
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
        }).on('change', function() {
            id = $(this).val();
            urlDatosCliente = $(this).attr('url_datos');
            $.getJSON( urlDatosCliente , {'id': id}).done(function(data){
                // actualizar partial datos
                if (data) {
                    $('#categoriaIva').val( data.categoriaIva );
                    // ocultar iva e iibb en resumen
                    $('#ivaTd, #iibbTd').hide();
                    if ($('.datos-cf').length > 0) {
                        if( data.esConsumidorFinal ){
                            $('.datos-cliente').hide();
                            $('.datos-cf').show();
                            $('[id*="_nombreCliente"]').attr('required',true);
                            $('[id*="_nombreCliente"]').focus();
                        }else{
                            $('.datos-cf').hide();
                            $('[id*="_nombreCliente"]').attr('required', false);
                            $('[id*="_nombreCliente"],[id*="_nroDocumentoCliente"],[id*="_tipoDocumentoCliente"]').val('').trigger('change');
                            $('.datos-cliente').html(data.partial);
                            $('.datos-cliente').show();
                        }
                    } else {
                        // ventas no tiene datos de cf
                        $('.datos-cliente').html(data.partial);
                        $('[id*="_transporte"]').val(data.transporte);
                    }
                    $('[id*="_precioLista"]').val(data.listaprecio);
                    $('[id*="_formaPago"]').val(data.formapago);
                    $('[id*="_formaPago"]').change();
                     // mostrar iva e iibb si corresponde
                    if( !esPresupuesto){
                        if (data.categoriaIva == 'M' || data.categoriaIva == 'I') {
                            $('#ivaTd').show();
                            if( data.categoriaIva == 'I'){
                                $('#iibbTd').show();
                            }
                        }
                    }
                    color = (data.cuitValido) ? '#666666' : 'orangered';
                    $('.cuitcliente').css('color',color);
                }
            });
        });

        // al modificar forma de pago actualizar datos del partial
        $('[id*="_formaPago"]').on('change', function () {
            $('.datos-formapago').html('');
            id = $(this).val();
            url_datos = $(this).attr('url_datos');
            $.get(url_datos, { 'id': id }).done(function (data) {
                // actualizar datos
                if(data){
                    $('.datos-formapago').html(data);
                    descuentoRecargo = $('#porcentajeRecargo').val();
                    $('.descuentoRecargo').text( descuentoRecargo )
                    $('[id*="_descuentoRecargo"]').val( descuentoRecargo )
                    actualizaTotales();
                }
            });
        });

        $('[id*="_moneda"]').on('change',function(){
            label = $(this).prev('label');
            label.find('small').remove();
            id = $(this).val();
            url_datos = $(this).attr('url_datos');
            $.getJSON( url_datos , {'id': id}).done(function(data){
                // actualizar datos
                if(data){
                    span = $('<small></small>').html(data.partial);
                    label.append(span);
                    $('.simbolo').html(data.simbolo);
                    $('[id*="_cotizacion"]').val( data.cotizacion );
                    actualizaTotales();
                }
            });
        });
        $('[id*="_moneda"]').change();

        // al presionar ctrl+enter abrir popup
        $(document).on('click change keydown',
            '[id*="_cliente"], [id*="_formaPago"],[name*="producto"]', function(e) {
            if (e.keyCode == 13 && e.ctrlKey ) {
                e.preventDefault();
                openModal($(this))
            }
        });
        // en buscar abrir popup correspondiente
        $(document).on('click','.btn_search',function(e) {
            obj = $(this).parent().find('select');
            e.preventDefault();
            openModal(obj)
        })

        $('#linkAdd').on('click', function(e) {
            e.preventDefault();
            addNewItem();
            e.stopPropagation();
        });

        // Get the container who holds the collection
        $collectionHolder = $('table.detalle tbody');
        $collectionHolder.find('.delTd').each(function() {
            addItemFormDeleteLink($(this));
        });
        $collectionHolder.find('.ordTd').each(function(i) {
            $(this).html(i + 1);
        });

        cliente.select2('focus');
    });
// funciones

    function openModal(obj){
        const url = obj.attr('url');
        const fnc = obj.attr('fnc')+'(obj)';
        const title = obj.attr('mtitle');
        $('#popup')
            .html('<div class="loaders" style="width: 100%;text-align: center;margin-top: 10px;">Cargando Datos...</div>')
            .load( url , function(){
                eval( fnc )
            })
            .dialog({
                modal: true, autoOpen: true, title: title, width: '40%', minHeight: 400,
                close: function() {
                    // volver focus al control
                    $(obj).focus();
                },
            });
    }

    // CLIENTES
    function openModalCliente() {
        url_list = cliente.attr('url_list');
        var oTable = $('#clientes_table').dataTable({
                    "columnDefs": [
                        // These are the column name variables that will be sent to the server
                        { "name": "nombre", "targets": 0 },
                        { "name": "cuit",  "targets": 1 },
                        { "targets"  : 'nosort', "orderable": false }
                    ],
                    "rowCallback": function (row, data) {
                        // seleccionar on click
                        $(row).find('a').on('click', function(){
                            var data = {
                                id: $(this).data('id'),
                                text: $(this).text()
                            };
                            var newOption = new Option(data.text, data.id, true, true);
                            cliente.append(newOption).trigger('change');
                            $('#popup').dialog("destroy");
                            cliente.select2('focus');
                        })
                    },
                    // Server-side parameters
                    "processing": true,
                    "serverSide": true,
                    // Ajax call
                    "ajax": {
                        "url": url_list,
                        "type": "POST"
                    },
                    // Classic DataTables parameters
                    "bPaginate" : true,
                    "bInfo" : true,
                    "bSearchable": true,
                    "bLengthChange": true,
                    "pageLength":10,
                    "order": [[0, 'asc']],
                    "sPaginationType": "full_numbers",
                    "oLanguage": {
                        "oPaginate": {
                            "sFirst": "<<",
                            "sNext": ">",
                            "sLast": ">>",
                            "sPrevious": "<"
                        },
                        "sProcessing": "Cargando...",
                        "sLengthMenu": "Mostrar _MENU_ registros ",
                        "sZeroRecords": "Sin datos",
                        "sInfo": " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
                        "sInfoEmpty": "Sin coincidencias",
                        "sInfoFiltered": "(filtrado de _MAX_ registros)",
                        "sSearch": "Buscar:"
                    }
                });
        // focus en buscar
        $('#clientes_table_filter input').focus();
    }

    // FORMAS DE PAGO
    function openModalFormaPago(){
        var fTable= $('#formapago_table').dataTable({
                    "bAutoWidth": false,
                    "bRetrieve" : true,
                    "columnDefs": [ {
                        "targets"  : 'nosort',
                        "orderable": false
                    }],
                    "rowCallback": function (row, data) {
                        // seleccionar on click
                        $(row).find('a').on('click', function () {
                            formaPago = $('[id*="_formaPago"]');
                            formaPago.val($(this).data('id'))
                            $('#popup').dialog("destroy");
                            formaPago.change();
                            formaPago.focus();
                        })
                    },
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
        // focus en buscar
        $('#formapago_table_filter input').focus();
    }

    // PRODUCTOS
function openModalProducto(obj){
    const listaprecio = $('[id*="_precioLista"]').val();
    const deposito = $('[id*="_deposito"]').val();
    const cotizacion = $('[id*="_cotizacion"]').val();
    const categoriaIva = $('#categoriaIva').val();
    const url_list = obj.attr('url_list');

    console.log(esPresupuesto)
    var oTable = $('#productos_table').dataTable({
                "columnDefs": [
                    // These are the column name variables that will be sent to the server
                    { "name": "nombre", "targets": 0 },
                    { "name": "codigo",  "targets": 1 },
                    { "name": "precio",  "targets": 2 },
                    { "name": "stock",  "targets": 3 },
                    { "targets"  : 'nosort', "orderable": false }
                ],
                "rowCallback": function (row, data) {
                    // registrar seleccion
                    $(row).find('td:nth-child(3n),td:nth-child(4n)').addClass('alignright');
                    $(row).find('a').on('click', function(e){
                        e.preventDefault();
                        var data = {
                            id: $(this).data('id'),
                            text: $(this).text()
                        };
                        var newOption = new Option(data.text, data.id, true, true);
                        obj.append(newOption).trigger('change');
                        $('#popup').dialog("destroy");
                        obj.select2('focus');
                    })
                 },
                // Server-side parameters
                "processing": true,
                "serverSide": true,
                // Ajax call
                "ajax": {
                    "url": url_list,
                    "type": "POST",
                    "data" : { 'listaprecio' : listaprecio,
                                'deposito' : deposito,
                                'cotizacion': cotizacion,
                                'categoriaIva' : categoriaIva,
                                'esPresupuesto' : esPresupuesto,
                            },
                },
                // Classic DataTables parameters
                "bPaginate" : true,
                "bInfo" : true,
                "bSearchable": true,
                "bLengthChange": true,
                "pageLength":10,
                "order": [[0, 'asc']],
                "sPaginationType": "full_numbers",
                "oLanguage": {
                    "oPaginate": {
                        "sFirst": "<<",
                        "sNext": ">",
                        "sLast": ">>",
                        "sPrevious": "<"
                    },
                    "sProcessing": "Cargando...",
                    "sLengthMenu": "Mostrar _MENU_ registros ",
                    "sZeroRecords": "Sin datos",
                    "sInfo": " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
                    "sInfoEmpty": "Sin coincidencias",
                    "sInfoFiltered": "(filtrado de _MAX_ registros)",
                    "sSearch": "Buscar:"
                }
            });
        // cambiar simbolo en columna precio
        oTable.find('.simbolo').text( $('#simbolo').val() );
        // focus en buscar
        $('#productos_table_filter input').focus();
    }

    // funciones personalizas para el formulario
 function addNewItem() {
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype.replace(/items/g, index);
    $collectionHolder.append(newForm);
    $collectionHolder.data('index', index + 1);
    addItemFormDeleteLink($collectionHolder.find('.delTd').last());
    $collectionHolder.find('.ordTd').last().html($collectionHolder.data('index'));
    $collectionHolder.find('.cantTd input').last().val(1);

    $('input').on('focus',function(){ $(this).select(); });
    $('.cantTd input').change(function(){
        if(isNaN( parseFloat($(this).val()) )) $(this).val(0);  actualizaTotales();
    });

    productolast = $('[name*="[producto]"]').last();
    url_producto_autocomplete = productolast.attr('url_autocomplete')
    productolast.select2({
            ajax: {
            url: url_producto_autocomplete,
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
        }).on('change', function() {
            obj = $(this);
            var data = {
                id: $(this).val(),
                listaprecio: $('[id*="_precioLista"]').val(),
            };
            urlDatosProducto = $(this).attr('url_datos');
            $.getJSON( urlDatosProducto , data).done(function(data){
                // actualizar datos
                objprecio = obj.parent().siblings('.precTd');
                objprecio.find('[id*="_precio"]').val( data.precio );
                objprecio.find('[id*="_alicuota"]').val( data.alicuota );
                actualizaTotales();
            });
        });
        productolast.select2('focus');

    }

    function addItemFormDeleteLink($itemFormTd) {
        var $removeFormA = jQuery('<a href="#" class="delItem" title="Quitar"><span class="del-item-button">-</span></a>');
        $itemFormTd.append($removeFormA);
        $removeFormA.on('click', function(e) {
            var res = true;
            if ($itemFormTd.parent().find(".cantTd input").val() > 0)
                res = confirm('Desea eliminar este item?');
            if (res) {
                e.preventDefault();
                $itemFormTd.parent().remove();
                actualizaTotales();
            }
        });
        $removeFormA.on('blur', function(e) {
            $('#linkAdd').focus();
        });
    }

    function actualizaTotales(){
        let iva = iibb = descrec = 0;
        let subTotal = totalIVA = totalIIBB = 0;
        const cotizacion = parseFloat($('[id*="_cotizacion"]').val());
        const categoriaIva = $('#categoriaIva').val();
        const porcentaje = checknumero( $('[id*="_descuentoRecargo"]') ) ;
        $("tr.item").each(function(){
            let item = $(this);
            const cant = checknumero(item.find('.cantTd input'));
            let precio = checknumero( item.find('[id*="_precio"]') );
            let alicuota = checknumero(item.find('[id*="_alicuota"]'));
            if (!esPresupuesto) {
                if( categoriaIva == 'I' || categoriaIva == 'M'  ){
                    // aplicar dto para calcular el iva
                    dto = precio * (porcentaje/100)
                    iva = (precio + dto) * (alicuota/100);
                    if( categoriaIva == 'I' ){
                        iibb = (precio + dto) * 0.035;
                    }
                    dtoTot = (dto*cant) / cotizacion;
                    descrec += dtoTot;
                }else{
                    // precio + iva
                    precio = precio * ( 1 + (alicuota/100));
                }
            }
            // calcular la cotización si es distinta a 1
            precUnit = precio / cotizacion;
            precTot = (precio * cant) / cotizacion;
            item.find('.precTd span').html( precUnit.toFixed(3) );
            item.find('.itmSubtotalTd').text(precTot.toFixed(3));
            // totalizar
            subTotal += (precio * cant);
            totalIVA += (iva * cant);
            totalIIBB += ( iibb * cant );
        });
        subTotalResumen = subTotal/cotizacion;
        totalIvaResumen = totalIVA/cotizacion;
        totalIibbResumen = totalIIBB/cotizacion;
        $('#subtotalTh').html( subTotalResumen.toFixed(3));
        $('#importeSubtotal').html(subTotalResumen.toFixed(3));

        if( (categoriaIva != 'I' && categoriaIva != 'M') || esPresupuesto ){
            descrec = subTotalResumen * (porcentaje/100);
        }
        const totalgral = subTotalResumen + descrec + totalIvaResumen + totalIibbResumen;
        $('#importeRecargo').text(descrec.toFixed(3));
        $('#importeTotal').text(totalgral.toFixed(3));
        // iva e iibb
        $('#importeIVA').text( totalIvaResumen.toFixed(3));
        $('#importeIIBB').text( totalIibbResumen.toFixed(3));

        $collectionHolder.find('.ordTd').each(function(index) {
            $(this).html(index+1);
        });
    }

    function detectarControles(e) {
        if( e.ctrlKey && e.altKey ){
            // ctrl + alt + C
            if( e.keyCode == 67 ){
                e.preventDefault();
                openModal($('[id*="_cliente"]'))
            }
            // ctrl + alt + J
            if( e.keyCode == 70 ){
                e.preventDefault();
                openModal($('[id*="_formapago"]'))
            }
            // ctrl + alt + G
            if( e.keyCode == 71 ){
                e.preventDefault();
                $('.form-horizontal').submit();
            }
        }
        // tecla + agrega item
        if (e.keyCode == 171) {
            e.preventDefault();
            $('#linkAdd').click();
        }
    }
});