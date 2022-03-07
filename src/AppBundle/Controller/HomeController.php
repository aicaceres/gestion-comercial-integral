<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;

//use ComprasBundle\Entity\Factura;

class HomeController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $session = $this->get('session');
            $em = $this->getDoctrine()->getManager();
            if (!$session->has('equipo')) {
                //$accesstype = $this->container->getParameter('accesstype');
                // if( $accesstype == '1' ){
                //accede al server
                $nombreEquipo = 'SERVER';
                // }else{
                //equipo cliente con base local
                //    $nombreEquipo = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                // }                
                $equipo = $em->getRepository('ConfigBundle:Equipo')->findOneByNombre($nombreEquipo);
                if ($equipo) {
                    $session->set('equipo', $equipo->getId());
                }
            }
            // CAJA=1
            $caja = $em->getRepository('ConfigBundle:Caja')->find(1);
            $session->set('caja_abierta', $caja->getAbierta());

            return $this->render('AppBundle:Home:inicio.html.twig');
        }
        else {
            return $this->redirectToRoute('usuario_login');
        }
    }

    /**
     * @Route("/sistema", name="sistema")
     * @Method("GET")
     * @Template()
     */
    public function sistemaAction() {
        return $this->render('AppBundle:Home:sistema.html.twig');
    }

    /**
     * @Route("/stock", name="stock")
     * @Method("GET")
     * @Template()
     */
    public function stockAction() {
        return $this->render('AppBundle:Home:stock.html.twig');
    }

    /**
     * @Route("/compras", name="compras")
     * @Method("GET")
     * @Template()
     */
    public function comprasAction() {
        return $this->render('AppBundle:Home:compras.html.twig');
    }

    /**
     * @Route("/ventas", name="ventas")
     * @Method("GET")
     * @Template()
     */
    public function ventasAction() {
        return $this->render('AppBundle:Home:ventas.html.twig');
    }

    /**
     * @Route("/setEstadoArticulo", name="setEstadoArticulo")
     * @Method("GET")
     */
    public function setEstadoArticulo() {
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request');
        $tipo = $request->get('tipo');
        $id = $request->get('id');
        $estado = $request->get('estado');
        $articulo = $em->getRepository('AppBundle:Articulo')->find($id);

        if ($tipo == 'venta') {
            if ($estado == '0') {
                $txt = 'En Venta';
                $articulo->setEnventa(1);
            }
            else {
                $txt = 'Vendido';
                $articulo->setEnventa(0);
            }
        }
        else {
            if ($estado == '0') {
                $txt = 'Pedido';
                $articulo->setEnventa(1);
            }
            else {
                $txt = 'Entregado';
                $articulo->setEnventa(0);
            }
        }

        $articulo->setEstado($txt);
        $em->persist($articulo);
        $em->flush();
        return new Response('OK');
    }

}