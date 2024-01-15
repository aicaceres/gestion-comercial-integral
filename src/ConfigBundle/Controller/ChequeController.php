<?php

namespace ConfigBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Cheque;
use ConfigBundle\Entity\BancoMovimiento;
use ConfigBundle\Form\ChequeType;

/**
 * @Route("/cheque")
 */
class ChequeController extends Controller {

    /**
     * @Route("/", name="sistema_cheque")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $tipo = $request->get('tipo');
        $entities = $em->getRepository('ConfigBundle:Cheque')->findNoRetenciones($tipo);
        return $this->render('ConfigBundle:Cheque:index.html.twig', array(
                'entities' => $entities,
                'tipo' => $tipo
        ));
    }

    /**
     * @Route("/", name="sistema_cheque_create")
     * @Method("POST")
     * @Template("ConfigBundle:Cheque:edit.html.twig")
     */
    public function createAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $entity = new Cheque();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // $equipo = $em->getRepository('ConfigBundle:Equipo')->find($this->get('session')->get('equipo'));

            // $entity->setPrefijoNro(sprintf("%03d", $equipo->getPrefijo()));
            // $nro = $equipo->getNroInternoCheque() + 1;
            // $entity->setChequeNro(sprintf("%06d", $nro));
            // $equipo->setNroInternoCheque($nro);
            // $em->persist($equipo);
            if($entity->getTipo()=='P'){
              // generar movimiento bancario
              $movim = new BancoMovimiento();
              $movim->setFechaCarga(new \DateTime());
              $movim->setImporte($entity->getValor());
              $movim->setObservaciones($entity->getObservaciones());
              $tipoMov = $em->getRepository('ConfigBundle:BancoTipoMovimiento')->findOneByNombre('CHEQUE');
              $movim->setTipoMovimiento($tipoMov);
              $movim->setNroMovimiento($entity->getNroCheque());
              $movim->setBanco($entity->getBanco());
              $movim->setCuenta($entity->getCuenta());
              $movim->setCheque($entity);
              $em->persist($movim);
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_cheque'));
        }
        return $this->render('ConfigBundle:Cheque:edit.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Cheque entity.
     * @param Cheque $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Cheque $entity) {
        $form = $this->createForm(new ChequeType(), $entity, array(
            'action' => $this->generateUrl('sistema_cheque_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_cheque_new")
     * @Method("GET")
     * @Template("ConfigBundle:Cheque:edit.html.twig")
     */
    public function newAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $entity = new Cheque();
        $tipo = $request->get('tipo');
        $entity->setTipo($tipo);
        if($tipo === 'P') $entity->setDador('PROPIO');
        $form = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:Cheque:edit.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_cheque_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Cheque')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cheque entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Cheque:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
                //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Cheque entity.
     * @param Cheque $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Cheque $entity) {
        $form = $this->createForm(new ChequeType(), $entity, array(
            'action' => $this->generateUrl('sistema_cheque_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_cheque_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Cheque:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Cheque')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cheque entity.');
        }

        // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_cheque'));
        }
        return $this->render('ConfigBundle:Cheque:edit.html.twig', array(
                'entity' => $entity,
                'form' => $editForm->createView(),
                //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_cheque_delete")
     * @Method("POST")
     */
    public function deleteAction($id) {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Cheque')->find($id);
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