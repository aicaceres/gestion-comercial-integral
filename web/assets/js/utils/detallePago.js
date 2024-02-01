var pagosHolder = jQuery("table.tabla-pagos tbody")

jQuery(document).ready(function ($) {

  checkStatusDetallePago()

  $("#dialog-tipopago button").on("click", function () {
    let tipo = $(this).data("tipo")
    $("#dialog-tipopago").dialog("close")
    addNewPago(tipo)
  })

  const pagosTr = pagosHolder.find("tr")
  pagosTr.each(function (i, tr) {
      addPagoDeleteLink(jQuery(tr).find('.delTd'))
  })

})

function checkStatusDetallePago() {
  let tipopago = jQuery(".datos-formapago").data('tipopago')
//  jQuery(".detalle_pago").toggle(tipopago != "CTACTE")
  if (tipopago === "CTACTE") {
    jQuery(".detalle_pago").css('display','none')
    pagosHolder.find("tr").remove()
  } else {
    quitarElementosSegunTipo(pagosHolder.find('tr'))
    jQuery(".detalle_pago").css('display','block')
//    if (pagosHolder.find('tr').length === 0 ) {
//      addNewPago(tipopago)
//    } else {
//      quitarElementosSegunTipo(pagosHolder.find('tr'))
//    }
  }
}

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
  actualizarSumaPagos()

  const imp = jQuery(".vuelto").html() ? jQuery(".vuelto").html().replace(",", ".") : 0
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
            (jQuery(this).val())
              ? jQuery(this).addClass("error")
              : jQuery(this).removeClass("error")
          },
          oncomplete: function () {
            jQuery(this).removeClass("error")
          },
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
        chequeTr = itemTr
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
              newTag: true, // add additional parameters
            }
          },
        })
        itemTr.find('[id*="_chequeRecibido_nroCheque"]').focus()
      } else {
        itemTr.find(".chequeTd").remove()
      }

  })

}

// click del boton tipo de pago
function linkAddPago() {
  if (jQuery(".datos-formapago").data('tipopago') != "CTACTE") {
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
  let removeFormA = jQuery('<a href="#" class="delItem" title="Quitar"><span class="del-item-button">-</span></a>')
  itemFormTd.append(removeFormA)
  removeFormA.on("click", function (e) {
			if (confirm("Desea eliminar este item?")) {
				e.preventDefault()
				itemFormTd.parent().remove()
				actualizarSumaPagos()
			}
		})
		removeFormA.on("blur", function () {
			jQuery("#linkAddPago").focus()
		})
}

function handleChangeImporte(target) {
  let obj = jQuery(target)
  if (obj.siblings('[id*="_tipoPago"]').val() === 'CHEQUE') {
    // cargar valor al cheque
    let chequeTd = obj.parent().parent().find("td.chequeTd")
    chequeTd.find('[id*="_valor"]').val(obj.val())
    chequeTd.find('[id*="_tomado"]').val(chequeTd.find('[id*="_fecha"]').val())
  }
  actualizarSumaPagos()
}

function actualizarSumaPagos() {
  let tipopago = jQuery(".datos-formapago").data("tipopago")
  let vuelto = 0
	if (tipopago !== "CTACTE") {
    let total = parseFloat(jQuery("#importeTotal").html().replace(",", "."))
    let pagos = 0
		let items = jQuery(".tabla-pagos tbody tr.item")
    items.each(function (i, item) {
      let importe = checknumero(jQuery(item).find('[id*="_importe"]'))
      pagos += importe
    })
		jQuery(".pago").html(pagos.toFixed(3))
    vuelto = pagos.toFixed(3) - total.toFixed(3)
    //vuelto = ( Math.abs(vuelto.toFixed(3)) < 0.100) ? 0 : vuelto
		jQuery(".vuelto").html(vuelto.toFixed(3).replace(".", ","))
//		jQuery("#linkAddPago").toggle(vuelto < 0)
	}
	jQuery(".vuelto").html(vuelto.toFixed(3).replace(".", ","))
}