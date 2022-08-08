jQuery(document).ready(function($) {
    $('.saveFactura').on('click', function() {
        if ($('tbody tr').length > 0) {
            var url = $('form').attr('action');
            jPresupuesto('Seleccione tipo de impresión:', 'P', 'Impresion de Presupuestos', function(r) {
                if (r) {
                    $('#cantCopias').val(r);
                    $.ajax({
                        url: url,
                        async: true,
                        data: $('form').serialize(),
                        type: "POST",
                        success: function(data) {
                            if (data === '"OK"') {
                              window.location.href= "//reimprimirFactura" ;
                            } else
                                alert(data);
                        }, error: function() {
                            alert('No se puede realizar la operación en este momento');
                        }});
               }
            });
        } else {
            alert('Debe ingresar items a la factura');
            return false;
        }
        return false;
    });
});