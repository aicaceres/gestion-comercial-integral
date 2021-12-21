<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\CentroCosto;
use ConfigBundle\Form\CentroCostoType;

/**
 * @Route("/centrocosto")
 */
class CentroCostoController extends Controller {

    /**
     * @Route("/", name="sistema_centrocosto")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_centrocosto');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:CentroCosto')->findAll();
        return $this->render('ConfigBundle:CentroCosto:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * @Route("/", name="sistema_centrocosto_create")
     * @Method("POST")
     * @Template("ConfigBundle:CentroCosto:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_centrocosto');
        $entity = new CentroCosto();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_centrocosto'));
        }
        return $this->render('ConfigBundle:CentroCosto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a CentroCosto entity.
     * @param CentroCosto $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(CentroCosto $entity) {
        $form = $this->createForm(new CentroCostoType(), $entity, array(
            'action' => $this->generateUrl('sistema_centrocosto_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_centrocosto_new")
     * @Method("GET")
     * @Template("ConfigBundle:CentroCosto:edit.html.twig")
     */
    public function newAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_centrocosto_new');
        $entity = new CentroCosto();
        $form = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:CentroCosto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_centrocosto_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_centrocosto');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:CentroCosto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Centro Costo entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:CentroCosto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a CentroCosto entity.
     * @param CentroCosto $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(CentroCosto $entity) {
        $form = $this->createForm(new CentroCostoType(), $entity, array(
            'action' => $this->generateUrl('sistema_centrocosto_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_centrocosto_update")
     * @Method("PUT")
     * @Template("ConfigBundle:CentroCosto:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_centrocosto');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:CentroCosto')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Centro Costo entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_centrocosto'));
        }
        return $this->render('ConfigBundle:CentroCosto:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_centrocosto_delete")
     * @Method("POST")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_centrocosto');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:CentroCosto')->find($id);
        try {
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getTraceAsString();
        }
        return new Response(json_encode($msg));
    }

}