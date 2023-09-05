<?php

namespace ConfigBundle\Controller;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Mapping\ClassMetadata;
use ConfigBundle\Controller\UtilsController;
use ConfigBundle\Entity\Pais;
use ConfigBundle\Entity\Localidad;
use ConfigBundle\Entity\FormaPago;
use ConfigBundle\Entity\Tarjeta;
use ConfigBundle\Entity\Banco;
use ConfigBundle\Entity\Parametro;
use ConfigBundle\Entity\Escalas;
use ConfigBundle\Entity\Transporte;
use ConfigBundle\Entity\Parametrizacion;
use AppBundle\Entity\PrecioLista;
use ComprasBundle\Entity\Proveedor;

/**
 * @Route("importDataLoad")
 */
class ImportDataLoadController extends Controller {
    private $csvPath;
    private $csvSystemPath;
    private $logger;

    /**
     * @Route("/{key}", name="import-dataload")
     * @Method("GET")
     */
    public function dataLoadAction($key) {
        $hashKey = md5($key);
        if ($hashKey === $this->getParameter('key_dataload')) {

            try {
                $this->csvPath = $this->get('kernel')->getRootDir() . '/../web/uploads/import/';
                $this->csvSystemPath = $this->csvPath . 'system/';
                $this->logger = new Logger('importacion');
                $this->logger->pushHandler(new StreamHandler($this->csvSystemPath . 'log/importacion.log', Logger::INFO));
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction();

                if ($this->container->has('profiler')) {
                    $this->container->get('profiler')->disable();
                }
                $log = $this->logger;
//                $log->addInfo('Region');
//                $this->loadRegion($em);
//                $log->addInfo('FormaPago');
//                $this->loadFormaPago($em);
//                $log->addInfo('Moneda');
//                $this->loadMoneda($em);
//                $log->addInfo('Tarjeta');
//                $this->loadTarjeta($em);
//                $log->addInfo('ActividadComercial');
//                $this->loadActividadComercial($em);
//                $log->addInfo('RubroCompras');
//                $this->loadRubroCompras($em);
//                 PARAMETROS
//                $this->addUnidadMedida($em);
//                $log->addInfo('SitImpositiva');
//                $this->loadSitImpositiva($em);
//                $log->addInfo('Rubro');
//                $this->loadRubro($em);
//                $log->addInfo('TipoDocumento');
//                $this->addTipoDocumento($em);
//                $this->addParametroPadre($em, 'calificacion-proveedor', 'Calificacion de Proveedor');
//                $log->addInfo('Escalas');
//                $this->loadEscalas($em);
//                $log->addInfo('Transporte');
//                $this->loadTransporte($em);
//                $log->addInfo('PrecioLista');
//                $this->addPrecioLista($em);
//                 MAESTROS
//                $log->addInfo('Proveedor');
//                $this->loadProveedor($em);
//                $log->addInfo('Cliente');
//                $this->loadCliente($em);
//                $log->addInfo('Producto');
//                $this->loadProducto($em);
                $log->addInfo('Precio');
                $this->loadPrecio($em);

                $em->getConnection()->commit();
                $log->addInfo('FIN IMPORTACION');
                return new Response('Importacion exitosa!');
            }
            catch (\Exception $ex) {
                $em->getConnection()->rollback();
                $log->addInfo('ERROR - ' . $ex->getMessage());
                return new Response('Ha ocurrido un error en la importacion: ' . $ex->getMessage());
            }
        }
        else {
            return new Response('No posee permisos para esta accion');
        }
    }

    private function loadRegion($em) {
        // Pais
        $pais = new Pais();
        $pais->setName('Argentina');
        $pais->setShortName('Arg');
        $pais->setByDefault(1);
        $em->persist($pais);
        // Provincia
        $filename = $this->csvSystemPath . 'provincia.csv';
        $data = UtilsController::convertCsvToArray($filename);
        $columns = array('id', 'pais_id', 'name', 'shortname', 'by_default', 'cod_sicore');
        UtilsController::loadCsvToTable($em, $data, 'provincia', $columns);

        // Localidad
        $filename = $this->csvPath . 'localidad.csv';
        $dataLocalidad = UtilsController::convertCsvToArray($filename);
        $localidad = new Localidad();
        $metadata = $em->getClassMetadata(get_class($localidad));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        foreach ($dataLocalidad as $loc) {
            if (!empty($loc[1])) {
                $localidad = new Localidad();
                $localidad->setId($loc[0]);
                $localidad->setName($loc[1]);
                $localidad->setCodpostal($loc[2]);
                $localidad->setShortName($loc[3]);
                $provincia = $em->getRepository('ConfigBundle:Provincia')->findOneByShortname($loc[3]);
                $localidad->setProvincia($provincia);
                $localidad->setByDefault($loc[0] == 1);
                $em->persist($localidad);
            }
        }
        $em->flush();
    }

    private function loadFormaPago($em) {
        $filename = $this->csvPath . 'forma_pago.csv';
        $data = UtilsController::convertCsvToArray($filename);
        $entity = new FormaPago();
        $metadata = $em->getClassMetadata(get_class($entity));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        foreach ($data as $row) {
            if (!empty($row[1])) {
                $exits = $em->getRepository('ConfigBundle:FormaPago')->findOneByNombre($row[1]);
                if (!$exits) {
                    $entity = new FormaPago();
                    $entity->setId($row[0]);
                    $entity->setNombre($row[1]);
                    $entity->setCuentaCorriente($row[2] == 'S' ? 1 : 0);
                    $entity->setTarjeta($row[3] == 'S' ? 1 : 0);
                    $entity->setCuotas($row[4]);
                    $entity->setVencimiento($row[5]);
                    $entity->setPlazo($row[6]);
                    $entity->setTipoRecargo($row[7]);
                    $entity->setPorcentajeRecargo($row[8]);
                    $entity->setDescuentoPagoAnticipado($row[9]);
                    $entity->setMora($row[15]);
                    $entity->setDiasMora($row[16]);
                    $entity->setCopiasComprobante($row[17]);
                    $entity->setContado($row[1] == 'Contado');
                    $em->persist($entity);
                    $em->flush();
                }
            }
        }
    }

    private function loadMoneda($em) {
        $file = $this->csvSystemPath . 'moneda.csv';
        $data = UtilsController::convertCsvToArray($file);
        $columns = array('id', 'nombre', 'simbolo', 'cotizacion', 'tope', 'by_default', 'codigo_afip');
        UtilsController::loadCsvToTable($em, $data, 'moneda', $columns);
    }

    private function loadTarjeta($em) {
        $file = $this->csvPath . 'tarjeta.csv';
        $data = UtilsController::convertCsvToArray($file);
        $entity = new Tarjeta();
        $metadata = $em->getClassMetadata(get_class($entity));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        foreach ($data as $row) {
            if (!empty($row[1])) {
                $exits = $em->getRepository('ConfigBundle:Tarjeta')->findOneByNombre($row[1]);
                if (!$exits) {
                    $entity = new Tarjeta();
                    $entity->setId($row[0]);
                    $entity->setNombre($row[1]);
                    $entity->setMaximoCuotas($row[2]);
                    $entity->setLimiteSinAutorizacion($row[3]);
                    $entity->setPresentarAlFacturar($row[4]);
                    $entity->setPresentar($row[5]);
                    $entity->setDiaPresentar($row[6]);
                    $entity->setTipoCobro($row[7]);
                    $entity->setCuenta($row[9]);
                    $entity->setDiaParaCobrar($row[10]);
                    $entity->setComisionTarjeta($row[11]);
                    // crear banco si corresponde
                    if (!empty($row[8])) {
                        $banco = $em->getRepository('ConfigBundle:Banco')->findOneByNombre($row[8]);
                        if (!$banco) {
                            $banco = new Banco();
                        }
                        $banco->setNombre($row[8]);
                        $em->persist($banco);
                        $entity->setBanco($banco);
                    }
                    $em->persist($entity);
                    $em->flush();
                }
            }
        }
    }

    private function loadActividadComercial($em) {
        $file = $this->csvSystemPath . 'actividad_comercial.csv';
        $data = UtilsController::convertCsvToArray($file);
        $columns = array('id', 'nombre', 'exento', 'inscripto', 'no_inscripto', 'minimo', 'by_default', 'codigo', 'codigo_impuesto', 'codigo_regimen', 'codigo_rg');
        UtilsController::loadCsvToTable($em, $data, 'actividad_comercial', $columns);
    }

    private function loadRubroCompras($em) {
        $file = $this->csvPath . 'rubro_compras.csv';
        $data = UtilsController::convertCsvToArray($file);
        $columns = array('id', 'nombre', 'tipo');
        UtilsController::loadCsvToTable($em, $data, 'rubro_compras', $columns);
    }

    private function addUnidadMedida($em) {
        $padre = $this->addParametroPadre($em, 'unidad-medida', 'Unidad de medida');

        $hijo = new Parametro();
        $hijo->setAgrupador($padre);
        $hijo->setNombre('Unid');
        $hijo->setDescripcion('Unidad');
        $em->persist($hijo);
        $em->flush();
    }

    private function loadSitImpositiva($em) {
        $file = $this->csvPath . 'tipo_contribuyente.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $padre = $this->addParametroPadre($em, 'sit-impositiva', 'SituaciÃ³n Impositiva');
        foreach ($data as $row) {
            $hijo = new Parametro();
            $hijo->setAgrupador($padre);
            $hijo->setNombre($row[0]);
            $hijo->setDescripcion($row[1]);
            $hijo->setCodigo($row[1]);
            $hijo->setBoleano(1);
            // riResponsableInscripto = 0; riMonotributo = 1; riExento = 3; riConsumidorFinal = 4
            $sitTicket = ( $row[0] == 'I' ? 0 : ( $row[0] == 'M' ? 1 : ( $row[0] == 'E' ? 3 : ($row[0] == 'C' ? 4 : null))));
            $hijo->setNumerico2($sitTicket);
            $em->persist($hijo);
            $em->flush();
        }
    }

    private function loadRubro($em) {
        $file = $this->csvPath . 'rubro.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $rubro = $this->addParametroPadre($em, 'rubro', 'Rubros');
        $padre = new Parametro();
        $padre->setPadre($rubro);
        $padre->setAgrupador($padre);
        $padre->setNombre('GENERAL');
        $em->persist($padre);
        $em->flush();
        foreach ($data as $row) {
            $hijo = new Parametro();
            $hijo->setAgrupador($padre);
            $hijo->setNombre($row[1]);
            $hijo->setNumerico($row[0]);
            $em->persist($hijo);
            $em->flush();
        }
    }

    private function addTipoDocumento($em) {
        $file = $this->csvSystemPath . 'tipo_documento.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $padre = $this->addParametroPadre($em, 'tipo-documento', 'Tipos de Documento');
        foreach ($data as $row) {
            $hijo = new Parametro();
            $hijo->setAgrupador($padre);
            $hijo->setNombre($row[0]);
            $hijo->setNumerico($row[1]);
            $hijo->setCodigo($row[2]);
            $em->persist($hijo);
            $em->flush();
        }
    }

    private function addParametroPadre($em, $nombre, $descripcion) {
        $padre = new Parametro();
        $padre->setNombre($nombre);
        $padre->setDescripcion($descripcion);
        $em->persist($padre);
        $em->flush();
        return $padre;
    }

    private function loadEscalas($em) {
        // Escalas Ganancias
        $file = $this->csvPath . 'escalas.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        foreach ($data as $row) {
            $this->addEscala($em, $row[0], $row);
        }
        // Escalas Retenciones Rentas
        $file = $this->csvPath . 'escalas_retencion.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        foreach ($data as $row) {
            if ($row[1]) {
                $this->addEscala($em, 'R', $row);
            }
        }
        // Escalas Percepciones Rentas
        $file = $this->csvPath . 'escalas_percepcion.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        foreach ($data as $row) {
            if ($row[1]) {
                $this->addEscala($em, 'P', $row);
            }
        }
    }

    private function addEscala($em, $tipo, $row) {
        $entity = new Escalas();
        $entity->setTipo($tipo);
        $entity->setNombre($row[1]);
        $entity->setRetencion($row[2]);
        $entity->setAdicional($row[3]);
        $entity->setMinimo($row[4]);
        $entity->setCodigoAtp($row[5]);
        if (in_array($tipo, ['P', 'R'])) {
            $entity->setIdImportado($row[0]);
        }
        $em->persist($entity);
        $em->flush();
    }

    private function loadTransporte($em) {
        $file = $this->csvPath . 'transporte.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        foreach ($data as $row) {
            if ($row[1]) {
                $entity = new Transporte();
                $entity->setNombre($row[1]);
                $entity->setDireccion($row[2]);
                $entity->setCuit($row[6]);
                $entity->setTelefono($row[7]);
                if ($row[3]) {
                    $localidad = $em->getRepository('ConfigBundle:Localidad')->find($row[3]);
                    $entity->setLocalidad($localidad);
                }
                $em->persist($entity);
            }
        }
        $em->flush();
    }

    private function loadProveedor($em) {
        $file = $this->csvPath . 'proveedor.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $entity = new Proveedor();
        $metadata = $em->getClassMetadata(get_class($entity));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        foreach ($data as $row) {
            if ($row[0]) {
                $entity = new Proveedor();
                $entity->setProvId($row[0]);
                $entity->setNombre($row[1]);
                $entity->setDireccion($row[2]);
                // localidad
                if ($row[3]) {
                    $localidad = $em->getRepository('ConfigBundle:Localidad')->find($row[3]);
                    $entity->setLocalidad($localidad);
                }
                $entity->setRepresentanteLocal($row[6]);
                $entity->setRepresentanteSede($row[7]);
                $entity->setTelefono($row[8]);
                $entity->setCuit($row[9]);
                $entity->setIibb($row[10]);
                // categoria Iva
                if ($row[11]) {
                    $catIva = $em->getRepository('ConfigBundle:Parametro')->filterByCodigo($row[11], 'sit-impositiva');
                    $entity->setCategoriaIva($catIva);
                }
                // rubro compras
                if ($row[12]) {
                    $rubroCompras = $em->getRepository('ConfigBundle:RubroCompras')->find($row[12]);
                    $entity->setRubroCompras($rubroCompras);
                }
                // categ rentas
                $catRentas = $em->getRepository('ConfigBundle:Escalas')->findOneBy(array('idImportado' => $row[13], 'tipo' => 'R'));
                $entity->setCategoriaRentas($catRentas);
                // activadad comercial
                if ($row[14]) {
                    $actComercial = $em->getRepository('ConfigBundle:ActividadComercial')->findOneByCodigo($row[14]);
                    $entity->setActividadComercial($actComercial);
                }
                // fechas de venc
                $entity->setVencCertNoRetenerRentas($this->transformFecha($row[15]));
                $entity->setVencCertExcepcionGanancias($this->transformFecha($row[16]));

                $em->persist($entity);
            }
        }
        $em->flush();
    }

    private function addPrecioLista($em) {
        $lista = new PrecioLista();
        $lista->setNombre('Lista1');
        $lista->setDescripcion('Lista de Precios 1');
        $lista->setVigenciaDesde(new \Datetime());
        $lista->setPrincipal(1);
        $em->persist($lista);
        $em->flush();
    }

    private function loadCliente($em) {
        $file = $this->csvPath . 'cliente.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $columns = array('id', 'categoria_iva_id', 'precio_lista_id', 'localidad_id', 'nombre', 'dni', 'cuit', 'direccion', 'telefono', 'nro_inscripcion', 'consumidor_final',
            'activo', 'categoria_rentas_id', 'forma_pago_id', 'transporte_id', 'provincia_rentas_id', 'localidad_trabajo_id', 'limite_credito', 'ult_verificacion_cuit',
            'trabajo', 'direccion_trabajo', 'telefono_trabajo', 'venc_cert_no_retener', 'created');
        $arrCliente = array();
        $lista = $em->getRepository('AppBundle:PrecioLista')->findOneByPrincipal(1);
        $hoy = new \Datetime();
        $i = $j = 0;
        foreach ($data as $row) {
            if (!empty($row[1])) {
                $i++;
                $j++;
                $cf = $row[1] == 'CONSUMIDOR FINAL' ? '1' : '0';
                $catIva = $em->getRepository('ConfigBundle:Parametro')->filterByCodigo($row[16], 'sit-impositiva');
                $catIvaId = $catIva ? $catIva->getId() : null;
                $localidad = $em->getRepository('ConfigBundle:Localidad')->find($row[3]);
                $localidadId = $localidad ? $localidad->getId() : null;
                $catRentas = $em->getRepository('ConfigBundle:Escalas')->findOneBy(array('idImportado' => $row[21], 'tipo' => 'P'));
                $catRentasId = $catRentas ? $catRentas->getId() : null;
                $formaPago = $em->getRepository('ConfigBundle:FormaPago')->find($row[18]);
                $formaPagoId = $formaPago ? $formaPago->getId() : null;
                $transporte = $em->getRepository('ConfigBundle:Transporte')->find($row[23]);
                $transporteId = $transporte ? $transporte->getId() : null;
                $provRentas = $em->getRepository('ConfigBundle:Provincia')->findOneByShortname($row[7]);
                $provRentasId = $provRentas ? $provRentas->getId() : null;
                $locTrabajo = $em->getRepository('ConfigBundle:Localidad')->find($row[10]);
                $locTrabajoId = $locTrabajo ? $locTrabajo->getId() : null;
                $ultVerifCuit = $this->transformFecha($row[22], 'S');
                $vencCert = $this->transformFecha($row[24], 'S');

                ////
                $arrCliente[] = array(
                    $row[0],
                    $catIvaId,
                    $lista->getId(),
                    $localidadId,
                    $row[1],
                    $row[14],
                    $row[15],
                    $row[2],
                    $row[6],
                    $row[17],
                    $cf,
                    "1",
                    $catRentasId,
                    $formaPagoId,
                    $transporteId,
                    $provRentasId,
                    $locTrabajoId,
                    $row[19],
                    $ultVerifCuit,
                    $row[8],
                    $row[9],
                    $row[12],
                    $vencCert,
                    $hoy->format('Y-m-d H:i:s')
                );
            }
            if ($i > 100) {
                UtilsController::loadCsvToTable($em, $arrCliente, 'cliente', $columns);
                $i = 0;
                $arrCliente = array();
                $this->logger->addInfo('Inserted - ' . $j);
            }
        }
        UtilsController::loadCsvToTable($em, $arrCliente, 'cliente', $columns);

        // consumidor final es cliente por defecto en ventas
        $clienteCf = $em->getRepository('VentasBundle:Cliente')->findOneByConsumidorFinal(1);
        $param = new Parametrizacion();
        $param->setVentasClienteBydefault($clienteCf);
        $em->persist($param);
        $em->flush();

        return true;
    }

    private function loadProducto($em) {
        $file = $this->csvPath . 'producto.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $columns = array('id', 'rubro_id', 'unidad_medida_id', 'proveedor_id', 'codigo', 'codigo_barra', 'nombre', 'descripcion', 'costo', 'iva',
            'activo', 'comodin', 'created');
        $array = array();
        $unidMed = $em->getRepository('ConfigBundle:Parametro')->findOneByNombre('Unid');
        $hoy = new \Datetime();
        $i = $j = 0;
        foreach ($data as $row) {
            if (!empty($row[7])) {
                $i++;
                $j++;
                $rubro = $em->getRepository('ConfigBundle:Parametro')->filterRubroByNumerico($row[1]);
                $rubroId = $rubro ? $rubro->getId() : null;
                $prov = $em->getRepository('ComprasBundle:Proveedor')->find($row[2]);
                $provId = $prov ? $prov->getId() : null;
                $comodin = trim($row[5]) == '999999' ? '1' : '0';
                $array[] = array(
                    $row[0],
                    $rubroId,
                    $unidMed->getId(),
                    $provId,
                    trim($row[5]),
                    $row[6],
                    $row[7],
                    $row[8],
                    $row[9],
                    $row[10],
                    '1',
                    $comodin,
                    $hoy->format('Y-m-d H:i:s')
                );
            }
            if ($i > 100) {
                UtilsController::loadCsvToTable($em, $array, 'producto', $columns);
                $i = 0;
                $array = array();
                $this->logger->addInfo('Inserted - ' . $j);
            }
        }
        UtilsController::loadCsvToTable($em, $array, 'producto', $columns);
        return true;
    }

    private function loadPrecio($em) {
        $file = $this->csvPath . 'precio.csv';
        $data = UtilsController::convertCsvToArray($file, false);
        $columns = array('id', 'precio_lista_id', 'producto_id', 'precio', 'updated');
        $array = array();
        $lista = $em->getRepository('AppBundle:PrecioLista')->findOneByPrincipal(1);
        $hoy = new \Datetime();
        $i = $j = 0;
        foreach ($data as $row) {
            if (!empty($row[0])) {
                $prod = $em->getRepository('AppBundle:Producto')->find($row[0]);
                if ($prod) {
                    $i++;
                    $j++;
                    $array[] = array(
                        $j,
                        $lista->getId(),
                        $prod->getId(),
                        $row[2],
                        $hoy->format('Y-m-d H:i:s')
                    );
                }
            }
            if ($i > 100) {
                UtilsController::loadCsvToTable($em, $array, 'precio', $columns);
                $i = 0;
                $array = array();
                $this->logger->addInfo('Inserted - ' . $j);
            }
        }
        UtilsController::loadCsvToTable($em, $array, 'precio', $columns);
        return true;
    }

    private function transformFecha($row, $format = 'D') {
        $fecha = null;
        $arr = explode('/', $row);
        if (!empty(trim($arr[0]))) {
            $dateStr = $arr[2] . '-' . trim($arr[1]) . '-' . trim($arr[0]);
            $fecha = new \DateTime($dateStr);
        }
        return $fecha ? ($format == 'D' ? $fecha : $fecha->format('Y-m-d')) : $fecha;
    }

}