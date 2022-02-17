<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Caja;
use ConfigBundle\Form\CajaType;

/**
 * @Route("/caja")
 */
class CajaController extends Controller
{
    /**
     * @Route("/", name="sistema_caja")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_caja');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Caja')->findAll();
        return $this->render('ConfigBundle:Caja:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    /**
     * @Route("/", name="sistema_caja_create")
     * @Method("POST")
     * @Template("ConfigBundle:Caja:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_caja_new');
        $entity = new Caja();        
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
            $entity->setUnidadNegocio($unidneg);
            $entity->setNombre( strtoupper(str_replace(' ','',$entity->getNombre())) );    
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_caja'));
        }
        return $this->render('ConfigBundle:Caja:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Caja entity.
    * @param Caja $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Caja $entity)
    {
        $form = $this->createForm(new CajaType(), $entity, array(
            'action' => $this->generateUrl('sistema_caja_create'),
            'method' => 'POST',
        ));
        return $form;
    }
    
    /**
     * @Route("/new", name="sistema_caja_new")
     * @Method("GET")
     * @Template("ConfigBundle:Caja:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_caja_new');
        $entity = new Caja();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:Caja:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * @Route("/{id}/edit", name="sistema_caja_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_caja_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Caja')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Caja entity.');
        }        
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Caja:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Caja entity.
    * @param Caja $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Caja $entity)
    {
        $form = $this->createForm(new CajaType(), $entity, array(
            'action' => $this->generateUrl('sistema_caja_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
    /**
     * @Route("/{id}", name="sistema_caja_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Caja:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_caja_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Caja')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Caja entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $entity->setNombre( strtoupper(str_replace(' ','',$entity->getNombre())) );  
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_caja'));
        }
        return $this->render('ConfigBundle:Caja:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_caja_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_caja_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Caja')->find($id);
        try {
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        } catch (\Exception $ex) {
            $msg = $ex->getTraceAsString();
        }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/autocompletePuntosVenta", name="autocomplete_puntosventa")
     * @Method("GET")
     */
    /*
    public function autocompletePuntosVentaAction(Request $request)
    {
        $unidneg_id = $request->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $puntos = $em->getRepository('ConfigBundle:Caja')->findByUnidadNegocio($unidneg_id);
        $array = array();
        foreach ($puntos as $pto) {
            $array[] = ['id' => $pto->getId(), 'text' => $pto->getNombre()];
        }
        return new Response(json_encode($array));
    }  */ 

}