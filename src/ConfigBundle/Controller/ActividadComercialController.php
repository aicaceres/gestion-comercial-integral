<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\ActividadComercial;
use ConfigBundle\Form\ActividadComercialType;

/**
 * @Route("/actividadcomercial") 
 */
class ActividadComercialController extends Controller
{
    /**
     * @Route("/", name="sistema_actividadcomercial")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_actividadcomercial');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:ActividadComercial')->findAll();
        return $this->render('ConfigBundle:ActividadComercial:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    /**
     * @Route("/", name="sistema_actividadcomercial_create")
     * @Method("POST")
     * @Template("ConfigBundle:ActividadComercial:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_actividadcomercial_new');
        $entity = new ActividadComercial();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_actividadcomercial'));
        }
        return $this->render('ConfigBundle:ActividadComercial:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a ActividadComercial entity.
    * @param ActividadComercial $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(ActividadComercial $entity)
    {
        $form = $this->createForm(new ActividadComercialType(), $entity, array(
            'action' => $this->generateUrl('sistema_actividadcomercial_create'),
            'method' => 'POST',
        ));
        return $form;
    }
    
    /**
     * @Route("/new", name="sistema_actividadcomercial_new")
     * @Method("GET")
     * @Template("ConfigBundle:ActividadComercial:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_actividadcomercial_new');
        $entity = new ActividadComercial();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:ActividadComercial:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * @Route("/{id}/edit", name="sistema_actividadcomercial_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_actividadcomercial_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:ActividadComercial')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ActividadComercial entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:ActividadComercial:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a ActividadComercial entity.
    * @param ActividadComercial $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ActividadComercial $entity)
    {
        $form = $this->createForm(new ActividadComercialType(), $entity, array(
            'action' => $this->generateUrl('sistema_actividadcomercial_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
    /**
     * @Route("/{id}", name="sistema_actividadcomercial_update")
     * @Method("PUT")
     * @Template("ConfigBundle:ActividadComercial:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_actividadcomercial_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:ActividadComercial')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ActividadComercial entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_actividadcomercial'));
        }
        return $this->render('ConfigBundle:ActividadComercial:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }
    
    /**
     * @Route("/delete/{id}", name="sistema_actividadcomercial_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {   
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_actividadcomercial_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:ActividadComercial')->find($id);
        try{
            $em->remove($entity); 
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }
}