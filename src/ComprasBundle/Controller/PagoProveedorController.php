<?php

namespace ComprasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;
use ComprasBundle\Entity\PagoProveedor;
use ComprasBundle\Form\PagoProveedorType;

class PagoProveedorController extends Controller {

    public function indexAction() {

        $id = $this->getRequest()->get('proveedorId');
        $em = $this->getDoctrine()->getManager();
        $proveedores = $em->getRepository('AdminBundle:Proveedor')->findAll();
        if ($id)
            $proveedor = $em->getRepository('AdminBundle:Proveedor')->find($id);
        else
            $proveedor = $proveedores[0];

        $entities = $em->getRepository('ComprasBundle:PagoProveedor')->findByProveedor($proveedor);
        foreach ($entities as $pago) {
            $text = '';
            $conceptos = json_decode($pago->getConcepto());
            foreach ($conceptos as $item) {
                $factura = $em->getRepository('ComprasBundle:Factura')->find($item->id);
                $text = $text . ' [FAC ' . $factura->getTipoFactura() . $factura->getNroFactura() . ' $' . $item->monto . '] ';
            }
            $pago->setConcepto(UtilsController::myTruncate($text, 30));
            $detalle = UtilsController::myTruncate($pago->getDetalle(), 30);
            $pago->setDetalle($detalle);
        }
        return $this->render('ComprasBundle:PagoProveedor:list.html.twig', array(
                    'entities' => $entities, 'proveedores' => $proveedores, 'proveedor' => $proveedor
        ));
    }

    /**
     * Displays a form to create a new PagoProveedor entity.
     */
    public function newAction($prov) {
        $entity = new PagoProveedor();
        $em = $this->getDoctrine()->getManager();
        $proveedor = $em->getRepository('AdminBundle:Proveedor')->find($prov);
        $equipo = $em->getRepository('AdminBundle:Equipo')->find($this->get('session')->get('equipo'));
        $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
        $entity->setPagoNro(sprintf("%06d", $equipo->getNroPagoCompra() + 1));
        $entity->setProveedor($proveedor);
        $entity->setFecha(new \DateTime);
        $form = $this->createCreateForm($entity);
        return $this->render('ComprasBundle:PagoProveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new PagoProveedor entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new PagoProveedor();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $arrayConceptos = explode(',', $request->get('txtconcepto'));
            $facturasImpagas = $em->getRepository('AdminBundle:Proveedor')->getFacturasImpagas($entity->getProveedor()->getId());
            $arrayFacturas = array();
            foreach ($facturasImpagas as $fact) {
                array_push($arrayFacturas, $fact['id']);
            }
            $conceptos = array_unique(array_merge($arrayConceptos, $arrayFacturas));

            $total = $entity->getTotal();
            $txtConcepto = array();
            try {
                // Proceso de facturas - Ajustar los saldos
                foreach ($conceptos as $item) {
                    $factura = $em->getRepository('ComprasBundle:Factura')->find($item);
                    if ($factura && $total > 0) {
                        if ($total >= $factura->getSaldo()) {
                            //alcanza para cubrir el saldo
                            $total -= $factura->getSaldo();
                            $factxt = array('id' => $item, 'monto' => $factura->getSaldo());
                            array_push($txtConcepto, $factxt);
                            $factura->setSaldo(0);
                            $factura->setEstado('PAGADO');
                        }
                        else {
                            //no alcanza, impacta el total
                            $factura->setSaldo($factura->getSaldo() - $total);
                            $factxt = array('id' => $item, 'monto' => $total);
                            array_push($txtConcepto, $factxt);
                            $total = 0;
                            $factura->setEstado('PAGO PARCIAL');
                        }
                        $em->persist($factura);
                    }
                }
                $entity->setConcepto(json_encode($txtConcepto));
                $equipo = $em->getRepository('AdminBundle:Equipo')->find($this->get('session')->get('equipo'));
                $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                $entity->setPagoNro(sprintf("%06d", $equipo->getNroPagoCompra() + 1));
                /* Guardar ultimo nro */
                $equipo->setNroPagoCompra($equipo->getNroPagoCompra() + 1);

                // cheques
                foreach ($entity->getChequesPagados() as $cheque) {
                    if (is_null($cheque->getId())) {
                        $cheque->setTipo('T');
                        $cheque->setUsado(true);
                        $cheque->setTomado(new \DateTime);
                        $cheque->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
                        $cheque->setChequeNro(sprintf("%06d", $equipo->getNroInternoCheque() + 1));
                        $equipo->setNroInternoCheque($equipo->getNroInternoCheque() + 1);
                    }
                    else {
                        $obj = $em->getRepository('AdminBundle:Cheque')->find($cheque->getId());
                        $obj->setUsado(true);
                        $entity->removeChequesPagado($cheque);
                        $entity->addChequesPagado($obj);
                    }
                }

                $em->persist($entity);
                $em->persist($equipo);
                $em->flush();
                $em->getConnection()->commit();

                return $this->redirect($this->generateUrl('compras_pagoproveedor'));
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
            }
        }

        return $this->render('ComprasBundle:PagoProveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PagoProveedor entity.
     * @param PagoProveedor $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PagoProveedor $entity) {
        $form = $this->createForm(new PagoProveedorType(), $entity, array(
            'action' => $this->generateUrl('compras_pagoproveedor_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * Finds and displays a PagoProveedor entity.
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PagoProveedor entity.');
        }
        $text = '';
        $conceptos = json_decode($entity->getConcepto());
        foreach ($conceptos as $item) {
            $factura = $em->getRepository('ComprasBundle:Factura')->find($item->id);
            $text = $text . ' [FAC ' . $factura->getTipoFactura() . $factura->getNroFactura() . ' $' . $item->monto . '] ';
        }
        $entity->setConcepto($text);
        return $this->render('ComprasBundle:PagoProveedor:show.html.twig', array(
                    'entity' => $entity,));
    }

    /**
     * Deletes a PagoProveedor entity.
     *
     */
    public function deleteAction($id) {
        $id = $this->getRequest()->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        try {
            //restaurar saldos en facturas.
            $conceptos = json_decode($entity->getConcepto());
            foreach ($conceptos as $item) {
                $factura = $em->getRepository('ComprasBundle:Factura')->find($item->id);
                $factura->setSaldo($factura->getSaldo() + $item->monto);
                if ($factura->getSaldo() == $factura->getTotal())
                    $factura->setEstado('PENDIENTE');
                else
                    $factura->setEstado('PAGO PARCIAL');
                $em->persist($factura);
            }
            // liberar cheques
            foreach ($entity->getChequesPagados() as $item) {
                $cheque = $em->getRepository('AdminBundle:Cheque')->find($item->getId());
                $cheque->setUsado(false);
                $em->persist($cheque);
            }
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getTraceAsString();
        }
        return new Response(json_encode($msg));
    }

    public function getFacturasProveedorAction() {
        // facturas y notas de debito
        $id = $this->getRequest()->get('prov');
        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('AdminBundle:Proveedor')->getFacturasImpagas($id);
        $datos = array();
        foreach ($facturas as $value) {
            $text = '(' . $value['tipoFactura'] . sprintf("%03d", $value['prefijoNro']) . '-' . sprintf("%08d", $value['facturaNro']) . ') $' . $value['saldo'];
            array_push($datos, array('id' => $value['id'], 'text' => $text));
        }
        $partial = $this->renderView('ComprasBundle:PagoProveedor:factura-proveedor-row.html.twig',
                array('datos' => $datos));
        return new Response($partial);
    }

    public function getChequesCarteraAction() {
        $em = $this->getDoctrine()->getManager();
        $cheques = $em->getRepository('AdminBundle:Cheque')->getChequesParaPago();
        $partial = $this->renderView('AdminBundle:Cheque:_partial-cheques-cartera.html.twig',
                array('cheques' => $cheques));
        return new Response($partial);
    }

    //////////

    /**
     * Displays a form to edit an existing PagoProveedor entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PagoProveedor entity.');
        }
        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);
        return $this->render('ComprasBundle:PagoProveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a PagoProveedor entity.
     *
     * @param PagoProveedor $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(PagoProveedor $entity) {
        $form = $this->createForm(new PagoProveedorType(), $entity, array(
            'action' => $this->generateUrl('compras_pagoproveedor_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * Edits an existing PagoProveedor entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:PagoProveedor')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PagoProveedor entity.');
        }
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('compras_pagoproveedor_edit', array('id' => $id)));
        }
        return $this->render('ComprasBundle:PagoProveedor:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to delete a PagoProveedor entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('pagoproveedor_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * @Route("/{id}/imprimir.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="compras_pago_print")
     * @Method("GET")
     */
    public function printAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_pedido');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ComprasBundle:Pedido')->find($id);
        $facade = $this->get('ps_pdf.facade');
        $response = new Response();
        $this->render('ComprasBundle:Pedido:pdf-pedido.pdf.twig',
                array('entity' => $entity), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=pedido' . $entity->getNroPedido() . '.pdf'));
        /*  return $this->render('AppBundle:Pedido:print.html.twig', array(
          'entity' => $entity)); */
    }

}