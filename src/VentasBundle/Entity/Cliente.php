<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\Cliente
 * @ORM\Table(name="cliente",indexes={@ORM\Index(name="activo_idx",columns={"activo"}),@ORM\Index(name="nombre_idx",columns={"nombre"})}))
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\ClienteRepository")
 * @Gedmo\Loggable()
 */
class Cliente {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Gedmo\Versioned()
     */
    protected $nombre;

    /**
     * @var string $dni
     * @ORM\Column(name="dni", type="string", length=8, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $dni;

    /**
     * @var string $cuit
     * @ORM\Column(name="cuit", type="string", length=13, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $cuit;

    /**
     * @var string $nroInscripcion
     * @ORM\Column(name="nro_inscripcion", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroInscripcion;

    /**
     * @var string $direccion
     * @ORM\Column(name="direccion", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $direccion;

    /**
     * @var string $telefono
     * @ORM\Column(name="telefono", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $telefono;

    /**
     * @var string $email
     * @ORM\Column(name="email", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
     * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $localidad;

    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
     * @var integer $saldoInicial
     * @ORM\Column(name="saldo_inicial", type="decimal", scale=2, nullable=true )
     * @Gedmo\Versioned()
     */
    protected $saldoInicial;

    /**
     * @var integer $limiteCredito
     * @ORM\Column(name="limite_credito", type="decimal", scale=2, nullable=true )
     */
    protected $limiteCredito;

    /**
     * @var date $ultVerificacionCuit
     * @ORM\Column(name="ult_verificacion_cuit", type="date", nullable=true)
     */
    private $ultVerificacionCuit;

    /**
     * @var date $vencCertNoRetener
     * @ORM\Column(name="venc_cert_no_retener", type="date", nullable=true)
     * @Gedmo\Versioned()
     */
    private $vencCertNoRetener;

    /**
     * @ORM\Column(name="consumidor_final", type="boolean",nullable=true)
     */
    protected $consumidorFinal = false;

    /**
     * @ORM\Column(name="activo", type="boolean",nullable=true)
     * @Gedmo\Versioned()
     */
    protected $activo = true;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\TipoCliente")
     * @ORM\JoinColumn(name="tipo_cliente_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     * */
    protected $tipoCliente;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="categoria_iva_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     * */
    protected $categoria_iva;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Escalas")
     * @ORM\JoinColumn(name="categoria_rentas_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     * */
    protected $categoriaRentas;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\FormaPago")
     * @ORM\JoinColumn(name="forma_pago_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     * */
    protected $formaPago;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PrecioLista")
     * @ORM\JoinColumn(name="precio_lista_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $precioLista;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Transporte")
     * @ORM\JoinColumn(name="transporte_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $transporte;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Provincia")
     * @ORM\JoinColumn(name="provincia_rentas_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $provinciaRentas;

    /**
     * Trabajo
     */

    /**
     * @var string $trabajo
     * @ORM\Column(name="trabajo", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $trabajo;

    /**
     * @var string $direccionTrabajo
     * @ORM\Column(name="direccion_trabajo", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $direccionTrabajo;

    /**
     * @var string $telefonoTrabajo
     * @ORM\Column(name="telefono_trabajo", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $telefonoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
     * @ORM\JoinColumn(name="localidad_trabajo_id", referencedColumnName="id")
     * @ORM\OrderBy({"name" = "ASC"})
     * @Gedmo\Versioned()
     */
    protected $localidadTrabajo;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\Cobro", mappedBy="cliente")
     */
    protected $cobros;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\PagoCliente", mappedBy="cliente")
     */
    protected $pagos;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\NotaDebCred", mappedBy="cliente")
     */
    protected $notasDebCredVenta;

    /**
     * @ORM\OneToMany(targetEntity="VentasBundle\Entity\FacturaElectronica", mappedBy="cliente")
     */
    protected $facturasElectronicas;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

//    public function setCliId($id) {
//        $this->id = $id;
//        return $this;
//    }

    public function getPercepcionRentas() {
        $porcRentas = 0;
        $hoy = new \DateTime();
        $vencNoRetencion = $this->getVencCertNoRetener() ? $this->getVencCertNoRetener()->format('Ymd') : null;
        if ($vencNoRetencion < $hoy->format('Ymd') || is_null($vencNoRetencion)) {
            $porcRentas = floatval($this->getCategoriaRentas()->getRetencion());
        }
        return $porcRentas;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->activo = true;
        $this->consumidorFinal = false;
        $this->saldoInicial = 0;
    }

    public function __toString() {
        return $this->nombre;
    }

    public function getDomicilioCompleto() {
        return $this->direccion . ', ' . $this->getLocalidad() . ', ' . $this->getLocalidad()->getProvincia();
    }

    public function getSaldo() {
        $saldo = 0;
        $cobros = $this->cobros;
        $pagos = $this->pagos;
        $notaDebCred = $this->notasDebCredVenta;
        $saldo = $this->saldoInicial;
        foreach ($cobros as $cobro) {
            if ($cobro->getEstado() === 'FINALIZADO' && $cobro->getFormaPago()->getCuentaCorriente())
                $saldo += $cobro->getFacturaElectronica()->getTotal();
        }
        foreach ($notaDebCred as $nota) {
            if ($nota->getEstado() === 'ACREDITADO' && $nota->getFormaPago()->getCuentaCorriente())
                $saldo -= $nota->getTotal();
        }
        foreach ($pagos as $pag) {
            $saldo -= $pag->getTotal();
        }
        return $saldo;
    }

    public function getFechaUltimaCompra() {
        $fecha = null;
        return $fecha;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Cliente
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Set dni
     *
     * @param string $dni
     * @return Cliente
     */
    public function setDni($dni) {
        $this->dni = $dni;

        return $this;
    }

    /**
     * Get dni
     *
     * @return string
     */
    public function getDni() {
        return $this->dni;
    }

    /**
     * Set cuit
     *
     * @param string $cuit
     * @return Cliente
     */
    public function setCuit($cuit) {
        $this->cuit = $cuit;

        return $this;
    }

    /**
     * Get cuit
     *
     * @return string
     */
    public function getCuit() {
        return $this->cuit;
    }

    /**
     * Set nroInscripcion
     *
     * @param string $nroInscripcion
     * @return Cliente
     */
    public function setNroInscripcion($nroInscripcion) {
        $this->nroInscripcion = $nroInscripcion;

        return $this;
    }

    /**
     * Get nroInscripcion
     *
     * @return string
     */
    public function getNroInscripcion() {
        return $this->nroInscripcion;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Cliente
     */
    public function setDireccion($direccion) {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion() {
        return $this->direccion;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Cliente
     */
    public function setTelefono($telefono) {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono() {
        return $this->telefono;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Cliente
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Cliente
     */
    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones() {
        return $this->observaciones;
    }

    /**
     * Set saldoInicial
     *
     * @param string $saldoInicial
     * @return Cliente
     */
    public function setSaldoInicial($saldoInicial) {
        $this->saldoInicial = $saldoInicial;

        return $this;
    }

    /**
     * Get saldoInicial
     *
     * @return string
     */
    public function getSaldoInicial() {
        return $this->saldoInicial;
    }

    /**
     * Set limiteCredito
     *
     * @param string $limiteCredito
     * @return Cliente
     */
    public function setLimiteCredito($limiteCredito) {
        $this->limiteCredito = $limiteCredito;

        return $this;
    }

    /**
     * Get limiteCredito
     *
     * @return string
     */
    public function getLimiteCredito() {
        return $this->limiteCredito;
    }

    /**
     * Set ultVerificacionCuit
     *
     * @param \DateTime $ultVerificacionCuit
     * @return Cliente
     */
    public function setUltVerificacionCuit($ultVerificacionCuit) {
        $this->ultVerificacionCuit = $ultVerificacionCuit;

        return $this;
    }

    /**
     * Get ultVerificacionCuit
     *
     * @return \DateTime
     */
    public function getUltVerificacionCuit() {
        return $this->ultVerificacionCuit;
    }

    /**
     * Set consumidorFinal
     *
     * @param boolean $consumidorFinal
     * @return Cliente
     */
    public function setConsumidorFinal($consumidorFinal) {
        $this->consumidorFinal = $consumidorFinal;

        return $this;
    }

    /**
     * Get consumidorFinal
     *
     * @return boolean
     */
    public function getConsumidorFinal() {
        return $this->consumidorFinal;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return Cliente
     */
    public function setActivo($activo) {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo() {
        return $this->activo;
    }

    /**
     * Set trabajo
     *
     * @param string $trabajo
     * @return Cliente
     */
    public function setTrabajo($trabajo) {
        $this->trabajo = $trabajo;

        return $this;
    }

    /**
     * Get trabajo
     *
     * @return string
     */
    public function getTrabajo() {
        return $this->trabajo;
    }

    /**
     * Set direccionTrabajo
     *
     * @param string $direccionTrabajo
     * @return Cliente
     */
    public function setDireccionTrabajo($direccionTrabajo) {
        $this->direccionTrabajo = $direccionTrabajo;

        return $this;
    }

    /**
     * Get direccionTrabajo
     *
     * @return string
     */
    public function getDireccionTrabajo() {
        return $this->direccionTrabajo;
    }

    /**
     * Set telefonoTrabajo
     *
     * @param string $telefonoTrabajo
     * @return Cliente
     */
    public function setTelefonoTrabajo($telefonoTrabajo) {
        $this->telefonoTrabajo = $telefonoTrabajo;

        return $this;
    }

    /**
     * Get telefonoTrabajo
     *
     * @return string
     */
    public function getTelefonoTrabajo() {
        return $this->telefonoTrabajo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Cliente
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Cliente
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return Cliente
     */
    public function setLocalidad(\ConfigBundle\Entity\Localidad $localidad = null) {
        $this->localidad = $localidad;

        return $this;
    }

    /**
     * Get localidad
     *
     * @return \ConfigBundle\Entity\Localidad
     */
    public function getLocalidad() {
        return $this->localidad;
    }

    /**
     * Set categoria_iva
     *
     * @param \ConfigBundle\Entity\Parametro $categoriaIva
     * @return Cliente
     */
    public function setCategoriaIva(\ConfigBundle\Entity\Parametro $categoriaIva = null) {
        $this->categoria_iva = $categoriaIva;

        return $this;
    }

    /**
     * Get categoria_iva
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCategoriaIva() {
        return $this->categoria_iva;
    }

    /**
     * Set categoriaRentas
     *
     * @param \ConfigBundle\Entity\Escalas $categoriaRentas
     * @return Cliente
     */
    public function setCategoriaRentas(\ConfigBundle\Entity\Escalas $categoriaRentas = null) {
        $this->categoriaRentas = $categoriaRentas;

        return $this;
    }

    /**
     * Get categoriaRentas
     *
     * @return \ConfigBundle\Entity\Escalas
     */
    public function getCategoriaRentas() {
        return $this->categoriaRentas;
    }

    /**
     * Set formaPago
     *
     * @param \ConfigBundle\Entity\FormaPago $formaPago
     * @return Cliente
     */
    public function setFormaPago(\ConfigBundle\Entity\FormaPago $formaPago = null) {
        $this->formaPago = $formaPago;

        return $this;
    }

    /**
     * Get formaPago
     *
     * @return \ConfigBundle\Entity\FormaPago
     */
    public function getFormaPago() {
        return $this->formaPago;
    }

    /**
     * Set precioLista
     *
     * @param \AppBundle\Entity\PrecioLista $precioLista
     * @return Cliente
     */
    public function setPrecioLista(\AppBundle\Entity\PrecioLista $precioLista = null) {
        $this->precioLista = $precioLista;

        return $this;
    }

    /**
     * Get precioLista
     *
     * @return \AppBundle\Entity\PrecioLista
     */
    public function getPrecioLista() {
        return $this->precioLista;
    }

    /**
     * Set transporte
     *
     * @param \ConfigBundle\Entity\Transporte $transporte
     * @return Cliente
     */
    public function setTransporte(\ConfigBundle\Entity\Transporte $transporte = null) {
        $this->transporte = $transporte;

        return $this;
    }

    /**
     * Get transporte
     *
     * @return \ConfigBundle\Entity\Transporte
     */
    public function getTransporte() {
        return $this->transporte;
    }

    /**
     * Set provinciaRentas
     *
     * @param \ConfigBundle\Entity\Provincia $provinciaRentas
     * @return Cliente
     */
    public function setProvinciaRentas(\ConfigBundle\Entity\Provincia $provinciaRentas = null) {
        $this->provinciaRentas = $provinciaRentas;

        return $this;
    }

    /**
     * Get provinciaRentas
     *
     * @return \ConfigBundle\Entity\Provincia
     */
    public function getProvinciaRentas() {
        return $this->provinciaRentas;
    }

    /**
     * Set localidadTrabajo
     *
     * @param \ConfigBundle\Entity\Localidad $localidadTrabajo
     * @return Cliente
     */
    public function setLocalidadTrabajo(\ConfigBundle\Entity\Localidad $localidadTrabajo = null) {
        $this->localidadTrabajo = $localidadTrabajo;

        return $this;
    }

    /**
     * Get localidadTrabajo
     *
     * @return \ConfigBundle\Entity\Localidad
     */
    public function getLocalidadTrabajo() {
        return $this->localidadTrabajo;
    }

    /**
     * Add pagos
     *
     * @param \VentasBundle\Entity\PagoCliente $pagos
     * @return Cliente
     */
    public function addPago(\VentasBundle\Entity\PagoCliente $pagos) {
        $this->pagos[] = $pagos;

        return $this;
    }

    /**
     * Remove pagos
     *
     * @param \VentasBundle\Entity\PagoCliente $pagos
     */
    public function removePago(\VentasBundle\Entity\PagoCliente $pagos) {
        $this->pagos->removeElement($pagos);
    }

    /**
     * Get pagos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPagos() {
        return $this->pagos;
    }

    /**
     * Add notasDebCredVenta
     *
     * @param \VentasBundle\Entity\NotaDebCred $notasDebCredVenta
     * @return Cliente
     */
    public function addNotasDebCredVentum(\VentasBundle\Entity\NotaDebCred $notasDebCredVenta) {
        $this->notasDebCredVenta[] = $notasDebCredVenta;

        return $this;
    }

    /**
     * Remove notasDebCredVenta
     *
     * @param \VentasBundle\Entity\NotaDebCred $notasDebCredVenta
     */
    public function removeNotasDebCredVentum(\VentasBundle\Entity\NotaDebCred $notasDebCredVenta) {
        $this->notasDebCredVenta->removeElement($notasDebCredVenta);
    }

    /**
     * Get notasDebCredVenta
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotasDebCredVenta() {
        return $this->notasDebCredVenta;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Cliente
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return Cliente
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Add cobros
     *
     * @param \VentasBundle\Entity\Cobro $cobros
     * @return Cliente
     */
    public function addCobro(\VentasBundle\Entity\Cobro $cobros) {
        $this->cobros[] = $cobros;

        return $this;
    }

    /**
     * Remove cobros
     *
     * @param \VentasBundle\Entity\Cobro $cobros
     */
    public function removeCobro(\VentasBundle\Entity\Cobro $cobros) {
        $this->cobros->removeElement($cobros);
    }

    /**
     * Get cobros
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCobros() {
        return $this->cobros;
    }

    /**
     * Set vencCertNoRetener
     *
     * @param \DateTime $vencCertNoRetener
     * @return Cliente
     */
    public function setVencCertNoRetener($vencCertNoRetener) {
        $this->vencCertNoRetener = $vencCertNoRetener;

        return $this;
    }

    /**
     * Get vencCertNoRetener
     *
     * @return \DateTime
     */
    public function getVencCertNoRetener() {
        return $this->vencCertNoRetener;
    }

    /**
     * Set tipoCliente
     *
     * @param \ConfigBundle\Entity\TipoCliente $tipoCliente
     * @return Cliente
     */
    public function setTipoCliente(\ConfigBundle\Entity\TipoCliente $tipoCliente = null) {
        $this->tipoCliente = $tipoCliente;

        return $this;
    }

    /**
     * Get tipoCliente
     *
     * @return \ConfigBundle\Entity\TipoCliente
     */
    public function getTipoCliente() {
        return $this->tipoCliente;
    }

    /**
     * Get facturasElectronicas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturasElectronicas() {
        return $this->facturasElectronicas;
    }

}