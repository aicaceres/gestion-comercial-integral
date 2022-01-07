<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Transporte;
use ConfigBundle\Form\TransporteType;

/**
 * @Route("/transporte")
 */
class TransporteController extends Controller
{
    /**
     * @Route("/", name="sistema_transporte")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_transporte');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Transporte')->findAll();
        return $this->render('ConfigBundle:Transporte:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    /**
     * @Route("/", name="sistema_transporte_create")
     * @Method("POST")
     * @Template("ConfigBundle:Transporte:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_transporte_new');
        $entity = new Transporte();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_transporte'));
        }
        return $this->render('ConfigBundle:Transporte:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Transporte entity.
    * @param Transporte $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Transporte $entity)
    {
        $form = $this->createForm(new TransporteType(), $entity, array(
            'action' => $this->generateUrl('sistema_transporte_create'),
            'method' => 'POST',
        ));
        return $form;
    }
    
    /**
     * @Route("/new", name="sistema_transporte_new")
     * @Method("GET")
     * @Template("ConfigBundle:Transporte:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_transporte_new');
        $entity = new Transporte();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:Transporte:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * @Route("/{id}/edit", name="sistema_transporte_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_transporte_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Transporte')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transporte entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Transporte:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Transporte entity.
    * @param Transporte $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Transporte $entity)
    {
        $form = $this->createForm(new TransporteType(), $entity, array(
            'action' => $this->generateUrl('sistema_transporte_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
    /**
     * @Route("/{id}", name="sistema_transporte_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Transporte:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_transporte_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Transporte')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transporte entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_transporte'));
        }
        return $this->render('ConfigBundle:Transporte:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }
    
    /**
     * @Route("/delete/{id}", name="sistema_transporte_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {   
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_transporte_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Transporte')->find($id);
        try{
            $em->remove($entity); 
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }
}