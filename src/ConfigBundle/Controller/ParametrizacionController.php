<?php

namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Entity\Parametrizacion;
use ConfigBundle\Form\ParametrizacionType;

/**
 * @Route("/parametrizacion")
 */
class ParametrizacionController extends Controller
{

    /**
     * @Route("/renderVentas", name="ventas_parametrizacion")
     * @Method("GET")
     * @Template()
     */
    public function renderVentasAction()
    {        
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_parametrizacion');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if (!$entity) {
            throw $this->createNotFoundException('No existe parametrización para el sistema. Consulte al administrador!');
        }
        $editForm = $this->createEditForm($entity,'ventas');        
        return $this->render('ConfigBundle:Parametrizacion:edit_ventas.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Moneda entity.
    * @param Parametrizacion $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Parametrizacion $entity,$route)
    {
        $form = $this->createForm(new ParametrizacionType(), $entity, array(
            'action' => $this->generateUrl('parametrizacion_update', array('route' => $route)),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{route}", name="parametrizacion_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Parametrizacion:edit.html.twig")
     */
    public function updateAction(Request $request,$route)
    {
        $unidneg_id = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_parametrizacion');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
        if (!$entity) {
            throw $this->createNotFoundException('No existe parametrización para el sistema. Consulte al administrador!');
        }
        $editForm = $this->createEditForm($entity,$route);    
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl($route));
        }
        return $this->render('ConfigBundle:Parametrizacion:edit_ventas.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        ));
    }
}
