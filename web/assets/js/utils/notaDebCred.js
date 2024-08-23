jQuery(document).ready(function ($) {
  // change de producto para detectar comodin
  $(document).on('select2:close', '.widgetProducto', function (e) {
    let textoComodin = $(this).parent().find('[id*="_textoComodin"]')
    let precTd = $(this).parent().siblings('.precTd')
    const option = jQuery(e.currentTarget).find('option:selected').data()
    if(option){
        const comodin = option.comodin
        if (comodin) {
          // implementacion por agregado de precio unitario editable en comodin
          idInputPrecio = precTd.find('[id*="_precio"]').attr("id")
          inputPrecioUnitario = $(
            `<input type="text" class="precioUnitarioComodin" data-precio="${idInputPrecio}" value="0" style="text-align:right" required="required" onchange="handleChangePrecio(this)" onblur="handleBlurPrecio(this)"/>`
          )
          precTd.prepend(inputPrecioUnitario)
          precTd.find('span').hide()
        }
    } else {
      precTd.find($(".precioUnitarioComodin")).remove()
      precTd.find('span').show()
    }
  })

	let selectComprobante = jQuery("#ventasbundle_notadebcred_comprobanteAsociado")

	selectComprobante
		.select2({
			ajax: {
				url: selectComprobante.data("urlselect"),
				type: "get",
				dataType: "json",
				cache: false,
				data: (params) => {
					return {
						searchTerm: params.term,
						id: jQuery("#widgetCliente").val(),
					}
				},
                                processResults: (response) => {
					results = response.map((x) => {
						return {
							id: x.id,
							text: x.text,
							cargaritems: x.cargaritems
						}
					})
					return { results }
				},
			},
			placeholder: "Seleccionar...",
			allowClear: true,
			width: "style",
		})
		.on("select2:opening", function () {
                    selectComprobante.data("select2").results.clear()
		})
                .on("select2:selecting", function (e) {
                  let id = e.params.args.data.id
//                  if(e.params.args.data.cargaritems){
                  if (confirm("Desea cargar los items del comprobante asociado?")) {
                          eliminarItemsCargados()
                          cargarItems(id)
                      }
//                  }
                    filtrarTipoComprobante(id)
                })
                .on("select2:unselect", function (e) {
                  // habilitar todos los tipos de comprobantes
                  setTipoComprobante()
                  // quitar los items cargados?
                  eliminarItemsCargados()
                  actualizarImportes(1)
                })



  function cargarItems(id) {
    jQuery(".divcarga").removeClass("hidden")
		jQuery.getJSON(
			selectComprobante.data("urlitems"),
			{ id },
      function (data) {
        const itemsCount = data.items.length - 1;
        jQuery("#ventasbundle_notadebcred_descuentoRecargo").val(data.dtorec)

        jQuery.each(data.items, function (i, item) {
          addNewItem()
          const newOption = new Option(item.text, item.id, true, true)
           newOption.setAttribute('data-precio', item.precio);
           newOption.setAttribute('data-alicuota', item.alicuota);
           newOption.setAttribute('data-comodin', item.comodin);
           newOption.setAttribute('data-bajominimo', item.bajominimo);

          // producto
          let prod = jQuery(".widgetProducto").last()
          prod.append(newOption).trigger("select2:select")
          prod.change()
          prod.select2("close")
          // texto comodin
          let textoComodin = prod.parent().find('[id*="_textoComodin"]')
          textoComodin.val(item.comodin)
          //cantidad
          jQuery('[name*="[cantidad]"]').last().val(item.cant)
          // precio
          let precioComodin = prod.closest('.item').find('.precioUnitarioComodin')
          if (precioComodin.length > 0) {
            precioComodin.val(item.precio)
            let precio = prod.closest('.item').find('[id*="_precio"]')
            precio.val( item.precio)
            let alicuota = prod.closest('.item').find('[id*="_alicuota"]')
            alicuota.val( item.alicuota )
          }
        })
        setTimeout(function () {
//          jQuery("#ventasbundle_notadebcred_tipoComprobante").focus()
          jQuery(".divcarga").addClass("hidden")
          actualizarImportes(1)
					}, 500)
			}
    )
  }

  function eliminarItemsCargados() {
    tbody = jQuery('table.detalle tbody')
    const index = tbody.data("index")
    tbody.find('tr.item').remove()
    tbody.data("index", 0)
}

function filtrarTipoComprobante(id) {
    jQuery.getJSON(
            selectComprobante.data("urltiposvalidos"),
            {id},
      function (data) {
                objTiposComprobante = jQuery('[id*="_tipoComprobante"]')
                objTiposComprobante.find("option").each(function (e) {

                    jQuery(this).attr("disabled", !data.includes(parseInt(jQuery(this).val())))

                })
                objTiposComprobante.val(
                        objTiposComprobante.find("option:not([disabled]):first").val()
                        ).focus()
            }
    )
}
})

function handleChangePrecio(el) {
  let input = jQuery(el)
  let inputPrecio = jQuery('#' + input.data('precio'))
  inputPrecio.val( input.val() )
  actualizarImportes(1)
}
function handleBlurPrecio(el) {
  let value = parseFloat(jQuery(el).val())
  jQuery(el).val( value.toFixed(2))
}
