jQuery(function ($) {
	var last_formapago
	var tipoPago
	const esPresupuesto = $(location).attr("pathname").includes("presupuesto")
	const esNotaDebCred = $(location).attr("pathname").includes("notadebcred")

	$(window).on("load", function () {
		var onLoad = true


		// change moneda
		// $(".ventasbundle_moneda")
		// 	.on("change", function () {
		// 		let small = $(this).parent().find("small")
		// 		let id = $(this).val()
		// 		let url_datos = $(this).attr("url_datos")
		// 		$.getJSON(url_datos, { id: id }).done(function (data) {
		// 			// actualizar datos
		// 			if (data) {
		// 				$(".datos-moneda").data("cotizacion", data.cotizacion)
		// 				$(".datos-moneda").data("simbolo", data.simbolo)
		// 				let smallText =
		// 					data.cotizacion > 1 ? "TIPO DE CAMBIO: " + data.cotizacion : ""
		// 				small.html(smallText)
		// 			}
		// 		})
		// 	})
		// 	.change()

		/**+++++++++++  */

		cliente = $(".form-horizontal").find('[id*="_cliente"]')
		url_cliente_autocomplete = cliente.attr("url_autocomplete")
		cliente
			.select2({
				ajax: {
					url: url_cliente_autocomplete,
					type: "post",
					dataType: "json",
					delay: 250,
					data: function (params) {
						return {
							searchTerm: params.term, // search term
						}
					},
					processResults: function (response) {
						return {
							results: response,
						}
					},
					cache: true,
				},
				minimumInputLength: 3,
			})
			.on("change", function () {
				id = $(this).val()
				urlDatosCliente = $(this).attr("url_datos")
				$.getJSON(urlDatosCliente, { id: id }).done(function (data) {
					// actualizar partial datos
					if (data) {
						$("#categoriaIva").val(data.categoriaIva)
						$("#esConsumidorFinal").val(data.esConsumidorFinal)
						// ocultar iva e iibb en resumen
						$("#ivaTd, #iibbTd").hide()
						if ($(".datos-cf").length > 0) {
							if (data.esConsumidorFinal) {
								$(".datos-cliente").hide()
								$(".datos-cf").show()
								//$('[id*="_nombreCliente"]').attr('required',true);
								$('[id*="_nombreCliente"]').focus()
							} else {
								$(".datos-cf").hide()
								$('[id*="_nombreCliente"]').attr("required", false)
								$(
									'[id*="_nombreCliente"],[id*="_nroDocumentoCliente"],[id*="_tipoDocumentoCliente"]'
								)
									.val("")
									.trigger("change")
								$(".datos-cliente").html(data.partial)
								$(".datos-cliente").show()
							}
						} else {
							// ventas no tiene datos de cf
							$(".datos-cliente").html(data.partial)
							$('[id*="_transporte"]').val(data.transporte)
						}
						if (onLoad) {
							onLoad = false
						} else {
							$('[id*="_precioLista"]').val(data.listaprecio)
							$('[id*="_formaPago"]').val(data.formapago)
							$('[id*="_formaPago"]').blur()
						}
						// mostrar iva e iibb si corresponde
						//if( !esPresupuesto){
						if (data.categoriaIva == "M" || data.categoriaIva == "I") {
							$("#ivaTd").show()
							if (data.showiibb) {
								$("#iibbTd").show()
							}
						}
						//}
						color = data.cuitValido ? "#666666" : "orangered"
						$(".cuitcliente").css("color", color)

						// if (esNotaDebCred) {
						// 	// recargar facturas segun cliente
						// 	objfact = $("#ventasbundle_notadebcred_comprobanteAsociado")
						// 	url_fact = objfact.attr("url_autocomplete")
						// 	$.getJSON(url_fact, { id: cliente.val() }).done(function (data) {
						// 		// actualizar datos
						// 		if (data) {
						// 			objfact.html("<option></option>").select2({ data: data })
						// 		}
						// 	})
						// }
						actualizaTotales()
					}
				})
			})
			.trigger("change")

		// al modificar forma de pago actualizar datos del partial
		// $("#widgetFormaPago").on("change", function () {
		// 	actualizaTotales()
		// })
		// $('[id*="_formaPago"]').on("focus",function(){
		//   last_formapago = $(this).val();
		// }).on("blur", function (e) {
		//     e.preventDefault()
		//     id = $(this).val()
		//     url_datos = $(this).attr("url_datos")
		//     $.get(url_datos, { id: id }).done(function (data) {
		//       // actualizar datos
		//       if (data) {
		//         $(".datos-formapago").html(data)
		//         let esCtaCte = $("#esCtaCte").val()
		//         let esConsumidorFinal = $("#esConsumidorFinal").val() == 'true'
		//         if (checkConsumidorFinal(esCtaCte,esConsumidorFinal)) {
		//           descuentoRecargo = $("#porcentajeRecargo").val()
		//           //$('.descuentoRecargo').text( descuentoRecargo.toFixed(2) )
		//           $('[id*="_descuentoRecargo"]').val(descuentoRecargo)
		//           if (esNotaDebCred) {
		//             detallePago()
		//           }
		//           actualizaTotales()
		//         }
		//       }
		//     })
		//   })

		// $('[id*="_moneda"]').on("change", function () {
		// 	label = $(this).prev("label")
		// 	label.find("small").remove()
		// 	id = $(this).val()
		// 	url_datos = $(this).attr("url_datos")
		// 	$.getJSON(url_datos, { id: id }).done(function (data) {
		// 		// actualizar datos
		// 		if (data) {
		// 			span = $("<small></small>").html(data.partial)
		// 			label.append(span)
		// 			$(".simbolo").html(data.simbolo)
		// 			$('[id*="_cotizacion"]').val(data.cotizacion)
		// 			actualizaTotales()
		// 		}
		// 	})
		// })
		// $('[id*="_moneda"]').change()




		$("#linkAdd").on("click", function (e) {
			e.preventDefault()
			addNewItem()
			e.stopPropagation()
		})

		// Get the container who holds the collection
		$collectionHolder = $("table.detalle tbody")
		$collectionHolder.find("tr.item").each(function (i) {
			addItemFormDeleteLink($(this).find(".delTd"))
			$(this)
				.find(".ordTd")
				.html(i + 1)
			$(this)
				.find(".cantTd input")
				.change(function () {
					actualizaTotales()
				})
			$(this)
				.find(".prodTd input")
				.attr("required", $(this).find(".prodTd input").is(":visible"))
			productolast = $(this).find('[name*="[producto]"]')

			url_producto_autocomplete = productolast.attr("url_autocomplete")
			productolast
				.select2({
					ajax: {
						url: url_producto_autocomplete,
						type: "post",
						dataType: "json",
						delay: 250,
						data: function (params) {
							return {
								searchTerm: params.term, // search term
								lista: $('[id*="_precioLista"]').val(),
								cativa: $("#categoriaIva").val(),
							}
						},
						processResults: function (response) {
							return {
								results: response,
							}
						},
						cache: true,
					},
					minimumInputLength: 3,
				})
				.on("change", function () {
					obj = $(this)
					var data = {
						id: obj.val(),
						listaprecio: $('[id*="_precioLista"]').val(),
						deposito: $('[id*="_deposito"]').val(),
					}
					urlDatosProducto = obj.attr("url_datos")
					$.ajax({
						dataType: "json",
						url: urlDatosProducto,
						async: false,
						data: data,
						success: function (data) {
							// actualizar datos
							objprecio = obj.parent().siblings(".precTd")
							objprecio.find('[id*="_precio"]').val(data.precio)
							objprecio.find('[id*="_alicuota"]').val(data.alicuota)
							textoComodin = obj.siblings('[id*="_textoComodin"]')
							if (data.comodin) {
								textoComodin.attr("required", true)
								textoComodin.show()
								textoComodin.focus()
							} else {
								textoComodin.attr("required", false)
								textoComodin.hide()
								objcant = obj.parent().siblings(".cantTd")
								objcant.find('[id*="_cantidad"]').focus()
							}
							obj.siblings(".bajominimo").toggle(data.bajominimo)
							actualizaTotales()
						},
					})
				})
				.trigger("change")
		})

		lista = $(".form-horizontal").find('[id*="_precioLista"]')
		lista.on("change", function (e) {
			$collectionHolder.find("tr.item").each(function (i) {
				$(this).find('[name*="[producto]"]').change()
			})
		})

		actualizaTotales()
		if (esNotaDebCred) {
			// $("#ventasbundle_notadebcred_comprobanteAsociado").on(
			// 	"change",
			//   function () {
			//     if (confirm("Desea cargar los items del comprobante asociado?")) {
			//       cargarItems($(this))
			//     }
			//     filtrarTipoComprobante($(this))
			// 	}
			// )

			$pagosHolder = $("table.tabla-pagos tbody")
			$pagosHolder.find(".delTd").each(function () {
				addPagoDeleteLink($(this))
			})
			$pagosHolder.find(".ordTd").each(function (i) {
				$(this).html(i + 1)
			})
			$("#linkAddPago").on("click", function (e) {
				e.preventDefault()
				if ($("#tipoPago").val() != "CTACTE") {
					$("#dialog-tipo").dialog("open")
					$("#dialog-tipo button").first().focus()
				}
			})

			$("#dialog-tipo button").on("click", function () {
				tipo = $(this).data("tipo")
				$("#dialog-tipo").dialog("close")
				addNewPago(tipo)
			})

			detallePago()
		}

		cliente.select2("focus")
	})

	// FUNCIONES


	function openModal(obj) {
		const url = obj.attr("url")
		const fnc = obj.attr("fnc") + "(obj)"
		const title = obj.attr("mtitle")
		$("#popup")
			.html(
				'<div class="loaders" style="width: 100%;text-align: center;margin-top: 10px;">Cargando Datos...</div>'
			)
			.load(url, function () {
				eval(fnc)
			})
			.dialog({
				modal: true,
				autoOpen: true,
				title: title,
				width: "50%",
				minHeight: 400,
				position: { my: "top", at: "top", of: ".bodywrapper" },
				close: function () {
					// volver focus al control
					$(obj).focus()
				},
			})
	}

	// CLIENTES
	// function openModalCliente() {
	// 	url_list = cliente.attr("url_list")
	// 	var oTable = $("#clientes_table").dataTable({
	// 		columnDefs: [
	// 			// These are the column name variables that will be sent to the server
	// 			{ name: "nombre", targets: 0 },
	// 			{ name: "cuit", targets: 1 },
	// 			{ targets: "nosort", orderable: false },
	// 		],
	// 		rowCallback: function (row, data) {
	// 			// seleccionar on click
	// 			$(row)
	// 				.find("a")
	// 				.on("click", function () {
	// 					var data = {
	// 						id: $(this).data("id"),
	// 						text: $(this).text(),
	// 					}
	// 					var newOption = new Option(data.text, data.id, true, true)
	// 					cliente.append(newOption).trigger("change")
	// 					$("#popup").dialog("destroy")
	// 					cliente.select2("focus")
	// 				})
	// 		},
	// 		// Server-side parameters
	// 		processing: true,
	// 		serverSide: true,
	// 		// Ajax call
	// 		ajax: {
	// 			url: url_list,
	// 			type: "POST",
	// 		},
	// 		// Classic DataTables parameters
	// 		bPaginate: true,
	// 		bInfo: true,
	// 		bSearchable: true,
	// 		bLengthChange: true,
	// 		pageLength: 10,
	// 		order: [[0, "asc"]],
	// 		sPaginationType: "full_numbers",
	// 		oLanguage: {
	// 			oPaginate: {
	// 				sFirst: "<<",
	// 				sNext: ">",
	// 				sLast: ">>",
	// 				sPrevious: "<",
	// 			},
	// 			sProcessing: "Cargando...",
	// 			sLengthMenu: "Mostrar _MENU_ registros ",
	// 			sZeroRecords: "Sin datos",
	// 			sInfo: " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
	// 			sInfoEmpty: "Sin coincidencias",
	// 			sInfoFiltered: "(filtrado de _MAX_ registros)",
	// 			sSearch: "Buscar:",
	// 		},
	// 	})
	// 	// focus en buscar
	// 	$("#clientes_table_filter input").focus()
	// }

	// // FORMAS DE PAGO
	// function openModalFormaPago() {
	// 	var fTable = $("#formapago_table").dataTable({
	// 		bAutoWidth: false,
	// 		bRetrieve: true,
	// 		columnDefs: [
	// 			{
	// 				targets: "nosort",
	// 				orderable: false,
	// 			},
	// 		],
	// 		rowCallback: function (row, data) {
	// 			// seleccionar on click
	// 			$(row)
	// 				.find("a")
	// 				.on("click", function () {
	// 					formaPago = $('[id*="_formaPago"]')
	// 					formaPago.val($(this).data("id"))
	// 					$("#popup").dialog("destroy")
	// 					formaPago.change()
	// 					formaPago.focus()
	// 				})
	// 		},
	// 		sPaginationType: "full_numbers",
	// 		oLanguage: {
	// 			oPaginate: {
	// 				sFirst: "<<",
	// 				sNext: ">",
	// 				sLast: ">>",
	// 				sPrevious: "<",
	// 			},
	// 			sLengthMenu: "Mostrar _MENU_ registros ",
	// 			sZeroRecords: "Sin datos",
	// 			sInfo: " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
	// 			sInfoEmpty: "Sin coincidencias",
	// 			sInfoFiltered: "(filtrado de _MAX_ registros)",
	// 			sSearch: "Buscar:",
	// 			sSelect: "%d seleccionados",
	// 		},
	// 	})
	// 	// focus en buscar
	// 	$("#formapago_table_filter input").focus()
	// }

	// PRODUCTOS
	function openModalProducto(obj) {
		const listaprecio = $('[id*="_precioLista"]').val()
		const deposito = $('[id*="_deposito"]').val()
		const cotizacion = $('[id*="_cotizacion"]').val()
		const categoriaIva = $("#categoriaIva").val()
		const descuento = $('[id*="_descuentoRecargo"]').val()
		const url_list = obj.attr("url_list")

		var oTable = $("#productos_table").dataTable({
			columnDefs: [
				// These are the column name variables that will be sent to the server
				{ name: "nombre", orderData: 3, targets: 0 },
				{ name: "codigo", orderData: 3, targets: 1 },
				{ name: "precio", orderData: 3, targets: 2 },
				{ name: "stock", orderData: 3, targets: 3 },
				{ targets: "nosort", orderable: false },
			],
			rowCallback: function (row, data) {
				// registrar seleccion
				$(row).find("td:nth-child(3n),td:nth-child(4n)").addClass("alignright")
				$(row)
					.find("a")
					.on("click", function (e) {
						e.preventDefault()
						var data = {
							id: $(this).data("id"),
							text: $(this).text(),
						}
						var newOption = new Option(data.text, data.id, true, true)
						obj.append(newOption).trigger("change")
						$("#popup").dialog("destroy")
						obj.select2("focus")
					})
			},
			// Server-side parameters
			processing: true,
			serverSide: true,
			// Ajax call
			ajax: {
				url: url_list,
				type: "POST",
				data: {
					listaprecio: listaprecio,
					deposito: deposito,
					cotizacion: cotizacion,
					categoriaIva: categoriaIva,
					descuento: descuento,
					//'esPresupuesto' : esPresupuesto,
				},
			},
			// Classic DataTables parameters
			bPaginate: true,
			bInfo: true,
			bSearchable: true,
			bLengthChange: true,
			pageLength: 25,
			sPaginationType: "full_numbers",
			oLanguage: {
				oPaginate: {
					sFirst: "<<",
					sNext: ">",
					sLast: ">>",
					sPrevious: "<",
				},
				sProcessing: "Cargando...",
				sLengthMenu: "Mostrar _MENU_ registros ",
				sZeroRecords: "Sin datos",
				sInfo: " _START_ / _END_  -  <strong>Total: _TOTAL_ </strong>",
				sInfoEmpty: "Sin coincidencias",
				sInfoFiltered: "(filtrado de _MAX_ registros)",
				sSearch: "Buscar:",
			},
		})
		// cambiar simbolo en columna precio
		oTable.find(".simbolo").text($("#simbolo").val())
		// focus en buscar
		$("#productos_table_filter input").focus()
	}

	// funciones personalizas para el formulario
	function addNewItem() {
		console.log("aa")
		var prototype = $collectionHolder.data("prototype")
		var index = $collectionHolder.data("index")
		var newForm = prototype.replace(/items/g, index)
		$collectionHolder.append(newForm)
		$collectionHolder.data("index", index + 1)
		addItemFormDeleteLink($collectionHolder.find(".delTd").last())
		$collectionHolder
			.find(".ordTd")
			.last()
			.html($collectionHolder.data("index"))
		$collectionHolder.find(".cantTd input").last().val(1)

		$("input").on("focus", function () {
			$(this).select()
		})
		$(".cantTd input").change(function () {
			if (isNaN(parseFloat($(this).val()))) $(this).val(0)
			actualizaTotales()
		})

		productolast = $('[name*="[producto]"]').last()
		url_producto_autocomplete = productolast.attr("url_autocomplete")
		productolast
			.select2({
				ajax: {
					url: url_producto_autocomplete,
					type: "post",
					dataType: "json",
					delay: 250,
					data: function (params) {
						return {
							searchTerm: params.term, // search term
							lista: $('[id*="_precioLista"]').val(),
							cativa: $("#categoriaIva").val(),
						}
					},
					processResults: function (response) {
						return {
							results: response,
						}
					},
					cache: true,
				},
				minimumInputLength: 3,
			})
			.on("change", function () {
				obj = $(this)
				var data = {
					id: obj.val(),
					listaprecio: $('[id*="_precioLista"]').val(),
					deposito: $('[id*="_deposito"]').val(),
				}
				urlDatosProducto = obj.attr("url_datos")
				$.ajax({
					dataType: "json",
					url: urlDatosProducto,
					async: false,
					data: data,
					success: function (data) {
						// actualizar datos
						objprecio = obj.parent().siblings(".precTd")
						objprecio.find('[id*="_precio"]').val(data.precio)
						objprecio.find('[id*="_alicuota"]').val(data.alicuota)
						textoComodin = obj.siblings('[id*="_textoComodin"]')
// implementacion por agregado de precio unitario editable en comodin
						idInputPrecio = objprecio.find('[id*="_precio"]').attr("id")
						inputPrecioUnitario = $(
							`<input type="text" data-venta="si" class="precioUnitarioComodin" data-precio="${idInputPrecio}" value="0" style="text-align:right" onchange="handleChangePrecio()"/>`
						)

						if (data.comodin) {
							textoComodin.attr("required", true)
							textoComodin.show()
							textoComodin.focus()

							if (esNotaDebCred) {
								//objprecio.find("span").addClass('hidden')
								objprecio.prepend(inputPrecioUnitario)
								inputPrecioUnitario.on("change", function () {
									precio = $(this).parent().find('[id*="_precio"]')
									precio.val($(this).val())
									actualizaTotales()
								})
							}
						} else {
							textoComodin.attr("required", false)
							textoComodin.hide()

							if (esNotaDebCred) {
								objprecio.find($(".precioUnitarioComodin")).remove()
								//objprecio.find("span").removeClass('hidden')
							}

							objcant = obj.parent().siblings(".cantTd")
							objcant.find('[id*="_cantidad"]').focus()
						}
						obj.siblings(".bajominimo").toggle(data.bajominimo)
						actualizaTotales()
					},
				})
			})
		productolast.select2("focus")
	}

	function addItemFormDeleteLink($itemFormTd) {
		var $removeFormA = jQuery(
			'<a href="#" class="delItem" title="Quitar"><span class="del-item-button">-</span></a>'
		)
		$itemFormTd.append($removeFormA)
		$removeFormA.on("click", function (e) {
			var res = true
			if ($itemFormTd.parent().find(".cantTd input").val() > 0)
				res = confirm("Desea eliminar este item?")
			if (res) {
				e.preventDefault()
				$itemFormTd.parent().remove()
				actualizaTotales()
			}
		})
		$removeFormA.on("blur", function (e) {
			$("#linkAdd").focus()
		})
	}

	function actualizaTotales() {
		let iva = (iibb = descrec = 0)
		let subTotal = (totalIVA = totalIIBB = 0)
		let subtotalTh = 0
		const cotizacion = parseFloat($('[id*="_cotizacion"]').val())
		const categoriaIva = $("#categoriaIva").val()
		const porcentaje = checknumero($('[id*="_descuentoRecargo"]'))
		$('[id*="_descuentoRecargo"]').val(porcentaje.toFixed(2))
		const table = $("table.detalle")
		table.find("tr.item").each(function () {
			let item = $(this)
			const cant = checknumero(item.find(".cantTd input"))
			let precio = checknumero(item.find('[id*="_precio"]'))
			let alicuota = checknumero(item.find('[id*="_alicuota"]'))
			//if (!esPresupuesto) {
			if (categoriaIva == "I" || categoriaIva == "M") {
				// aplicar dto para calcular el iva
				dto = precio * (porcentaje / 100)
				iva = (precio + dto) * (alicuota / 100)
				if (categoriaIva == "I") {
					iibb = (precio + dto) * 0.035
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
			item.find(".precTd span").html(precUnit.tofixed(2))
			item.find(".itmSubtotalTd").text(precTot.tofixed(2))
			// totalizar
			subTotal += precio * cant
			totalIVA += iva * cant
			totalIIBB += iibb * cant
		})
		subTotalResumen = subTotal / cotizacion
		totalIvaResumen = totalIVA / cotizacion
		totalIibbResumen = totalIIBB / cotizacion
		$("#subtotalTh").html(subtotalTh.tofixed(2))
		$("#importeSubtotal").html(subTotalResumen.tofixed(2).replace(".", ","))

		if (categoriaIva != "I" && categoriaIva != "M") {
			descrec = subTotalResumen * (porcentaje / 100)
		}
		const totalgral =
			subTotalResumen + descrec + totalIvaResumen + totalIibbResumen
		$("#importeRecargo").text(descrec.tofixed(2).replace(".", ","))
		$("#importeTotal").text(totalgral.tofixed(2).replace(".", ","))
		// iva e iibb
		$("#importeIVA").text(totalIvaResumen.tofixed(2).replace(".", ","))
		$("#importeIIBB").text(totalIibbResumen.tofixed(2).replace(".", ","))

		$collectionHolder.find(".ordTd").each(function (index) {
			$(this).html(index + 1)
		})
		// if (esNotaDebCred) {
		// 	actualizarSuma()
		// }
	}

	function actualizarSuma() {
		if (tipoPago == "CTACTE") {
			vuelto = 0
		} else {
			total = parseFloat($("#importeTotal").html().replace(",", "."))
			pagos = 0
			items = $(".tabla-pagos tbody tr.item")
			items.each(function () {
				importe = checknumero($(this).find('[id*="_importe"]'))
				pagos += importe
			})
			$(".pago").html(pagos.toFixed(2))
			vuelto = pagos.toFixed(2) - total.toFixed(2)
			$(".vuelto").html(vuelto.toFixed(2).replace(".", ","))
			$("#linkAddPago").toggle(vuelto < 0)
		}
		// verificar datos faltantes
		/*if( $(':invalid').length=0 || vuelto>=0 ){
            setBotonGuardar(true);
        }else{
            setBotonGuardar(false);
        }*/
	}

	function setBotonGuardar(valid) {
		if (valid) {
			$("#guardar").removeClass("disabled")
			$("#guardar").attr("disabled", false)
		} else {
			$("#guardar").addClass("disabled")
			$("#guardar").attr("disabled", true)
		}
	}

	function detallePago() {
		$pagosHolder.html("")
		const tipoPago = $(".datos-formapago").data('tipopago')

		//$('#guardar').find('span').html('FACTURA');
		// si no es cta cte se habilita detalle de pago
		$(".detalle_pago").toggle(tipoPago != "CTACTE")
		// si es cta cte habilitar facturar sin mas datos
		if (tipoPago == "CTACTE") {
			//setBotonGuardar(true);
			return true
		} else {
			//if( categoriaIva == 'C' && tipoPago == 'EFECTIVO'){
			// se emite ticket
			//$('#guardar').find('span').html('TICKET');
			//}
			//setBotonGuardar(false);
			addNewPago(tipoPago)
		}
	}

	function addNewPago(tipo) {
		const prototype = $pagosHolder.data("prototype")
		const index = $pagosHolder.data("index")
		const newForm = prototype.replace(/items/g, index)

		$pagosHolder.append(newForm)
		$pagosHolder.data("index", index + 1)
		lastTr = $pagosHolder.find("tr").last()
		addPagoDeleteLink(lastTr.find(".delTd"))
		lastTr.find('[id*="_tipoPago"]').val(tipo)
		importe = lastTr.find('[id*="_importe"]')

		const imp = $(".vuelto").html() ? $(".vuelto").html().replace(",", ".") : 0
		importe.val(Math.abs(parseFloat(imp)))
		//importe.val(0)
		importe.on("focus", function (e) {
			$(this).select()
		})
		importe.on("change", function () {
			if ($(this).siblings('[id*="_tipoPago"]').val() == "CHEQUE") {
				// cargar valor al cheque
				chequeTd = $(this).parent().parent().find("td.chequeTd")
				chequeTd.find('[id*="_valor"]').val($(this).val())
			}
			actualizarSuma()
		})

		if (tipo != "TARJETA") {
			lastTr.find(".tarjetaTd").remove()
		} else {
			// tarjeta
			lastTr.find('[id*="_datosTarjeta_tarjeta"]').attr("required", true)
			lastTr.find('[id*="_datosTarjeta_tarjeta"]').select2()
			lastTr.find('[id*="_datosTarjeta_numero"]').inputmask({
				mask: "9999 9999 9999 9999",
				onincomplete: function () {
					if ($(this).val()) {
						$(this).addClass("error")
					} else {
						$(this).removeClass("error")
					}
				},
				oncomplete: function () {
					$(this).removeClass("error")
				},
			})
			lastTr.find('[id*="_datosTarjeta_cuota"]').val(1)
			// chequetd required false
			lastTr.find(".chequeTd :required").each(function () {
				$(this).attr("required", false)
			})
			lastTr.find('[id*="_datosTarjeta_tarjeta"]').focus()
		}
		if (tipo != "CHEQUE") {
			lastTr.find(".chequeTd").remove()
		} else {
			lastTr
				.find('[id*="_chequeRecibido_fecha"]')
				.datepicker({ dateFormat: "dd-mm-yy" })
			// tarjetaTd required false
			lastTr.find(".tarjetaTd :required").each(function () {
				$(this).attr("required", false)
			})
			selectBanco = lastTr.find(".selectBanco")
			selectBanco.select2({
				tags: true,
				createTag: function (params) {
					var term = $.trim(params.term).toUpperCase()
					if (term === "") {
						return null
					}
					return {
						id: term,
						text: term,
						newTag: true, // add additional parameters
					}
				},
			})
			lastTr.find('[id*="_chequeRecibido_nroCheque"]').focus()
		}
		if (tipo != "EFECTIVO") {
			lastTr.find(".monedaTd").hide()
		} else {
			lastTr
				.find(".tarjetaTd :required, .chequeTd :required")
				.each(function () {
					$(this).attr("required", false)
				})
			importe.focus()
		}
		actualizarSuma()
	}
	function addPagoDeleteLink($itemFormTd) {
		var $removeFormA = $(
			'<a href="#" class="delItem" title="Quitar"><span class="del-item-button">-</span></a>'
		)
		$itemFormTd.append($removeFormA)
		$removeFormA.on("click", function (e) {
			if (confirm("Desea eliminar este item?")) {
				e.preventDefault()
				$itemFormTd.parent().remove()
				actualizarSuma()
			}
		})
		$removeFormA.on("blur", function (e) {
			$("#linkAddPago").focus()
		})
	}
})
