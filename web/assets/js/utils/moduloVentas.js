var $collectionHolder = jQuery("table.detalle tbody")

jQuery(document).ready(function ($) {

	// si la pantalla es chica expandir
	if ($("#contentwrapper").width() < 1000) {
		$(".togglemenu").click()
	}

  $(document).on('focus', 'input', function () { this.select() })
  $('input').removeAttr('autocomplete')

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
		let value = 0
		if (jQuery(this).val() > 0) {
			alert("No se pueden realizar recargos!!")
		} else {
			value = parseFloat(e.target.value)
			actualizarImportes()
		}
		jQuery(this).val(value.toFixed(3))
  })

  $('[id*="_precioLista"]').on("change", function (e) {
    $(".widgetProducto").each(function (_,item) {
       jQuery(item).trigger('select2:selecting')
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
	$collectionHolder.find(".ordTd").last().html($collectionHolder.data("index"))
	$collectionHolder.find(".cantTd input").last().val(1)

  // PROCESO PARA GUARDAR EL NUEVO ITEM AL LOCALSTORAGE

	// widget producto
  const lastProduct = $collectionHolder.find(".widgetProducto").last()
  setSelect2ToProduct(lastProduct)
  lastProduct.select2("open")
}

function actualizarImportes() {
	let iva = (iibb = descrec = subTotal = totalIVA = totalIIBB = subtotalTh = 0)
  const cotizacion = jQuery(".datos-moneda").data("cotizacion")
  const categoriaIva = jQuery(".selectorCliente").data("categiva")
  const porcentaje = checknumero(jQuery('[id*="_descuentoRecargo"]'))
  jQuery('[id*="_descuentoRecargo"]').val(porcentaje.toFixed(3))
  jQuery('span.descuentoRecargo').html(porcentaje.toFixed(2))

  $collectionHolder.find("tr.item").each(function (i, tr) {
		item = jQuery(tr)
		// set orden
    item.find(".ordTd").html(i + 1)

		const cant = checknumero(item.find(".cantTd input"))
    let precio = checknumero(item.find('[id*="_precio"]'))
		let alicuota = checknumero(item.find('[id*="_alicuota"]'))
		if (categoriaIva == "I" || categoriaIva == "M") {
			// aplicar dto para calcular el iva
			dto = precio * (porcentaje / 100)
			iva = (precio + dto) * (alicuota / 100)
      if (categoriaIva == "I") {
        iibb_percent = jQuery('#iibbPercent').val()
				iibb = (precio + dto) * (parseFloat(iibb_percent)/100)
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
		precUnit = precUnit * (1 + porcentaje / 100)
		precTot = precUnit * cant
		// subtotal para vista
		subtotalTh += precTot
		item.find(".precTd span").html(precUnit.toFixed(3))
		item.find(".itmSubtotalTd").text(precTot.toFixed(3))
		// totalizar
		subTotal += precio * cant
		totalIVA += iva * cant
		totalIIBB += iibb * cant
	})
	subTotalResumen = subTotal / cotizacion
	totalIvaResumen = totalIVA / cotizacion
	totalIibbResumen = totalIIBB / cotizacion
	jQuery("#subtotalTh").html(subtotalTh.toFixed(3))
	jQuery("#importeSubtotal").html(subTotalResumen.toFixed(3).replace(".", ","))

	if (categoriaIva != "I" && categoriaIva != "M") {
		descrec = subTotalResumen * (porcentaje / 100)
	}
	const totalgral =
    subTotalResumen + descrec + totalIvaResumen + totalIibbResumen
  jQuery("#importeRecargo").text(descrec.toFixed(3).replace(".", ","))
	jQuery("#importeTotal").text(totalgral.toFixed(3).replace(".", ","))
	// iva e iibb
	jQuery("#importeIVA").text(totalIvaResumen.toFixed(3).replace(".", ","))
	jQuery("#importeIIBB").text(totalIibbResumen.toFixed(3).replace(".", ","))

	// set index del holder
  $collectionHolder.data("index", $collectionHolder.find("tr.item").length)

  if (typeof actualizarSumaPagos !== 'undefined' && jQuery.isFunction(actualizarSumaPagos)) {
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
  const descuento = jQuery('.datos-formapago').data("porcentajerecargo")

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
						text: item.text()+ ' | '+ item.data("codigo")+ ' | $'+ item.data("contado")
					}
					var newOption = new Option(data.text, data.id, true, true)
					prod.append(newOption).trigger("select2:selecting")
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
				cache: true,
				data: (params) => {
					return {
						searchTerm: params.term,
						lista: jQuery('[id*="_precioLista"]').val(),
						cativa: jQuery(".selectorCliente").data("categiva")
					}
				},
				processResults: function (response) {
					return {
						results: response
					}
				}
			},
			minimumInputLength: 3,
			width: "style"
		})
    .on("select2:selecting", function (e) {
      const obj = e.target
      const id = (e.params) ? e.params.args.data.id : parseInt(e.currentTarget.value)
			const data = {
				id: id,
				listaprecio: jQuery('[id*="_precioLista"]').val(),
				deposito: jQuery('[id*="_deposito"]').val()
			}

			jQuery.ajax({
				dataType: "json",
				url: urldatos,
				async: false,
				data: data,
				success: function (data) {
					const tr = jQuery(obj).closest("tr")
					//bajominimo
					jQuery(obj).siblings(".bajominimo").toggle(data.bajominimo)
					//precios
					const precTd = tr.find(".precTd")
					precTd.find('[id*="_precio"]').val(data.precio)
					precTd.find('[id*="_alicuota"]').val(data.alicuota)
					//comodin
					const textoComodin = tr.find('[id*="_textoComodin"]')
					textoComodin.toggle(data.comodin)
					textoComodin.attr("required", data.comodin)

					actualizarImportes()

					setTimeout(function () {
						const objFocus = data.comodin
							? textoComodin
							: tr.find('.cantTd [id*="_cantidad"]')
						objFocus.focus()
					}, 500)
				}
			})
		})
}

function detectarControles(e) {
	// tecla + ver detalle venta
	if (e.keyCode == 171) {
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
