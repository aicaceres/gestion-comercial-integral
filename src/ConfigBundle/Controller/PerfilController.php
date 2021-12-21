<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Rol;
use ConfigBundle\Form\PerfilType;

/**
 * @Route("/perfil")
 */
class PerfilController extends Controller
{
    protected $paginas = array('homepage'=>'Inicio Administrador');

    /**
     * @Route("/", name="sistema_seguridad_perfil")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_perfil');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Rol')->findAll();
        $deleteForms = array();
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }
        return array(
            'entities' => $entities,
            'deleteForms' => $deleteForms
        );        
    }
    /**
     * @Route("/", name="perfil_create")
     * @Method("POST")
     * @Template("ConfigBundle:Perfil:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_perfil');
        $entity = new Rol();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entity->setNombre(strtoupper($entity->getNombre()));
            $permisos = $request->get('permisos');
            $em = $this->getDoctrine()->getManager();
            foreach ($permisos as $id) {
                $permiso = $em->getRepository('ConfigBundle:Permiso')->find($id);
                $entity->addPermiso($permiso);
            }
            if ($entity->getAdmin()) $entity->setActivo(TRUE);
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','El perfil fue creado!' );
            return $this->redirectToRoute('sistema_seguridad_perfil');
        }
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * @param Rol $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Rol $entity)
    {
        $form = $this->createForm(new PerfilType(), $entity, array(
            'action' => $this->generateUrl('perfil_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_seguridad_perfil_new")
     * @Method("GET")
     * @Template("ConfigBundle:Perfil:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_perfil');
        $entity = new Rol();
        $em = $this->getDoctrine()->getManager();
        $modulos = $em->getRepository('ConfigBundle:Permiso')->getPadres();        
        $form   = $this->createCreateForm($entity);
        return array(
            'entity' => $entity,
            'modulos' => $modulos,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("/{id}/edit", name="sistema_seguridad_perfil_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
       UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_perfil');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Rol')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el perfil.');
        }
        $modulos = $em->getRepository('ConfigBundle:Permiso')->getPadres();
        $editForm = $this->createEditForm($entity);
        $entity->setNombre( strtoupper($entity->getNombre()) );
        return array(
            'entity'      => $entity,
            'modulos' => $modulos,
            'form'   => $editForm->createView()
        );
    }

    /**
    * @param Rol $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Rol $entity)
    {
        $form = $this->createForm(new PerfilType(), $entity, array(
            'action' => $this->generateUrl('perfil_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }
    /**
     * @Route("/{id}", name="perfil_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Perfil:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_perfil');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Rol')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Nacionalidad entity.');
        }
        $permisos = new ArrayCollection();       
        
        foreach ($request->get('permisos') as $item) {
            $obj = $em->getRepository('ConfigBundle:Permiso')->find($item);
            $permisos->add($obj);
        }    
        
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
  
        if ($editForm->isValid()) {
            foreach ($permisos as $item) {
                if (false === $entity->getPermisos()->contains($item)) {
                    $entity->addPermiso($item);
                }
            }
            foreach ($entity->getPermisos() as $item) {
                if (false === $permisos->contains($item)) {
                    $entity->removePermiso($item);
                }
            }
            $entity->setNombre( strtoupper($entity->getNombre()) );
            if ($entity->getAdmin()) $entity->setActivo(TRUE);
            $em->persist($entity);
            $em->flush();
            return $this->redirectToRoute('sistema_seguridad_perfil');
        }
        $modulos = $em->getRepository('ConfigBundle:Permiso')->getPadres();
        return array(
            'entity'      => $entity,
            'modulos' => $modulos,
            'form'   => $editForm->createView()
        );
    }
    
    /**
     * @Route("/delete/{id}", name="sistema_seguridad_perfil_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_perfil');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Rol')->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Doctrine\DBAL\DBALException $e) {
                    //$this->addFlash('danger', 'Este dato no puede ser eliminado porque est� siendo utilizado en el sistema.');
                    //$msg= $ex->getTraceAsString();
                    $msg= 'Este dato no puede ser eliminado porque se utiliza en el sistema.';
                }            
        return new Response(json_encode($msg));               
    }    
    
    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sistema_seguridad_perfil_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }    

}