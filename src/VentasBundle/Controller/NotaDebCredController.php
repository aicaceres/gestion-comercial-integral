<?php

namespace VentasBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use VentasBundle\Entity\NotaDebCred;
use VentasBundle\Form\NotaDebCredType;
use VentasBundle\Entity\FacturaElectronica;

/**
 * @Route("/notadebcredVentas")
 */
class NotaDebCredController extends Controller {

    /**
     * @Route("/", name="ventas_notadebcred")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $cliId = $request->get('cliId');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');
        $clientes = $em->getRepository('VentasBundle:Cliente')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
        $entities = $em->getRepository('VentasBundle:NotaDebCred')->findByCriteria($unidneg, $cliId, $desde, $hasta);
        return $this->render('VentasBundle:NotaDebCred:index.html.twig', array(
                    'entities' => $entities,
                    'clientes' => $clientes,
                    'cliId' => $cliId,
                    'desde' => $desde,
                    'hasta' => $hasta
        ));
    }

    /**
     * @Route("/new", name="ventas_notadebcred_new")
     * @Method("GET")
     * @Template("VentasBundle:NotaDebCred:edit.html.twig")
     */
    public function newAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $entity = new NotaDebCred();
        // $em = $this->getDoctrine()->getManager();
        // $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
        // $entity->setPrefijoNro( sprintf("%03d", $equipo->getPrefijo()) );
        // $entity->setNotaDebCredNro( sprintf("%08d", $equipo->getNroNotaDebCredCompra()+1) );
        $form = $this->createCreateForm($entity);
        return $this->render('VentasBundle:NotaDebCred:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(NotaDebCred $entity) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('ventas_notadebcred_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/", name="ventas_notadebcred_create")
     * @Method("POST")
     * @Template("VentasBundle:NotaDebCred:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        // credito - (descuenta de la deuda con proveedor)
        // debito + (aumenta la deuda con el proveedor)
        $entity = new NotaDebCred();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {

                /* $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));
                  $entity->setPrefijoNro( sprintf("%03d", $equipo->getPrefijo()) );
                  $entity->setNotaDebCredNro( sprintf("%08d", $equipo->getNroNotaDebCredCompra()+1) );
                  $equipo->setNroNotaDebCredCompra($equipo->getNroNotaDebCredCompra()+1); */
                $entity->setTotal($entity->getTotal());
                $entity->setSaldo($entity->getTotal());
                $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
                $entity->setUnidadNegocio($unidneg);
                if (intval($entity->getAfipPuntoVenta()) == 0 || intval($entity->getAfipNroComprobante()) == 0) {
                    $this->addFlash('error', 'Debe ingresar punto de venta y número de comprobante!');
                    $em->getConnection()->rollback();
                    return $this->render('VentasBundle:NotaDebCred:edit.html.twig', array(
                                'entity' => $entity,
                                'form' => $form->createView(),
                    ));
                }
                $em->persist($entity);
                //$em->persist($equipo);
                $em->flush();
                $datos = $request->get('ventasbundle_notadebcred');
                $electronica = new FacturaElectronica();
                $electronica->setCae($datos['cae']);
                $electronica->setCaeVto($datos['caeVto']);
                $electronica->setFacturadoDesde($datos['facturadoDesde']);
                $electronica->setFacturadoHasta($datos['facturadoHasta']);
                $electronica->setPagoVto($datos['pagoVto']);
                $electronica->setNotaDebCred($entity);
                $electronica->setIva($entity->getIva());
                $electronica->setNeto($entity->getSubTotal());
                $em->persist($electronica);
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirectToRoute('ventas_notadebcred');
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }

        return $this->render('VentasBundle:NotaDebCred:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/show", name="ventas_notadebcred_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota de Débito/Crédito.');
        }
        return $this->render('VentasBundle:NotaDebCred:show.html.twig', array(
                    'entity' => $entity));
    }

    /**
     * @Route("/{id}/edit", name="ventas_notadebcred_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $editForm = $this->createEditForm($entity);
        // if($entity->getEstado()=='NUEVO')
        //     $this->get('session')->getFlashBag()->add('alert','Los productos serán reingresados al stock cuando se marque el envío al Proveedor' );
        return $this->render('VentasBundle:NotaDebCred:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a NotaCredito entity.
     * @param NotaCredito $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(NotaDebCred $entity) {
        $form = $this->createForm(new NotaDebCredType(), $entity, array(
            'action' => $this->generateUrl('ventas_notadebcred_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="ventas_notadebcred_update")
     * @Method("PUT")
     * @Template("VentasBundle:NotaDebCred:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_notadebcred');
        $modificaStock = ($request->get('modificaStock') == 'SI') ? true : false;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaDebCred')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra la Nota.');
        }
        $original = new ArrayCollection();
        // Create an ArrayCollection of the current objects in the database
        foreach ($entity->getDetalles() as $item) {
            $original->add($item);
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->getConnection()->beginTransaction();
            try {
                // remove the relationship between the item and the pedido
                foreach ($original as $item) {
                    if (false === $entity->getDetalles()->contains($item)) {
                        $em->remove($item);
                    }
                }
                $em->flush();


                $em->getConnection()->commit();
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('VentasBundle:NotaCredito:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

    public function acreditarAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('VentasBundle:NotaCredito')->find($id);
        $entity->setEstado('ACREDITADO');
        $em->persist($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('ventas_notacredito'));
    }

    /**
     * IMPRESION DE listado
     */

    /**
     * @Route("/printVentasNotaDebCred.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_ventas_notadebcred")
     * @Method("POST")
     */
    public function printVentasNotaDebCredAction(Request $request) {
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
        $this->render('VentasBundle:NotaDebCred:pdf-notasdebcred.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=listado_ventas_notasdebcred_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

}