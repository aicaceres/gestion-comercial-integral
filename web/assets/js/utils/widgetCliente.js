jQuery(document).ready(function ($) {
	let selectCliente = $("#widgetCliente")

	selectCliente
		.select2({
			ajax: {
				url: selectCliente.data("urlselect"),
				type: "post",
				dataType: "json",
				delay: 300,
				cache: true,
				data: (params) => {
					return {
						searchTerm: params.term,
						limit: 10,
					}
				},
				processResults: (response) => {
					results = response.map((x) => {
						return {
							id: x.id,
							text: x.text,
							cuit: x.cuit,
							dni: x.dni,
						}
					})
					return { results }
				},
			},
			templateResult: (data) => {
				let doc = data.cuit != 0 ? data.cuit : data.dni
				txtdoc = doc && doc != 0 ? " [ " + doc + " ]" : ""
				return data.text + txtdoc
			},
			minimumInputLength: 3,
			width: "style",
		})
		.on("select2:selecting", function (e) {
			let divDatos = $(".datos-cliente")
      if (divDatos.length) {
        jQuery("#updating-data").removeClass("hidden")
				id = e.params.args.data.id
        url_datos = selectCliente.data("urldatos")
        // ocultar iva e iibb en resumen
        $("#ivaTd, #iibbTd").hide()

				const getDataCliente = $.get(url_datos, { id: id }).done(function (data) {
          if (data) {
            divDatos.replaceWith(data.partial)
            $(".selectorCliente").data("categiva", data.condicionIva)
            $(".selectorCliente").data("percrentas", data.percRentas)
            $('[id*="_categoriaIva"]').val(data.condicionIva)
            $('[id*="_percepcionRentas"]').val(data.percRentas)
            $("#percrentas").html(data.percRentas)
						// Forma de pago
						let cf = data.esConsumidorFinal ? 1 : 0
						selectFormaPago = $("#widgetFormaPago")
						selectFormaPago.data("consumidorfinal", cf)

						if (selectFormaPago.find("option[value='" + data.formapago + "']").length) {
              selectFormaPago.val(data.formapago).trigger("change")
              selectFormaPago.trigger("select2:selecting")
						} else {
              select2_search(selectFormaPago,data.formapagotext)
            }
						$('[id*="_precioLista"]').val(data.listaprecio)
            $('[id*="_transporte"]').val(data.transporte)
            // mostrar resumen de iva e iibb si corresponde
            if (data.condicionIva == "M" || data.condicionIva == "I") {
							$("#ivaTd").show()

						}
            if (data.percRentas>0) {
							$("#iibbTd").show()
						}
            // cuit
            color = data.cuitValido ? "#666666" : "orangered"
            $(".cuitcliente").css("color", color)

            setTimeout(function () {
                if (typeof setTipoComprobante === "function") {
                    setTipoComprobante()
                  }

              jQuery("#updating-data").addClass("hidden")
              selectCliente.select2('focus')
            }, 2000)
					}
        }, "json")
			}
		})

	function select2_search($el, term) {
		// Get the search box within the dropdown or the selection
		// Dropdown = single, Selection = multiple
		var $search = $el.data("select2").dropdown.$search
		// This is undocumented and may change in the future
		$search.hide()
		$search.val(term)
		$search.trigger("input")
		setTimeout(function () {
			$(".select2-results__option").trigger("mouseup")
			$search.show()
                        jQuery("#updating-data").addClass("hidden")
		}, 5000)
	}
})
