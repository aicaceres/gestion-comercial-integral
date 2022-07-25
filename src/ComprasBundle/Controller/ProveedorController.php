<?php

namespace ComprasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Num2Str;
use ComprasBundle\Entity\Proveedor;
use ComprasBundle\Form\ProveedorType;
use ComprasBundle\Entity\PagoProveedor;
use ComprasBundle\Form\PagoProveedorType;
use ComprasBundle\Entity\RetencionGanancia;

/**
 * @Route("/proveedor")
 */
class ProveedorController extends Controller {

    /**
     * @Route("/", name="compras_proveedor")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $rubroId = $request->get('rubroId');
        $em = $this->getDoctrine()->getManager();
        $rubros = $em->getRepository('ConfigBundle:RubroCompras')->findBy(array(),array('tipo'=> 'ASC'));
        if( $rubroId ){
            $entities = $em->getRepository('ComprasBundle:Proveedor')->findByRubroCompras($rubroId);
        }else{
            $entities = $em->getRepository('ComprasBundle:Proveedor')->findAll();
        }
        foreach ($entities as $prov) {
            $saldo = $prov->getSaldoInicial();
            // Facturas
            $facturas = $em->getRepository('ComprasBundle:Proveedor')->getFacturasCompraxFecha($prov->getId(), $desde, $hasta, $rubroId);
            $saldo = $saldo + $facturas;
            // Notas debito
            $notaDebito = $em->getRepository('ComprasBundle:Proveedor')->getNotasCompraxFecha($prov->getId(), $desde, $hasta, '+', $rubroId);
            $saldo = $saldo + $notaDebito;
            // Notas crédito
            $notaCredito = $em->getRepository('ComprasBundle:Proveedor')->getNotasCompraxFecha($prov->getId(), $desde, $hasta, '-', $rubroId);
            $saldo = $saldo - $notaCredito;
            // Pagos
            $pagos = $em->getRepository('ComprasBundle:Proveedor')->getPagosxFecha($prov->getId(), $desde, $hasta, $rubroId);
            foreach ($pagos as $pago) {
                $saldo -= $pago->getTotal();
            }

            $prov->setSaldoxFechas($saldo);
        }
        return $this->render('ComprasBundle:Proveedor:index.html.twig', array(
                    'entities' => $entities,
                    'desde' => $desde,
                    'hasta' => $hasta,
                    'rubroId' => $rubroId,
                    'rubros' => $rubros
        ));
    }

    /**
     * @Route("/new", name="compras_proveedor_new")
     * @Method("GET")
     * @Template("ComprasBundle:Proveedor:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_new');
        $em = $this->getDoctrine()->getManager();
        $localidad = $em->getRepository('ConfigBundle:Localidad')->findOneByByDefault(1);
        $entity = new Proveedor();
        $entity->setLocalidad($localidad);
        $form = $this->createCreateForm($entity);

        return $this->render('ComprasBundle:Proveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Proveedor entity.
     * @param Proveedor $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Proveedor $entity) {
        $form = $this->createForm(new ProveedorType(), $entity, array(
            'action' => $this->generateUrl('compras_proveedor_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="compras_proveedor_create")
     * @Method("POST")
     * @Template("ComprasBundle:Proveedor:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_new');
        $entity = new Proveedor();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'El proveedor fue creado con Éxito!');
            return $this->redirect($this->generateUrl('compras_proveedor'));
        }
        return $this->render('ComprasBundle:Proveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="compras_proveedor_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Proveedor entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ComprasBundle:Proveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Proveedor entity.
     * @param Proveedor $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Proveedor $entity) {
        $form = $this->createForm(new ProveedorType(), $entity, array(
            'action' => $this->generateUrl('compras_proveedor_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="compras_proveedor_update")
     * @Method("PUT")
     * @Template("ComprasBundle:Proveedor:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Proveedor entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Los datos fueron modificados con Éxito!');
            return $this->redirect($this->generateUrl('compras_proveedor'));
        }
        return $this->render('ComprasBundle:Proveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="compras_proveedor_delete")
     * @Method("POST")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        try {
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Doctrine\DBAL\DBALException $e) {
            $msg = 'No puede eliminarse el Proveedor. Se utiliza en el sistema.';
        }
        return new Response(json_encode($msg));
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printComprasProveedores",
     * name="print_compras_proveedor")
     * @Method("POST")
     */
    public function printComprasProveedoresAction(Request $request) {
        $items = json_decode($request->get('datalist'));
        $hoy = new \DateTime();
        $filename = 'ListadoProveedores_' . $hoy->format('dmY_Hi') . '.xls';

        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $sheet = $phpExcelObject->setActiveSheetIndex(0);

        $user = $this->getUser()->getUsername();
        $phpExcelObject->getProperties()->setCreator($user)
                ->setLastModifiedBy($user)
                ->setTitle($filename)
                ->setDescription("Listado de Proveedores");

        // Escribir títulos
        $sheet->setCellValue('A1', 'LISTADO DE PROVEEDORES');
        $search = $request->get('searchterm');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $i = 2;
        if ($search) {
            $sheet->setCellValue('A' . $i, 'Término de Búsqueda: ' . $search);
            $i++;
        }
        if ($fdesde) {
            $sheet->setCellValue('A' . $i, 'Período: ' . $fdesde . ' al ' . $fhasta);
            $i++;
        }
        $i++;
        // Escribir encabezado
        $sheet->setCellValue('A' . $i, 'NOMBRE Y APELLIDO')
                ->setCellValue('B' . $i, 'CUIT')
                ->setCellValue('C' . $i, 'DIRECCION')
                ->setCellValue('D' . $i, 'LOCALIDAD')
                ->setCellValue('E' . $i, 'TELEFONO')
                ->setCellValue('F' . $i, 'SALDO')
                ->setCellValue('G' . $i, 'ACTIVO');

        // Escribir contenido
        $i++;
        foreach ($items as $item) {
            $sheet->getStyle('F' . $i)->getNumberFormat()->setFormatCode('0.000');

            $saldo = str_replace(',', '', $item['5']);

            $sheet->setCellValue('A' . $i, $item['0'])
                    ->setCellValue('B' . $i, $item['1'])
                    ->setCellValue('C' . $i, $item['2'])
                    ->setCellValue('D' . $i, $item['3'])
                    ->setCellValue('E' . $i, $item['4'])
                    ->setCellValue('F' . $i, $saldo)
                    ->setCellValue('G' . $i, $item['6']);
            $i++;
        }

        $phpExcelObject->getActiveSheet()->setTitle('Listado de Proveedores');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers


        $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;



        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Proveedor:pdf-proveedores.pdf.twig',
                array('items' => json_decode($items), 'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_proveedores' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /**
     * @Route("/printProveedorPagos.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_proveedor_pagos")
     * @Method("POST")
     */
    public function printProveedorPagosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $proveedorId = $request->get('proveedorid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $textoFiltro = array($proveedor ? $proveedor->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Proveedor:pdf-pagosproveedor.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_pagos_proveedor_' . $proveedor->getNombre() . '.pdf'));
    }

    /**
     * @Route("/ctacte", name="compras_proveedor_ctacte")
     * @Method("get")
     * @Template()
     */
    public function ctacteAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_proveedor_ctacte');
        $id = $request->get('proveedorId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $em = $this->getDoctrine()->getManager();
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findBy(array(), array('nombre' => 'ASC'));
        if ($id)
            $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        else
            $proveedor = $proveedores[0];
        $ctacte = $em->getRepository('ComprasBundle:Proveedor')->getDetalleCtaCte($proveedor->getId(), $desde, $hasta);
        foreach ($ctacte as $key => &$var) {
            if ($var['tipo'] == 1) {
                $fact = $em->getRepository('ComprasBundle:Factura')->find($var['id']);
                //$concepto = $fact->getCantidadItems() . ' Art.';
                $var['concepto'] = 'Factura ' . $fact->getTipoFactura();
                $var['comprobante'] = 'FAC ' . $fact->getTipoFactura() . $fact->getNroComprobante();
            }
            elseif ($var['tipo'] == 2) {
                $cred = $em->getRepository('ComprasBundle:NotaDebCred')->find($var['id']);
                $letranro = $cred->getTipoNota() . $cred->getNroComprobante();
                if ($cred->getSigno() == '+') {
                    $var['tipo'] = 4;
                    $var['comprobante'] = 'DEB '. $letranro;
                    $var['concepto'] = 'Nota de Débito';
                }
                else {
                    $var['comprobante'] = 'CRE '. $letranro;
                    $var['concepto'] = 'Nota de Crédito';
                }
            }
            elseif ($var['tipo'] == 3) {
                $pago = $em->getRepository('ComprasBundle:PagoProveedor')->find($var['id']);
                $var['comprobante'] = 'REC ' . $pago->getNroPago();
                $text = '';
                $conceptos = json_decode($pago->getConcepto());
                foreach ($conceptos as $item) {
                    $doc = explode('-', $item->clave);
                    if ($doc[0] == 'FAC') {
                        $comprob = $em->getRepository('ComprasBundle:Factura')->find($doc[1]);
                    }
                    else {
                        $comprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($doc[1]);
                    }
                    $text = $text . ' [ ' . $doc[0] . ' ' . $comprob->getNroComprobante() . ' $' . $item->monto . '] ';
                }
                $var['concepto'] = 'Pago: ' . UtilsController::myTruncate($text, 30);
                $var['importe'] = $pago->getImporte();
            }
        }
        $ord = usort($ctacte, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Ymd'));
            $value2 = strtotime($a2['fecha']->format('Ymd'));
            if ($value1 != $value2) {
                return $value1 > $value2;
            }
            return $a1['comprobante'] > $a2['comprobante'];
        });
        return $this->render('ComprasBundle:Proveedor:ctacte.html.twig', array(
                    'entities' => $ctacte, 'proveedores' => $proveedores, 'proveedor' => $proveedor,
                    'desde' => $desde, 'hasta' => $hasta
        ));
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printProveedorCtaCte.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_proveedor_ctacte")
     * @Method("POST")
     */
    public function printProveedorCtaCteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $proveedorId = $request->get('proveedorid');
        $fdesde = $request->get('fdesde');
        $fhasta = $request->get('fhasta');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($proveedorId);
        $textoFiltro = array($proveedor ? $proveedor->getNombre() : 'Todos', $fdesde ? $fdesde : '', $fhasta ? $fhasta : '');

        //    $logo1 = __DIR__.'/../../../web/bundles/app/img/logobanner1.jpg';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';
        $mas = __DIR__ . '/../../../web/assets/images/icons/in.png';
        $menos = __DIR__ . '/../../../web/assets/images/icons/out.png';
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Proveedor:pdf-ctacte.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'mas' => $mas, 'menos' => $menos), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);

        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=ctacte_' . $proveedor->getNombre() . '.pdf'));
    }

    /**
     * @Route("/selectFacturas", name="select_facturas")
     * @Method("GET")
     */
    public function facturasAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('ComprasBundle:Factura')->findByProveedorId($id);
        //$facturas = $em->getRepository('ComprasBundle:Factura')->findBy(array('proveedor'=>$id,'estado'=>'PENDIENTE'), array('fechaFactura' => 'ASC'));

        return new JsonResponse($facturas);
    }

    /**
     * @Route("/updateCuit", name="update_cuit_proveedor")
     * @Method("GET")
     */
    public function updateCuitAction(Request $request) {
        $id = $request->get('id');
        $txt = $request->get('txt');
        if (UtilsController::validarCuit($txt)) {
            // actualizar el cuit al proveedor 2720837312
            $em = $this->getDoctrine()->getManager();
            $formatCuit = substr($txt, 0, 2) . '-' . substr($txt, 2, 8) . '-' . substr($txt, 10, 1);
            $existe = $em->getRepository('ComprasBundle:Proveedor')->checkExiste($formatCuit, $id);
            if ($existe) {
                return new JsonResponse('EXISTE');
            }
            else {
                $prov = $em->getRepository('ComprasBundle:Proveedor')->find($id);
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
     * PAGOS
     */

    /**
     * @Route("/pagos", name="compras_proveedor_pagos")
     * @Method("GET")
     * @Template()
     */
    public function pagosIndexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'compras_proveedor_pagos');

        $provId = $request->get('provId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $em = $this->getDoctrine()->getManager();
        $proveedor = null;
        if($provId){
            $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($provId);
        }
        $entities = $em->getRepository('ComprasBundle:PagoProveedor')->findPagosByCriteria($provId, $desde, $hasta);
        foreach ($entities as $pago) {
            $texto = UtilsController::textoListaFacturasAction($pago->getConcepto(), $em);
            $pago->setConceptoTxt(UtilsController::myTruncate($texto, 30));
            $pago->setConcepto($texto);
            $detalle = UtilsController::myTruncate($pago->getDetalle(), 30);
            $pago->setDetalle($detalle);
        }
        return $this->render('ComprasBundle:Proveedor:pago_index.html.twig', array(
            'entities' => $entities, 'provId' => $provId, 'proveedor'=>$proveedor,
            'desde' => $desde, 'hasta' => $hasta
        ));
    }

    /**
     * @Route("/pagos/new", name="compras_proveedor_pagos_new")
     * @Method("GET")
     * @Template("ComprasBundle:Proveedor:pago_edit.html.twig")
     */
    public function pagosNewAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_pagos');
        $entity = new PagoProveedor();
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $entity->setPagoNro(sprintf("%06d", $equipo->getNroPagoCompra() + 1));
        $entity->setProveedor($proveedor);
        $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneByCodigoAfip('PES');
        $entity->setMoneda($moneda);
        $porcRentas = $this->getPorcentajesRentas($id,$em);
        $adic = ($porcRentas['porcAdicRentas']>0) ? ' + '.$porcRentas['porcAdicRentas'].'%' : '';
        $lblRentas = $porcRentas['porcRetRentas'].'%'. $adic;
        $form = $this->pagosCreateCreateForm($entity);
        return $this->render('ComprasBundle:Proveedor:pago_edit.html.twig', array(
                    'entity' => $entity,
                    'lblRentas' => $lblRentas,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PagoProveedor entity.
     * @param PagoProveedor $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function pagosCreateCreateForm(PagoProveedor $entity) {
        $form = $this->createForm(new PagoProveedorType(), $entity, array(
            'action' => $this->generateUrl('compras_proveedor_pagos_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/pagos", name="compras_proveedor_pagos_create")
     * @Method("POST")
     */
    public function pagosCreateAction(Request $request) {
        $session = $this->get('session');
        $unidneg_id = $session->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'compras_proveedor_pagos');
        $msg = 'OK';
        $formData = $request->get('comprasbundle_pagoproveedor');
        $entity = new PagoProveedor();
        $form = $this->pagosCreateCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $conceptos = explode(',', $request->get('txtconcepto'));
            //$baseImponibleGanancias = $request->get('baseImponibleGanancias');
            /*$arrayConceptos = explode(',', $request->get('txtconcepto'));
            $facturasImpagas = $em->getRepository('ComprasBundle:Proveedor')->getFacturasImpagas($entity->getProveedor()->getId());
            $arrayFacturas = array();
            foreach ($facturasImpagas as $fact) {
                array_push($arrayFacturas, $fact['tipo'] . '-' . $fact['id']);
            }
            $conceptos = array_unique(array_merge($arrayConceptos, $arrayFacturas));*/

            $txtConcepto = array();
            try {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                // checkear apertura de caja
                $apertura = $em->getRepository('VentasBundle:CajaApertura')->findOneBy(array('caja'=>1,'fechaCierre'=>null));
                if( !$apertura ){
                    $this->addFlash('error', 'La caja está cerrada. Debe realizar la apertura para iniciar cobros');
                    return $this->redirect( $request->headers->get('referer') );
                }

                $totalPago = 0;
                // limpiar cheque y tarjeta si no corresponde
                foreach( $entity->getCobroDetalles() as $detalle ){
                    if( $detalle->getImporte()==0 ){
                        $entity->removeCobroDetalle($detalle);
                        continue;
                    }
                    $detalle->setCajaApertura($apertura);
                    if(!$detalle->getMoneda()){
                        $detalle->setMoneda($entity->getMoneda());
                    }
                    $tipoPago = $detalle->getTipoPago();
                    if( $tipoPago != 'CHEQUE' ){
                        $detalle->setChequeRecibido(null);
                    }else{
                        $cheque = $detalle->getChequeRecibido();
                        if ($cheque->getId()) {
                            $obj = $em->getRepository('ConfigBundle:Cheque')->find($cheque->getId());
                            $detalle->setChequeRecibido($obj);
                            $detalle->getChequeRecibido()->setUsado(true);
                        }else{
                            $cheque->setTomado(new \DateTime);
                            $cheque->setUsado(true);
                        }
                    }
                    if( $tipoPago != 'TARJETA' ){
                        $detalle->setDatosTarjeta(null);
                    }
                    // sumar importes para calcular nc
                    $totalPago += $detalle->getImporte();
                }
                $resto = round($totalPago + $formData['montoRentas'] + $formData['montoGanancias'], 3);
                $entity->setSaldo( $formData['importe'] - $totalPago );
                $entity->setImporte( $resto );
                // recorrer comprobantes, imputar el pago y marcar como retencionesAplicadas
                foreach ($conceptos as $item) {
                    $doc = explode('-', $item);
                    if ($doc[0] == 'FAC') {
                        $comprob = $em->getRepository('ComprasBundle:Factura')->find($doc[1]);
                    }
                    else {
                        $comprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($doc[1]);
                    }
                    $montocomp = $saldoComprob = round($comprob->getSaldo(), 3);
                    if ($resto >= $saldoComprob) {
                        //alcanza para cubrir el saldo
                        $resto = round(($resto - $saldoComprob), 3);
                        $comprob->setSaldo(0);
                        $comprob->setEstado('PAGADO');
                    }
                    else {
                        //no alcanza, impacta el total
                        $montocomp = $resto;
                        $comprob->setSaldo(round(($saldoComprob - $resto), 3));
                        $resto = 0;
                        $comprob->setEstado('PAGO PARCIAL');
                    }
                    $comprob->setRetencionesAplicadas(1);
                    $comptxt = array('clave' => $item, 'monto' => $montocomp);
                    array_push($txtConcepto, $comptxt);
                    $em->persist($comprob);
                }

/*
                $total = round($formData['importe'] + $formData['montoRentas'] + $formData['montoGanancias'], 3);

                // Proceso de facturas - Ajustar los saldos
                foreach ($conceptos as $item) {
                    $doc = explode('-', $item);
                    if ($doc[0] == 'FAC') {
                        $comprob = $em->getRepository('ComprasBundle:Factura')->find($doc[1]);
                    }
                    else {
                        $comprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($doc[1]);
                    }
                    $saldoComprob = round($comprob->getSaldo(), 3);
                    // cancelar el comprobante
                    if ($comprob && $total > 0) {
                        if ($total >= $saldoComprob) {
                            //alcanza para cubrir el saldo
                            $montofinal = $saldoComprob;
                            $total = round($total - $comprob->getSaldo(), 3);
                            $comprob->setSaldo(0);
                            $comprob->setEstado('PAGADO');
                        }
                        else {
                            //no alcanza, impacta el total
                            $montofinal = $total;
                            $comprob->setSaldo(round(($saldoComprob - $total), 3));
                            $total = 0;
                            $comprob->setEstado('PAGO PARCIAL');
                        }
                        $comptxt = array('clave' => $item, 'monto' => $montofinal);
                        array_push($txtConcepto, $comptxt);
                        $em->persist($comprob);
                    }
                }*/

                $entity->setConcepto(json_encode($txtConcepto));
                $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $entity->setPagoNro(sprintf("%06d", $equipo->getNroPagoCompra() + 1));
                /* Guardar ultimo nro */
                $equipo->setNroPagoCompra($equipo->getNroPagoCompra() + 1);
                $baseImponibleGanancias = $formData['baseImponibleRentas'];
                // si hay retencion ganancias guardar el acumulado
                if( $entity->getRetencionGanancias()==0 ){
                    // buscar periodo
                    $hoy = new \DateTime();
                    $retencionGanancia = $em->getRepository('ComprasBundle:RetencionGanancia')->findOneBy(
                            array('proveedor'=>$entity->getProveedor()->getId(), 'periodo' => $hoy->format('Ym') )
                    );
                    if( !$retencionGanancia ){
                        $retencionGanancia = new RetencionGanancia();
                        $retencionGanancia->setPeriodo( $hoy->format('Ym'));
                        $retencionGanancia->setProveedor($entity->getProveedor());
                    }
                    $retencionGanancia->setAcumuladoTotal( $retencionGanancia->getAcumuladoTotal() + $baseImponibleGanancias );
                    $retencionGanancia->setAcumuladoRetencion( $retencionGanancia->getAcumuladoRetencion() + $entity->getRetencionGanancias());
                    $em->persist($retencionGanancia);
                }

                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();
                $em->getConnection()->commit();

                $urlretrentas = $entity->getRetencionRentas()>0 ? $this->generateUrl('print_comprobante_pago', array('id' => $entity->getId() )) : '';

                $res = array( 'msg' => 'OK',
                                'urlback' =>  $this->generateUrl('compras_proveedor_pagos', ['provId'=>$entity->getProveedor()->getId()]),
                                'urlretrentas' => $urlretrentas
                            );
                $em->getConnection()->commit();

                return new JsonResponse($res) ;
            }
            catch (\Exception $ex) {
                $msg = $ex->getMessage();
                $em->getConnection()->rollback();
                return new JsonResponse( array( 'msg' => $msg ) );
            }
        }

        $errors = array();
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = (String) $form[$child->getName()]->getErrors();
                }
            }
        }
        return new JsonResponse( array( 'msg' => $errors ) );
    }

    /**
     * @Route("/pagos/show", name="compras_proveedor_pagos_show")
     * @Method("GET")
     * @Template("ComprasBundle:Proveedor:pago_show.html.twig")
     */
    public function showAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_pagos');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el pago.');
        }
        $texto = UtilsController::textoListaFacturasAction($entity->getConcepto(), $em);
        $entity->setConceptoTxt($texto);
        return $this->render('ComprasBundle:Proveedor:pago_show.html.twig', array(
                    'entity' => $entity));
    }

    /**
     * IMPRESION DE comprobante
     */

    /**
     * @Route("/{id}/printComprobantePago.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_comprobante_pago")
     * @Method("GET")
     */
    public function printComprobantePagoAction($id){
        $em = $this->getDoctrine()->getManager();
        $pago = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        $texto = UtilsController::arrayListaFacturasPrintAction($pago->getConcepto(), $em);
        $pago->setConceptoTxt( $texto );

        $empresa = $em->getRepository('ConfigBundle:Empresa')->find(1);
        $logo = __DIR__.'/../../../web/assets/images/logo_comprobante_bn.png';
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $num2str = new Num2Str();
        $letras = $num2str->ValorEnLetras($pago->getMontoRetencionRentas());
        $array = array( 'pago'=> $pago, 'letras' => $letras, 'empresa'=>$empresa, 'logo'=>$logo );

        $this->render('ComprasBundle:Proveedor:comprobante-pago.pdf.twig', $array, $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition'=>'filename='.$hoy->format('YmdHi').'.pdf'));
    }


    /**
     * @Route("/{id}/imprimir.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="compras_pago_print")
     * @Method("GET")
    */
    /*
    public function printAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_pagos');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        $comprobantes = UtilsController::textoListaFacturasPrintAction($entity->getConcepto(), $em);
        //$entity->setConceptoTxt($texto);
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Proveedor:pdf-pago.pdf.twig',
                array('entity' => $entity, 'comprobantes' => $comprobantes), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $filename = 'pago_"' . UtilsController::Slug(Trim($entity->getProveedor()->getNombre())) . '"_"' . $entity->getNroPago() . '".pdf';
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=' . $filename));
    } */

    /**
     * @Route("/pagos/deleteAjax/{id}", name="compras_proveedor_pagos_delete_ajax")
     * @Method("POST")
     */
    public function pagosDeleteAjaxAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_proveedor_pagos');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        try {
            $em->getConnection()->beginTransaction();
            //restaurar saldos en facturas y notas de debito.
            $conceptos = json_decode($entity->getConcepto());

            foreach ($conceptos as $item) {
                $doc = explode('-', $item->clave);
                if ($doc[0] == 'FAC') {
                    $comprob = $em->getRepository('ComprasBundle:Factura')->find($doc[1]);
                }
                else {
                    $comprob = $em->getRepository('ComprasBundle:NotaDebCred')->find($doc[1]);
                }
                $comprob->setSaldo($comprob->getSaldo() + $item->monto);
                if ($comprob->getSaldo() == $comprob->getTotal())
                    $comprob->setEstado('PENDIENTE');
                else
                    $comprob->setEstado('PAGO PARCIAL');
                if( $entity->getBaseImponibleRentas()>0 ){
                    // desmarcar retenciones aplicadas unicamente si se aplicaron en este pago
                    $comprob->setRetencionesAplicadas(0);
                }

                $em->persist($comprob);
            }
            // descontar acumulado de rentencion ganancias si corresponde
            if( $entity->getRetencionGanancias()>0){
                $retencionGanancia = $em->getRepository('ComprasBundle:RetencionGanancia')->findOneBy(
                            array('proveedor'=>$entity->getProveedor()->getId(), 'periodo' => $entity->getFecha()->format('Ym') )
                    );
                if($retencionGanancia){
                    $retencionGanancia->setAcumuladoTotal( $retencionGanancia->getAcumuladoTotal() - $entity->getBaseImponibleRentas() );
                    $retencionGanancia->setAcumuladoRetencion( $retencionGanancia->getAcumuladoRetencion() - $entity->getMontoGanancias() );
                    $em->persist($retencionGanancia);
                }
            }

            // liberar cheques y eliminar cobrodetalle
            foreach ($entity->getCobroDetalles() as $item) {
                if( $item->getChequeRecibido() ){
                    $cheque = $em->getRepository('ConfigBundle:Cheque')->find($item->getChequeRecibido()->getId());
                    $cheque->setUsado(false);
                    $em->persist($cheque);
                }
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
     * @Route("/getFacturasProveedor", name="compras_proveedor_pagos_getfacturas")
     * @Method("GET")
     */
    public function getFacturasProveedorAction(Request $request) {
        // facturas y notas de debito
        $id = $request->get('prov');
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('ComprasBundle:Proveedor')->getFacturasImpagas($id);
        // genera select de comprobantes adeudados
        $partial = $this->renderView('ComprasBundle:Proveedor:factura-proveedor-row.html.twig',
                array('facturas' => $facturas ));
        return new Response($partial);
    }
    /**
     * @Route("/getMontoAPagar", name="compras_proveedor_pagos_getMontoAPagar")
     * @Method("GET")
     */
    public function getMontoAPagarAction(Request $request) {
        // facturas y notas de debito seleccionadas
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('provId');
        $porcRentas = $this->getPorcentajesRentas($id, $em);
        $datos = array(
            'baseImponible'=> 0,
            'rentas'=> 0,
            'porcRentas'=> 0,
            'adicional'=> 0,
            'porcAdicional'=> 0,
            'lblrentas' => '',
            'porcGanancia'=> 0,
            'ganancias'=> 0,
            'total'=> 0);
        $datos['porcRentas'] = $porcRentas['porcRetRentas'];
        $datos['porcAdicional'] = $porcRentas['porcAdicRentas'];
        $selected = $request->get('selected');
        if( is_array($selected) ){
            $montoRetRentas = 0;
            foreach ($selected as $sel){
                $comp = explode("-", $sel);
                if( $comp[0] == 'FAC' ){
                    $obj = $em->getRepository('ComprasBundle:Factura')->find($comp[1]);
                }else{
                    $obj = $em->getRepository('ComprasBundle:NotaDebCred')->find($comp[1]);
                }
                $datos['total'] += $obj->getSaldo();
                $proveedor = $obj->getProveedor();

                // si ya se imputaron las retenciones saltar comprobante para calculo
                if( $obj->getRetencionesAplicadas() ) continue;

                $neto = $obj->getSaldoImponible();
                // calcular retencion rentas
                $retrentas = $porcRentas['porcRetRentas'];
                $adicrentas = $porcRentas['porcAdicRentas'];
                if( $retrentas>0 ){
                    if( $neto >= floatval( $proveedor->getCategoriaRentas()->getMinimo())  ){
                        $datos['baseImponible'] += $neto;
                        $rentas = $neto * ( $retrentas / 100 );
                        $adicional = $rentas * ( $adicrentas / 100 );
                        $monto = $rentas + $adicional;
                    }else{
                        // setear en cero porque no se aplica retención por ser menor al mínimo
                        $monto = $rentas = $adicional = 0;
                    }
                }
                $datos['rentas'] += $rentas;
                $datos['adicional'] += $adicional;
                $montoRetRentas += $monto;
            }
            // si montoRetRentas es 0 limpiar los porcentajes.
            if( $montoRetRentas == 0 ){
                $datos['porcRentas'] = $datos['porcAdicional'] = 0;
            }
            $aux = ($datos['porcAdicional']>0) ? ' + '.$datos['porcAdicional'].'%' : '';
            $datos['lblrentas'] = $datos['porcRentas'].'%'. $aux;

            // calcular retencion Ganancias
            $hoy = new \DateTime();
            $fechaExcepcionGanancias = $proveedor->getVencCertExcepcionGanancias() ? $proveedor->getVencCertExcepcionGanancias()->format('Y-m-d') : null;
            $exento = 0;
            $retencionGanancia = $em->getRepository('ComprasBundle:RetencionGanancia')->findOneBy(
                                            array('proveedor'=>$proveedor->getId(), 'periodo'=> $hoy->format('Ym') ));
            $acumuladoTotalGanancia = $retencionGanancia ? $retencionGanancia->getAcumuladoTotal() : 0;
            $acumuladoRetencionGanancia = $retencionGanancia ? $retencionGanancia->getAcumuladoRetencion() : 0;
            if( $proveedor->getActividadComercial() && $proveedor->getCategoriaIva() && ($fechaExcepcionGanancias < $hoy->format('Y-m-d') ||  is_null($fechaExcepcionGanancias) )  ){
                $catIva = $proveedor->getCategoriaIva()->getNombre();
                $exento = floatval($proveedor->getActividadComercial()->getExento()) ;
                if( $catIva == 'N' ){
                    $datos['porcGanancia'] = floatval($proveedor->getActividadComercial()->getNoInscripto());
                    $exento = 0;
                }elseif( $catIva == 'I' ){
                    $datos['porcGanancia'] = floatval($proveedor->getActividadComercial()->getInscripto());
                }
            }
            if( $proveedor->getActividadComercial() && ($datos['baseImponible'] + $acumuladoTotalGanancia) > $exento ){
                $hasta = $datos['baseImponible'] + $acumuladoTotalGanancia - $exento;

                $escala = $em->getRepository('ConfigBundle:Escalas')->getEscalaByHasta( $proveedor->getActividadComercial()->getCodigo(), $hasta );
                if($escala){
                    $datos['ganancias'] = ( $datos['baseImponible'] - $escala->getDesde() ) * ( $escala->getPorcExcedente()/100) + $escala->getFijo() - $acumuladoRetencionGanancia;
                }else{
                    $datos['ganancias'] = ($datos['baseImponible'] * ($datos['porcGanancia'] / 100)) - $acumuladoRetencionGanancia;
                }

                $minimo = $proveedor->getActividadComercial()->getMinimo();
                if( $acumuladoRetencionGanancia < $minimo){
                    if( $datos['ganancias'] < $minimo){
                        $datos['ganancias'] = 0;
                    }
                }

            }
            // total a pagar menos las retenciones
            $datos['porcGanancia'] = ($datos['ganancias'] == 0) ? 0 : $datos['porcGanancia'] ;
            $datos['total'] = $datos['total'] - $montoRetRentas - $datos['ganancias'];
        }
        return new JsonResponse( $datos );
    }

    /**
     * Obtener porcentaje de retencion de rentas
     */
    private function getPorcentajesRentas($id, $em)
    {
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        $hoy = new \DateTime();
        $retrentas = $adicrentas = 0;
        // get periodo de excepción de rentas
        $fechaNoRetenerRentas = $proveedor->getVencCertNoRetenerRentas() ? $proveedor->getVencCertNoRetenerRentas()->format('Y-m-d') : null;
        if( $proveedor->getCategoriaRentas() && ( $fechaNoRetenerRentas < $hoy->format('Y-m-d') ||  is_null($fechaNoRetenerRentas) ) ){
            $retrentas = $proveedor->getCategoriaRentas()->getRetencion();
            $adicrentas = $proveedor->getCategoriaRentas()->getAdicional() ;
        }
        return array('porcRetRentas'=> $retrentas, 'porcAdicRentas'=>$adicrentas);
    }
/*
        // facturas y notas de dÃ©bito
        $id = $request->get('prov');
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('ComprasBundle:Proveedor')->getFacturasImpagas($id);
        $datos = array();
        ///  obtener porcentaje de retencion de rentas si corresponde
        $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        $hoy = new \DateTime();
        $retrentas = $adicrentas = 0;
        // get periodo de excepción de rentas y ganancias
        $fechaNoRetenerRentas = $proveedor->getVencCertNoRetenerRentas() ? $proveedor->getVencCertNoRetenerRentas()->format('Y-m-d') : null;
        $fechaExcepcionGanancias = $proveedor->getVencCertExcepcionGanancias() ? $proveedor->getVencCertExcepcionGanancias()->format('Y-m-d') : null;
        if( $proveedor->getCategoriaRentas() && ( $fechaNoRetenerRentas < $hoy->format('Y-m-d') ||  is_null($fechaNoRetenerRentas) ) ){
            $retrentas = $proveedor->getCategoriaRentas()->getRetencion();
            $adicrentas = $proveedor->getCategoriaRentas()->getAdicional() ;
        }
        // para calculo de retencion ganancias
        $porcGanancias = $exento = 0;
        $retencionGanancia = $em->getRepository('ComprasBundle:RetencionGanancia')->findOneBy(
                                        array('proveedor'=>$proveedor->getId(), 'periodo'=> $hoy->format('Ym') ));
        $acumuladoTotalGanancia = $retencionGanancia ? $retencionGanancia->getAcumuladoTotal() : 0;
        $acumuladoRetencionGanancia = $retencionGanancia ? $retencionGanancia->getAcumuladoRetencion() : 0;
        if( $proveedor->getActividadComercial() && $proveedor->getCategoriaIva() && ($fechaExcepcionGanancias < $hoy->format('Y-m-d') ||  is_null($fechaExcepcionGanancias) )  ){
            $catIva = $proveedor->getCategoriaIva()->getNombre();
            $exento = floatval($proveedor->getActividadComercial()->getExento()) ;
            if( $catIva == 'N' ){
                $porcGanancias = $proveedor->getActividadComercial()->getNoInscripto();
                $exento = 0;
            }elseif( $catIva == 'I' ){
                $porcGanancias = $proveedor->getActividadComercial()->getInscripto();
            }
        }

        $baseImponible = 0;
        foreach ($facturas as $value) {
            $montoRetRentas = 0;
            $neto = $value['total'] - $value['iva'];
            $baseImponible += $neto;
            // calcular retencion rentas
            if( $retrentas>0 ){
                if( $neto >= floatval( $proveedor->getCategoriaRentas()->getMinimo())  ){
                    $retencion = $neto * ( $retrentas / 100 );
                    $adicional = $retencion * ( $adicrentas / 100 );
                    $montoRetRentas = $retencion + $adicional;
                }
            }
            $total = $value['total'] - $montoRetRentas;
            $text = '[ ' . $value['tipo'] .'-'. $value['letra']. ' ' . $value['nroComprobante'] . ' | ' . $value['fecha']->format('d-m-Y') . ' | $' . $value['total'] . ' ]';
            array_push($datos,
                array('clave' => $value['tipo'] . '-' . $value['id'], 'text' => $text,
                    'montoRetRentas'=>$montoRetRentas, 'total' => $total));
        }
        $montoRetGanancias = 0;
        if( $proveedor->getActividadComercial() && ($baseImponible + $acumuladoTotalGanancia) > $exento ){
            $hasta = $baseImponible + $acumuladoTotalGanancia - $exento;

            $escala = $em->getRepository('ConfigBundle:Escalas')->getEscalaByHasta( $proveedor->getActividadComercial()->getCodigo(), $hasta );
            if($escala){
                $montoRetGanancias = ( $baseImponible - $escala->getDesde() ) * ( $escala->getPorcExcedente()/100) + $escala->getFijo() - $acumuladoRetencionGanancia;
            }else{
                $montoRetGanancias = ($baseImponible * ($porcGanancias / 100)) - $acumuladoRetencionGanancia;
            }

            $minimo = $proveedor->getActividadComercial()->getMinimo();
            if( $acumuladoRetencionGanancia < $minimo){
                if( $montoRetGanancias < $minimo){
                    $montoRetGanancias = 0;
                }
            }

        }

        $partial = $this->renderView('ComprasBundle:Proveedor:factura-proveedor-row.html.twig',
                array('datos' => $datos ));
        return new JsonResponse( array( 'partial' => $partial, 'baseImponible' => $baseImponible,
            'retganancias' => $montoRetGanancias,
            'retrentas' => $retrentas, 'adicrentas' => $adicrentas ) );
    }*/

    /**
     * @Route("/getChequesCartera", name="compras_proveedor_pagos_getcheques")
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
     * @Route("/getDatosCheque", name="compras_proveedor_pagos_getdatoscheque")
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
            'dador' => $cheque->getDador(), 'banco' => $banco, 'tomado' =>  $cheque->getTomado() ? $cheque->getTomado()->format('d-m-Y') : null,
            'sucursal' => $cheque->getSucursal(), 'valor' => $cheque->getValor());
        return new Response(json_encode($array));
    }

    /**
     * @Route("/getCuitProveedor", name="get_cuit_proveedor")
     * @Method("GET")
     */
    public function getCuitProveedor(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $prov = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        $res = array('cuit' => $prov->getCuit(), 'valido' => UtilsController::validarCuit($prov->getCuit()));
        return new Response(json_encode($res));
    }

    /*
     * INFORME IIBB CHACO
     */

    /**
     * @Route("/informe/iibbchaco", name="compras_informe_iibbchaco")
     * @Method("GET")
     * @Template()
     */
    public function iibbChacoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $desde = UtilsController::toAnsiDate($request->get('fecha_desde'));
        $hasta = UtilsController::toAnsiDate($request->get('fecha_hasta'));

        $facturas = $em->getRepository('ComprasBundle:Factura')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));
        $notas = $em->getRepository('ComprasBundle:NotaDebCred')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));
        $provincia = 0;
        $items = array();
        foreach ($facturas as $fact) {
            if ($fact->getProveedor()->getLocalidad()) {
                $provincia = $fact->getProveedor()->getLocalidad()->getProvincia()->getId();
            }
            if ($fact->getFechaFactura()->format('Y-m-d') >= $desde && $fact->getFechaFactura()->format('Y-m-d') <= $hasta && $fact->getEstado() != 'CANCELADO') {
                $nro = explode('-', $fact->getNroComprobante());
                $item = array('fecha' => $fact->getFechaFactura(), 'tipoComprobante' => 'FC', 'tipo' => $fact->getTipoFactura(),
                    'tipofact' => $nro[0], 'nrocomp' => (isset($nro[1])) ? $nro[1] : '0', 'cuit' => $fact->getProveedor()->getCuit(),
                    'razon' => $fact->getProveedor()->getNombre(),
                    'pcia' => $provincia,
                    'iibb' => $fact->getProveedor()->getIibb(),
                    'neto' => $fact->getSubtotalNeto(),
                    'percDgr' => $fact->getPercepcionDgr());
                array_push($items, $item);
            }
        }
        $provincia = 0;
        foreach ($notas as $nota) {
            if ($nota->getProveedor()->getLocalidad()) {
                $provincia = $nota->getProveedor()->getLocalidad()->getProvincia()->getId();
            }
            if ($nota->getFecha()->format('Y-m-d') >= $desde && $nota->getFecha()->format('Y-m-d') <= $hasta && ( $provincia == 5 || $provincia == 15 )) {
                $nro = explode('-', $nota->getNroComprobante());
                if ($nota->getSigno() == '-') {
                    $i = -1;
                    $tipo = 'NC';
                }
                else {
                    $i = 1;
                    $tipo = 'ND';
                }
                $item = array('fecha' => $nota->getFecha(), 'tipoComprobante' => $tipo, 'tipo' => $nota->getTipoNota(),
                    'tipofact' => $nro[0], 'nrocomp' => (isset($nro[1])) ? $nro[1] : '0', 'cuit' => $nota->getProveedor()->getCuit(),
                    'razon' => $nota->getProveedor()->getNombre(),
                    'iibb' => $nota->getProveedor()->getIibb(),
                    'pcia' => $provincia,
                    'neto' => $nota->getSubtotalNeto() * $i,
                    'percDgr' => $nota->getPercepcionDgr() * $i);
                array_push($items, $item);
            }
        }

        $ord = usort($items, function($a1, $a2) {
            $value1 = strtotime($a1['fecha']->format('Y-m-d'));
            $value2 = strtotime($a2['fecha']->format('Y-m-d'));
            return $value1 - $value2;
        });
        return $this->render('ComprasBundle:Informe:iibb-chaco.html.twig', array(
                    'path' => $this->generateUrl('compras_informe_iibbchaco'),
                    'items' => $items, 'desde' => $request->get('fecha_desde'), 'hasta' => $request->get('fecha_hasta')
        ));
    }

    /**
     * @Route("/informe/controlPagos", name="compras_informe_controlpago")
     * @Method("GET")
     * @Template()
     */
    public function controlPagos(Request $request) {
        $id = $request->get('provId');
        $em = $this->getDoctrine()->getManager();
        $proveedores = $em->getRepository('ComprasBundle:Proveedor')->findAll();
        $resultado = array();
        if ($id) {
            $proveedor = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        }
        else {
            $proveedor = $proveedores[0];
            $id = $proveedor->getId();
        }

        foreach ($proveedor->getFacturasCompra() as $fact) {
            $monto = 0;
            $montonota = 0;
            if ($fact->getEstado() == 'PAGADO') {

                $pagos = $em->getRepository('ComprasBundle:PagoProveedor')->getPagosByFactura($id, $fact->getId());
                foreach ($pagos as $pago) {
                    $concepto = json_decode($pago['concepto']);
                    foreach ($concepto as $item) {
                        if ($item->clave == 'FAC-' . $fact->getId()) {
                            $monto = $monto + $item->monto;
                        }
                    }
                }
                foreach ($proveedor->getNotasDebCred() as $nota) {
                    foreach ($nota->getFacturas() as $nf) {
                        if ($nf->getId() == $fact->getId()) {
                            $montonota = $montonota + $nota->getTotal();
                        }
                    }
                }

                $res = array(
                    'id' => $fact->getId(),
                    'nroFactura' => $fact->getTipoFactura() . ' ' . $fact->getNuevoNroComprobante(),
                    'fecha' => $fact->getFechaFactura(),
                    'total' => $fact->getTotal(),
                    'saldo' => $fact->getSaldo(),
                    'pago' => $monto,
                    'nota' => $montonota
                );

                array_push($resultado, $res);
            }
        }

        return $this->render('ComprasBundle:Proveedor:control.html.twig', array(
                    'resultado' => $resultado, 'provId' => $id, 'proveedores' => $proveedores
        ));
    }

    /**
     * @Route("/getAutocompleteProveedores", name="get_autocomplete_proveedores")
     * @Method("POST")
     */
    public function getAutocompleteProveedoresAction( Request $request) {
        $term = $request->get('searchTerm');
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('ComprasBundle:Proveedor')->filterByTerm($term);
        return new JsonResponse($results);
    }

    /**
     * @Route("/getDatosProveedor", name="get_datos_proveedor")
     * @Method("GET")
     */
    public function getDatosProveedorAction(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Proveedor')->find($id);
        $partial = $this->renderView(
            'ComprasBundle:Proveedor:_partial-datos-proveedor.html.twig',
            array('item' => $entity)
        );
        $cuit = $entity->getCuit();
        $valido = UtilsController::validarCuit($cuit);
        $data = array(
            'partial' => $partial,
            'categoriaIva' => ($entity->getCategoriaIva() ) ?  $entity->getCategoriaIva()->getNombre() : null,
            'cuitValido' => $valido
        );
        return new Response( json_encode($data));
    }

}