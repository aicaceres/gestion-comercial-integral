<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UtilsController extends Controller {

    /**
     * @Route("/checkIngreso", name="check_ingreso")
     * @Method("POST")
     */
    public function checkIngresoAction(Request $request) {
        $user = $request->get('user');
        $unidad = $request->get('unidad');
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('ConfigBundle:Usuario')->findOneByUsername($user);
        foreach ($usuario->getRolesUnidadNegocio() as $rolesUN) {
            $unidadNegocio = $rolesUN->getUnidadNegocio();
            if ($unidadNegocio->getId() == $unidad) {
                $session = $this->get('session');
                $session->set('iva', '21');
                $empresa = $unidadNegocio->getEmpresa();
                $labels = array('label1' => $empresa->getLabel1(), 'label2' => $empresa->getLabel2());
                $session->set('unidneg_id', $unidadNegocio->getId());
                $session->set('unidneg_nombre', $unidadNegocio->getNombre());
                $session->set('theme', $empresa->getEstilo());
                $session->set('labels', $labels);

                $depositos = array();
                foreach ($rolesUN->getDepositos() as $deposito) {
                    $depositos[] = $deposito->getId();
                    /* $depositos[$deposito->getId()] = array( 'id'=>$deposito->getId(), 'nombre' => $deposito->getNombre(),
                      'empresaUnidadDeposito' => $deposito->getEmpresaUnidadDeposito(), 'central'=> $deposito->getCentral()); */
                }
                $session->set('depositos', $depositos);
                return new JsonResponse('OK');
            }
        }
        return new JsonResponse('ERROR');
    }

    // controla permiso para acceso a ruta
    public static function haveAccess($user, $unidNeg, $route) {
        if ($user->getAccess($unidNeg, $route)) {
            return TRUE;
        }
        throw new AccessDeniedException('No posee permiso para acceder a esta página!');
    }

    /*
     * Pad string con caracteres especiales
     */

    public static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
        $diff = strlen($input) - mb_strlen($input, mb_detect_encoding($input));
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }

    /**
     * @Route("/datosHeader", name="datos_header")
     * @Method("GET")
     */
    public function datosHeaderAction() {
        $em = $this->getDoctrine()->getManager();

        $ipedidosrec = $this->getUser()->getCantPedidosDemandados($this->get('session')->get('unidneg_id'));
        $ipedidosenv = $this->getUser()->getCantPedidosSolicitados($this->get('session')->get('unidneg_id'));
        $cpedidos = $em->getRepository('ComprasBundle:Pedido')->getCountPendientes();

        //  $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findAll();
        //  $clientes = $em->getRepository('AdminBundle:Cliente')->findAll();
        //  $porpagar = $porcobrar = $venc30dias = 0;
        /*  foreach ($proveedores as $proveedor) {
          $porpagar += $proveedor->getSaldo();
          }
          foreach ($clientes as $cliente) {
          $porcobrar += $cliente->getSaldo();
          $venc30dias += $cliente->getFacturas30Dias();
          }
         */
        $datos = array('pedcompra' => $cpedidos, 'pedenviados' => $ipedidosenv, 'pedrecibidos' => $ipedidosrec);
        //,'venc30dias' => round($venc30dias), 'porcobrar'=>round($porcobrar),'porpagar'=>round($porpagar)
        return new JsonResponse($datos);
    }

    ##Location

    public function provinciasAction(Request $request) {
        $pais_id = $request->request->get('pais_id');
        $em = $this->getDoctrine()->getManager();
        $provincias = $em->getRepository('ConfigBundle:Provincia')->findByPaisId($pais_id);
        return new JsonResponse($provincias);
    }

    public function localidadesAction(Request $request) {
        $provincia_id = $request->request->get('provincia_id');
        $em = $this->getDoctrine()->getManager();
        $localidades = $em->getRepository('ConfigBundle:Localidad')->findByProvinciaId($provincia_id);
        return new JsonResponse($localidades);
    }

    public function codPostalAction(Request $request) {
        $localidad_id = $request->request->get('localidad_id');
        $em = $this->getDoctrine()->getManager();
        $localidad = $em->getRepository('ConfigBundle:Localidad')->find($localidad_id);
        $cod = ($localidad->getCodPostal()) ? $localidad->getCodPostal() : '';
        return new JsonResponse($cod);
    }

    public function calculaEdadAction($fecha) {
        list($Y, $m, $d) = explode("-", $fecha);
        $edad = date("md") < $m . $d ? date("Y") - $Y - 1 : date("Y") - $Y;
        return new JsonResponse($edad);
    }

    // Lista de facturas en texto
    public static function textoListaFacturasAction($concepto, $em) {
        $lista = json_decode($concepto);
        $text = '';
        foreach ($lista as $item) {
            $doc = explode('-', $item->clave);
            if ($doc[0] == 'FAC') {
                $comprob = $em->getRepository('ComprasBundle:Factura')->find($doc[1]);
                $tipo = ' [FAC ';
            }
            else {
                $comprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($doc[1]);
                $tipo = ' [DEB ';
            }
            //$factura = $em->getRepository('ComprasBundle:Factura')->find($item->id);
            $text = $text . $tipo . $comprob->getNroComprobante() . ' $' . $item->monto . '] ';
        }
        return $text;
    }

    // Lista de facturas en texto para impresion del pago
    public static function textoListaFacturasPrintAction($concepto, $em) {
        $lista = json_decode($concepto);
        $comprobantes = array();

        foreach ($lista as $item) {
            $text = '';
            $doc = explode('-', $item->clave);
            if ($doc[0] == 'FAC') {
                $comprob = $em->getRepository('ComprasBundle:Factura')->find($doc[1]);
                $tipo = ' FAC N°';
            }
            else {
                $comprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($doc[1]);
                $tipo = ' DEB N°';
            }
            //$factura = $em->getRepository('ComprasBundle:Factura')->find($item->id);
            $text = $tipo . $comprob->getNroComprobante() . ' $' . $item->monto . ' ';
            $comprobantes[] = $text;
        }
        return $comprobantes;
    }

    public static function ultimoMesParaFiltro($desde, $hasta) {
        $hoy = new \DateTime();
        $inicio = date("d-m-Y", strtotime($hoy->format('d-m-Y') . "- 30 days"));
        $ini = ($desde) ? $desde : $inicio;
        $fin = ($hasta) ? $hasta : $hoy->format('d-m-Y');
        return array('ini' => $ini, 'fin' => $fin);
    }

    // SLUG TEXT
    public static function Slug($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    /// PARA FECHAS
    public static function toAnsiDate($value, $guiones = true) {
        if (is_array($value))
            $value = isset($value['text']) ? $value['text'] : null;

        if (strpos($value, '-') === false)
            return $value;

        $date = UtilsController::toArray($value);

        if ($guiones) {
            $ansi = $date['Y'] . '-' . $date['m'] . '-' . $date['d'];
        }
        else {
            $ansi = $date['Y'] . $date['m'] . $date['d'];
        }
        /* $ansi .= isset($date['H']) ? ' '.$date['H'].':'.$date['i'].':'.$date['s'] : ''; */

        return $ansi;
    }

    public static function toArray($value) {
        if (strpos($value, '-') === false)
            return array('Y' => '1969', 'm' => '01', 'd' => '01');

        $parts = explode('-', $value);
        $years = explode(' ', $parts[2]);
        //    $hours = isset($years[1]) ? explode(':',$years[1]) : null;

        $date = array('d' => $parts[0], 'm' => $parts[1], 'Y' => $years[0]);
        //       if($hours)
        //       $date = array_merge($date,array('H'=>$hours[0],'i'=>$hours[1],'s'=>$hours[2]));

        return $date;
    }


    public static function longDateSpanish($fecha, $dayname = FALSE) {
        $date = strtotime($fecha->format('Y-m-d'));
        $dia = date("l", $date);

        if ($dia == "Monday")
            $dia = "Lunes";
        if ($dia == "Tuesday")
            $dia = "Martes";
        if ($dia == "Wednesday")
            $dia = "Miércoles";
        if ($dia == "Thursday")
            $dia = "Jueves";
        if ($dia == "Friday")
            $dia = "Viernes";
        if ($dia == "Saturday")
            $dia = "Sabado";
        if ($dia == "Sunday")
            $dia = "Domingo";

        $dia2 = date("d", $date);

        $mes = date("F", $date);

        if ($mes == "January")
            $mes = "Enero";
        if ($mes == "February")
            $mes = "Febrero";
        if ($mes == "March")
            $mes = "Marzo";
        if ($mes == "April")
            $mes = "Abril";
        if ($mes == "May")
            $mes = "Mayo";
        if ($mes == "June")
            $mes = "Junio";
        if ($mes == "July")
            $mes = "Julio";
        if ($mes == "August")
            $mes = "Agosto";
        if ($mes == "September")
            $mes = "Setiembre";
        if ($mes == "October")
            $mes = "Octubre";
        if ($mes == "November")
            $mes = "Noviembre";
        if ($mes == "December")
            $mes = "Diciembre";

        $ano = date("Y", $date);
        if ($dayname)
            $fecha = "$dia, $dia2 de $mes de $ano";
        else
            $fecha = "$dia2 de $mes de $ano";

        return $fecha;
    }

    //// TRUNCADO DE CADENAS
    public static function myTruncate($string, $limit, $break = " ", $pad = "…") {
        // return with no change if string is shorter than $limit
        if (strlen($string) <= $limit)
            return $string;
        // is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        } return $string;
    }

// Errores de formulario inválido
    public static function getFormErrorMessages($form) {
        $errors = array();
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = (String) $form[$child->getName()]->getErrors();
                }
            }
        }
        return $errors;
    }

    public static function errorMessage($codError) {
        switch ($codError) {
            case 1062:
                $msg = 'El dato que intenta ingresar está duplicado.';
                break;
            case 1451:
                $msg = 'Este dato no puede ser eliminado porque está siendo utilizado en el sistema.';
                break;
            default:
                $msg = 'No se puede realizar esta acción. Código de Error:' . $codError;
                break;
        }
        return $msg;
    }

    // VALIDAR CUIT
    public static function validarCuit($cuitprov) {
        $sum = 0;
        $res = false;
        $array_dig2 = array(20, 23, 24, 27, 30, 33, 34);

        $digitos = substr($cuitprov, 0, 2);

        if (in_array($digitos, $array_dig2)) { //si los 2 primeros digistos son validos
            $coeficiente = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);

            $cuit2 = str_replace('-', '', $cuitprov);
            // si no posee 11 dígitos es invalido
            if (strlen($cuit2) <> 11)
                return false;

            $cuit = str_split($cuit2);
            $verificador = array_pop($cuit);

            for ($i = 0; $i < 10; $i++) {

                $sum += ($cuit[$i] * $coeficiente[$i]);
                $resultado = $sum % 11;
                $resultado = 11 - $resultado;
//saco el digito verificador
                $veri_nro = intval($verificador);
            }

            if ($resultado === 11) {
                $resultado = 0;
            }
            else if ($resultado === 10) {
                $resultado = 9;
            }
            $res = ($veri_nro === $resultado);
        }
        return $res;
    }

    public static function sanear_string($string) {

        $string = trim($string);

        $string = str_replace(
                array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
                array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
                $string
        );

        $string = str_replace(
                array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
                array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
                $string
        );

        $string = str_replace(
                array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
                array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
                $string
        );

        $string = str_replace(
                array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
                array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
                $string
        );

        $string = str_replace(
                array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
                array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
                $string
        );

        $string = str_replace(
                array('ñ', 'Ñ', 'ç', 'Ç'),
                array('n', 'N', 'c', 'C',),
                $string
        );
        $string = str_replace('"', '', $string);

        return $string;
    }

}