<?php

namespace ConfigBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Empresa;
use ConfigBundle\Entity\UnidadNegocio;
use ConfigBundle\Entity\Caja;
use ConfigBundle\Entity\Rol;
use ConfigBundle\Entity\RolUnidadNegocio;
use ConfigBundle\Entity\Usuario;
use ConfigBundle\Entity\Equipo;
use ConfigBundle\Entity\Parametrizacion;
use AppBundle\Entity\Deposito;

/**
 * @Route("initialDataLoad")
 */
class InitialDataLoadController extends Controller {
    private $csvPath;
    private $csvSystemPath;
    private $empresa;
    private $unidadNegocio;
    private $permisos;
    private $deposito;

    /**
     * @Route("/{key}", name="initial-dataload")
     * @Method("GET")
     */
    public function dataLoadAction($key) {
        $hashKey = md5($key);
        if ($hashKey === $this->getParameter('key_dataload')) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();
                $this->csvPath = $this->get('kernel')->getRootDir() . '/../web/uploads/import/';
                $this->csvSystemPath = $this->csvPath . 'system/';
                // PARA ACCESO INICIAL
                $this->loadPermisos($em);
                $this->addEmpresa($em);
                $this->addUserAdmin($em);
                $this->addEquipo($em);
                $this->addParametrizacion($em);
                // PARAMETROS AFIP
                $this->loadAfipAlicuota($em);
                $this->loadAfipComprobante($em);

                $em->getConnection()->commit();
                return new Response('Importación inicial exitosa!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                return new Response('Ha ocurrido un error en la carga inicial: ' . $ex->getMessage());
            }
        }
        else {
            return new Response('No posee permisos para esta acción');
        }
    }

    private function loadPermisos($em) {
        $filename = $this->csvSystemPath . 'permiso.csv';
        $data = UtilsController::convertCsvToArray($filename);
        $columns = array('id', 'padre_id', 'route', 'text', 'orden');
        UtilsController::loadCsvToTable($em, $data, 'permiso', $columns);
        $this->permisos = $em->getRepository('ConfigBundle:Permiso')->findAll();
    }

    private function addEmpresa($em) {
        $empresa = new Empresa();
        $empresa->setNombreCorto('AIDES');
        $empresa->setNombre('Casa Aides');
        $empresa->setCuit('30-52588208-6');
        $empresa->setDireccion('--- JUAN DOMINGO PERON 120 --- RESISTENCIA');
        $empresa->setTelefono('0362-4424468 4429888 (0362) 4431253');
        $empresa->setLabel1('Casa');
        $empresa->setLabel2('Aides');
        $empresa->setEstilo('blueline');
        $empresa->setEmail('administracion@casaaides.com.ar');
        $empresa->setIibb('30-52588208-6');
        $empresa->setInicioActividades('21/08/1967');
        $empresa->setCondicionIva('Responsable Inscripto');

        $unidadNegocio = new UnidadNegocio();
        $unidadNegocio->setNombre('Central');
        $empresa->addUnidad($unidadNegocio);
        $em->persist($empresa);
        $this->empresa = $empresa;
        $this->unidadNegocio = $unidadNegocio;

        $caja = new Caja();
        $caja->setUnidadNegocio($unidadNegocio);
        $caja->setNombre('CAJA1');
        $caja->setDescripcion('Caja Nro 1');
        $em->persist($caja);

        $deposito = new Deposito();
        $deposito->setNombre('Deposito Central');
        $deposito->setCentral(1);
        $deposito->setPordefecto(1);
        $deposito->setUnidadNegocio($unidadNegocio);
        $em->persist($deposito);
        $this->deposito = $deposito;

        $em->flush();
    }

    private function addUserAdmin($em) {
        $rol = new Rol();
        $rol->setNombre('ROLE_ADMIN');
        $rol->setDescripcion('Sysadmin');
        $rol->setAdmin(1);
        $rol->setFijo(1);
        // agregar permisos al rol
        foreach ($this->permisos as $permiso) {
            $rol->addPermiso($permiso);
        }
        $em->persist($rol);

        $user = new Usuario();
        $user->setUsername('ADMIN');
        $user->setNombre('SysAdmin');
        $user->setEmail('contacto@tresit.com.ar');
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password = $encoder->encodePassword('28675703', $user->getSalt());
        $user->setPassword($password);
        $em->persist($user);

        // agregar rol al usuario para la unidad de negocio
        $rolUnidadNegocio = new RolUnidadNegocio();
        $rolUnidadNegocio->setUsuario($user);
        $rolUnidadNegocio->setRol($rol);
        $rolUnidadNegocio->setUnidadNegocio($this->unidadNegocio);
        $em->persist($rolUnidadNegocio);

        $em->flush();
    }

    private function addEquipo($em) {
        $equipo = new Equipo();
        $equipo->setNombre('SERVER');
        $equipo->setPrefijo('11');
        $em->persist($equipo);
        $em->flush();
    }

    private function addParametrizacion($em) {
        $param = new Parametrizacion();
        $un = $em->getRepository('ConfigBundle:UnidadNegocio')->find(1);
        $param->setUnidadNegocio($un);
        $param->setCantidadItemsParaFactura(10);
        $param->setVentasDepositoBydefault($this->deposito);
        $em->persist($param);
        $em->flush();
    }

    private function loadAfipAlicuota($em) {
        $table = 'afip_comprobante';
        $file = $this->csvSystemPath . $table . '.csv';
        $data = UtilsController::convertCsvToArray($file);
        $columns = array('id', 'codigo', 'nombre', 'valor', 'activo');
        UtilsController::loadCsvToTable($em, $data, $table, $columns);
    }

    private function loadAfipComprobante($em) {
        $table = 'afip_comprobante';
        $file = $this->csvSystemPath . $table . '.csv';
        $data = UtilsController::convertCsvToArray($file);
        $columns = array('id', 'codigo', 'nombre', 'valor', 'activo', 'visible_compras');
        UtilsController::loadCsvToTable($em, $data, $table, $columns);
    }

}