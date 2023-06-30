<?php

namespace VentasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Controller\MonedaController;
use ConfigBundle\Controller\FormaPagoController;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockMovimiento;
use VentasBundle\Entity\Venta;
use VentasBundle\Entity\VentaDetalle;
use VentasBundle\Form\VentaType;

/**
 * @Route("/venta")
 */
class VentaController extends Controller
{

  /**
   * @Route("/", name="ventas_venta")
   * @Method("GET")
   * @Template()
   */
  public function indexAction(Request $request)
  {
    $unidneg = $this->get('session')->get('unidneg_id');
    $user = $this->getUser();
    UtilsController::haveAccess($user, $unidneg, 'ventas_venta');
    $em = $this->getDoctrine()->getManager();

    $desde = $request->get('desde');
    $hasta = $request->get('hasta');

    if ($user->getAccess($unidneg, 'ventas_venta_own') && !$user->isAdmin($unidneg)) {
      $id = $user->getId();
      $owns = true;
    } else {
      $id = $request->get('userId');
      $owns = false;
    }
    $entities = $em->getRepository('VentasBundle:Venta')->findByCriteria($unidneg, $desde, $hasta, $id);
    $users = $em->getRepository('VentasBundle:Venta')->getUsers();
    return $this->render('VentasBundle:Venta:index.html.twig', array(
      'entities' => $entities,
      'id' => $id,
      'owns' => $owns,
      'users' => $users,
      'desde' => $desde,
      'hasta' => $hasta
    ));
  }

  /**
   * @Route("/{id}/show", name="ventas_venta_show")
   * @Method("GET")
   * @Template()
   */
  public function showAction($id)
  {
    UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta');
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('VentasBundle:Venta')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Venta entity.');
    }
    return $this->render('VentasBundle:Venta:show.html.twig', array(
      'entity' => $entity
    ));
  }

  /**
   * @Route("/newVenta", name="ventas_venta_new")
   * @Method("GET")
   * @Template()
   */
  public function newVentaAction(Request $request)
  {
    $session = $this->get('session');
    $unidneg_id = $session->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta_new');

    $em = $this->getDoctrine()->getManager();
    $referer = $request->headers->get('referer');

    $entity = new Venta();

    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
    if ($param) {
      // ultimo nro de operacion de venta
      $entity->setNroOperacion($param->getUltimoNroOperacionVenta() + 1);

      $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
      $entity->setCliente($cliente);

      $deposito = $em->getRepository('AppBundle:Deposito')->find($param->getVentasDepositoBydefault());
      $entity->setDeposito($deposito);

      // set datos asociados al cliente con su definicion por defecto
      $entity->setFormaPago($cliente->getFormaPago());
      $entity->setDescuentoRecargo($cliente->getFormaPago()->getPorcentajeRecargo());
      $entity->setPrecioLista($cliente->getPrecioLista());
      $entity->setTransporte($cliente->getTransporte());
    } else {
      $this->addFlash('error', 'No se ha podido acceder a la parametrización. Intente nuevamente o contacte a servicio técnico.');
      return $this->redirect($referer);
    }

    $moneda = MonedaController::getMonedaByDefault();
    $entity->setMoneda($moneda);
    $entity->setCotizacion($moneda->getCotizacion());

    $form = $this->createCreateForm($entity, 'new');
    return $this->render('VentasBundle:Venta:new.html.twig',
            $this->arrayParameters($entity, $form->createView()) );
  }

  /**
   * Creates a form to create a Venta entity.
   * @param Venta $entity The entity
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCreateForm(Venta $entity, $type)
  {
    $form = $this->createForm(new VentaType(), $entity, array(
      'action' => $this->generateUrl('ventas_venta_create'),
      'method' => 'POST',
      'attr' => array('type' => $type),
    ));
    return $form;
  }

  /**
   * @Route("/", name="ventas_venta_create")
   * @Method("POST")
   * @Template("VentasBundle:Venta:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $session = $this->get('session');
    $unidneg_id = $session->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta');

    $entity = new Venta();
    $form = $this->createCreateForm($entity, 'create');
    $form->handleRequest($request);
    $em = $this->getDoctrine()->getManager();
    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
    if ($form->isValid()) {
      $em->getConnection()->beginTransaction();
      try {
        // set fecha de operacion
        $entity->setFechaVenta(new \DateTime());
        // cargar referencias faltantes
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($request->get('ventasbundle_cliente'));
        $entity->setCliente($cliente);
        $entity->setNombreCliente( $request->get('ventasbundle_nombreCliente') );
        $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
        $entity->setFormaPago($formapago);
        $entity->setCotizacion($entity->getMoneda()->getCotizacion());

        // set unidad negocio desde session
        $unidneg = $em->getRepository('ConfigBundle:UnidadNegocio')->find($unidneg_id);
        $entity->setUnidadNegocio($unidneg);
        if (!$entity->getDescuentoRecargo()) {
          $entity->setDescuentoRecargo($entity->getFormaPago()->getPorcentajeRecargo());
        }

        // set nro operacion
        $nroOperacion = $param->getUltimoNroOperacionVenta() + 1;
        $entity->setNroOperacion($nroOperacion);
        // update ultimoNroOperacion en parametrizacion
        $param->setUltimoNroOperacionVenta($nroOperacion);

        // cargar productos y verificar que no haya items sin producto.
        $productos = $request->get('ventasbundle_producto');
        foreach ($entity->getDetalles() as $key => $detalle){
          $producto = $em->getRepository('AppBundle:Producto')->find($productos[$key]);
          if($producto){
            $detalle->setProducto($producto);
          }else{
            $entity->removeDetalle($detalle);
          }
        }

        $em->persist($entity);
        $em->persist($param);
        $em->flush();

        if ($entity->getDescuentaStock()) {
          // Descuento de stock
          $this->registrarMovimientoStock($entity->getId(), $entity->getDeposito(), $entity->getDetalles(), '-', $em);
        }
        $em->getConnection()->commit();
        //$this->addFlash('success', 'Se ha registrado la venta:  <span class="notif_operacion"> #'.$entity->getNroOperacion().'</span>');
        // requiere login al volver a ingresar a venta
        $session->set('checkrequired', '1');
        return $this->redirect($this->generateUrl('ventas_venta_new'));
      } catch (\Exception $ex) {
        $this->addFlash('error', $ex->getMessage());
        $em->getConnection()->rollback();
      }
    }
    // Set cliente segun parametrizacion
    if ($param) {
      $cliente = $em->getRepository('VentasBundle:Cliente')->find($param->getVentasClienteBydefault());
      $entity->setCliente($cliente);
    }
    // no requiere login si vuelve con error
    $session->set('checkrequired', '0');
    return $this->render('VentasBundle:Venta:new.html.twig',
            $this->arrayParameters($entity, $form->createView())
      );
  }


  /**
   * @Route("/{id}/repeat", name="ventas_venta_repeat")
   * @Method("GET")
   * @Template()
   */
  public function repeatVentaAction($id)
  {
    $unidneg_id = $this->get('session')->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta_new');
    $em = $this->getDoctrine()->getManager();
    $venta = $em->getRepository('VentasBundle:Venta')->find($id);
    if (!$venta) {
      throw $this->createNotFoundException('No se encuentra la Venta.');
    }
    $entity = clone $venta;
    $entity->setFechaVenta(new \DateTime());
    $entity->setCotizacion($entity->getMoneda()->getCotizacion());
    $entity->setDescuentaStock(true);
    $entity->setEstado('PENDIENTE');
    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
    if ($param) {
      // ultimo nro de operacion de venta
      $entity->setNroOperacion($param->getUltimoNroOperacionVenta() + 1);
    }
    // actualizar los precios
    foreach ($entity->getDetalles() as $det) {
      $det->setPrecio($det->getProducto()->getPrecioByLista($entity->getPrecioLista()->getId()));
    }
    $form = $this->createCreateForm($entity, 'new');

    return $this->render('VentasBundle:Venta:new.html.twig',
            $this->arrayParameters($entity, $form->createView())
    );
  }
  /**
   * @Route("/fromPresupuesto/{id}", name="ventas_venta_presupuesto")
   * @Method("GET")
   * @Template()
   */
  public function fromPresupuestoAction($id)
  {
    $unidneg_id = $this->get('session')->get('unidneg_id');
    UtilsController::haveAccess($this->getUser(), $unidneg_id, 'ventas_venta_new');
    $em = $this->getDoctrine()->getManager();
    $presupuesto = $em->getRepository('VentasBundle:Presupuesto')->find($id);
    if (!$presupuesto) {
      throw $this->createNotFoundException('No se encuentra la Venta.');
    }
    $entity = new Venta();
    $entity->setFechaVenta(new \DateTime());
    // setear datos del presupuesto
    $entity->setCliente($presupuesto->getCliente());
    $entity->setNombreCliente($presupuesto->getNombreCliente());
    $entity->setDeposito($presupuesto->getDeposito());
    $entity->setPrecioLista($presupuesto->getPrecioLista());
    $entity->setFormaPago($presupuesto->getFormaPago());
    $entity->setDescuentoRecargo($presupuesto->getDescuentoRecargo());
    $entity->setTransporte($entity->getCliente()->getTransporte());

    $param = $em->getRepository('ConfigBundle:Parametrizacion')->findOneBy(array('unidadNegocio' => $unidneg_id));
    if ($param) {
      // ultimo nro de operacion de venta
      $entity->setNroOperacion($param->getUltimoNroOperacionVenta() + 1);
    }
    $moneda = $em->getRepository('ConfigBundle:Moneda')->findOneByByDefault(1);
    $entity->setMoneda($moneda);
    $entity->setCotizacion($moneda->getCotizacion());
    $entity->setDescuentaStock(!$presupuesto->getDescuentaStock());
    // cargar detalle
    foreach ($presupuesto->getDetalles() as $det) {
      $new = new VentaDetalle();
      $new->setProducto($det->getProducto());
      $new->setCantidad($det->getCantidad());
      $new->setTextoComodin($det->getTextoComodin());
      $new->setPrecio($det->getPrecio());
      $new->setAlicuota($det->getAlicuota());
      $entity->addDetalle($new);
    }

    $form = $this->createCreateForm($entity, 'new');

    return $this->render('VentasBundle:Venta:new.html.twig',
            $this->arrayParameters($entity, $form->createView())
          );
  }
  /**
   * @Route("/{id}/edit", name="ventas_venta_edit")
   * @Method("GET")
   * @Template()
   */
  public function editAction($id)
  {
    UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta_new');
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('VentasBundle:Venta')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('No se encuentra la Venta.');
    }
    $editForm = $this->createEditForm($entity, 'new');

    return $this->render('VentasBundle:Venta:new.html.twig',
            $this->arrayParameters($entity, $editForm->createView()));
  }

  /**
   * Creates a form to edit a Venta entity.
   * @param Venta $entity The entity
   * @return \Symfony\Component\Form\Form The form
   */
  private function createEditForm(Venta $entity, $type)
  {
    $form = $this->createForm(new VentaType(), $entity, array(
      'action' => $this->generateUrl('ventas_venta_update', array('id' => $entity->getId())),
      'method' => 'PUT',
      'attr' => array('type' => $type),
    ));
    return $form;
  }

  /**
   * @Route("/{id}", name="ventas_venta_update")
   * @Method("PUT")
   * @Template("VentasBundle:Venta:new.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    UtilsController::haveAccess($this->getUser(), $this->get('session')->get('unidneg_id'), 'ventas_venta_new');
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('VentasBundle:Venta')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('No se encuentra la Venta.');
    }
    $depositoAnterior = $entity->getDeposito();
    $detalleAnterior = clone ($entity->getDetalles());
    $detalleAnteriorJson = $this->getDetalleJson($detalleAnterior);

    $editForm = $this->createEditForm($entity, 'create');
    $editForm->handleRequest($request);
    if ($editForm->isValid()) {
      $em->getConnection()->beginTransaction();
      try {
        // cargar referencias faltantes
        $cliente = $em->getRepository('VentasBundle:Cliente')->find($request->get('ventasbundle_cliente'));
        $entity->setCliente($cliente);
        $entity->setNombreCliente( $request->get('ventasbundle_nombreCliente') );
        $formapago = $em->getRepository('ConfigBundle:FormaPago')->find($request->get('select_formapago'));
        $entity->setFormaPago($formapago);
        $entity->setCotizacion($entity->getMoneda()->getCotizacion());

        // actualizar los productos
        $productos = $request->get('ventasbundle_producto');
        $i = 0;
        foreach ($entity->getDetalles() as $detalle){
          $producto = $em->getRepository('AppBundle:Producto')->find($productos[$i]);
          $detalle->setProducto( $producto );
          $i++;
        }

        if ($entity->getDescuentaStock()) {
          // realizar los ajustes en el stock - deshacer el movimiento y rehacer
          $depositoNuevo = $entity->getDeposito();
          $detalleNuevo = $entity->getDetalles();
          $difDetalle =  strcmp($detalleAnteriorJson, $this->getDetalleJson($detalleNuevo));

          if ($difDetalle || ($depositoAnterior->getId() != $depositoNuevo->getId())) {

            // reingresar anteriores articulos al stock
            $res = $this->registrarMovimientoStock($entity->getId(), $depositoAnterior, $detalleAnterior, '+', $em);
            if ($res) {
              // descontar los nuevos
              $res = $this->registrarMovimientoStock($entity->getId(), $depositoNuevo, $detalleNuevo, '-', $em);
            }
            if (!$res) {
              throw $this->createNotFoundException('No se realizó el ajuste en el stock.');
            }
          }
        }
        $em->flush();
        $em->getConnection()->commit();
        $this->addFlash('success', 'Los datos fueron modificados con éxito!');
        return $this->redirect($this->generateUrl('ventas_venta'));
      } catch (\Exception $ex) {
        $this->addFlash('error', $ex->getMessage());
        $em->getConnection()->rollback();
      }
    }
    return $this->render('VentasBundle:Venta:new.html.twig',
            $this->arrayParameters($entity, $editForm->createView()));
  }

  private function getDetalleJson($detalle)
  {
    // devuelve el string del detalle para comparacion
    $array = [];
    foreach ($detalle as $item) {
      $array[] = array('prod' => $item->getProducto()->getId(), 'cant' => round($item->getCantidad(), 2));
    }
    return json_encode($array);
  }

  // registrar ingresos y egresos de stock
  private function registrarMovimientoStock($ventaId, $deposito, $detalles, $signo, $em)
  {
    $hoy = new \DateTime();
    try {
      foreach ($detalles as $detalle) {
        $cantidad = ($signo == '-') ? $detalle->getCantidad() * -1 : $detalle->getCantidad();
        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
        if ($stock) {
          $stock->setCantidad($stock->getCantidad() + $cantidad);
        } else {
          $stock = new Stock();
          $stock->setProducto($detalle->getProducto());
          $stock->setDeposito($deposito);
          $stock->setCantidad(0 + $cantidad);
        }
        $em->persist($stock);

        // Cargar movimiento
        $movim = new StockMovimiento();
        $movim->setFecha($hoy);
        $movim->setTipo('ventas_venta');
        $movim->setSigno($signo);
        $movim->setMovimiento($ventaId);
        $movim->setProducto($detalle->getProducto());
        $movim->setCantidad($detalle->getCantidad());
        $movim->setDeposito($deposito);
        $em->persist($movim);
        $em->flush();
      }
    } catch (\Exception $ex) {
      return $ex->getMessage();
    }
    return true;
  }


  /**
   * LOGIN PARA VENTAS
   */

  /**
   * @Route("/login", name="ventas_login")
   */
  public function loginAction()
  {
    return $this->render('VentasBundle:Venta:login.html.twig');
  }

  /**
   * @Route("/checkrequired", name="ventas_checkrequired")
   * @Method("GET")
   */
  public function checkRequiredAction()
  {
    return new Response($this->get('session')->get('checkrequired'));
  }

  /**
   * @Route("checkLogin", name="ventas_login_check")
   */
  public function checkLoginAction(Request $request)
  {
    //$username = strtoupper($request->get('username')) ;
    $password = trim($request->get('password'));
    $em = $this->get('doctrine')->getEntityManager();
    $reload = false;

    $user = $this->getUserByPassword($password);

    if ($user) {
      // verificar si es el mismo usuario
      if ($this->getUser()->getUsername() !== $user->getUsername()) {
        // loguear al nuevo usuario
        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
        $this->get('security.context')->setToken($token);
        $this->get('session')->set('_security_secured_area', serialize($token));
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        // RECARGAR LA PÁGINA PARA CAMBIAR USUARIO
        $reload = true;
        // No requiere login
        $this->get('session')->set('checkrequired', '0');
      }
      $result = array('msg' => 'OK', 'url' => $this->generateUrl('ventas_venta_new'), 'reload' => $reload);
    } else {
      $result = array('msg' => 'Ingrese una contraseña válida!', 'field' => 'password');
    }
    return new Response(json_encode($result));
  }

  private function getUserByPassword($password)
  {
    $em = $this->get('doctrine')->getEntityManager();
    $users = $em->getRepository('ConfigBundle:Usuario')->findByActivo(1);
    foreach ($users as $user) {
      // Get the encoder for the users password
      $encoder = $this->get('security.encoder_factory')->getEncoder($user);
      if ($encoder->isPasswordValid($user->getPassword(), $password, null)) {
        return $user;
      }
    }
    return null;
  }

  /**
   * @Route("/anularVenta", name="ventas_venta_anular")
   * @Method("POST")
   */
  public function anularVentaAction(Request $request)
  {
    $id = $request->get('id');
    $em = $this->get('doctrine')->getEntityManager();
    $entity = $em->getRepository('VentasBundle:Venta')->find($id);
    try {
      $em->getConnection()->beginTransaction();
      $entity->setEstado('ANULADO');
      $em->persist($entity);
      $em->flush();

      // Revertir descuento de stock
      $deposito = $entity->getDeposito();
      foreach ($entity->getDetalles() as $detalle) {
        $stock = $em->getRepository('AppBundle:Stock')->findProductoDeposito($detalle->getProducto()->getId(), $deposito->getId());
        if ($stock) {
          $stock->setCantidad($stock->getCantidad() + $detalle->getCantidad());
        } else {
          $em->getConnection()->rollback();
          return new Response('ERROR');
        }
        $em->persist($stock);

        // Cargar movimiento
        $movim = new StockMovimiento();
        $movim->setFecha($entity->getUpdated());
        $movim->setTipo('ventas_venta');
        $movim->setSigno('+');
        $movim->setMovimiento($entity->getId());
        $movim->setProducto($detalle->getProducto());
        $movim->setCantidad($detalle->getCantidad());
        $movim->setDeposito($deposito);
        $em->persist($movim);
        $em->flush();
      }
      $em->getConnection()->commit();
      $this->addFlash('success', 'Se ha anulado la venta #' . $entity->getNroOperacion());
      return new Response('OK');
    } catch (\Exception $ex) {
      $em->getConnection()->rollback();
      return new Response($ex->getMessage());
    }
  }

  private function arrayParameters($entity, $view)
  {
    $cuit = $entity->getCliente()->getCuit();
    $valido = UtilsController::validarCuit($cuit);
    return array(
      'entity' => $entity,
      'form' => $view,
      'cuitvalido' => $valido,
      'descuentoContado' => FormaPagoController::getDescuentoContado()
    );
  }

}
