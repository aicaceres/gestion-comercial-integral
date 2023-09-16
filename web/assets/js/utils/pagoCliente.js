var pagosHolder = jQuery("table.tabla-pagos tbody")

jQuery(document).ready(function ($) {
	// si la pantalla es chica expandir
	if ($("#contentwrapper").width() < 1000) {
		$(".togglemenu").click()
	}
	// refresca la hora en un campo fecha-hora
	horaRefresh = setInterval(function () {
		$(".js-hora").html(new Date().toLocaleString().slice(9))
	}, 1000)
	$(".btn_search").remove()

	$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" })

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
					actualizarPagos()
				}
			})
		})
		.change()

	$("#dialog-tipopago button").on("click", function () {
		let tipo = $(this).data("tipo")
		$("#dialog-tipopago").dialog("close")
		addNewPago(tipo)
	})
})

// nuevo pago
function addNewPago(tipo) {
	const prototype = pagosHolder.data("prototype")
	const index = pagosHolder.data("index")
	const newForm = prototype.replace(/items/g, index)
	pagosHolder.append(newForm)
	pagosHolder.data("index", index + 1)

	var lastTr = pagosHolder.find("tr").last()
	addPagoDeleteLink(lastTr.find(".delTd"))

	lastTr.find('[id*="_tipoPago"]').val(tipo)
	let importe = lastTr.find('[id*="_importe"]')

	quitarElementosSegunTipo(lastTr)
	actualizarPagos()

	const imp = jQuery(".vuelto").html()
		? jQuery(".vuelto").html().replace(",", ".")
		: 0
	importe.val(Math.abs(parseFloat(imp)).toFixed(3)).change()
}

function quitarElementosSegunTipo(pagosTr) {
	pagosTr.each(function (i, tr) {
		let itemTr = jQuery(tr)
		let tipo = itemTr.find('[id*="_tipoPago"]').val()
		// quitar elementos innecesarios para el tipo de pago
		// efectivo
		if (tipo === "EFECTIVO") {
			itemTr
				.find(".tarjetaTd :required, .chequeTd :required")
				.each(function () {
					jQuery(this).attr("required", false)
				})
			itemTr.find('[id*="_importe"]').focus()
		} else {
			itemTr.find(".monedaTd").hide()
		}
		// tarjeta
		if (tipo === "TARJETA") {
			// tarjeta
			itemTr.find('[id*="_datosTarjeta_tarjeta"]').attr("required", true)
			itemTr.find('[id*="_datosTarjeta_tarjeta"]').select2()
			itemTr.find('[id*="_datosTarjeta_numero"]').inputmask({
				mask: "9999 9999 9999 9999",
				onincomplete: function () {
					jQuery(this).val()
						? jQuery(this).addClass("error")
						: jQuery(this).removeClass("error")
				},
				oncomplete: function () {
					jQuery(this).removeClass("error")
				}
			})
			itemTr.find('[id*="_datosTarjeta_cuota"]').val(1)
			// chequetd required false
			itemTr.find(".chequeTd :required").each(function () {
				jQuery(this).attr("required", false)
			})
			itemTr.find('[id*="_datosTarjeta_tarjeta"]').focus()
		} else {
			itemTr.find(".tarjetaTd").remove()
		}
		//cheque
		if (tipo === "CHEQUE") {
			itemTr
				.find('[id*="_chequeRecibido_fecha"]')
				.datepicker({ dateFormat: "dd-mm-yy" })
			// tarjetaTd required false
			itemTr.find(".tarjetaTd :required").each(function () {
				jQuery(this).attr("required", false)
			})
			let selectBanco = itemTr.find(".selectBanco")
			selectBanco.select2({
				tags: true,
				createTag: function (params) {
					var term = jQuery.trim(params.term).toUpperCase()
					if (term === "") {
						return null
					}
					return {
						id: term,
						text: term,
						newTag: true // add additional parameters
					}
				}
			})
			itemTr.find('[id*="_chequeRecibido_nroCheque"]').focus()
		} else {
			itemTr.find(".chequeTd").remove()
		}
	})
}

// click del boton tipo de pago
function linkAddPago() {
	if (jQuery(".datos-formapago").data("tipopago") != "CTACTE") {
		jQuery("#dialog-tipopago").dialog({
			autoOpen: true,
			height: 100,
			width: 350,
			modal: true
		})
		jQuery("#dialog-tipopago button").first().focus()
	}
}
// borrar pago
function addPagoDeleteLink(itemFormTd) {
	let removeFormA = jQuery(
		'<a href="#" class="delItem" title="Quitar"><span class="del-item-button">-</span></a>'
	)
	itemFormTd.append(removeFormA)
	removeFormA.on("click", function (e) {
		if (confirm("Desea eliminar este item?")) {
			e.preventDefault()
			itemFormTd.parent().remove()
			actualizarPagos()
		}
	})
	removeFormA.on("blur", function () {
		jQuery("#linkAddPago").focus()
	})
}

function handleChangeImporte(target) {
	let obj = jQuery(target)
	if (obj.siblings('[id*="_tipoPago"]').val() === "CHEQUE") {
		// cargar valor al cheque
		let chequeTd = obj.parent().parent().find("td.chequeTd")
		chequeTd.find('[id*="_valor"]').val(obj.val())
	}
	actualizarPagos()
}

function actualizarPagos() {
    generanc = jQuery("#ventasbundle_pagocliente_generaNotaCredito").is(
		":checked"
	)
    if(!jQuery('#ventasbundle_pagocliente_comprobantes').val() && generanc){
        alert('No se puede generar NC sin asociar comprobante!')
        jQuery("#ventasbundle_pagocliente_generaNotaCredito").attr('checked',false)
        jQuery.uniform.update()
        return false
    }
	total = checknumero(jQuery("#ventasbundle_pagocliente_total"), 2)
	pagos = nc = 0
	items = jQuery(".tabla-pagos tbody tr.item")
	items.each(function () {
		importe = checknumero(jQuery(this).find('[id*="_importe"]'), 2)
		pagos += importe
	})
	// si se genera nc calcular y sumar importe
	
        if (generanc) {
                // calcular valor de nc
                nc = total - pagos
        }
        jQuery("#nota_credito").val(nc.toFixed(2))
        jQuery(".nota-credito span").html(nc.toFixed(2))
        jQuery(".nota-credito").toggle(generanc)
        jQuery(".nota-credito").toggleClass("red", nc < 0)
        
	jQuery(".pago").html(pagos.toFixed(2))
	vuelto = pagos - total + nc
	jQuery(".vuelto").html(vuelto.toFixed(2))
}

function calcularTotal() {
	obj = jQuery("#ventasbundle_pagocliente_comprobantes")
	data = obj.val()
	if (data) {
            jQuery('#ventasbundle_pagocliente_total').attr('readonly',true)
		url = obj.attr("url")
		jQuery.ajax({
			url: url,
			async: false,
			data: { ids: data },
			success: function (data) {
				total = parseFloat(data)
				jQuery("#ventasbundle_pagocliente_total").val(total.toFixed(2))
				actualizarPagos()
			}
		})
	} else {
            jQuery('#ventasbundle_pagocliente_total').attr('readonly',false)
		total = 0
		jQuery("#ventasbundle_pagocliente_total").val(total.toFixed(2))
		actualizarPagos()
	}
}
