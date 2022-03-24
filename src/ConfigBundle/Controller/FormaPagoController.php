<?php

namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\FormaPago;
use ConfigBundle\Form\FormaPagoType;

/**
 * @Route("/formapago")
 */
class FormaPagoController extends Controller
{
    /**
     * @Route("/", name="sistema_formapago")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_formapago');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:FormaPago')->findAll();
        return $this->render('ConfigBundle:FormaPago:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * @Route("/", name="sistema_formapago_create")
     * @Method("POST")
     * @Template("ConfigBundle:FormaPago:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_formapago_new');
        $entity = new FormaPago();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_formapago'));
        }
        return $this->render('ConfigBundle:FormaPago:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a FormaPago entity.
     * @param FormaPago $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(FormaPago $entity)
    {
        $form = $this->createForm(new FormaPagoType(), $entity, array(
            'action' => $this->generateUrl('sistema_formapago_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_formapago_new")
     * @Method("GET")
     * @Template("ConfigBundle:FormaPago:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_formapago_new');
        $entity = new FormaPago();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:FormaPago:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_formapago_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_formapago_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:FormaPago')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FormaPago entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:FormaPago:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a FormaPago entity.
     * @param FormaPago $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(FormaPago $entity)
    {
        $form = $this->createForm(new FormaPagoType(), $entity, array(
            'action' => $this->generateUrl('sistema_formapago_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_formapago_update")
     * @Method("PUT")
     * @Template("ConfigBundle:FormaPago:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_formapago_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:FormaPago')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FormaPago entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_formapago'));
        }
        return $this->render('ConfigBundle:FormaPago:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_formapago_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_formapago_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:FormaPago')->find($id);
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
     * @Route("/getDatosFormaPago", name="get_datos_formapago")
     * @Method("GET")
     */
    public function getDatosFormaPagoAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:FormaPago')->find($id);
        $partial = $this->renderView(
            'VentasBundle:Partial:_partial-datos-formapago.html.twig',
            array('item' => $entity)
        );
        return new Response($partial);
    }

    /**
     * @Route("/getListaFormaPago", name="get_lista_formapago")
     * @Method("GET")
     */
    public function getListaFormaPagoAction() {
        $em = $this->getDoctrine()->getManager();
        $formas = $em->getRepository('ConfigBundle:FormaPago')->findAll();
        $partial = $this->renderView('ConfigBundle:FormaPago:_partial-lista-formapago.html.twig',
                array('formas' => $formas));
        return new Response($partial);
    }
}
