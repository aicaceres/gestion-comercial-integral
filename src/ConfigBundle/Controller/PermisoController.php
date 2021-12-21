<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;

use ConfigBundle\Entity\Permiso;
use ConfigBundle\Form\PermisoType;

/**
 * @Route("/permiso")
 */
class PermisoController extends Controller
{

    /**
     * @Route("/", name="sistema_seguridad_permiso")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_permiso');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Permiso')->findAll();
        return array(
            'entities' => $entities,
        );
    }
    /**
     * @Route("/", name="permiso_create")
     * @Method("POST")
     * @Template("ConfigBundle:Permiso:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Permiso();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('sistema_seguridad_permiso');
        }
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @param Permiso $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Permiso $entity)
    {
        $form = $this->createForm(new PermisoType(), $entity, array(
            'action' => $this->generateUrl('permiso_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_seguridad_permiso_new")
     * @Method("GET")
     * @Template("ConfigBundle:Permiso:edit.html.twig")
     */
    public function newAction()
    {
        $entity = new Permiso();
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="sistema_seguridad_permiso_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Permiso')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nacionalidad entity.');
        }
        $editForm = $this->createEditForm($entity);
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView()
        );
    }

    /**
    * @param Permiso $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Permiso $entity)
    {
        $form = $this->createForm(new PermisoType(), $entity, array(
            'action' => $this->generateUrl('permiso_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    /**
     * @Route("/{id}", name="permiso_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Permiso:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Permiso')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nacionalidad entity.');
        }
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirectToRoute('sistema_seguridad_permiso');
        }
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView()
        );
    }

}
