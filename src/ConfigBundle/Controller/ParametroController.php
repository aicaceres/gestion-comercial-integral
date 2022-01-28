<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Parametro;
use ConfigBundle\Form\ParametroType;
use ConfigBundle\Entity\Localidad;
use ConfigBundle\Form\LocalidadType;

/**
 * @Route("/parametro")
 */
class ParametroController extends Controller {
    /*
     * PARAMETROS AFIP
     */

    /**
     * @Route("/afip/{slug}", name="sistema_parametro_afip")
     * @Method("GET")
     * @Template()
     */
    public function afipAction($slug = 'alicuota') {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        switch ($slug) {
            case 'alicuota':
                $entity = 'ConfigBundle:AfipAlicuota';
                break;
            case 'comprobante':
                $entity = 'ConfigBundle:AfipComprobante';
                break;
            case 'operacion':
                $entity = 'ConfigBundle:AfipOperacion';
                break;
        }
        $paramafip = $em->getRepository($entity)->findAll();
        return $this->render('ConfigBundle:Parametro:parametro-afip.html.twig', array(
                    'entities' => $paramafip, 'slug' => $slug));
    }

    /**
     * @Route("/setParamAfipActivo", name="set_paramafip_activo")
     * @Method("GET")
     */
    public function setParamAfipActivo(Request $request) {
        $id = $request->get('id');
        $valor = $request->get('valor');
        switch ($request->get('slug')) {
            case 'alicuota':
                $entity = 'ConfigBundle:AfipAlicuota';
                break;
            case 'comprobante':
                $entity = 'ConfigBundle:AfipComprobante';
                break;
            case 'operacion':
                $entity = 'ConfigBundle:AfipOperacion';
                break;
        }
        $em = $this->getDoctrine()->getManager();
        $dato = $em->getRepository($entity)->find($id);
        try {
            $dato->setActivo($valor);
            $em->persist($dato);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getMessage();
        }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/getAfipLetraComprobante", name="get_afip_letra_comprobante")
     * @Method("GET")
     */
    public function getAfipLetraComprobante(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $comp = $em->getRepository('ConfigBundle:AfipComprobante')->find($id);
        $valor = split('-', $comp->getValor());
        $signo = ( $valor[0] == 'DEB' ) ? '+' : '-';
        return new Response(json_encode(array('letra' => $valor[1], 'signo' => $signo)));
    }

    /*
     * OTROS PARAMETROS
     */

    /**
     * @Route("/rubro_new", name="sistema_parametro_rubro_new")
     * @Method("GET")
     */
    public function newRubroAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $rub = $request->get('rub');
        $em = $this->getDoctrine()->getManager();
        $agrupador = $em->getRepository('ConfigBundle:Parametro')->findOneByNombre('rubro');
        $rubro = new Parametro();
        $rubro->setAgrupador($agrupador);
        $rubro->setPadre($agrupador);
        $rubro->setNombre(strtoupper($rub));
        $rubro->setBoleano(false);
        $rubro->setActivo(true);
        $em->persist($rubro);
        $em->flush();
        $rubros = $em->getRepository('ConfigBundle:Parametro')->findRubros();
        $partial = $this->renderView('ConfigBundle:Parametro:_partial-rubros.html.twig',
                array('rubros' => $rubros, 'dato' => $rubro));
        return new Response($partial);
    }

    /**
     * @Route("/{slug}", name="sistema_parametro")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($slug = 'rubro') {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        if ($slug == 'rubro') {
            $rubros = $em->getRepository('ConfigBundle:Parametro')->findRubros();
            $subrubros = $em->getRepository('ConfigBundle:Parametro')->findSubRubros();
            return $this->render('ConfigBundle:Parametro:list-rubro.html.twig', array(
                        'rubros' => $rubros, 'subrubros' => $subrubros));
        }
        elseif ($slug == 'localidad') {
            $parametros = $em->getRepository('ConfigBundle:Localidad')->findAll();
        }
        else {
            $agrupador = $em->getRepository('ConfigBundle:Parametro')->findOneByNombre($slug);
            $parametros = $em->getRepository('ConfigBundle:Parametro')->findByAgrupadorDQL($agrupador)->getResult();
        }
        return $this->render('ConfigBundle:Parametro:list-' . $slug . '.html.twig', array(
                    'entities' => $parametros));
    }

    /**
     * @Route("/{slug}/new", name="sistema_parametro_new")
     * @Method("GET")
     * @Template("ConfigBundle:Parametro:edit.html.twig")
     */
    public function newAction($slug = 'rubro') {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        if ($slug == 'localidad') {
            $entity = new Localidad();
            $prov = $em->getRepository('ConfigBundle:Provincia')->findOneByByDefault(1);
            $entity->setProvincia($prov);
            $form = $this->createForm(new LocalidadType(), $entity, array('attr' => array('slug' => $slug),
                'action' => $this->generateUrl('sistema_parametro_create', array('slug' => 'localidad')),
                'method' => 'PUT',
            ));
            return $this->render('ConfigBundle:Parametro:edit-localidad.html.twig', array(
                        'entity' => $entity, 'form' => $form->createView()));
        }
        else {
            $agrupador = $em->getRepository('ConfigBundle:Parametro')->findOneByNombre($slug);
            $entity = new Parametro();
            $entity->setAgrupador($agrupador);
            if ($slug == 'calificacion-proveedor') {
                $orden = $em->getRepository('ConfigBundle:Parametro')->findOrdenCalificacionProveedor();
                $entity->setNumerico($orden + 1);
            }
            $form = $this->createForm(new ParametroType(), $entity, array('attr' => array('slug' => $slug),
                'action' => $this->generateUrl('sistema_parametro_create', array('slug' => $slug)),
                'method' => 'PUT',
            ));
            if ($slug == 'rubro') {
                $rubros = $em->getRepository('ConfigBundle:Parametro')->findRubros();
                /* $form   = $this->createForm(new ParametroType(), $entity, array('attr'=>array('slug'=>$slug)));
                  $form   = $this->createForm(new ParametroType(), $entity, array(
                  'action' => $this->generateUrl('sistema_parametro_create', array('slug' => 'rubro')),
                  'method' => 'PUT',
                  )); */
                return $this->render('ConfigBundle:Parametro:edit-rubro.html.twig', array(
                            'entity' => $entity,
                            'rubros' => $rubros,
                            'dato' => null,
                            'form' => $form->createView()));
            }
            // $form   = $this->createForm(new ParametroType(), $entity, array('attr'=>array('slug'=>$slug)));
        }
        return $this->render('ConfigBundle:Parametro:edit-' . $slug . '.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()));
    }

    /**
     * @Route("/{slug}/{id}/edit", name="sistema_parametro_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($slug, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        if ($slug == 'localidad') {
            $entity = $em->getRepository('ConfigBundle:Localidad')->find($id);
            $editForm = $this->createForm(new LocalidadType(), $entity, array(
                'action' => $this->generateUrl('sistema_parametro_localidad_update', array('id' => $entity->getId())),
                'method' => 'PUT',
            ));

            //$editForm = $this->createForm(new LocalidadType(), $entity);
        }
        else {
            $entity = $em->getRepository('ConfigBundle:Parametro')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Parametro entity.');
            }
            if ($slug == 'numeracion') {
                $entity->setNumerico(sprintf("%04d", $entity->getNumerico()));
                $entity->setNumerico2(sprintf("%08d", $entity->getNumerico2()));
            }
            //$editForm = $this->createForm(new ParametroType(), $entity, array('attr'=>array('slug'=>$slug)));
            $editForm = $this->createForm(new ParametroType(), $entity, array('attr' => array('slug' => $slug),
                'action' => $this->generateUrl('sistema_parametro_update', array('id' => $entity->getId())),
                'method' => 'PUT',
            ));
            if ($slug == 'rubro') {
                $rubros = $em->getRepository('ConfigBundle:Parametro')->findRubros();
                $dato = $em->getRepository('ConfigBundle:Parametro')->find($entity->getAgrupador());

                return $this->render('ConfigBundle:Parametro:edit-rubro.html.twig', array(
                            'entity' => $entity,
                            'rubros' => $rubros,
                            'dato' => $dato,
                            'form' => $editForm->createView()));
            }
        }
        return $this->render('ConfigBundle:Parametro:edit-' . $slug . '.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/delete", name="sistema_parametro_delete")
     * @Method("POST")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Parametro')->find($id);
        try {
            $em->remove($entity);
            $em->flush();
            $msg = 'OK';
        }
        catch (\Exception $ex) {
            $msg = $ex->getMessage();
        }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/{id}/deleteLocalidad", name="sistema_localidad_delete")
     * @Method("POST")
     */
    public function deleteLocalidadAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Localidad')->find($id);
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

    /**
     * @Route("/{slug}/create", name="sistema_parametro_create")
     * @Method("PUT")
     * @Template()
     */
    public function createAction(Request $request, $slug) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        if ($slug == 'localidad') {
            $entity = new Localidad();
            $form = $this->createForm(new LocalidadType(), $entity, array('attr' => array('slug' => $slug),
                'action' => $this->generateUrl('sistema_parametro_create', array('slug' => $slug)),
                'method' => 'PUT',
            ));
        }
        else {
            $entity = new Parametro();
            // $form    = $this->createForm(new ParametroType(), $entity, array('attr'=>array('slug'=>$slug)));
            $form = $this->createForm(new ParametroType(), $entity, array('attr' => array('slug' => $slug),
                'action' => $this->generateUrl('sistema_parametro_create', array('slug' => $slug)),
                'method' => 'PUT',
            ));
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($slug == 'localidad') {
                try {
                    $em->persist($entity);
                    $em->flush();
                    $this->addFlash('success', 'El registro fue creado con Éxito!');
                    return $this->redirect($this->generateUrl('sistema_parametro', array('slug' => $slug)));
                }
                catch (\Doctrine\DBAL\DBALException $e) {
                    $this->addFlash('error', $e->getPrevious()->getMessage());
                }
            }
            else {
                if ($slug == 'rubro') {
                    $agrupador = $em->getRepository('ConfigBundle:Parametro')->find($request->get('txtrubro'));
                }
                else {
                    $agrupador = $em->getRepository('ConfigBundle:Parametro')->findOneByNombre($slug);
                }
                $entity->setAgrupador($agrupador);
                try {
                    if ($slug != 'calificacion-proveedor') {
                        $entity->setNumerico($entity->getNumerico() ? 1 : 0 );
                        $entity->setNumerico2($entity->getNumerico2() ? 1 : 0 );
                    }
                    $em->persist($entity);
                    $em->flush();
                    $this->addFlash('success', 'El registro fue creado con Éxito!');
                    return $this->redirect($this->generateUrl('sistema_parametro', array('slug' => $slug)));
                }
                catch (\Doctrine\DBAL\DBALException $e) {
                    $this->addFlash('error', $e->getPrevious()->getMessage());
                }
            }
        }
        if ($slug == 'rubro') {
            $rubros = $em->getRepository('ConfigBundle:Parametro')->findRubros();
            $dato = $em->getRepository('ConfigBundle:Parametro')->find($request->get('txtrubro'));
            return $this->render('ConfigBundle:Parametro:edit-' . $slug . '.html.twig', array(
                        'entity' => $entity,
                        'rubros' => $rubros,
                        'dato' => $dato,
                        'form' => $form->createView()));
        }
        return $this->render('ConfigBundle:Parametro:edit-' . $slug . '.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/update", name="sistema_parametro_update")
     * @Method("PUT")
     * @Template()
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Parametro')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Parametro entity.');
        }
        $slug = $entity->getAgrupador()->getPadre() ? 'rubro' : $entity->getAgrupador()->getNombre();
        $slug = $slug ? $slug : 'rubro';
        //$editForm   = $this->createForm(new ParametroType(), $entity, array('attr'=>array('slug'=>$slug)));
        $editForm = $this->createForm(new ParametroType(), $entity, array('attr' => array('slug' => $slug),
            'action' => $this->generateUrl('sistema_parametro_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if ($slug == 'rubro') {
                $agrupador = $em->getRepository('ConfigBundle:Parametro')->find($request->get('txtrubro'));
                $entity->setAgrupador($agrupador);
                $entity->setNumerico($entity->getNumerico() ? 1 : 0 );
                $entity->setNumerico2($entity->getNumerico2() ? 1 : 0 );
            }

            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'Los datos fueron modificados con Éxito!');
            return $this->redirect($this->generateUrl('sistema_parametro', array('slug' => $slug)));
        }

        return $this->render('ConfigBundle:Parametro:edit-' . $slug . '.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/updateLocalidad", name="sistema_parametro_localidad_update")
     * @Method("PUT")
     * @Template()
     */
    public function updateLocalidadAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_parametro');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Localidad')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Parametro entity.');
        }

        $editForm = $this->createForm(new LocalidadType(), $entity, array(
            'action' => $this->generateUrl('sistema_parametro_localidad_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            //$entity->setSlug($entity->slugName());

            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'Los datos fueron modificados con Éxito!');
            return $this->redirect($this->generateUrl('sistema_parametro', array('slug' => 'localidad')));
        }

        return $this->render('ConfigBundle:Parametro:edit-localidad.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
        ));
    }

}