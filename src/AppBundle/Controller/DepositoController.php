<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\Deposito;
use AppBundle\Form\DepositoType;

/**
 * @Route("/deposito")
 */
class DepositoController extends Controller
{
    /**
     * @Route("/", name="sistema_deposito")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_deposito');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppBundle:Deposito')->findByUnidadNegocio( $this->get('session')->get('unidneg_id') );
        return $this->render('AppBundle:Deposito:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * @Route("/", name="sistema_deposito_create")
     * @Method("POST")
     * @Template("AppBundle:Deposito:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_deposito_new');
        $entity = new Deposito();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
            $entity->setUnidadNegocio($unidneg);

            if($entity->getPordefecto()){
                // dejar solo este por defecto.
               $em->getRepository('AppBundle:Deposito')->setPorDefectoFalse($unidneg->getId());
               $entity->setPordefecto(1);
            }
            if( !$entity->getLocalidad() ){
                $localidad = $em->getRepository('ConfigBundle:Localidad')->findOneByByDefault(1);
                $entity->setLocalidad($localidad);
            }

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_deposito'));
        }
        return $this->render('AppBundle:Deposito:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Deposito entity.
    * @param Deposito $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Deposito $entity)
    {
        $form = $this->createForm(new DepositoType(), $entity, array(
            'action' => $this->generateUrl('sistema_deposito_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_deposito_new")
     * @Method("GET")
     * @Template("AppBundle:Deposito:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_deposito_new');
        $em = $this->getDoctrine()->getManager();
        $localidad = $em->getRepository('ConfigBundle:Localidad')->findOneByByDefault(1);
        $entity = new Deposito();
        $entity->setLocalidad($localidad);
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Deposito:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_deposito_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_deposito_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Deposito')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Deposito entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Deposito:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Deposito entity.
    * @param Deposito $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Deposito $entity)
    {
        $form = $this->createForm(new DepositoType(), $entity, array(
            'action' => $this->generateUrl('sistema_deposito_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_deposito_update")
     * @Method("PUT")
     * @Template("AppBundle:Deposito:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'sistema_deposito_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Deposito')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Deposito entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
             if($entity->getPordefecto()){
                // dejar solo este por defecto.
               $em->getRepository('AppBundle:Deposito')->setPorDefectoFalse($unidneg);
               $entity->setPordefecto(1);
            }

            $em->flush();

            return $this->redirect($this->generateUrl('sistema_deposito'));
        }
        return $this->render('AppBundle:Deposito:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_deposito_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_deposito_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Deposito')->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/selectDepositos", name="select_depositos")
     * @Method("POST")
     */
    public function depositosAction(Request $request)
    {
        $unidneg_id = $request->request->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $depositos = $em->getRepository('AppBundle:Pedido')->findByUnidadNegocioId($unidneg_id);
        return new JsonResponse($depositos);
    }

    /**
     * @Route("/autocompleteDepositos", name="autocomplete_depositos")
     * @Method("GET")
     */
    public function autocompleteDepositosAction(Request $request)
    {
        $unidneg_id = $request->get('unidneg_id');
        $em = $this->getDoctrine()->getManager();
        $depositos = $em->getRepository('AppBundle:Deposito')->findByUnidadNegocio($unidneg_id);
        $array = array();
        foreach ($depositos as $dep) {
            $array[] = ['id'=>$dep->getId(), 'text'=>$dep->getNombre()];
        }
        return new Response(json_encode($array));
    }
}