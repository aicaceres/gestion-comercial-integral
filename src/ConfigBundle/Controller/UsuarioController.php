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
use ConfigBundle\Entity\Usuario;
use ConfigBundle\Entity\RolUnidadNegocio;
use ConfigBundle\Form\UsuarioType;
use ConfigBundle\Form\ProfileType;

/**
 * @Route("/usuario")
 */
class UsuarioController extends Controller
{
/**
 * 
 * LOGIN 
 * 
 */        

    /**
    * @Route("/login", name="usuario_login")
    */
    public function loginAction() {        
        $authUtils = $this->get('security.authentication_utils');
        $em = $this->getDoctrine()->getManager();
        $empresas = $em->getRepository('ConfigBundle:Empresa')->findAll();
        return $this->render('::login.html.twig', array(
            'empresas' => $empresas,
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/login_check", name="usuario_login_check")
     */
    public function loginCheckAction() {
        // el "login check" lo hace Symfony automáticamente
    }

    /**
     * @Route("/logout", name="usuario_logout")
     */
    public function logoutAction() {
        // el logout lo hace Symfony automáticamente
    }

    /**
     * @Route("/profile", name="usuario_profile")
     * @Method("GET")
     * @Template("ConfigBundle:Usuario:profile.html.twig") 
     */
    public function profileAction(){
        $form = $this->createForm(new ProfileType(), $this->getUser(), array(
            'action' => $this->generateUrl('usuario_profile_update'),
            'method' => 'PUT',
        ));
        return array(
            'entity' => $this->getUser(),
            'form'   => $form->createView()
        );        
    }     
    
    /**
     * @Route("/profile_update", name="usuario_profile_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Usuario:profile.html.twig") 
     */
        
    public function profileUpdateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getUser();
        $form = $this->createForm(new ProfileType(), $entity, array(
            'action' => $this->generateUrl('usuario_profile_update'),
            'method' => 'PUT',
        ));
        $passwordOriginal = $form->getData()->getPassword();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null == $entity->getPassword()) {
                $entity->setPassword($passwordOriginal);
            } else {
                $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                $password = $encoder->encodePassword($entity->getPassword(),$entity->getSalt());
                $entity->setPassword($password);
            }
            $em->flush();
            $this->addFlash('success','El perfil fue actualizado!' );
            return $this->redirectToRoute('homepage');
        }
        
        return array(
            'entity'      => $entity,
            'form'   => $form->createView()
        );
    }    
    
    
    
    /**
     * @Route("/", name="sistema_seguridad_usuario")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {   
        //UtilsController::haveAccess($this->getUser(), $this->getUnidadNegocioId(), 'sistema_seguridad_usuario');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Usuario')->findAll();
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
     * @Route("/", name="usuario_create")
     * @Method("POST")
     * @Template("ConfigBundle:Usuario:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_usuario_new');
        $entity = new Usuario();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
            $password = $encoder->encodePassword($entity->getPassword(),$entity->getSalt());
            $entity->setPassword($password);
            $entity->setNombre( strtoupper($entity->getNombre()) );
            $entity->setEmail(strtolower($entity->getEmail()) );
 
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','El usuario fue creado!' );
            return $this->redirectToRoute('sistema_seguridad_usuario');            
            
        }
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }    

    /**
     * @param Usuario $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Usuario $entity)
    {
        $form = $this->createForm(new UsuarioType(), $entity, array(
            'action' => $this->generateUrl('usuario_create'),
            'method' => 'POST',
        ));
        //$form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }    
    
    /**
     * @Route("/new", name="sistema_seguridad_usuario_new")
     * @Method("GET")
     * @Template("ConfigBundle:Usuario:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_usuario_new');
        $em = $this->getDoctrine()->getManager();
        $unidnegocio = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $roles = $em->getRepository('ConfigBundle:Rol')->findAll();
        $rolunid = new RolUnidadNegocio();
        $rolunid->setUnidadNegocio($unidnegocio);
        
        $entity = new Usuario();          
        $entity->setEmail('mail@mail.com');
        $entity->addRolesUnidadNegocio($rolunid);
        $form   = $this->createCreateForm($entity);        
        return array(
            'entity' => $entity,
            'roles' => $roles,
            'form'   => $form->createView()
        );
    }    

    /**
     * @Route("/{id}", name="usuario_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario.');
        }
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }    
    
    /**
     * @Route("/{id}/edit", name="sistema_seguridad_usuario_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_usuario_edit');
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('ConfigBundle:Rol')->findAll();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'roles' => $roles,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }    
    
    /**
    * @param Usuario $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Usuario $entity)
    {
        $form = $this->createForm(new UsuarioType(), $entity, array(
            'action' => $this->generateUrl('usuario_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

       // $form->add('submit', 'submit', array('label' => 'Update'));
        return $form;
    }    
    
    /**
     * @Route("/update/{id}", name="usuario_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Usuario:edit.html.twig")
     */
        
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_usuario_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No existe el usuario.');
        }
        
        $origUnid = new ArrayCollection();
        foreach ($entity->getRolesUnidadNegocio() as $item) {
            $origUnid->add($item);
        }
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $passwordOriginal = $editForm->getData()->getPassword();
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if (null == $entity->getPassword()) {
                $entity->setPassword($passwordOriginal);
            } else {
                $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                $password = $encoder->encodePassword($entity->getPassword(),$entity->getSalt());
                $entity->setPassword($password);
            }
            foreach ($origUnid as $item) {
                if (false === $entity->getRolesUnidadNegocio()->contains($item)) {
                    $em->remove($item);
                }
            }
            
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success','El usuario fue actualizado!' );
            return $this->redirectToRoute('sistema_seguridad_usuario');
        }
        
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }    

    /**
     * @Route("/delete/{id}", name="sistema_seguridad_usuario_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);
        try{
            $band=false;
            // verificar que no sea administrador
            foreach ($entity->getRolesUnidadNegocio() as $rol) {
                if( $rol->getRol()->getFijo()){
                    $band=true;
                    break;
                }
            }
            if($band){
                $msg = "El usuario Administrador no puede ser eliminado!!!";
                return new Response(json_encode($msg));
            }
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {
            
            $msg= "El usuario no puede ser eliminado. Posee registros asociados en el sistema.";
        }
        return new Response(json_encode($msg));
    }    
    
    
    /*public function deleteAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('datos')['id'], 'usuario_delete');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ConfigBundle:Usuario')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No existe el usuario.');
            }

            $em->remove($entity);
            $em->flush();
            $this->addFlash('success','El usuario fue eliminado!' );
        }
        return $this->redirectToRoute('sistema_seguridad_usuario');
    }*/

    /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sistema_seguridad_usuario_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


}
