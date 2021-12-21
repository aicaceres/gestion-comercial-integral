<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Empresa;
use ConfigBundle\Form\EmpresaType;

/**
 * @Route("/empresa")
 */
class EmpresaController extends Controller {

    /**
     * @Route("/", name="sistema_seguridad_empresa")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_empresa');
        $em = $this->getDoctrine()->getManager();
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($this->get('session')->get('unidneg_id'));
        $empresa = $unidneg->getEmpresa();
        if (!$empresa) {
            throw $this->createNotFoundException('Unable to find Empresa entity.');
        }
        $editForm = $this->createEditForm($empresa);
        return $this->render('ConfigBundle:Empresa:edit.html.twig', array(
                    'entity' => $empresa,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * @param Permiso $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Empresa $entity) {
        $form = $this->createForm(new EmpresaType(), $entity, array(
            'action' => $this->generateUrl('sistema_seguridad_empresa_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_seguridad_empresa_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Empresa:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_seguridad_empresa');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Empresa')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Empresa entity.');
        }
        $origUnid = new ArrayCollection();
        foreach ($entity->getUnidades() as $item) {
            $origUnid->add($item);
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            foreach ($origUnid as $item) {
                if (false === $entity->getUnidades()->contains($item)) {
                    $em->remove($item);
                }
            }
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'Datos modificados con éxito!');
        }
        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

}