<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Banco;
use ConfigBundle\Form\BancoType;

/**
 * @Route("/banco")
 */
class BancoController extends Controller
{
    /**
     * @Route("/", name="sistema_banco")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Banco')->findAll();
        return $this->render('ConfigBundle:Banco:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * @Route("/", name="sistema_banco_create")
     * @Method("POST")
     * @Template("ConfigBundle:Banco:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_new');
        $entity = new Banco();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_banco'));
        }
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Banco entity.
    * @param Banco $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Banco $entity)
    {
        $form = $this->createForm(new BancoType(), $entity, array(
            'action' => $this->generateUrl('sistema_banco_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_banco_new")
     * @Method("GET")
     * @Template("ConfigBundle:Banco:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_new');
        $entity = new Banco();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_banco_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Banco')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Banco entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Banco entity.
    * @param Banco $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Banco $entity)
    {
        $form = $this->createForm(new BancoType(), $entity, array(
            'action' => $this->generateUrl('sistema_banco_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_banco_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Banco:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Banco')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Banco entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_banco'));
        }
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_banco_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Banco')->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/add", name="sistema_banco_add")
     * @Method("GET")
     */
    public function addBancoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $banco = new Banco();
        try{
            $banco->setNombre( strtoupper($request->get('nombre')) );
            $em->persist($banco);
            $em->flush();
            $msg = array( 'id'=>$banco->getId(), 'text'=>$banco->getNombre() ) ;
        } catch (\Exception $ex) {  $msg= $ex->getMessage();     }
        return new Response(json_encode($msg));
    }

}