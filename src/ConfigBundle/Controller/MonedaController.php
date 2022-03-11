<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Moneda;
use ConfigBundle\Form\MonedaType;

/**
 * @Route("/moneda")
 */
class MonedaController extends Controller
{
    /**
     * @Route("/", name="sistema_moneda")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_moneda');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Moneda')->findAll();
        return $this->render('ConfigBundle:Moneda:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * @Route("/", name="sistema_moneda_create")
     * @Method("POST")
     * @Template("ConfigBundle:Moneda:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_moneda_new');
        $entity = new Moneda();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_moneda'));
        }
        return $this->render('ConfigBundle:Moneda:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Moneda entity.
    * @param Moneda $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Moneda $entity)
    {
        $form = $this->createForm(new MonedaType(), $entity, array(
            'action' => $this->generateUrl('sistema_moneda_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_moneda_new")
     * @Method("GET")
     * @Template("ConfigBundle:Moneda:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_moneda_new');
        $entity = new Moneda();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:Moneda:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_moneda_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_moneda_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Moneda')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Moneda entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Moneda:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Moneda entity.
    * @param Moneda $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Moneda $entity)
    {
        $form = $this->createForm(new MonedaType(), $entity, array(
            'action' => $this->generateUrl('sistema_moneda_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_moneda_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Moneda:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_moneda_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Moneda')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Moneda entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_moneda'));
        }
        return $this->render('ConfigBundle:Moneda:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_moneda_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_moneda_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Moneda')->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/getDatosMoneda", name="get_datos_moneda")
     * @Method("GET")
     */
    public function getDatosMonedaAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Moneda')->find($id);
        $partial = $this->renderView(
            'VentasBundle:Venta:_partial-datos-moneda.html.twig',
            array('item' => $entity)
        );
        $datos = array('partial' => $partial, 'simbolo' => $entity->getSimbolo(), 'cotizacion' => $entity->getCotizacion());
        return new Response( json_encode($datos) );
    }
}