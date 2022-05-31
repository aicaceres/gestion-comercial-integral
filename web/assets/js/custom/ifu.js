/**
 * Created by amiranda on 24/11/2016.
 */

// Comandos Fiscales

cmAbrirComprobante = 0;
cmDatosCliente = 1;
cmCancelarComprobante = 2;
cmCierreX = 3;
cmCierreZ = 4;
cmImprimirTextoFiscal = 5;
cmImprimirItem = 6;
cmImprimirPago = 7;
cmImprimirDescuento = 8;
cmImprimirDescuentoUltimoItem = 9;
cmCerrarComprobante = 10;
cmReporteZFechas = 11;
cmObtenerFechaHora = 12;
cmEspecificarEncabezado = 13;
cmEspecificarPie = 14;
cmInformarPercepcionGlobal = 15;
cmInformarPercepcionIVA = 16;
cmDocumentoDeReferencia = 17;
cmObtenerDatosDeInicializacion = 18;
cmSubtotal = 19;
cmUltimoComprobante = 20;
cmImprimirItem2g = 21;
cmImprimirPago2g = 22;
cmImprimirOtrosTributos = 23;
cmDocumentoDeReferencia2g = 24;
cmEspecificarFechaHora = 25;
cmCargarTransportista = 26;
cmImprimirConceptoRecibo = 27;
cmImprimirTextoNoFiscal = 28;
cmCopias = 29;
cmCortarPapel = 30;
cmPrecioBase = 31;
cmSetearTipoComprobante = 32;
cmObtenerPrimerBloqueReporteElectronico = 33;
cmObtenerSiguienteBloqueReporteElectronico = 34;
cmAbrirCajon = 35;
cmReporteZNumeros = 36;
cmIniciarCargaLogoEmisor = 37;
cmEliminarLogoEmisor = 38;
cmCargarCodigoBarras = 39;
cmConfigurarImpresoraFiscal = 40;
cmIniciarCargaLogoAdicional = 41;
cmEliminarLogoAdicional = 42;
cmCambiarLogoUsuario = 43;
cmCrearCodigoQR = 44;
cmEliminarCodigoQR = 45;
cmCopiarComprobante = 46;
cmConsultarAcumuladosMT = 47;

// TipoDeComprobante
tcNo_Fiscal = 0;
tcFactura_A = 1;
tcFactura_B = 2;
tcFactura_C = 3;
tcNota_Debito_A = 4;
tcNota_Debito_B = 5;
tcNota_Debito_C = 6;
tcNota_Credito_A = 7;
tcNota_Credito_B = 8;
tcNota_Credito_C = 9;
tcTicket = 10;
tcRemito = 11;
tcTiqueNotaCredito = 12;
tcRemitoX = 13;
tcReciboX = 14;
tcReciboA = 15;
tcReciboB = 16;
tcReciboC = 17;
tcPresupuestoX = 18;
tcCierreZ = 19;

// Puertos

pcCOM1 = 1;
pcCOM2 = 2;
pcCOM3 = 3;
pcCOM4 = 4;
pcCOM5 = 5;
pcCOM6 = 6;
pcCOM7 = 7;
pcCOM8 = 8;
pcCOM9 = 9;

// codigos de error

errNoError = 0;
errControladorNoDisponible = 1;
errComandoInvalido = 2;
errParametroInvalido = 3;
errExcepcion = 4;
errMemoriaFiscal = 5;
errMemoriaTrabajo = 6;
errBateriaBaja = 7;
errComandoDesconocido = 8;
errDesbordamientoTotales = 9;
errMemoriaFiscalLlena = 10;
errMemoriaFiscalCasiLlena = 11;
errFallaImpresora = 13;
errImpresoraFueraLinea = 14;
errFaltaPapelDiario = 15;
errFaltaPapelTicket = 16;
errTapaImpresoraAbierta = 18;
errCajonCerradoOAusente = 19;
errCampoDatosInvalido = 20;

// Modelos de impresora

modHasar715 = 0;
modHasar715v2 = 2;
modHasar615 = 3;
modHasar320 = 4;
modHasarPR4F = 5;
modHasarPR5F = 6;
modHasar950 = 7;
modHasar951 = 8;
modHasar441 = 9;
modHasar321 = 10;
modHasar322 = 11;
modHasar322v2 = 12;
modHasar330 = 13;
modHasar1120 = 14;
modHasarPL8F = 15;
modHasarPL8Fv2 = 16;
modHasarPL23 = 17;
modEpsonTM300AF = 18;
modEpsonTMU220AF = 19;
modEpsonTM2000 = 20;
modEpsonTM2000AFPlus = 21;
modEpsonLX300 = 22;
modHasarPT1000F = 23;
modEpsonTMT900FA = 24;
modEpsonTMU220AFII = 25;

// Tipos de documento

tdCUIT = 0;
tdDNI = 1;
tdPasaporte = 2;
tdCedula = 3;

// Responsabilidad ante IVA

riResponsableInscripto = 0;
riMonotributo = 1;
riExento = 3;
riConsumidorFinal = 4;

// Tipos de Tributos

SinImpuesto = 0;
ImpuestosNacionales = 1;
ImpuestosProvinciales = 2;
ImpuestosMunicipales = 3;
ImpuestosInternos = 4;
IIBB = 5;
PercepcionIVA = 6;
PercepcionIIBB = 7;
PercepcionImpuestosMunicipales = 8;
OtrasPercepciones = 9;
ImpuestoInternoItem = 10;
OtrosTributos = 11;

// Condiciones de IVA

NoGravado = 1;
Exento = 2;
Gravado = 7;

// Unidades de medida

SinDescripcion = 0;
Kilo = 1;
Metro = 2;
MetroCuadrado = 3;
MetroCubico = 4;
Litro = 5;
KWH = 6;
Unidad = 7;
Par = 8;
Docena = 9;
Quilate = 10;
Millar = 11;
MegaUInterActAntib = 12;
UnidadInternaActInmung = 13;
Gramo = 14;
Milimetro = 15;
MilimetroCubico = 16;
Kilometro = 17;
Hectolitro = 18;
MegaUnidadIntActInmung = 19;
Centimetro = 20;
KilogramoActivo = 21;
GramoActivo = 22;
GramoBase = 23;
UIACTHOR = 24;
JuegoPaqueteMazoNaipes = 25;
MUIACTHOR = 26;
CentimetroCubico = 27;
UIACTANT = 28;
Tonelada = 29;
DecametroCubico = 30;
HectometroCubico = 31;
KilometroCubico = 32;
Microgramo = 33;
Nanogramo = 34;
Picogramo = 35;
MUIACTANT = 36;
UIACTIG = 37;
Miligramo = 41;
Mililitro = 47;
Curie = 48;
Milicurie = 49;
Microcurie = 50;
UInterActHormonal = 51;
MegaUInterActHor = 52;
KilogramoBase = 53;
Gruesa = 54;
MUIACTIG = 55;
KilogramoBruto = 61;
Pack = 62;
Horma = 63;
Donacion = 90;
Ajustes = 91;
Anulacion = 96;
SenasAnticipos = 97;
OtrasUnidades = 98;
Bonificacion = 99;

// Medios de pago

Cambio = 0;
CartaDeCreditoDocumentario = 1;
CartaDeCreditoSimple = 2;
Cheque = 3;
ChequeCancelatorios = 4;
CreditoDocumentario = 5;
CuentaCorriente = 6;
Deposito = 7;
Efectivo = 8;
EndosoDeCheque = 9;
FacturaDeCredito = 10;
GarantiaBancaria = 11;
Giro = 12;
LetraDeCambio = 13;
MedioDePagoDeComercioExterior = 14;
OrdenDePagoDocumentaria = 15;
OrdenDePagoSimple = 16;
PagoContraReembolso = 17;
RemesaDocumentaria = 18;
RemesaSimple = 19;
TarjetaDeCredito = 20;
TarjetaDeDebito = 21;
Ticket = 22;
TransferenciaBancaria = 23;
TransferenciaNoBancaria = 24;
OtrosMediosPago = 99;

// Tipos de impuestointerno

tiFijo = 0;
tiPorcentaje = 1;

// Tipos de reporte electronico

trfecha = 0;
trNroCierre = 1;

function Request(){
    this.cmd = cmAbrirComprobante;
    this.params = new Object();
}

function Driver(){
    this.host = "localhost";
    this.errorCode = 0;
    this.errorDesc = "";

    this.modelo = modHasar715;
    this.puerto = pcCOM2;
    this.baudios = 9600;

    this.errorCode = 0;
    this.errorDesc = "";
    this.lastResponse = null;

    this.datosCli = null;

    this.usarHttps = false;

    this.http = new XMLHttpRequest();

    this.http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            this.resultado = JSON.parse(this.responseText);
            this.errorCode = this.resultado.result.code;
            this.errorDesc = this.resultado.result.description;
            if (typeof(this.resultado.response) != 'undefined'){
                this.lastResponse = this.resultado.response;
            } else {
                this.lastResponse = null;
            }
        }
    };

    this.checkErrors = function(){
        this.errorCode = this.http.errorCode;
        this.errorDesc = this.http.errorDesc;
        this.lastResponse = this.http.lastResponse;
        if (this.errorCode != 0){
            throw this.errorDesc;
        }
    }

    this.sendData = function(request) {
        if (this.usarHttps){
            url = "https://" + this.host +  ":3011";
        } else {
            url = "http://" + this.host +  ":3001";
        }
        this.http.open("POST", url, false);

        request.modelo = this.modelo;
        request.puerto = this.puerto;
        request.baudios = this.baudios;

        var stringData = JSON.stringify(request);
        console.log(stringData)
        this.http.send(stringData);
        this.checkErrors();
        return this.lastResponse;
    }

    this.iniciarTrabajo = function(){
        this.work = new Object();
        this.work.requests = [];
    }

    this.finalizarTrabajo = function () {
        this.response = this.sendData(this.work);
    }

    this.abrirComprobante = function(tipoDeComprobante){
        request = new Request();
        request.cmd = cmAbrirComprobante;
        request.params.tipoComprobante = tipoDeComprobante;
        if (this.datosCli != null) {
            request.params.datosCliente = this.datosCli;
        }
        this.work.requests.push(request);
        this.datosCli = null;
    }

    this.datosCliente = function(nombre, tipoDeDocumento, documento, responsIVA, direccion){
        this.datosCli = new Object();
        this.datosCli.nombre = nombre;
        this.datosCli.tipoDeDocumento = tipoDeDocumento;
        this.datosCli.documento = documento;
        this.datosCli.responsIVA = responsIVA;
        this.datosCli.direccion = direccion;
    }

    this.cancelarComprobante = function(){
        request = new Request();
        request.cmd = cmCancelarComprobante;
        this.work.requests.push(request);
    }

    this.imprimirItem = function(descripcion, cantidad, precio, iva, impuestosInternos){
        request = new Request();
        request.cmd = cmImprimirItem;
        request.params.descripcion = descripcion;
        request.params.cantidad = cantidad;
        request.params.precio = precio;
        request.params.iva = iva;
        request.params.impuestosInternos = impuestosInternos;
        this.work.requests.push(request);
    }

    this.imprimirItem2g = function(descripcion, cantidad, precio, iva, impuestosInternos,
                                   g2CondicionIVA, g2TipoImpuestoInterno, g2UnidadReferencia,
                                   g2CodigoProducto, g2CodigoInterno, g2UnidadMedida){
        request = new Request();
        request.cmd = cmImprimirItem2g;
        request.params.descripcion = descripcion;
        request.params.cantidad = cantidad;
        request.params.precio = precio;
        request.params.iva = iva;
        request.params.impuestosInternos = impuestosInternos;
        request.params.g2CondicionIVA = g2CondicionIVA;
        request.params.g2TipoImpuestoInterno = g2TipoImpuestoInterno;
        request.params.g2UnidadReferencia = g2UnidadReferencia;
        request.params.g2CodigoProducto = g2CodigoProducto;
        request.params.g2CodigoInterno = g2CodigoInterno;
        request.params.g2UnidadMedida = g2UnidadMedida;
        this.work.requests.push(request);
    }

    this.imprimirTextoFiscal = function(texto){
        request = new Request();
        request.cmd = cmImprimirTextoFiscal;
        request.params.texto = texto;
        this.work.requests.push(request);
    }

    this.imprimirTextoNoFiscal = function(texto){
        request = new Request();
        request.cmd = cmImprimirTextoNoFiscal;
        request.params.texto = texto;
        this.work.requests.push(request);
    }

    this.imprimirDescuentoGeneral = function(descripcion, monto){
        request = new Request();
        request.cmd = cmImprimirDescuento;
        request.params.descripcion = descripcion;
        request.params.monto = monto;
        this.work.requests.push(request);
    }

    this.imprimirDescuentoUltimoItem = function(descripcion, monto){
        request = new Request();
        request.cmd = cmImprimirDescuentoUltimoItem;
        request.params.descripcion = descripcion;
        request.params.monto = monto;
        this.work.requests.push(request);
    }

    this.imprimirPago = function(descripcion, monto){
        request = new Request();
        request.cmd = cmImprimirPago;
        request.params.descripcion = descripcion;
        request.params.monto = monto;
        this.work.requests.push(request);
    }

    this.imprimirPago2g = function(descripcion, monto, g2DescripcionAdicional, g2CodigoFormaPago, g2Cuotas,
                                   g2Cupones, g2Referencia){
        request = new Request();
        request.cmd = cmImprimirPago2g;
        request.params.descripcion = descripcion;
        request.params.monto = monto;
        request.params.g2DescripcionAdicional = g2DescripcionAdicional;
        request.params.g2CodigoFormaPago = g2CodigoFormaPago;
        request.params.g2Cuotas = g2Cuotas;
        request.params.g2Cupones = g2Cupones;
        request.params.g2Referencia = g2Referencia;
        this.work.requests.push(request);
    }

    this.ImprimirOtrosTributos = function(codigo, descripcion, baseImponible, importe, alicuota){
        request = new Request();
        request.cmd = cmImprimirOtrosTributos;
        request.params.codigo = codigo;
        request.params.descripcion = descripcion;
        request.params.baseImponible = baseImponible;
        request.params.importe = importe;
        request.params.alicuota = alicuota;
        this.work.requests.push(request);
    }

    this.cerrarComprobante = function(){
        request = new Request();
        request.cmd = cmCerrarComprobante;
        this.work.requests.push(request);
    }

    this.cierreX = function(){
        request = new Request();
        request.cmd = cmCierreX;
        this.work.requests.push(request);
    }

    this.cierreZ = function(){
        request = new Request();
        request.cmd = cmCierreZ;
        this.work.requests.push(request);
    }

    this.reporteZFechas = function(fechaInicio, fechaFin, detallado){
        request = new Request();
        request.cmd = cmReporteZFechas;
        request.params.fechaInicio = fechaInicio;
        request.params.fechaFin = fechaFin;
        request.params.detallado = detallado;
        this.work.requests.push(request);
    }

    this.reporteZNumeros = function(nroInicio, nroFin, detallado){
        request = new Request();
        request.cmd = cmReporteZNumeros;
        request.params.nroInicio = nroInicio;
        request.params.nroFin = nroFin;
        request.params.detallado = detallado;
        this.work.requests.push(request);
    }

    this.obtenerFechaHora = function(){
        request = new Request();
        request.cmd = cmObtenerFechaHora;
        this.work.requests.push(request);
    }

    this.especificarEncabezado = function(linea, texto){
        request = new Request();
        request.cmd = cmEspecificarEncabezado;
        request.params.linea = linea;
        request.params.texto = texto;
        this.work.requests.push(request);
    }

    this.especificarPie = function(linea, texto){
        request = new Request();
        request.cmd = cmEspecificarPie;
        request.params.linea = linea;
        request.params.texto = texto;
        this.work.requests.push(request);
    }

    this.informarPercepcionGlobal = function(descripcion, monto){
        request = new Request();
        request.cmd = cmInformarPercepcionGlobal;
        request.params.descripcion = descripcion;
        request.params.monto = monto;
        this.work.requests.push(request);
    }

    this.informarPercepcionIVA = function(descripcion, monto, alicuota){
        request = new Request();
        request.cmd = cmInformarPercepcionIVA;
        request.params.descripcion = descripcion;
        request.params.monto = monto;
        request.params.alicuota = alicuota;
        this.work.requests.push(request);
    }

    this.documentoDeReferencia = function(documento){
        request = new Request();
        request.cmd = cmDocumentoDeReferencia;
        request.params.documento = documento
        this.work.requests.push(request);
    }

    this.documentoDeReferencia2g = function(tipoComprobante, documento){
        request = new Request();
        request.cmd = cmDocumentoDeReferencia2g;
        request.params.tipoComprobante = tipoComprobante
        request.params.documento = documento
        this.work.requests.push(request);
    }

    this.subtotal = function(){
        request = new Request();
        request.cmd = cmSubtotal;
        this.work.requests.push(request);
    }

    this.obtenerDatosDeInicializacion = function(){
        request = new Request();
        request.cmd = cmObtenerDatosDeInicializacion;
        this.work.requests.push(request);
    }

    this.ultimoComprobante = function(tipoComprobante){
        request = new Request();
        request.cmd = cmUltimoComprobante;
        request.params.tipoComprobante = tipoComprobante;
        this.work.requests.push(request);
    }

    this.imprimirConceptoRecibo = function(texto){
        request = new Request();
        request.cmd = cmImprimirConceptoRecibo;
        request.params.texto = texto;
        this.work.requests.push(request);
    }

    this.copias = function(copias){
        request = new Request();
        request.cmd = cmCopias;
        request.params.copias = copias;
        this.work.requests.push(request);
    }

    this.cortarPapel = function(){
        request = new Request();
        request.cmd = cmCortarPapel;
        this.work.requests.push(request);
    }

    this.precioBase = function(precioBase){
        request = new Request();
        request.cmd = cmPrecioBase;
        request.params.precioBase = precioBase;
        this.work.requests.push(request);
    }

    this.setearTipoComprobante = function(tipoComprobante){
        request = new Request();
        request.cmd = cmSetearTipoComprobante;
        request.params.tipoComprobante = tipoComprobante;
        this.work.requests.push(request);
    }

    this.obtenerPrimerBloqueReporteElectronico = function(rangoInicial, rangoFinal, nombreArchivo, tipoReporte){
        request = new Request();
        request.cmd = cmObtenerPrimerBloqueReporteElectronico;
        request.params.rangoInicial = rangoInicial;
        request.params.rangoFinal = rangoFinal;
        request.params.nombreArchivo = nombreArchivo;
        request.params.tipoReporte = tipoReporte;
        this.work.requests.push(request);
    }

    this.obtenerSiguienteBloqueReporteElectronico = function(){
        request = new Request();
        request.cmd = cmObtenerSiguienteBloqueReporteElectronico;
        this.work.requests.push(request);
    }

    this.abrirCajon = function(){
        request = new Request();
        request.cmd = cmAbrirCajon;
        this.work.requests.push(request);
    }

    this.iniciarCargaLogoEmisor = function(archivoLogo){
        request = new Request();
        request.cmd = cmIniciarCargaLogoEmisor;
        request.params.archivoLogo = archivoLogo;
        this.work.requests.push(request);
    }

    this.eliminarLogoEmisor = function(){
        request = new Request();
        request.cmd = cmEliminarLogoEmisor;
        this.work.requests.push(request);
    }

    this.cargarCodigoBarras = function(tipoCodigo, numero, imprimeNumero){
        request = new Request();
        request.cmd = cmCargarCodigoBarras;
        request.params.tipoCodigo = tipoCodigo;
        request.params.numero = numero;
        request.params.imprimeNumero = imprimeNumero;
        this.work.requests.push(request);
    }

    this.configurarImpresoraFiscal = function(variable, valor){
        request = new Request();
        request.cmd = cmConfigurarImpresoraFiscal;
        request.params.variable = variable;
        request.params.valor = valor;
        this.work.requests.push(request);
    }

    this.iniciarCargaLogoAdicional = function(numeroLogo, archivoLogo){
        request = new Request();
        request.cmd = cmIniciarCargaLogoAdicional;
        request.params.numeroLogo = numeroLogo;
        request.params.archivoLogo = archivoLogo;
        this.work.requests.push(request);
    }

    this.eliminarLogoAdicional = function(numeroLogo){
        request = new Request();
        request.params.numeroLogo = numeroLogo;
        request.cmd = cmEliminarLogoAdicional;
        this.work.requests.push(request);
    }

    this.cambiarLogoUsuario = function(numeroLogo){
        request = new Request();
        request.params.numeroLogo = numeroLogo;
        request.cmd = cmCambiarLogoUsuario;
        this.work.requests.push(request);
    }

    this.crearCodigoQR = function(informacion){
        request = new Request();
        request.params.informacion = informacion;
        request.cmd = cmCrearCodigoQR;
        this.work.requests.push(request);
    }

    this.eliminarCodigoQR = function(){
        request = new Request();
        request.cmd = cmEliminarCodigoQR;
        this.work.requests.push(request);
    }

    this.copiarComprobante = function(tipoDeComprobante, nroComprobante){
        request = new Request();
        request.cmd = cmCopiarComprobante;
        request.params.tipoDeComprobante = tipoDeComprobante;
        request.params.nroComprobante = nroComprobante;
        this.work.requests.push(request);
    }

    this.consultarAcumuladosMT = function(tipoDeComprobante){
        request = new Request();
        request.cmd = cmConsultarAcumuladosMT;
        request.params.tipoDeComprobante = tipoDeComprobante;
        this.work.requests.push(request);
    }

}