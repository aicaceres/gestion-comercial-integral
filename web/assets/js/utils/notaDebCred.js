jQuery(document).ready(function ($) {
  // change de producto para detectar comodin
  $(document).on('change', '.widgetProducto', function (event) {
    let textoComodin = $(this).parent().find('[id*="_textoComodin"]')
    let precTd = $(this).parent().siblings('.precTd')
    if (textoComodin.is(":visible")) {
      // implementacion por agregado de precio unitario editable en comodin
      idInputPrecio = precTd.find('[id*="_precio"]').attr("id")
      inputPrecioUnitario = $(
        `<input type="text" class="precioUnitarioComodin" data-precio="${idInputPrecio}" value="0" style="text-align:right" required="required" onchange="handleChangePrecio(this)" onblur="handleBlurPrecio(this)"/>`
      )
      precTd.prepend(inputPrecioUnitario)
      precTd.find('span').hide()
    } else {
      precTd.find($(".precioUnitarioComodin")).remove()
      precTd.find('span').show()
    }
  })

	let selectComprobante = $("#ventasbundle_notadebcred_comprobanteAsociado")

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
						id: $("#widgetCliente").val(),
					}
				},
				processResults: (response) => ({ results: response }),
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
      if (confirm("Desea cargar los items del comprobante asociado?")) {
				cargarItems(id)
			}
			filtrarTipoComprobante(id)
    })
    .on("select2:unselect", function (e) {
      // habilitar todos los tipos de comprobantes
      $('[id*="_tipoComprobante"] option').attr("disabled",false)
      // quitar los items cargados?
    })

  function cargarItems(id) {
    $(".divcarga").removeClass("hidden")
		$.getJSON(
			selectComprobante.data("urlitems"),
			{ id },
      function (data) {
        const itemsCount = data.length - 1;
        
        $.each(data, function (i, item) {
          addNewItem()
          const newOption = new Option(item.text, item.id, true, true)
          // producto
          let prod = jQuery(".widgetProducto").last()
          prod.append(newOption).trigger("select2:selecting")
          prod.change()
          prod.select2("close")
          // texto comodin
          let textoComodin = prod.parent().find('[id*="_textoComodin"]')
          textoComodin.val(item.comodin)
          //cantidad
          $('[name*="[cantidad]"]').last().val(item.cant)
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
          $("#ventasbundle_notadebcred_tipoComprobante").focus()
          $(".divcarga").addClass("hidden")
          actualizarImportes()
					}, 500)
			}
    )
  }

//  async function prueba(item) {
//    addNewItem()
//          $('[name*="[cantidad]"]').last().val(item.cant)
//
//          const newOption = new Option(item.text, item.id, true, true)
//          // producto
//          let prod = jQuery(".widgetProducto").last()
//          prod.append(newOption).trigger("select2:selecting")
//          prod.change()
//          prod.select2("close")
//          // texto comodin
//          let textoComodin = prod.parent().find('[id*="_textoComodin"]')
//          textoComodin.val(item.comodin)
//          // precio
//          let precioComodin = prod.closest('.item').find('.precioUnitarioComodin')
//          if (precioComodin.length > 0) {
//            precioComodin.val(item.precio)
//            let precio = prod.closest('.item').find('[id*="_precio"]')
//            precio.val( item.precio)
//            let alicuota = prod.closest('.item').find('[id*="_alicuota"]')
//            alicuota.val( item.alicuota )
//    }
//    return 1
//  }

function filtrarTipoComprobante(id) {
    $.getJSON(
            selectComprobante.data("urltiposvalidos"),
            {id},
            function (data) {
                objTiposComprobante = $('[id*="_tipoComprobante"]')
                objTiposComprobante.find("option").each(function (e) {

                    $(this).attr("disabled", !data.includes(parseInt($(this).val())))

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
  actualizarImportes()
}
function handleBlurPrecio(el) {
  let value = parseFloat(jQuery(el).val())
  jQuery(el).val( value.toFixed(3))
}