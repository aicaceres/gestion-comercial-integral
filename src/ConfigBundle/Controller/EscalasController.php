<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Escalas;
use ConfigBundle\Form\EscalasType;

/**
 * @Route("/escalas") 
 */
class EscalasController extends Controller
{
    private $tipos = array('R'=>'RETENCIÓN DE RENTAS','P' => 'PERCEPCIÓN DE RENTAS', 'G' => 'GANANCIAS' ,'H'=>'GANANCIAS');
    /**
     * @Route("/", name="sistema_escalas")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_escalas');
        $em = $this->getDoctrine()->getManager();
        $tipo = ($request->get('tipoId')) ? $request->get('tipoId') : 'R'; 
        $entities = $em->getRepository('ConfigBundle:Escalas')->filterEscalasByTipo($tipo);
        return $this->render('ConfigBundle:Escalas:index.html.twig', array(
            'entities' => $entities,
            'tipoId' => $tipo
        ));
    }
    
    /**
     * @Route("/", name="sistema_escalas_create")
     * @Method("POST")
     * @Template("ConfigBundle:Escalas:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_escalas_new');
        $entity = new Escalas();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_escalas'));
        }
        return $this->render('ConfigBundle:Escalas:edit.html.twig', array(
            'entity' => $entity,
            'tipos' => $this->tipos,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Escalas entity.
    * @param Escalas $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Escalas $entity)
    {
        $form = $this->createForm(new EscalasType(), $entity, array(
            'action' => $this->generateUrl('sistema_escalas_create'),
            'method' => 'POST',
        ));
        return $form;
    }
    
    /**
     * @Route("/{tipo}/new", name="sistema_escalas_new")
     * @Method("GET")
     * @Template("ConfigBundle:Escalas:edit.html.twig")
     */
    public function newAction($tipo)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_escalas_new');
        $entity = new Escalas();
        $entity->setTipo($tipo);
        if($tipo == 'G') $entity->setNombre( 'HONORARIOS DIRECTORES Y ADMINI');
        $form   = $this->createCreateForm($entity);        
        return $this->render('ConfigBundle:Escalas:edit.html.twig', array(
            'entity' => $entity,
            'tipos' => $this->tipos,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * @Route("/{id}/edit", name="sistema_escalas_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_escalas_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Escalas')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Escalas entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);
        return $this->render('ConfigBundle:Escalas:edit.html.twig', array(
            'entity'      => $entity,
            'tipos' => $this->tipos,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Escalas entity.
    * @param Escalas $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Escalas $entity)
    {
        $form = $this->createForm(new EscalasType(), $entity, array(
            'action' => $this->generateUrl('sistema_escalas_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    
    /**
     * @Route("/{id}", name="sistema_escalas_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Escalas:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_escalas_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Escalas')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Escalas entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_escalas'));
        }
        return $this->render('ConfigBundle:Escalas:edit.html.twig', array(
            'entity'      => $entity,
            'tipos' => $this->tipos,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }
    
    /**
     * @Route("/delete/{id}", name="sistema_escalas_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {   
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_escalas_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Escalas')->find($id);
        try{
            $em->remove($entity); 
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }
}