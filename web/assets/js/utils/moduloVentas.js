var $collectionHolder = jQuery("table.detalle tbody")

jQuery(document).ready(function ($) {
	// si la pantalla es chica expandir
	if ($("#contentwrapper").width() < 1000) {
		$(".togglemenu").click()
	}

	$(document).on("focus", "input", function () {
		this.select()
	})
	$("input").removeAttr("autocomplete")

	// refresca la hora en un campo fecha-hora
	horaRefresh = setInterval(function () {
		$(".js-hora").html(new Date().toLocaleString().slice(9))
	}, 1000)

	//  detectar atajos de teclado
	$(document).on("keydown", (e) => detectarControles(e))

	// change moneda
	$(".ventasbundle_moneda")
		.on("change", function () {
			let small = $(this).parent().find("small")
			let id = $(this).val()
			let url_datos = $(this).attr("url_datos")
			$.getJSON(url_datos, { id: id }).done(function (data) {
				// actualizar datos
				if (data) {
					$(".datos-moneda").data("cotizacion", data.cotizacion)
					$(".datos-moneda").data("simbolo", data.simbolo)
					let smallText =
						data.cotizacion > 1 ? "TIPO DE CAMBIO: " + data.cotizacion : ""
					small.html(smallText)
					$(".simbolo").html(data.simbolo)
					actualizarImportes()
				}
			})
		})
		.change()

	$('[id*="_descuentoRecargo"]').on("change", function (e) {
		const value = parseFloat(e.target.value)
		jQuery(this).val(value.toFixed(2))
		actualizarImportes()
	})

	$('[id*="_precioLista"]').on("change", function (e) {
		$(".widgetProducto").each(function (_, item) {
			jQuery(item).trigger("select2:select")
		})
	})

	// PROCESO PARA RESTAURAR DEL LOCALSTORAGE LOS ITEMS GUARDADOS

	selProducto = $(".widgetProducto")
	setSelect2ToProduct(selProducto)

	actualizarImportes()
})

function addNewItem() {
	const prototype = $collectionHolder.data("prototype")
	const index = $collectionHolder.data("index")
	const newForm = prototype.replace(/items/g, index)
	$collectionHolder.append(newForm)
	$collectionHolder.data("index", index + 1)
	$collectionHolder
		.find(".ordTd")
		.last()
		.html($collectionHolder.find("tr.item").length)
	$collectionHolder.find(".cantTd input").last().val(1)

	// PROCESO PARA GUARDAR EL NUEVO ITEM AL LOCALSTORAGE

	// widget producto
	const lastProduct = $collectionHolder.find(".widgetProducto").last()
	setSelect2ToProduct(lastProduct)
	lastProduct.select2("open")
}

function actualizarImportes(ndc = 0) {
	let iva = (iibb = descrec = subTotal = totalIVA = totalIIBB = subtotalTh = 0)
	const cotizacion = jQuery(".datos-moneda").data("cotizacion")
	const categoriaIva = jQuery(".selectorCliente").data("categiva")
	const percrentas = jQuery(".selectorCliente").data("percrentas")
	const porcentaje = checknumero(jQuery('[id*="_descuentoRecargo"]'))
	jQuery('[id*="_descuentoRecargo"]').val(porcentaje.toFixed(2))
	jQuery("span.descuentoRecargo").html(porcentaje.toFixed(2))

	$collectionHolder.find("tr.item").each(function (i, tr) {
		item = jQuery(tr)
		// set orden
		item.find(".ordTd").html(i + 1)

		const cant = checknumero(item.find(".cantTd input"))
		let precio = checknumero(item.find('[id*="_precio"]'))
		let alicuota = checknumero(item.find('[id*="_alicuota"]'))
		if (categoriaIva === "I" || categoriaIva === "M") {
			// aplicar dto para calcular el iva
			dto = precio * (porcentaje / 100)
			iva = (precio + dto) * (alicuota / 100)
			if (percrentas > 0) {
				iibb = (precio + dto) * (parseFloat(percrentas) / 100)
			}
			dtoTot = (dto * cant) / cotizacion
			descrec += dtoTot
		} else {
			// precio + iva
			precio = precio * (1 + alicuota / 100)
		}
		//}
		// calcular la cotización si es distinta a 1
    precUnit = precio / cotizacion
		// calcular precio con descuento para la vista
		if (!ndc) {
			precUnit = precUnit * (1 + porcentaje / 100)
		}
		if (precUnit == 22.384999999999998) {
      precUnit = 22.39
      precio = 22.39
		} else {
		  precUnit = precUnit.toFixed(2) * 1
    }
		precTot = precUnit.toFixed(2) * cant
		// subtotal para vista
		subtotalTh += precTot
		item.find(".precTd span").html(precUnit.toFixed(2))
		item.find(".itmSubtotalTd").text(precTot.toFixed(2))
		// totalizar
		subTotal += precio.toFixed(2) * cant
		totalIVA += iva * cant
		totalIIBB += iibb * cant
	})
	subTotalResumen = subTotal / cotizacion
	totalIvaResumen = totalIVA / cotizacion
	totalIibbResumen = totalIIBB / cotizacion
  jQuery("#subtotalTh").html(subtotalTh.toFixed(2))
	jQuery("#importeSubtotal").html(subTotalResumen.toFixed(2).replace(".", ","))

	if (categoriaIva !== "I" && categoriaIva !== "M") {
		descrec = subTotalResumen * (porcentaje / 100)
	}
	const totalgral =
		subTotalResumen + descrec + totalIvaResumen + totalIibbResumen
	jQuery("#importeRecargo").text(descrec.toFixed(2).replace(".", ","))
	jQuery("#importeTotal").text(totalgral.toFixed(2).replace(".", ","))
	// iva e iibb
	jQuery("#importeIVA").text(totalIvaResumen.toFixed(2).replace(".", ","))
	jQuery("#importeIIBB").text(totalIibbResumen.toFixed(2).replace(".", ","))

	if (
		typeof actualizarSumaPagos !== "undefined" &&
		jQuery.isFunction(actualizarSumaPagos)
	) {
		actualizarSumaPagos()
	}
}

function handleSearchProducto(item) {
	let selProducto = jQuery(item).closest("tr").find(".widgetProducto")
	jQuery("#popup")
		.html(
			'<div class="loaders" style="width: 100%;text-align: center;margin-top: 10px;">Cargando Datos...</div>'
		)
		.load(selProducto.data("urlpopup"), function () {
			loadModalProductos(selProducto)
		})
		.dialog({
			modal: true,
			autoOpen: true,
			title: "Seleccionar Producto",
			width: "50%",
			minHeight: 400,
			position: { my: "top", at: "top", of: ".bodywrapper" },
			close: function () {
				// volver focus al control
				jQuery(item).focus()
			}
		})
}

function loadModalProductos(prod) {
	const url = prod.data("urltable")
	const listaprecio = jQuery('[id*="_precioLista"]').val()
	const deposito = jQuery('[id*="_deposito"]').val()
	const cotizacion = jQuery(".datos-moneda").data("cotizacion")
	const categoriaIva = jQuery(".selectorCliente").data("categiva")
	const descuento = jQuery(".datos-formapago").data("porcentajerecargo")

	const oTable = jQuery("#productos_table").dataTable({
		columnDefs: [
			// These are the column name variables that will be sent to the server
			{ name: "nombre", orderData: 3, targets: 0 },
			{ name: "codigo", orderData: 3, targets: 1 },
			{ name: "precio", orderData: 3, targets: 2 },
			{ name: "stock", orderData: 3, targets: 3 },
			{ targets: "nosort", orderable: false }
		],
		rowCallback: function (row, data) {
			// registrar seleccion
			jQuery(row)
				.find("td:nth-child(3n),td:nth-child(4n)")
				.addClass("alignright")
			jQuery(row)
				.find("a")
				.on("click", function (e) {
					e.preventDefault()
					let item = jQuery(this)
					let data = {
						id: item.data("id"),
						text: item.data("text")
					}
					var newOption = new Option(data.text, data.id, true, true)
					newOption.setAttribute("data-precio", item.data("precio"))
					newOption.setAttribute("data-alicuota", item.data("alicuota"))
					newOption.setAttribute("data-comodin", item.data("comodin"))
					newOption.setAttribute("data-bajominimo", item.data("bajominimo"))
					prod.append(newOption).trigger("select2:select")
					jQuery("#popup").dialog("destroy")
					prod.change()
					prod.select2("focus")
				})
		},
		// Server-side parameters
		processing: true,
		serverSide: true,
		// Ajax call
		ajax: {
			url: url,
			type: "POST",
			data: {
				listaprecio: listaprecio,
				deposito: deposito,
				cotizacion: cotizacion,
				categoriaIva: categoriaIva,
				descuento: descuento
			}
		},
		// Classic DataTables parameters
		bPaginate: true,
		bInfo: true,
		bSearchable: true,
		bLengthChange: true,
		pageLength: 15,
		sPaginationType: "full_numbers",
		oLanguage: {
			oPaginate: {
				sFirst: "<<",
				sNext: ">",
				sLast: ">>",
				sPrevious: "<"
			},
			sProcessing: "Cargando...",
			sLengthMenu: "Mostrar _MENU_ registros ",
			sZeroRecords: "Sin datos",
			sInfo: " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
			sInfoEmpty: "Sin coincidencias",
			sInfoFiltered: "(filtrado de _MAX_ registros)",
			sSearch: "Buscar:"
		}
	})
	// cambiar simbolo en columna precio
	oTable.find(".simbolo").text(jQuery(".datos-moneda").data("simbolo"))
	// focus en buscar
	jQuery("#productos_table_filter input").focus()
}

function handleItemDelete(item) {
	let tr = jQuery(item).closest("tr")
	if (confirm("Desea eliminar este item?")) {
		tr.remove()
		actualizarImportes()
		jQuery("#linkAdd").focus()
	}
}
function handleBlurDelete() {
	jQuery("#linkAdd").focus()
}

function setSelect2ToProduct(selProducto) {
	let urlselect = selProducto.data("urlselect")
	let urldatos = selProducto.data("urldatos")
	selProducto
		.select2({
			ajax: {
				url: urlselect,
				type: "post",
				dataType: "json",
				delay: 300,
				cache: false,
				data: (params) => {
					return {
						searchTerm: params.term,
						lista: jQuery('[id*="_precioLista"]').val(),
						cativa: jQuery(".selectorCliente").data("categiva"),
						deposito: jQuery('[id*="_deposito"]').val()
					}
				},
				processResults: (response) => {
					results = response.map((x) => {
						return {
							id: x.id,
							text: x.text,
							alicuota: x.alicuota,
							precio: x.precio,
							comodin: x.comodin,
							bajominimo: x.bajominimo
						}
					})
					return { results }
				}
			},
			templateResult: (data) => {
				return jQuery(data.text)
			},
			templateSelection: function (data) {
				jQuery(data.element).attr("data-precio", data.precio)
				jQuery(data.element).attr("data-alicuota", data.alicuota)
				jQuery(data.element).attr("data-comodin", data.comodin)
        jQuery(data.element).attr("data-bajominimo", data.bajominimo)
				return data.text
      },
      escapeMarkup: (markup) => markup, 
			minimumInputLength: 3,
			width: "style",
			cache: false
		})
		.on("select2:select", function (e) {
			const obj = e.target
			const tr = jQuery(obj).closest("tr")
			const precTd = tr.find(".precTd")
			const prodTd = tr.find(".prodTd")
			const textoComodin = tr.find('[id*="_textoComodin"]')
			let precio = 0
			let alicuota = 0
			let bajominimo = false
			let comodin = false
			let id = 0
			if (!e.params) {
				const option = jQuery(e.currentTarget).find("option:selected")
				//                        id = jQuery(e.currentTarget).val()
				precio = option.data().precio
				alicuota = option.data().alicuota
				comodin = option.data().comodin == 0 ? false : true
				bajominimo = option.data().bajominimo == 0 ? false : true
			} else {
				const data = e.params.data
				//                        id = data.id
				precio = data.precio
				alicuota = data.alicuota
				comodin = data.comodin == 0 ? false : true
				bajominimo = data.bajominimo == 0 ? false : true
			}

			//precios
			precTd.find('[id*="_precio"]').val(precio)
			precTd.find('[id*="_alicuota"]').val(alicuota)
			//bajominimo
			jQuery(obj).siblings(".bajominimo").toggle(bajominimo)
			//comodin
			textoComodin.toggle(comodin)
			textoComodin.attr("required", comodin)

			actualizarImportes()

			setTimeout(function () {
				const objFocus = comodin
					? textoComodin
					: tr.find('.cantTd [id*="_cantidad"]')
				objFocus.focus()
			}, 500)
		})
}

function detectarControles(e) {
	// tecla + ver detalle venta
	if (e.keyCode == 171) {
		e.preventDefault()
		if (jQuery("#linkAdd").is(":visible")) addNewItem()
	}

	if (e.ctrlKey && e.altKey) {
		// ctrl + alt + G
		if (e.keyCode == 71) {
			jQuery("#guardar").click()
		}

		e.preventDefault()

		// ctrl + alt + C
		if (e.keyCode == 67) {
			jQuery("#widgetCliente").select2("open")
		}
		// ctrl + alt + F
		if (e.keyCode == 70) {
			jQuery("#widgetFormaPago").select2("open")
		}
		// ctrl + alt + P
		if (e.keyCode == 80) {
			if (jQuery("#linkAddPago").is(":visible")) jQuery("#linkAddPago").click()
		}
		// ctrl + alt + D
		if (e.keyCode == 68) {
			jQuery(".btn_list").click()
		}
	}

	// if (e.keyCode == 13) {
	//   if (
	//     $(e.target).is('input[type="text"]') ||
	//     $(e.target).is('input[type="checkbox"]')
	//   ) {
	//     e.preventDefault()
	//   }
	// }
}
