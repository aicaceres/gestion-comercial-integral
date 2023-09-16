<?php

namespace ComprasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ComprasBundle\Entity\NotaDebCred
 * @ORM\Table(name="compras_nota_debcred")
 * @ORM\Entity(repositoryClass="ComprasBundle\Entity\NotaDebCredRepository")
 * @UniqueEntity(
 *     fields={"proveedor","signo","nroComprobante"},
 *     errorPath="nroComprobante",
 *     message="El N° de Comprobante ya existe para este Proveedor."
 * )
 */
class NotaDebCred {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $tipoNota
     * @ORM\Column(name="tipo_nota", type="string", length=1)
     */
    protected $tipoNota;

    /**
     * @var integer $prefijoNro
     * @ORM\Column(name="prefijo_nro", type="string", length=3)
     */
    protected $prefijoNro;

    /**
     * @var integer $notaDebCredNro
     * @ORM\Column(name="nota_debcred_nro", type="string", length=8)
     */
    protected $notaDebCredNro;

    /**
     * @var integer $nroComprobante
     * @ORM\Column(name="nro_comprobante", type="string", length=30, nullable=true)
     */
    protected $nroComprobante;

    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    protected $estado;

    /**
     * 'CRE' = '-'; 'DEB' = '+'
     * @var string $signo
     * @ORM\Column(name="signo", type="string",length=1)
     */
    protected $signo = '-';

// TOTALES DE LA FACTURA

    /**
     * @var integer $subtotal
     * @ORM\Column(name="subtotal", type="decimal", scale=3,nullable=true )
     */
    protected $subtotal;

    /**
     * @var integer $subtotalNeto
     * @ORM\Column(name="subtotal_neto", type="decimal", scale=3,nullable=true )
     */
    protected $subtotalNeto;

    /**
     * @var integer $impuestoInterno
     * @ORM\Column(name="impuesto_interno", type="decimal", scale=3,nullable=true )
     */
    protected $impuestoInterno;

    /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=3,nullable=true )
     */
    protected $iva;

    /**
     * @var integer $percepcionIva
     * @ORM\Column(name="percepcion_iva", type="decimal", scale=3,nullable=true )
     */
    protected $percepcionIva;

    /**
     * @var integer $percepcionDgr
     * @ORM\Column(name="percepcion_dgr", type="decimal", scale=3,nullable=true )
     */
    protected $percepcionDgr;

    /**
     * @var integer $percepcionMunicipal
     * @ORM\Column(name="percepcion_municipal", type="decimal", scale=3,nullable=true )
     */
    protected $percepcionMunicipal;

    /**
     * @var integer $totalBonificado
     * @ORM\Column(name="total_bonificado", type="decimal", scale=3,nullable=true )
     */
    protected $totalBonificado;

    /**
     * @var integer $tmc
     * @ORM\Column(name="tmc", type="decimal", scale=3,nullable=true )
     */
    protected $tmc;

    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=3 )
     */
    protected $total;

    /**
     * @ORM\Column(name="modifica_stock", type="boolean",nullable=true)
     */
    protected $modificaStock = false;

    /**
     * @ORM\Column(name="retenciones_aplicadas", type="boolean",nullable=true)
     */
    protected $retencionesAplicadas = false;

    /**
     * @var integer $saldo
     * @ORM\Column(name="saldo", type="decimal", scale=3,nullable=true )
     */
    protected $saldo;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\Proveedor", inversedBy="notasDebCred")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     */
    protected $proveedor;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\ManyToMany(targetEntity="ComprasBundle\Entity\Factura")
     * @ORM\JoinTable(name="facturas_x_notadebcred",
     *      joinColumns={@ORM\JoinColumn(name="compras_nota_debcred_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="compras_factura_id", referencedColumnName="id")}
     * )
     */
    protected $facturas;

    /**
     * @ORM\Column(name="concepto", type="text", nullable=true)
     */
    protected $concepto;

    /**
     * @ORM\OneToMany(targetEntity="ComprasBundle\Entity\NotaDebCredDetalle", mappedBy="notaDebCred",cascade={"persist", "remove"})
     */
    protected $detalles;

    /*
     * DATOS PARA AFIP
     */

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipComprobante")
     * @ORM\JoinColumn(name="afip_comprobante_id", referencedColumnName="id")
     * */
    protected $afipComprobante;

    /**
     * @var integer $afipPuntoVenta
     * @ORM\Column(name="afip_punto_venta", type="string", length=5)
     */
    protected $afipPuntoVenta;

    /**
     * @var integer $afipNroComprobante
     * @ORM\Column(name="afip_nro_comprobante", type="string", length=20)
     */
    protected $afipNroComprobante;

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

    public function getTotalsinBonificacion() {
        return $this->getSubtotalNeto() + $this->getIva() + $this->getPercepcionIva() +
            $this->getPercepcionDgr() + $this->getPercepcionMunicipal() +
            $this->getImpuestoInterno() + $this->getTmc();
    }

    // calcula saldo imponible
    public function getSaldoImponible() {
//        if( !$this->retencionesAplicadas ){
        $imp = $this->getTotal() - $this->getIva();
        $porc = ($imp * 100) / $this->getTotal();
        $saldoImponible = ($this->getSaldo() * $porc) / 100;
        return $saldoImponible;
//        }else{
//            return $this->getSaldo();
//        }
    }

    public function getPorcImponible() {
        return ($this->getIva() * 100) / $this->getSaldoImponible();
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->signo = '-';
        $this->estado = 'ACREDITADO';
        $this->modificaStock = FALSE;
    }

    public function getSignoNota() {
        return ($this->signo == '-') ? 'Crédito' : 'Débito';
    }

    // control provisorio si se puede eliminar
    // si es credito y no modificó el stock o alguna de sus facturas asociadas ya tiene pagos
    // si es debito y estado es PENDIENTE
    public function eliminable() {
        if ($this->getSigno() == '-') {
            // si modifo stock no es eliminable
            if ($this->getModificaStock())
                return FALSE;
            // verificar facturas con pagos
            foreach ($this->getFacturas() as $fact) {
                if ($fact->getEstado() == 'PAGADO' || $fact->getEstado() == 'PAGO PARCIAL') {
                    return false;
                }
            }
            return true;
        }
        if ($this->getSigno() == '+' && $this->getEstado() == 'PENDIENTE') {
            return TRUE;
        }
        return FALSE;
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
     * Set prefijoNro
     *
     * @param string $prefijoNro
     * @return NotaDebCred
     */
    public function setPrefijoNro($prefijoNro) {
        $this->prefijoNro = $prefijoNro;

        return $this;
    }

    /**
     * Get prefijoNro
     *
     * @return string
     */
    public function getPrefijoNro() {
        return $this->prefijoNro;
    }

    /**
     * Set notaDebCredNro
     *
     * @param string $notaDebCredNro
     * @return NotaDebCred
     */
    public function setNotaDebCredNro($notaDebCredNro) {
        $this->notaDebCredNro = $notaDebCredNro;

        return $this;
    }

    /**
     * Get notaDebCredNro
     *
     * @return string
     */
    public function getNotaDebCredNro() {
        return $this->notaDebCredNro;
    }

    /**
     * Get nroNotaDebCred
     * @return integer
     */
    public function getNroNotaDebCred() {
        return $this->prefijoNro . '-' . $this->notaDebCredNro;
    }

    /**
     * Set nroComprobante
     *
     * @param string $nroComprobante
     * @return NotaDebCred
     */
    public function setNroComprobante($nroComprobante) {
        $this->nroComprobante = $nroComprobante;

        return $this;
    }

    /**
     * Get nroComprobante
     *
     * @return string
     */
    public function getNroComprobante() {
        return $this->nroComprobante;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return NotaDebCred
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return NotaDebCred
     */
    public function setEstado($estado) {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * Set proveedor
     *
     * @param \ComprasBundle\Entity\Proveedor $proveedor
     * @return NotaDebCred
     */
    public function setProveedor(\ComprasBundle\Entity\Proveedor $proveedor = null) {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ComprasBundle\Entity\Proveedor
     */
    public function getProveedor() {
        return $this->proveedor;
    }

    /**
     * Add detalles
     *
     * @param \ComprasBundle\Entity\NotaDebCredDetalle $detalles
     * @return NotaDebCred
     */
    public function addDetalle(\ComprasBundle\Entity\NotaDebCredDetalle $detalles) {
        $detalles->setNotaDebCred($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \ComprasBundle\Entity\NotaDebCredDetalle $detalles
     */
    public function removeDetalle(\ComprasBundle\Entity\NotaDebCredDetalle $detalles) {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalles() {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return NotaDebCred
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
     * @return NotaDebCred
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
     * Set concepto
     *
     * @param string $concepto
     * @return NotaDebCred
     */
    public function setConcepto($concepto) {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return string
     */
    public function getConcepto() {
        return $this->concepto;
    }

    /**
     * Set signo
     *
     * @param string $signo
     * @return NotaDebCred
     */
    public function setSigno($signo) {
        $this->signo = $signo;

        return $this;
    }

    /**
     * Get signo
     *
     * @return string
     */
    public function getSigno() {
        return $this->signo;
    }

    /**
     * Set subtotal
     *
     * @param string $subtotal
     * @return NotaDebCred
     */
    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return string
     */
    public function getSubtotal() {
        return $this->subtotal;
    }

    /**
     * Set subtotalNeto
     *
     * @param string $subtotalNeto
     * @return NotaDebCred
     */
    public function setSubtotalNeto($subtotalNeto) {
        $this->subtotalNeto = $subtotalNeto;

        return $this;
    }

    /**
     * Get subtotalNeto
     *
     * @return string
     */
    public function getSubtotalNeto() {
        return $this->subtotalNeto;
    }

    /**
     * Set impuestoInterno
     *
     * @param string $impuestoInterno
     * @return NotaDebCred
     */
    public function setImpuestoInterno($impuestoInterno) {
        $this->impuestoInterno = $impuestoInterno;

        return $this;
    }

    /**
     * Get impuestoInterno
     *
     * @return string
     */
    public function getImpuestoInterno() {
        return $this->impuestoInterno;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return NotaDebCred
     */
    public function setIva($iva) {
        $this->iva = $iva;

        return $this;
    }

    /**
     * Get iva
     *
     * @return string
     */
    public function getIva() {
        return $this->iva;
    }

    /**
     * Set percepcionIva
     *
     * @param string $percepcionIva
     * @return NotaDebCred
     */
    public function setPercepcionIva($percepcionIva) {
        $this->percepcionIva = $percepcionIva;
        return $this;
    }

    /**
     * Get percepcionIva
     *
     * @return string
     */
    public function getPercepcionIva() {
        return $this->percepcionIva;
    }

    /**
     * Set percepcionDgr
     *
     * @param string $percepcionDgr
     * @return NotaDebCred
     */
    public function setPercepcionDgr($percepcionDgr) {
        $this->percepcionDgr = $percepcionDgr;
        return $this;
    }

    /**
     * Get percepcionDgr
     *
     * @return string
     */
    public function getPercepcionDgr() {
        return $this->percepcionDgr;
    }

    /**
     * Set percepcionMunicipal
     *
     * @param string $percepcionMunicipal
     * @return NotaDebCred
     */
    public function setPercepcionMunicipal($percepcionMunicipal) {
        $this->percepcionMunicipal = $percepcionMunicipal;
        return $this;
    }

    /**
     * Get percepcionMunicipal
     *
     * @return string
     */
    public function getPercepcionMunicipal() {
        return $this->percepcionMunicipal;
    }

    /**
     * Set totalBonificado
     *
     * @param string $totalBonificado
     * @return NotaDebCred
     */
    public function setTotalBonificado($totalBonificado) {
        $this->totalBonificado = $totalBonificado;

        return $this;
    }

    /**
     * Get totalBonificado
     *
     * @return string
     */
    public function getTotalBonificado() {
        return $this->totalBonificado;
    }

    /**
     * Set tmc
     *
     * @param string $tmc
     * @return NotaDebCred
     */
    public function setTmc($tmc) {
        $this->tmc = $tmc;

        return $this;
    }

    /**
     * Get tmc
     *
     * @return string
     */
    public function getTmc() {
        return $this->tmc;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return NotaDebCred
     */
    public function setTotal($total) {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * Add facturas
     *
     * @param \ComprasBundle\Entity\Factura $facturas
     * @return NotaDebCred
     */
    public function addFactura(\ComprasBundle\Entity\Factura $facturas) {
        $this->facturas[] = $facturas;
        return $this;
    }

    /**
     * Remove facturas
     *
     * @param \ComprasBundle\Entity\Factura $facturas
     */
    public function removeFactura(\ComprasBundle\Entity\Factura $facturas) {
        $this->facturas->removeElement($facturas);
    }

    /**
     * Get facturas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFacturas() {
        return $this->facturas;
    }

    /**
     * Set saldo
     *
     * @param string $saldo
     * @return NotaDebCred
     */
    public function setSaldo($saldo) {
        $this->saldo = $saldo;

        return $this;
    }

    /**
     * Get saldo
     *
     * @return string
     */
    public function getSaldo() {
        return $this->saldo;
    }

    /**
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return NotaDebCred
     */
    public function setUnidadNegocio(\ConfigBundle\Entity\UnidadNegocio $unidadNegocio = null) {
        $this->unidadNegocio = $unidadNegocio;

        return $this;
    }

    /**
     * Get unidadNegocio
     *
     * @return \ConfigBundle\Entity\UnidadNegocio
     */
    public function getUnidadNegocio() {
        return $this->unidadNegocio;
    }

    /**
     * Set tipoNota
     *
     * @param string $tipoNota
     * @return NotaDebCred
     */
    public function setTipoNota($tipoNota) {
        $this->tipoNota = $tipoNota;

        return $this;
    }

    /**
     * Get tipoNota
     *
     * @return string
     */
    public function getTipoNota() {
        return $this->tipoNota;
    }

    /**
     * Set modificaStock
     *
     * @param boolean $modificaStock
     * @return NotaDebCred
     */
    public function setModificaStock($modificaStock) {
        $this->modificaStock = $modificaStock;

        return $this;
    }

    /**
     * Get modificaStock
     *
     * @return boolean
     */
    public function getModificaStock() {
        return $this->modificaStock;
    }

    /**
     * Set afipPuntoVenta
     *
     * @param string $afipPuntoVenta
     * @return NotaDebCred
     */
    public function setAfipPuntoVenta($afipPuntoVenta) {
        $this->afipPuntoVenta = $afipPuntoVenta;

        return $this;
    }

    /**
     * Get afipPuntoVenta
     *
     * @return string
     */
    public function getAfipPuntoVenta() {
        return $this->afipPuntoVenta;
    }

    /**
     * Set afipNroComprobante
     *
     * @param string $afipNroComprobante
     * @return NotaDebCred
     */
    public function setAfipNroComprobante($afipNroComprobante) {
        $this->afipNroComprobante = $afipNroComprobante;

        return $this;
    }

    /**
     * Get afipNroComprobante
     *
     * @return string
     */
    public function getAfipNroComprobante() {
        return $this->afipNroComprobante;
    }

    /**
     * Set afipComprobante
     *
     * @param \ConfigBundle\Entity\AfipComprobante $afipComprobante
     * @return NotaDebCred
     */
    public function setAfipComprobante(\ConfigBundle\Entity\AfipComprobante $afipComprobante = null) {
        $this->afipComprobante = $afipComprobante;

        return $this;
    }

    /**
     * Get afipComprobante
     *
     * @return \ConfigBundle\Entity\AfipComprobante
     */
    public function getAfipComprobante() {
        return $this->afipComprobante;
    }

    public function getNuevoNroComprobante() {
        if ($this->getAfipPuntoVenta() && $this->getAfipNroComprobante()) {
            return $this->getAfipPuntoVenta() . '-' . $this->getAfipNroComprobante();
        }
        else {
            return $this->getNroComprobante();
        }
    }

    /**
     * Set retencionesAplicadas
     *
     * @param boolean $retencionesAplicadas
     * @return NotaDebCred
     */
    public function setRetencionesAplicadas($retencionesAplicadas) {
        $this->retencionesAplicadas = $retencionesAplicadas;

        return $this;
    }

    /**
     * Get retencionesAplicadas
     *
     * @return boolean
     */
    public function getRetencionesAplicadas() {
        return $this->retencionesAplicadas;
    }

}