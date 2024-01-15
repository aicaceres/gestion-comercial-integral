<?php
namespace ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\CuentaBancaria;
use ConfigBundle\Entity\Banco;
use ConfigBundle\Entity\BancoMovimiento;
use ConfigBundle\Form\BancoType;
use ConfigBundle\Form\BancoMovimientoType;

/**
 * @Route("/banco")
 */
class BancoController extends Controller
{
    /**
     * @Route("/", name="sistema_banco")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Banco')->findAll();
        return $this->render('ConfigBundle:Banco:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * @Route("/", name="sistema_banco_create")
     * @Method("POST")
     * @Template("ConfigBundle:Banco:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_new');
        $entity = new Banco();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_banco'));
        }
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Banco entity.
    * @param Banco $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Banco $entity)
    {
        $form = $this->createForm(new BancoType(), $entity, array(
            'action' => $this->generateUrl('sistema_banco_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * @Route("/new", name="sistema_banco_new")
     * @Method("GET")
     * @Template("ConfigBundle:Banco:edit.html.twig")
     */
    public function newAction()
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_new');
        $entity = new Banco();
        $form   = $this->createCreateForm($entity);
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/edit", name="sistema_banco_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Banco')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Banco entity.');
        }
        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Banco entity.
    * @param Banco $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Banco $entity)
    {
        $form = $this->createForm(new BancoType(), $entity, array(
            'action' => $this->generateUrl('sistema_banco_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/{id}", name="sistema_banco_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Banco:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_edit');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Banco')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Banco entity.');
        }

       // $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_banco'));
        }
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),3
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="sistema_banco_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_delete');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:Banco')->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        } catch (\Exception $ex) {  $msg= $ex->getTraceAsString();     }
        return new Response(json_encode($msg));
    }

    /**
     * @Route("/add", name="sistema_banco_add")
     * @Method("GET")
     */
    public function addBancoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $banco = new Banco();
        try{
            $banco->setNombre( strtoupper($request->get('nombre')) );
            $em->persist($banco);
            $em->flush();
            $msg = array( 'id'=>$banco->getId(), 'text'=>$banco->getNombre() ) ;
        } catch (\Exception $ex) {  $msg= $ex->getMessage();     }
        return new Response(json_encode($msg));
    }

    /** MOVIMIENTOS */

    /**
     * @Route("/movimiento", name="sistema_banco_movimiento")
     * @Method("GET")
     * @Template()
     */
    public function movimientoAction(Request $request)
    {
        $unidneg = $this->get('session')->get('unidneg_id');
        UtilsController::haveAccess($this->getUser(), $unidneg, 'sistema_banco_movimiento');
        $option = $request->get('option') ? $request->get('option') : 'B';
        $em = $this->getDoctrine()->getManager();
        $conciliado = $this->getUser()->isAdmin($unidneg) ? $request->get('conciliado') : 0;
        $bancoId = $request->get('bancoId');
        $cuentaId = $request->get('cuentaId');
        $bancos = $em->getRepository('ConfigBundle:Banco')->findBy(array('activo' => 1), array('nombre' => 'ASC'));
        $banco = $bancoId ? $em->getRepository('ConfigBundle:Banco')->find($bancoId) : $bancos[0];

        $cuentas = $em->getRepository('ConfigBundle:CuentaBancaria')->findBy(array('banco' => $banco->getId(),'activo' => 1), array('nroCuenta' => 'ASC'));
        $cuenta = $cuentaId ? $em->getRepository('ConfigBundle:CuentaBancaria')->find($cuentaId) : ($cuentas ? $cuentas[0] : null);

        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $result = $em->getRepository('ConfigBundle:BancoMovimiento')->findByCriteria($banco->getId(),$cuenta ? $cuenta->getId() : 0, $periodo, $conciliado);

        if($option === 'I'){
          $dataMovimiento = array(
                'banco' => $banco->getNombre(),
                'cuenta'=> $cuenta->getNroCuenta(),
                'conciliado' => $conciliado,
                'periodo' => $periodo,
                'result' => $result
              );
          $this->get('session')->set('dataMovimiento', $dataMovimiento);
          return $this->redirectToRoute('print_banco_movimiento');
        }

        return $this->render('ConfigBundle:Banco:movimientos.html.twig', array(
            'entities' => $result['movimientos'],
            'saldoInicial' => $result['saldoInicial'],
            'saldoConciliado' => $result['saldoConciliado'],
            'saldoTotal' => $result['saldoTotal'],
            'desde' => $periodo['ini'],
            'hasta' => $periodo['fin'],
            'conciliado' => $conciliado,
            'bancos' => $bancos,
            'bancoId' => $banco->getId(),
            'cuentas' => $cuentas,
            'cuentaId' => $cuenta ? $cuenta->getId() : $cuentaId
        ));
    }

        /**
     * @Route("/{bancoId}/{cuentaId}/movimiento/new", name="sistema_banco_movimiento_new")
     * @Method("GET")
     * @Template("ConfigBundle:Banco:movimiento-edit.html.twig")
     */
    public function movimientoNewAction($bancoId, $cuentaId)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_movimiento');
        $entity = new BancoMovimiento();
        $em = $this->getDoctrine()->getManager();
        $banco = $em->getRepository('ConfigBundle:Banco')->find($bancoId);
        $entity->setBanco($banco);
        $cuenta = $em->getRepository('ConfigBundle:CuentaBancaria')->find($cuentaId);
        $entity->setCuenta($cuenta);
        $entity->setConciliado(true);
        $form = $this->movimientoCreateForm($entity);
        return $this->render('ConfigBundle:Banco:movimiento-edit.html.twig', array(
            'entity' => $entity,
            'bancoId' => $bancoId,
            'form'   => $form->createView(),
        ));
    }

        /**
     * @Route("/{bancoId}/movimiento/", name="sistema_banco_movimiento_create")
     * @Method("POST")
     * @Template("ConfigBundle:Banco:movimiento-edit.html.twig")
     */
    public function movimientoCreateAction(Request $request, $bancoId)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_movimiento');
        $entity = new BancoMovimiento();
        $em = $this->getDoctrine()->getManager();
        $banco = $em->getRepository('ConfigBundle:Banco')->find($bancoId);
        $entity->setBanco($banco);
        $form = $this->movimientoCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_banco_movimiento', array('bancoId' => $entity->getBanco()->getId(), 'cuentaId'=> $entity->getCuenta()->getId())));
        }
        return $this->render('ConfigBundle:Banco:movimiento-edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Banco entity.
    * @param BancoMovimiento $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function movimientoCreateForm(BancoMovimiento $entity)
    {
        $form = $this->createForm(new BancoMovimientoType(), $entity, array(
            'action' => $this->generateUrl('sistema_banco_movimiento_create', array('bancoId' => $entity->getBanco()->getId())),
            'method' => 'POST',
        ));
        return $form;
    }

     /**
     * @Route("/movimiento/{id}/edit", name="sistema_banco_movimiento_edit")
     * @Method("GET")
     * @Template()
     */
    public function movimientoEditAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_movimiento');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:BancoMovimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Banco entity.');
        }
        $editForm = $this->movimientoEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ConfigBundle:Banco:movimiento-edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Banco entity.
    * @param BancoMovimiento $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function movimientoEditForm(BancoMovimiento $entity)
    {
        $form = $this->createForm(new BancoMovimientoType(), $entity, array(
            'action' => $this->generateUrl('sistema_banco_movimiento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/movimiento/{id}", name="sistema_banco_movimiento_update")
     * @Method("PUT")
     * @Template("ConfigBundle:Banco:movimiento-edit.html.twig")
     */
    public function movimientoUpdateAction(Request $request, $id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_movimiento');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:BancoMovimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Banco entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->movimientoEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistema_banco_movimiento', array('bancoId' => $entity->getBanco()->getId(), 'cuentaId'=> $entity->getCuenta()->getId())));
        }
        return $this->render('ConfigBundle:Banco:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

       /**
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sistema_banco_movimiento_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

        /**
     * @Route("/movimiento/delete/{id}", name="sistema_banco_movimiento_delete")
     * @Method("POST")
     */
    public function movimientoDeleteAction($id)
    {
        UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'sistema_banco_movimiento');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('ConfigBundle:BancoMovimiento')->find($id);
        try{
            $em->remove($entity);
            $em->flush();
            $msg ='OK';
        }  catch (\Doctrine\DBAL\DBALException $e) {
            /*if ($this->getUser()->getRol()->getAdmin())
                $this->addFlash('danger', $e->getMessage());
              else*/
                $this->addFlash('danger', 'Este dato no puede ser eliminado porque está siendo utilizado en el sistema.');
        }
        return $this->redirectToRoute('sistema_banco_movimiento',array('bancoId'=>$entity->getBanco()->getId()));
    }

    /**
     * @Route("/movimiento/addCuenta", name="sistema_banco_add_cuenta")
     * @Method("GET")
     */
    public function movimientoAddCuentaAction(Request $request)
    {
        $id = $request->get('bancoId');
        $nroCuenta = $request->get('cuenta');
        $em = $this->getDoctrine()->getManager();
        $banco = $em->getRepository('ConfigBundle:Banco')->find($id);
        try{
            $cuenta = new CuentaBancaria();
            $cuenta->setBanco($banco);
            $cuenta->setNroCuenta($nroCuenta);
            $cuenta->setActivo(1);
            $em->persist($cuenta);
            $em->flush();
            return new Response(json_encode(array('msg'=>'OK', 'id'=>$cuenta->getId())));
        }  catch (\Doctrine\DBAL\DBALException $e) {
            return new Response(json_encode(array('msg'=>'ERROR', 'id'=>$e->getMessage())));
        }
    }
    /**
     * @Route("/getCuentas", name="sistema_banco_get_cuentas")
     * @Method("GET")
     */
    public function getCuentasAction(Request $request)
    {
        $id = $request->get('bancoId');
        $em = $this->getDoctrine()->getManager();
        $cuentas = $em->getRepository('ConfigBundle:CuentaBancaria')->findCuentasByBanco($id);
        return new Response(json_encode($cuentas));
    }

    /**
     * @Route("/movimiento/conciliar", name="sistema_banco_conciliar")
     * @Method("GET")
     */
    public function conciliarAction(Request $request)
    {
        $ids = explode(',', $request->get("ids")) ;
        $em = $this->getDoctrine()->getManager();
        try{
            foreach($ids as $id){
              $movimiento = $em->getRepository('ConfigBundle:BancoMovimiento')->find($id);
              $movimiento->setConciliado(1);
              $em->persist($movimiento);
            }
            $em->flush();
            $this->addFlash('success', 'Se han conciliado los movimientos seleccionados.');
        }  catch (\Doctrine\DBAL\DBALException $e) {
            $this->addFlash('error', 'Hubo un error al conciliar los movimientos. ' .$e->getMessage());
        }

        return $this->redirectToRoute('sistema_banco_movimiento',
          array(
            'bancoId'=> $request->get('bancoId'),
            'cuentaId' => $request->get('cuentaId'),
            'conciliado' => $request->get('conciliado'),
            'desde' => $request->get('desde'),
            'hasta' => $request->get('hasta')
          )
        );
    }

       /**
     * @Route("/printBancoMovimiento.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_banco_movimiento")
     * @Method("get")
     */
    public function printBancoMovimientoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $data = $this->get('session')->get('dataMovimiento');
        $banco = $data['banco'];
        $cuenta = $data['cuenta'];
        $conciliado = $data['conciliado'];
        $desde = $data['periodo']['ini'];
        $hasta = $data['periodo']['fin'];
        $saldos = array(
          'saldoInicial' => $data['result']['saldoInicial'],
          'saldoConciliado' => $data['result']['saldoConciliado'],
          'saldoTotal' => $data['result']['saldoTotal'],
        );

        $textoFiltro = array($banco, $cuenta, $desde, $hasta, $conciliado);

        if ($request->get('option') == 'I') {
            $items = $data['result']['movimientos'];
            $facade = $this->get('ps_pdf.facade');
            $response = new Response();
            $this->render('ConfigBundle:Banco:movimientos.pdf.twig',
                array('items' => $items, 'filtro' => $textoFiltro, 'saldos' => $saldos), $response);

            $xml = $response->getContent();
            $content = $facade->render($xml);
            $hoy = new \DateTime();
            return new Response($content, 200, array('content-type' => 'application/pdf',
                'Content-Disposition' => 'filename=banco_movimientos' . $hoy->format('dmY_Hi') . '.pdf'));
        }
        else {
            $items = json_decode($request->get('datalist'));
            $hoy = new \DateTime();
            $filename = 'InventarioValorizado_' . $hoy->format('dmY_Hi') . '.xls';
            $search = $request->get('searchterm');
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $sheet = $phpExcelObject->setActiveSheetIndex(0);

            $user = $this->getUser()->getUsername();
            $phpExcelObject->getProperties()->setCreator($user)
                ->setLastModifiedBy($user)
                ->setTitle($filename)
                ->setDescription("Listado de Proveedores");

            // Escribir títulos
            $sheet->setCellValue('A1', 'INVENTARIO VALORIZADO');

            $i = 2;
            if ($search) {
                $sheet->setCellValue('A' . $i, 'Término de Búsqueda: ' . $search);
                $i++;
            }
            if ($deposito) {
                $sheet->setCellValue('A' . $i, 'Depósito: ' . $deposito->getNombre());
                $i++;
            }
            if ($proveedor) {
                $sheet->setCellValue('A' . $i, 'Proveedor: ' . $proveedor->getNombre());
                $i++;
            }
            if ($formaPago) {
                $sheet->setCellValue('A' . $i, 'Forma de Pago: ' . $formaPago->getNombre());
                $i++;
            }
            $i++;
            // Escribir encabezado
            $sheet->setCellValue('A' . $i, 'DEPOSITO')
                ->setCellValue('B' . $i, 'RUBRO')
                ->setCellValue('C' . $i, 'CODIGO')
                ->setCellValue('D' . $i, 'PRODUCTO')
                ->setCellValue('E' . $i, 'PROVEEDOR')
                ->setCellValue('F' . $i, 'MINIMO')
                ->setCellValue('G' . $i, 'ACTUAL')
                ->setCellValue('H' . $i, 'VALORIZADO COSTO')
                ->setCellValue('I' . $i, 'VALORIZADO PRECIO');

            // Escribir contenido
            $i++;
            $totalCosto = $totalPrecio = 0;
            foreach ($items as $item) {
                $sheet->getStyle('H' . $i)->getNumberFormat()->setFormatCode('0.000');

                $valorizCosto = str_replace(',', '', $item['7']);
                $valorizPrecio = str_replace(',', '', $item['8']);

                $sheet->setCellValue('A' . $i, $item['0'])
                    ->setCellValue('B' . $i, $item['1'])
                    ->setCellValue('C' . $i, $item['2'])
                    ->setCellValue('D' . $i, $item['3'])
                    ->setCellValue('E' . $i, $item['4'])
                    ->setCellValue('F' . $i, $item['5'])
                    ->setCellValue('G' . $i, $item['6'])
                    ->setCellValue('H' . $i, $valorizCosto)
                    ->setCellValue('I' . $i, $valorizPrecio);
                $i++;
                $totalCosto = $totalCosto + $valorizCosto;
                $totalPrecio = $totalPrecio + $valorizPrecio;
            }
            $i++;
            $sheet->setCellValue('A' . $i, 'VALORIZADO COSTO: $' . number_format($totalCosto, 3) . '.-');
            $i++;
            $sheet->setCellValue('A' . $i, 'VALORIZADO PRECIO: $' . number_format($totalPrecio, 3) . '.-');

            $phpExcelObject->getActiveSheet()->setTitle('Inventario Valorizado');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers


            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;

        }
    }
}