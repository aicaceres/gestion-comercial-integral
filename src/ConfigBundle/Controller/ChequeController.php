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
use ConfigBundle\Form\ChequeLoteType;

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
        $tipoCheque = $request->get('tipocheque');
        $estado = $request->get('estado');
        $entities = $em->getRepository('ConfigBundle:Cheque')->findNoRetenciones($tipo, $tipoCheque, $estado);

        $option = $request->get('option');
        if($option){
          $dataCheque = array(
                'tipo' => $tipo,
                'tipocheque'=> $tipoCheque,
                'estado' => $estado,
                'result' => $entities
              );
          $this->get('session')->set('dataCheque', $dataCheque);
          return $this->redirectToRoute('print_cheque');
        }
        return $this->render('ConfigBundle:Cheque:index.html.twig', array(
                'entities' => $entities,
                'tipo' => $tipo,
                'tipocheque' => $tipoCheque,
                'estado'=> $estado
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

       /**
     * @Route("/printCheque.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_cheque")
     * @Method("get")
     */
    public function printChequeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $data = $this->get('session')->get('dataCheque');
        $tipo = $data['tipo'];
        $tipoCheque = $data['tipocheque'];
        $estado = $data['estado'];
        $items =$data['result'];

        $textoFiltro = array($tipo, $tipoCheque, $estado);

            $facade = $this->get('ps_pdf.facade');
            $response = new Response();
            $this->render('ConfigBundle:Cheque:print.pdf.twig',
                array('items' => $items, 'filtro' => $textoFiltro), $response);

            $xml = $response->getContent();
            $content = $facade->render($xml);
            $hoy = new \DateTime();
            return new Response($content, 200, array('content-type' => 'application/pdf',
                'Content-Disposition' => 'filename=lista_cheques' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    // crear lote de cheques
        /**
     * @Route("/lote", name="sistema_cheque_lote")
     * @Method("GET")
     * @Template()
     */
    public function loteAction() {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $entity = new Cheque();
        $entity->setTipo('P');
        $entity->setDador('PROPIO');
        $entity->setEstado('C');
        $entity->setValor(0);
        $form = $this->createForm(new ChequeLoteType(), $entity, array(
            'action' => $this->generateUrl('sistema_cheque_lote_create'),
            'method' => 'POST',
        ));
        return $this->render('ConfigBundle:Cheque:lote.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
        ));
    }

        /**
     * @Route("/lote", name="sistema_cheque_lote_create")
     * @Method("POST")
     * @Template("ConfigBundle:Cheque:lote.html.twig")
     */
    public function createLoteAction(Request $request) {
UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $cheque = new Cheque();
        $form = $this->createForm(new ChequeLoteType(), $cheque, array(
            'action' => $this->generateUrl('sistema_cheque_lote_create'),
            'method' => 'POST',
        ));
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $data = $request->get('configbundle_chequelote');
        $nroInicial = $data['nroCheque'];

        try {
          for ($i = 0; $i < $data['cantidad']; $i++) {
            $entity = new Cheque();
            $entity->setTipo('P');
            $entity->setDador('PROPIO');
            $entity->setEstado('C');
            $entity->setValor(0);
            $entity->setNroCheque($nroInicial++);
            $entity->setFecha(new \DateTime($data['fecha']));
            $banco = $em->getRepository('ConfigBundle:Banco')->find($data['banco']);
            $entity->setBanco($banco);
            $cuenta = $em->getRepository('ConfigBundle:CuentaBancaria')->find($data['cuenta']);
            $entity->setCuenta($cuenta);
            $entity->setTipoCheque($data['tipoCheque']);
            $entity->setObservaciones($data['observaciones']);
            $em->persist($entity);
            // movimiento
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
          $em->flush();
          $em->getConnection()->commit();
          return $this->redirect($this->generateUrl('sistema_cheque'));
        }catch (\Exception $ex) {
          // $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
          $em->getConnection()->rollback();
        }

        return $this->render('ConfigBundle:Cheque:lote.html.twig', array(
          'entity' => $cheque,
          'form' => $form->createView(),
        ));

    }


}