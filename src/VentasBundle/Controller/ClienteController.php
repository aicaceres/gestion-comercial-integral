<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\Cliente;
use VentasBundle\Form\ClienteType;
use VentasBundle\Entity\PagoCliente;
use VentasBundle\Form\PagoClienteType;

/**
 * @Route("/cliente")
 */
class ClienteController extends Controller {

    /**
     * @Route("/", name="ventas_cliente")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('VentasBundle:Cliente')->findAll();
        return $this->render('VentasBundle:Cliente:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * @Route("/new", name="ventas_cliente_new")
     * @Method("GET")
     * @Template("VentasBundle:Cliente:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_new');
        $em = $this->getDoctrine()->getManager();
        $localidad = $em->getRepository('ConfigBundle:Localidad')->findOneByByDefault(1);
        $entity = new Cliente();
        $entity->setLocalidad($localidad);
        $form = $this->createCreateForm($entity);

        return $this->render('VentasBundle:Cliente:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Cliente entity.
     * @param Cliente $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Cliente $entity) {
        $form = $this->createForm(new ClienteType(), $entity, array(
            'action' => $this->generateUrl('ventas_cliente_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_cliente_create")
     * @Method("POST")
     * @Template("VentasBundle:Cliente:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_new');
        $entity = new Cliente();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'El cliente fue creado con éxito!');
            return $this->redirect($this->generateUrl('ventas_cliente'));
        }
        return $this->render('VentasBundle:Cliente:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="ventas_cliente_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cliente')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Cliente.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('VentasBundle:Cliente:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Cliente entity.
     * @param Cliente $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Cliente $entity) {
        $form = $this->createForm(new ClienteType(), $entity, array(
            'action' => $this->generateUrl('ventas_cliente_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="ventas_cliente_update")
     * @Method("PUT")
     * @Template("VentasBundle:Cliente:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cliente')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el Cliente.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Los datos fueron modificados con éxito!');
            return $this->redirect($this->generateUrl('ventas_cliente'));
        }
        return $this->render('VentasBundle:Cliente:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/deleteAjax/{id}", name="ventas_cliente_delete_ajax")
     * @Method("POST")
     */
    public function deleteAjaxAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cliente')->find($id);
        try {
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Doctrine\DBAL\DBALException $e) {
            $msg = 'No puede eliminarse el Cliente. Se utiliza en el sistema.';
        }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/delete/{id}", name="ventas_cliente_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_delete');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VentasBundle:Cliente')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No se encuentra el Cliente.');
            }
            try {
                $em->remove($entity);
                $em->flush();
                $this->addFlash('success', 'El cliente fue eliminado!');
            }
            catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('error', 'Este dato no puede ser eliminado porque está siendo utilizado en el sistema.');
            }
        }
        return $this->redirectToRoute('ventas_cliente');
    }

    /**
     * Creates a form to delete a Cliente entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('ventas_cliente_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        // ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * @Route("/getDatosCliente", name="get_datos_factura_cliente")
     * @Method("GET")
     */
    public function getDatosClienteAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($id);
        $cuit = $cliente->getCuit();
        $valido = UtilsController::validarCuit($cuit);
        $condVta = $cliente->getCondicionVenta() ? $cliente->getCondicionVenta()->getId() : 0;
        $tipoFact = $cliente->getCategoriaIva()->getNumerico() == 1 ? 'A' : 'B';
        $iva = $cliente->getCategoriaIva()->getDescripcion();
        $exento = ($cliente->getCategoriaIva()->getNombre() == 'EXE') ? 1 : 0;
        $domicilio = $cliente->getDomicilioCompleto();
        return new Response(json_encode(array('condvta' => $condVta, 'tipofact' => $tipoFact,
                    'cuit' => $cuit, 'iva' => $iva, 'domicilio' => $domicilio, 'exento' => $exento, 'valido' => $valido)));
    }
    /**
     * @Route("/getDatosClienteVenta", name="get_datos_venta_cliente")
     * @Method("GET")
     */
    public function getDatosClienteVentaAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Cliente')->find($id);
        $partial = $this->renderView(
            'VentasBundle:Partial:_partial-datos-cliente.html.twig',
            array('item' => $entity)
        );
        $lista = ($entity->getPrecioLista()) ? $entity->getPrecioLista()->getId() : 1;
        $transporte = ($entity->getTransporte()) ? $entity->getTransporte()->getId() : 0;
        $formapago = ($entity->getFormaPago()) ? $entity->getFormaPago()->getId() : 1;
        $cuit = $entity->getCuit();
        $valido = UtilsController::validarCuit($cuit);
        $data = array(
            'partial' => $partial,
            'listaprecio' => $lista,
            'formapago' => $formapago,
            'transporte' => $transporte,
            'categoriaIva' => ($entity->getCategoriaIva() ) ?  $entity->getCategoriaIva()->getNombre() : null,
            'cuitValido' => $valido,
            'esConsumidorFinal' => $entity->getConsumidorFinal(),
        );
        return new Response( json_encode($data));
    }

    /**
     * @Route("/ctacte", name="ventas_cliente_ctacte")
     * @Method("get")
     * @Template()
     */
    public function ctacteAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_ctacte');
        $id = $request->get('clienteId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $em = $this->getDoctrine()->getManager();
        $clientes = $em->getRepository('VentasBundle:Cliente')->findBy(array(), array('nombre' => 'ASC'));
        if ($clientes) {
            if ($id)
                $cliente = $em->getRepository('VentasBundle:Cliente')->find($id);
            else
                $cliente = $clientes[0];

            $ctacte = $em->getRepository('VentasBundle:Cliente')->getDetalleCtaCte($cliente->getId(), $desde, $hasta);
            foreach ($ctacte as $key => &$var) {
                if ($var['tipo'] == 1) {
                    $fact = $em->getRepository('VentasBundle:Factura')->find($var['id']);
                    //$concepto = $fact->getCantidadItems() . ' Art.';
                    $var['concepto'] = 'Factura ' . $fact->getTipoFactura();
                    $var['comprobante'] = 'FAC ' . $fact->getTipoFactura() . $fact->getNroFactura();
                }
                elseif ($var['tipo'] == 2) {
                    $cred = $em->getRepository('VentasBundle:NotaDebCred')->find($var['id']);
                    if ($cred->getSigno() == '+') {
                        $var['tipo'] = 4;
                        $var['comprobante'] = 'DEB ' . $cred->getNroNotaDebCred();
                        $var['concepto'] = 'Nota de Débito';
                    }
                    else {
                        $var['comprobante'] = 'CRE ' . $cred->getNroNotaDebCred();
                        $var['concepto'] = 'Nota de Crédito';
                    }
                }
                elseif ($var['tipo'] == 3) {
                    $pago = $em->getRepository('VentasBundle:PagoCliente')->find($var['id']);
                    $var['comprobante'] = 'REC ' . $pago->getNroComprobante();
                    $text = '';
                    $conceptos = json_decode($pago->getConcepto());
                    foreach ($conceptos as $item) {
                        $doc = explode('-', $item->clave);
                        if ($doc[0] == 'FAC') {
                            $comprob = $em->getRepository('VentasBundle:Factura')->find($doc[1]);
                        }
                        else {
                            $comprob = $em->getRepository('VentasBundle:NotaDebCred')->find($doc[1]);
                        }
                        $text = $text . ' [ ' . $doc[0] . ' ' . $comprob->getNroComprobante() . ' $' . $item->monto . '] ';
                    }
                    $var['concepto'] = 'Pago: ' . UtilsController::myTruncate($text, 30);
                    $var['importe'] = $pago->getTotal();
                }
            }
            /* $ord = usort($ctacte, function($a1, $a2) {
              $value1 = strtotime($a1['fecha']->format('YmdHi')).$a1['comprobante'];
              $value2 = strtotime($a2['fecha']->format('YmdHi')).$a2['comprobante'];
              return $value1 > $value2;
              }); */
            $ord = usort($ctacte, function($a1, $a2) {
                $value1 = strtotime($a1['fecha']->format('Ymd'));
                $value2 = strtotime($a2['fecha']->format('Ymd'));
                if ($value1 != $value2) {
                    return $value1 > $value2;
                }
                return $a1['comprobante'] > $a2['comprobante'];
            });
        }
        else {
            $cliente = NULL;
            $ctacte = NULL;
        }
        return $this->render('VentasBundle:Cliente:ctacte.html.twig', array(
                    'entities' => $ctacte, 'clientes' => $clientes, 'cliente' => $cliente,
                    'desde' => $desde, 'hasta' => $hasta
        ));
    }

    /**
     * @Route("/printClienteCtaCte.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_cliente_ctacte")
     * @Method("POST")
     */
    public function printClienteCtaCteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $clienteId = $request->get('clienteid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($clienteId);
        $textoFiltro = array($cliente ? $cliente->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';
        $mas = __DIR__ . '/../../../web/assets/images/icons/in.png';
        $menos = __DIR__ . '/../../../web/assets/images/icons/out.png';
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:Cliente:pdf-ctacte.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'mas' => $mas, 'menos' => $menos), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=ctacte_' . $cliente->getNombre() . '.pdf'));
    }

    /**
     * @Route("/selectFacturasCliente", name="select_facturas_cliente")
     * @Method("GET")
     */
    public function facturasAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('VentasBundle:Factura')->findByClienteId($id);
        return new JsonResponse($facturas);
    }

    /**
     * @Route("/updateCuit", name="update_cuit_cliente")
     * @Method("GET")
     */
    public function updateCuitAction(Request $request) {
        $id = $request->get('id');
        $txt = $request->get('txt');
        if (UtilsController::validarCuit($txt)) {
            $em = $this->getDoctrine()->getManager();
            $formatCuit = substr($txt, 0, 2) . '-' . substr($txt, 2, 8) . '-' . substr($txt, 10, 1);
            $existe = $em->getRepository('VentasBundle:Cliente')->checkExiste($formatCuit, $id);
            if ($existe) {
                return new JsonResponse('EXISTE');
            }
            else {
                $prov = $em->getRepository('VentasBundle:Cliente')->find($id);
                $prov->setCuit($formatCuit);
                $em->persist($prov);
                $em->flush();
                return new JsonResponse('OK');
            }
        }
        else {
            return new JsonResponse('ERROR');
        }
    }

   /**
     * @Route("/selectClientesAjax", name="select_clientes_ajax")
     * @Method("GET")
     */
    public function selectClientesAjaxAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('VentasBundle:Factura')->findByClienteId(0);
        return new JsonResponse($facturas);
    }

    /**
     * PAGOS
     */

    /**
     * @Route("/pagos", name="ventas_cliente_pagos")
     * @Method("GET")
     * @Template()
     */
    public function pagosIndexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_pagos');
        $cliId = $request->get('cliId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $em = $this->getDoctrine()->getManager();
        $cliente = null;
        if($cliId){
            $cliente = $em->getRepository('VentasBundle:Cliente')->find($cliId);
        }
        $entities = $em->getRepository('VentasBundle:Cliente')->findPagosByCriteria($cliId, $desde, $hasta);
        /*if ($clientes) {
            foreach ($entities as $pago) {
                /* $text = '';
                  $conceptos = json_decode($pago->getConcepto());
                  foreach ($conceptos as $item) {
                  $factura = $em->getRepository('ComprasBundle:Factura')->find($item->id);
                  $text = $text.' [FAC '.$factura->getTipoFactura().$factura->getNroFactura().' $'.$item->monto.'] ';
                  } */
               /* $texto = UtilsController::textoListaFacturasAction($pago->getConcepto(), $em);
                $pago->setConceptoTxt(UtilsController::myTruncate($texto, 30));
                $pago->setConcepto($texto);
                $detalle = UtilsController::myTruncate($pago->getDetalle(), 30);
                $pago->setDetalle($detalle);
            }
        }
        else {
            $cliente = NULL;
            $entities = NULL;
        }*/

        return $this->render('VentasBundle:Cliente:pago_index.html.twig', array(
                    'entities' => $entities, 'cliId' => $cliId, 'cliente'=>$cliente,
                    'desde' => $desde, 'hasta' => $hasta
        ));
    }

    /**
     * @Route("/pagos/new", name="ventas_cliente_pagos_new")
     * @Method("GET")
     * @Template("VentasBundle:Cliente:pago_edit.html.twig")
     */
    public function pagosNewAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_pagos');
        $entity = new PagoCliente();
        $em = $this->getDoctrine()->getManager();
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($request->get('id'));
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $entity->setPagoNro(sprintf("%06d", $equipo->getNroPagoVenta() + 1));
        $entity->setCliente($cliente);
        $form = $this->pagosCreateCreateForm($entity);
        return $this->render('VentasBundle:Cliente:pago_edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PagoCliente entity.
     * @param PagoCliente $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function pagosCreateCreateForm(PagoCliente $entity) {
        $form = $this->createForm(new PagoClienteType(), $entity, array(
            'action' => $this->generateUrl('ventas_cliente_pagos_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/printClientePagos.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_cliente_pagos")
     * @Method("POST")
     */
    public function printClientesPagosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $clienteId = $request->get('clienteid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($clienteId);
        $textoFiltro = array($cliente ? $cliente->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:Cliente:pdf-pagoscliente.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_pagos_cliente_' . $cliente->getNombre() . '.pdf'));
    }

    /**
     * @Route("/pagos", name="ventas_cliente_pagos_create")
     * @Method("POST")
     * @Template("VentasBundle:Cliente:pago_edit.html.twig")
     */
    public function pagosCreateAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_pagos');
        $entity = new PagoCliente();
        $form = $this->pagosCreateCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $arrayConceptos = explode(',', $request->get('txtconcepto'));
            $facturasImpagas = $em->getRepository('VentasBundle:Cliente')->getFacturasImpagas($entity->getCliente()->getId());
            $arrayFacturas = array();
            foreach ($facturasImpagas as $fact) {
                array_push($arrayFacturas, $fact['tipo'] . '-' . $fact['id']);
            }
            $conceptos = array_unique(array_merge($arrayConceptos, $arrayFacturas));
            $total = $entity->getTotal();
            $txtConcepto = array();
            try {
                // Proceso de facturas - Ajustar los saldos
                if ($conceptos[0]) {
                    foreach ($conceptos as $item) {
                        $doc = explode('-', $item);
                        if ($doc[0] == 'FAC') {
                            $comprob = $em->getRepository('VentasBundle:Factura')->find($doc[1]);
                        }
                        else {
                            $comprob = $em->getRepository('VentasBundle:NotaDebCred')->find($doc[1]);
                        }

                        if ($comprob && $total > 0) {
                            $comptxt = array('clave' => $item, 'monto' => $comprob->getSaldo());
                            if ($total >= $comprob->getSaldo()) {
                                //alcanza para cubrir el saldo
                                $total -= $comprob->getSaldo();
                                $comprob->setSaldo(0);
                                $comprob->setEstado('PAGADO');
                            }
                            else {
                                //no alcanza, impacta el total
                                $comprob->setSaldo($comprob->getSaldo() - $total);
                                $total = 0;
                                $comprob->setEstado('PAGO PARCIAL');
                            }
                            array_push($txtConcepto, $comptxt);
                            $em->persist($comprob);
                        }
                    }
                }
                $entity->setConcepto(json_encode($txtConcepto));

                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $entity->setPagoNro(sprintf("%06d", $equipo->getNroPagoVenta() + 1));
                /* Guardar ultimo nro */
                $equipo->setNroPagoVenta($equipo->getNroPagoVenta() + 1);

                // cheques
                foreach ($entity->getChequesRecibidos() as $cheque) {
                    if (is_null($cheque->getId())) {
                        $cheque->setTipo('T');
                        $cheque->setUsado(false);
                        $cheque->setTomado(new \DateTime);
                        $cheque->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                        $cheque->setChequeNro(sprintf("%06d", $equipo->getNroInternoCheque() + 1));
                        $equipo->setNroInternoCheque($equipo->getNroInternoCheque() + 1);
                    }
                    else {
                        $obj = $em->getRepository('ConfigBundle:Cheque')->find($cheque->getId());
                        $obj->setUsado(false);
                        $entity->removeChequesRecibido($cheque);
                        $entity->addChequesRecibido($obj);
                    }
                }
                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();
                $em->getConnection()->commit();

                return $this->redirectToRoute('ventas_cliente_pagos');
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }

        return $this->render('VentasBundle:Cliente:pago_edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/pagos/show", name="ventas_cliente_pagos_show")
     * @Method("GET")
     * @Template("VentasBundle:Cliente:pago_show.html.twig")
     */
    public function showAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_pagos');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:PagoCliente')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el pago.');
        }
        $texto = UtilsController::textoListaFacturasAction($entity->getConcepto(), $em);
        $entity->setConceptoTxt($texto);
        return $this->render('VentasBundle:Cliente:pago_show.html.twig', array(
                    'entity' => $entity));
    }

    /**
     * @Route("/pagos/deleteAjax/{id}", name="ventas_cliente_pagos_delete_ajax")
     * @Method("POST")
     */
    public function pagosDeleteAjaxAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_cliente_pagos');
        $id = $this->getRequest()->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:PagoCliente')->find($id);
        try {
            $em->getConnection()->beginTransaction();
            //restaurar saldos en facturas y notas de debito.
            $conceptos = json_decode($entity->getConcepto());
            foreach ($conceptos as $item) {
                $doc = explode('-', $item->clave);
                if ($doc[0] == 'FAC') {
                    $comprob = $em->getRepository('VentasBundle:Factura')->find($doc[1]);
                }
                else {
                    $comprob = $em->getRepository('VentasBundle:NotaDebCred')->find($doc[1]);
                }
                $comprob->setSaldo($comprob->getSaldo() + $item->monto);
                if ($comprob->getSaldo() == $comprob->getTotal())
                    $comprob->setEstado('PENDIENTE');
                else
                    $comprob->setEstado('PAGO PARCIAL');
                $em->persist($comprob);
            }

            // liberar cheques
            foreach ($entity->getChequesRecibidos() as $item) {
                $cheque = $em->getRepository('ConfigBundle:Cheque')->find($item->getId());
                $cheque->setUsado(false);
                $cheque->setPagoCliente(NULL);
                $em->persist($cheque);
            }
            $em->remove($entity);
            $em->flush();
            $em->getConnection()->commit();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            $msg = $ex->getTraceAsString();
        }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/getFacturasCliente", name="ventas_cliente_pagos_getfacturas")
     * @Method("GET")
     */
    public function getFacturasClienteAction(Request $request) {
        // facturas y notas de débito
        $id = $request->get('cli');
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('VentasBundle:Cliente')->getFacturasImpagas($id);
        $datos = array();
        foreach ($facturas as $value) {
            $text = '[ ' . $value['tipo'] . ' ' . $value['nroComprobante'] . ' $' . $value['saldo'] . ' ]';
            array_push($datos, array('clave' => $value['tipo'] . '-' . $value['id'], 'text' => $text));
        }

        $partial = $this->renderView('VentasBundle:Cliente:factura-cliente-row.html.twig',
                array('datos' => $datos));
        return new Response($partial);
    }

    /**
     * @Route("/getChequesCartera", name="ventas_cliente_pagos_getcheques")
     * @Method("GET")
     */
    public function getChequesCarteraAction() {
        $em = $this->getDoctrine()->getManager();
        $cheques = $em->getRepository('ConfigBundle:Cheque')->getChequesParaPago();
        $partial = $this->renderView('ConfigBundle:Cheque:_partial-cheques-cartera.html.twig',
                array('cheques' => $cheques));
        return new Response($partial);
    }

    /**
     * @Route("/getDatosCheque", name="ventas_cliente_pagos_getdatoscheque")
     * @Method("GET")
     */
    public function getDatosChequeAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $cheque = $em->getRepository('ConfigBundle:Cheque')->find($id);
        $titular = ($cheque->getTitularCheque()) ? $cheque->getTitularCheque()->getId() : NULL;
        $banco = ($cheque->getBanco()) ? $cheque->getBanco()->getId() : NULL;
        $array = array('id' => $cheque->getId(), 'nroCheque' => $cheque->getNroCheque(), 'nroInterno' => $cheque->getNroInterno(),
            'fecha' => $cheque->getFecha()->format('d-m-Y'), 'titular' => $titular,
            'dador' => $cheque->getDador(), 'banco' => $banco, 'tomado' => $cheque->getTomado()->format('d-m-Y'),
            'sucursal' => $cheque->getSucursal(), 'valor' => $cheque->getValor());
        return new Response(json_encode($array));
    }

    /**
     * @Route("/getListaClientes", name="get_lista_clientes")
     * @Method("GET")
     */
    public function getListaClientesAction() {
        $em = $this->getDoctrine()->getManager();
        $clientes = $em->getRepository('VentasBundle:Cliente')->findByActivo(1);
        $partial = $this->renderView('VentasBundle:Cliente:_partial-lista-clientes.html.twig',
                array('clientes' => $clientes));
        return new Response($partial);
    }
    /**
     * @Route("/getAutocompleteClientes", name="get_autocomplete_clientes")
     * @Method("POST")
     */
    public function getAutocompleteClientesAction( Request $request) {
        $term = $request->get('searchTerm');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('VentasBundle:Cliente')->filterByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/clienteListDatatables", name="cliente_list_datatables")
     * @Method("POST")
     * @Template()
     */
    public function clienteListDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->repository = $this->entityManager->getRepository('VentasBundle:Cliente');
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->get('draw'));
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search');
            $orders = $request->get('order');
            $columns = $request->get('columns');
        }
        else // If the request is not a POST one, die hard
            die;

        // Process Parameters
        // Orders

        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";

        // Get results from the Repository
        $results = $this->repository->getListDTData($start, $length, $orders, $search, $columns, $otherConditions = null);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->listcount();
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        // Construct response
        $response = '{
            "draw": ' . $draw . ',
            "recordsTotal": ' . $total_objects_count . ',
            "recordsFiltered": ' . $filtered_objects_count . ',
            "data": [';

        $i = 0;

        foreach ($objects as $key => $cliente) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = "-";

                switch ($column['name']) {
                    case 'nombre': {
                            // Do this kind of treatments if you suspect that the string is not JS compatible
                            $name = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $cliente->getNombre()));
                            $responseTemp = "<a class='nombre-cliente' data-id='".$cliente->getId()."' href='javascript:void(0);'>".$name."</a>";
                            // View permission ?
                            /* if ($this->get('security.authorization_checker')->isGranted('view_town', $town))
                              {
                              // Get the ID
                              $id = $town->getId();
                              // Construct the route
                              $url = '';
                              //$this->generateUrl('playground_town_view', array('id' => $id));
                              // Construct the html code to send back to datatables
                              $responseTemp = "<a href='".$url."' target='_self'>".$ref."</a>";
                              }
                              else
                              {
                              $responseTemp = $name;
                              } */
                            break;
                        }
                    case 'cuit': {
                            $cuit = $cliente->getCuit();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $cuit));
                            break;
                        }
                }

                // Add the found data to the json
                $response .= $responseTemp;

                if (++$j !== $nbColumn)
                    $response .= '","';
            }

            $response .= '"]';

            // Not on the last item
            if (++$i !== $selected_objects_count)
                $response .= ',';
        }

        $response .= ']}';

        // Send all this stuff back to DataTables
        return new Response($response);
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printVentasClientes.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ventas_cliente")
     * @Method("POST")
     */
    public function printVentasClientesAction(Request $request) {
        $items = $request->get('datalist');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('VentasBundle:Cliente:pdf-clientes.pdf.twig',
                array('items' => json_decode($items), 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_clientes' . $hoy->format('dmY_Hi') . '.pdf'));
    }


    /**
     * @Route("/clienteIndexDatatables", name="cliente_index_datatables")
     * @Method("POST")
     * @Template()
     */
    public function clienteIndexDatatablesAction(Request $request) {
        // Set up required variables
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->repository = $this->entityManager->getRepository('VentasBundle:Cliente');
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->get('draw'));
            $start = $request->get('start');
            $length = $request->get('length');
            $search = $request->get('search');
            $orders = $request->get('order');
            $columns = $request->get('columns');
        }
        else // If the request is not a POST one, die hard
            die;

        // Process Parameters
        // Orders

        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = "array or whatever is needed";

        // Get results from the Repository
        $results = $this->repository->getIndexDTData($start, $length, $orders, $search, $columns, $otherConditions = null);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $this->repository->indexCount();
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];
        $unidNeg = $this->get('session')->get('unidneg_id');

        // Construct response
        $response = '{
            "draw": ' . $draw . ',
            "recordsTotal": ' . $total_objects_count . ',
            "recordsFiltered": ' . $filtered_objects_count . ',
            "data": [';

        $i = 0;

        foreach ($objects as $key => $cliente) {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column) {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = '';
                switch ($column['name']) {
                    case 'nombre': {
                            $nombre = $cliente->getNombre();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $nombre));
                            break;
                        }
                    case 'cuit': {
                            $cuit = $cliente->getCuit();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $cuit));
                            break;
                        }
                    case 'direccion': {
                            $direccion = $cliente->getDireccion();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $direccion));
                            break;
                        }
                    case 'localidad': {
                            $localidad = $cliente->getLocalidad();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $localidad));
                            break;
                        }
                    case 'telefono': {
                            $telefono = $cliente->getTelefono();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $telefono));
                            break;
                        }
                    case 'saldo': {
                            $saldo = $cliente->getSaldo();
                            $responseTemp = htmlentities(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $saldo));
                            break;
                        }
                    case 'activo': {
                            $activo = ($cliente->getActivo()) ? " checked='checked'" : "";
                            $title = ($cliente->getActivo()) ? " title='Activo'" : " title='Inactivo'";
                            $responseTemp = "<input type='checkbox' disabled='disabled' ".$activo.$title. " />" ;
                           break;
                    }
                    case 'actions': {
                            $user = $this->getUser();
                            if ($user->getAccess($unidNeg, 'ventas_cliente_edit')) {
                                $linkEdit = "<a href='" . $this->generateUrl('ventas_cliente_edit', array('id' => $cliente->getId())) . "' class='editar btn btnaction btn_pencil' title='Editar' ></a>&nbsp;";
                                $responseTemp = $responseTemp . $linkEdit;
                            }
                            if ($user->getAccess($unidNeg, 'ventas_cliente_delete')) {
                                $linkDel = "<a href url='" . $this->generateUrl('ventas_cliente_delete_ajax', array('id' => $cliente->getId())) . "' class='delete btn btnaction btn_trash' title='Borrar' ></a>&nbsp;";
                                $responseTemp = $responseTemp . $linkDel;
                            }
                            break;
                        }
                }

                // Add the found data to the json
                $response .= $responseTemp;

                if (++$j !== $nbColumn)
                    $response .= '","';
            }

            $response .= '"]';

            // Not on the last item
            if (++$i !== $selected_objects_count)
                $response .= ',';
        }

        $response .= ']}';

        // Send all this stuff back to DataTables
        return new Response($response);
    }

    /**
     * @Route("/exportClientes",
     * name="export_ventas_cliente")
     * @Template()
     */
    public function exportClientesAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $search =  $request->get('searchterm');
        $items = $em->getRepository('VentasBundle:Cliente')->getClientesForExportXls($search);

        $partial = $this->renderView('VentasBundle:Cliente:export-xls.html.twig',
                array('items' => $items, 'search' => $search));
        $hoy = new \DateTime();
        $fileName = 'Clientes_' . $hoy->format('dmY_Hi');
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'filename="' . $fileName . '.xls"');
        $response->setContent($partial);
        return $response;
    }

}