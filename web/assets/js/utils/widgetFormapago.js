jQuery(document).ready(function ($) {
	let select = $("#widgetFormaPago")

	select
		.select2({
			ajax: {
				url: select.data("urlselect"),
				type: "post",
				dataType: "json",
        data: (params) => {
					return {
						searchTerm: params.term,
						consumidorFinal: select.data("consumidorfinal"),
					}
				},
				processResults: (response) => {
					results = response.map((x) => {
						return {
							id: x.id,
							text: x.id + " - " + x.text,
							partial: x.partial,
						}
					})
					return { results }
				},
			},
			templateResult: (data) => $(data.partial),
			cache: false,
			delay: 250,
			width: "style",
			tags: true,
		})
    .on("select2:selecting", function (e) {
      id = (e.params) ? e.params.args.data.id : parseInt(e.currentTarget.value)
      if(typeof id == 'number')	replaceDatosFormaPago(id)
		})


  function replaceDatosFormaPago(id) {
    let divDatos = jQuery(".datos-formapago")
    if (divDatos.length) {
			url_datos = select.data("urldatos")
			jQuery.get(url_datos, { id: id }).done(function (data) {
        if (data) {
          const newDiv = jQuery(data)
          divDatos.replaceWith(newDiv)
          jQuery('[id*="_descuentoRecargo"]').val( jQuery(data).data('porcentajerecargo') ).change()

          // si hay detalle de pago actualizar
          if (jQuery(".detalle_pago").length) checkStatusDetallePago()
				}
			})
		}
	}
})
