<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\Factura;
use VentasBundle\Form\FacturaType;
use VentasBundle\Entity\FacturaElectronica;
//PUNTO VENTA
use VentasBundle\Form\PuntoVentaType;

/**
 * @Route("/facturaVentas")
 */
class FacturaController extends Controller {

    /**
     * @Route("/", name="ventas_factura")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $cliId = $request->get('cliId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        /*$clientes = $em->getRepository('VentasBundle:Cliente')->findBy(array('activo' => 1), array('nombre' => 'ASC'));*/
        $entities = $em->getRepository('VentasBundle:Factura')->findByCriteria($unidneg, $cliId, $desde, $hasta);
        //$entities = $em->getRepository('VentasBundle:Factura')->findByUnidadNegocio($this->get('session')->get('unidneg_id'));
        return $this->render('VentasBundle:Factura:index.html.twig', array(
                    'entities' => $entities,
                    //'clientes' => $clientes,
                    'cliId' => $cliId,
                    'desde' => $desde,
                    'hasta' => $hasta
        ));
    }

    /**
     * @Route("/new", name="ventas_factura_new")
     * @Method("GET")
     * @Template("VentasBundle:Factura:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura_new');
        $entity = new Factura();
        //   $em = $this->getDoctrine()->getManager();
        // sacar cuando se implemente factura electrónica
        //  $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        //  $entity->setNroFactura( sprintf("%03d", $equipo->getPrefijo()).'-'.sprintf("%08d", $equipo->getNroFacturaVentaA()+1) );
        //

        $form = $this->createCreateForm($entity);
        return $this->render('VentasBundle:Factura:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Factura entity.
     * @param Factura $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Factura $entity) {
        $form = $this->createForm(new FacturaType(), $entity, array(
            'action' => $this->generateUrl('ventas_factura_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_factura_create")
     * @Method("POST")
     * @Template("VentasBundle:Factura:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura_new');
        $entity = new Factura();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $entity->setEstado('PENDIENTE');
                $entity->setIva($entity->getTotalIva());
                //$entity->setTotal( $entity->getMontoTotal() );
                $entity->setTotal($entity->getTotal());
                $entity->setSaldo($entity->getTotal());
                // set Unidad de negocio
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);
                // set numeracion
                // $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                // $entity->setNroFactura( sprintf("%03d", $equipo->getPrefijo()).'-'.sprintf("%08d", $equipo->getNroFacturaVentaA()+1) );
                /* Guardar ultimo nro */
                // $equipo->setNroFacturaVentaA($equipo->getNroFacturaVentaA()+1);
                if (intval($entity->getAfipPuntoVenta()) == 0 || intval($entity->getAfipNroComprobante()) == 0) {
                    $this->addFlash('error', 'Debe ingresar punto de venta y número de comprobante!');
                    $em->getConnection()->rollback();
                    return $this->render('VentasBundle:Factura:edit.html.twig', array(
                                'entity' => $entity,
                                'form' => $form->createView(),
                    ));
                }

                $em->persist($entity);
                // $em->persist($equipo);
                $em->flush();

                $datos = $request->get('ventasbundle_factura');
                $electronica = new FacturaElectronica();
                $electronica->setCae($datos['cae']);
                $electronica->setCaeVto($datos['caeVto']);
                $electronica->setFacturadoDesde($datos['facturadoDesde']);
                $electronica->setFacturadoHasta($datos['facturadoHasta']);
                $electronica->setPagoVto($datos['pagoVto']);
                $electronica->setFactura($entity);
                $electronica->setIva($entity->getIva());
                $electronica->setNeto($entity->getSubTotal());
                $em->persist($electronica);
                $em->flush();

                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('ventas_factura'));
            }
            catch (\Exception $ex) {
                $this->addFlash('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('VentasBundle:Factura:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/show", name="ventas_factura_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_factura');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:Factura')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Factura entity.');
        }
        return $this->render('VentasBundle:Factura:show.html.twig', array(
                    'entity' => $entity));
    }

    /**
     * Deletes a Factura entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'compras_factura_delete');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ComprasBundle:Factura')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Factura entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('factura'));
    }

    /**
     * Creates a form to delete a Factura entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('factura_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * @Route("/{id}/cancelar", name="ventas_factura_cancel")
     * @Method("POST")
     * @Template()
     */
    public function cancelarFacturaAction() {
        // redireccionar a nota de credito
        $id = $this->getRequest()->get('id');
        $em = $this->getDoctrine()->getManager();
        try {
            $entity = $em->getRepository('ComprasBundle:Factura')->find($id);
            $entity->setEstado('CANCELADO');
            $em->persist($entity);
            $em->flush();
            return new Response('OK');
        }
        catch (\Exception $ex) {
            return new Response($ex);
        }
    }

    /**
     * @Route("/proximoNumeroFactura", name="proximo_numero_fact")
     * @Method("GET")
     * @Template()
     */
    public function getProximoNumeroAction($letra = null) {
        if ($this->getRequest()->get('letra'))
            $letra = $this->getRequest()->get('letra');
        $em = $this->getDoctrine()->getManager();
        $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        switch ($letra) {
            case 'A':
                $nro = $equipo->getNroFacturaVentaA();
                break;
            case 'B':
                $nro = $equipo->getNroFacturaVentaB();
                break;
        }
        $nro = sprintf("%03d", $equipo->getPrefijo()) . '-' . sprintf("%08d", $nro + 1);
        return new Response($nro);
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printVentasFactura.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ventas_factura")
     * @Method("POST")
     */
    public function printVentasFacturaAction(Request $request) {
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
        $this->render('VentasBundle:Factura:pdf-facturas.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_ventas_facturas_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

}