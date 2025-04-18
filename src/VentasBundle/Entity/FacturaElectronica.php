<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * VentasBundle\Entity\FacturaElectronica
 * @ORM\Table(name="ventas_factura_electronica")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\FacturaRepository")
 * @UniqueEntity(
 *     fields={"puntoVenta", "tipoComprobante", "nroComprobante"},
 *     errorPath="nroComprobante",
 *     message="Registro de comprobante duplicado."
 * )
 * @Gedmo\Loggable()
 */
class FacturaElectronica {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\UnidadNegocio")
     * @ORM\JoinColumn(name="unidad_negocio_id", referencedColumnName="id")
     */
    protected $unidadNegocio;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\Cliente", inversedBy="facturasElectronicas")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $cliente;

    /**
     * @var integer $puntoVenta
     * @ORM\Column(name="punto_venta", type="integer")
     * @Gedmo\Versioned()
     */
    protected $puntoVenta;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipComprobante")
     * @ORM\JoinColumn(name="afip_comprobante_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     * */
    protected $tipoComprobante;

    /**
     * @var integer $nroComprobante
     * voucher_number
     * @ORM\Column(name="nro_comprobante", type="integer")
     * @Gedmo\Versioned()
     */
    protected $nroComprobante;

    /**
     * @var string $cae
     * @ORM\Column(name="cae", type="string", length=14)
     * @Gedmo\Versioned()
     */
    protected $cae;

    /**
     * @var string $caeVto
     * CAEFchVto
     * @ORM\Column(name="cae_vto", type="string", length=10)
     * @Gedmo\Versioned()
     */
    protected $caeVto;

    /**
     * @var decimal $total
     * @ORM\Column(name="total", type="decimal", precision=20, scale=2 )
     * @Gedmo\Versioned()
     */
    protected $total;

    /**
     * @var decimal $saldo
     * @ORM\Column(name="saldo", type="decimal", precision=20, scale=2 )
     * @Gedmo\Versioned()
     */
    protected $saldo;

    //** DATOS ENVIADOS WEBSERVICE / IMP FISCAL / REPORTES AFIP */

    /**
     * (1)Productos, (2)Servicios, (3)Productos y Servicios
     * @ORM\Column(name="concepto", type="integer", nullable=true)
     */
    protected $concepto = 1;

    /**
     * 99 consumidor final
     * @ORM\Column(name="doc_tipo", type="integer", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $docTipo = 99;

    /**
     * @ORM\Column(name="doc_nro", type="string", length=13, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $docNro;

    /**
     * @ORM\Column(name="nombre_cliente", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nombreCliente;

    /**
     * Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
     * @ORM\Column(name="cbte_fch", type="integer", nullable=true)
     */
    protected $cbteFch;

    /**
     * Importe neto no gravado
     * @ORM\Column(name="imp_tot_conc", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $impTotConc;

    /**
     * Importe neto gravado
     * @ORM\Column(name="imp_neto", type="decimal", precision=20, scale=2, nullable=true )
     * @Gedmo\Versioned()
     */
    protected $impNeto;

    /**
     * Importe exento de IVA
     * @ORM\Column(name="imp_op_ex", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $impOpEx;

    /**
     * Importe total de IVA
     * @ORM\Column(name="imp_iva", type="decimal", precision=20, scale=2, nullable=true )
     * @Gedmo\Versioned()
     */
    protected $impIva;

    /**
     * Importe total de tributos
     * @ORM\Column(name="imp_trib", type="decimal", precision=20, scale=2, nullable=true )
     */
    protected $impTrib;

    /**
     * 'PES' para pesos argentinos
     * @ORM\Column(name="mon_id", type="string", length=3, nullable=true)
     */
    protected $monId = 'PES';

    /**
     * 1 para pesos argentinos
     * @ORM\Column(name="mon_cotiz", type="string", length=10, nullable=true)
     */
    protected $monCotiz = 1;

    /**
     * Detalle de tributos
     * @ORM\Column(name="tributos", type="text", nullable=true )
     * @Gedmo\Versioned()
     */
    protected $tributos;

    /**
     * Detalle de comprobantes asociados - ND NC
     * @ORM\Column(name="cbtes_asoc", type="text", nullable=true )
     * @Gedmo\Versioned()
     */
    protected $cbtesAsoc;

    /**
     * Periodo asociado - ND NC
     * @ORM\Column(name="periodo_asoc", type="text", nullable=true )
     */
    protected $periodoAsoc;

    /**
     * Detalle de alicuotas
     * @ORM\Column(name="iva", type="text", nullable=true )
     * @Gedmo\Versioned()
     */
    protected $iva;

    /**
     * Detalle de opcionales
     * @ORM\Column(name="opcionales", type="text", nullable=true )
     * @Gedmo\Versioned()
     */
    protected $opcionales;

    /**
     * Fecha de vto (yyyymmdd)
     * @ORM\Column(name="fch_vto_pago", type="integer", nullable=true)
     */
    protected $fchVtoPago;

    //***/

    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\Cobro", inversedBy="facturaElectronica")
     * @ORM\JoinColumn(name="ventas_cobro_id", referencedColumnName="id")
     * Registro del cobro por el cual se genero el voucher
     */
    protected $cobro;

    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\NotaDebCred", inversedBy="notaElectronica")
     * @ORM\JoinColumn(name="ventas_nota_debcred_id", referencedColumnName="id")
     * Registro de la nota de deb/cred por el cual se genero el voucher
     */
    protected $notaDebCred;

    /**
     * @ORM\ManyToMany(targetEntity="VentasBundle\Entity\PagoCliente", mappedBy="comprobantes")
     */
    protected $pagos;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;
    // protected $qr;
    // public function getQr()
    // {
    //     return $this->qr;
    // }
    // public function setQr($qr)
    // {
    //     $this->qr = $qr;
    //     return $this;
    // }
    protected $docTipoTxt;

    public function getDocTipoTxt() {
        return $this->docTipoTxt;
    }

    public function setDocTipoTxt($docTipoTxt) {
        $this->docTipoTxt = $docTipoTxt;
        return $this;
    }

    public function __toString() {
        return $this->getComprobanteTxt();
    }

    public function getNroComprobanteTxt() {
        return str_pad($this->getPuntoVenta(), 4, "0", STR_PAD_LEFT) . '-' . str_pad($this->getNroComprobante(), 8, "0", STR_PAD_LEFT);
    }

    public function getComprobanteTxt() {
        return $this->getTipoComprobante()->getValor() . ' ' . $this->getNroComprobanteTxt();
    }

    public function getSelectComprobanteTxt() {
        if ($this->getCobro()) {
            $fecha = $this->getCobro()->getFechaCobro()->format('d/m/Y');
            $simbolo = $this->getCobro()->getMoneda()->getSimbolo();
        }
        else {
            $fecha = $this->getNotaDebCred()->getFecha()->format('d/m/Y');
            $simbolo = $this->getNotaDebCred()->getMoneda()->getSimbolo();
        }
        return $this->getTipoComprobante()->getValor() . ' ' . $this->getNroComprobanteTxt() .
            ' | ' . $fecha . ' | Total: ' . $simbolo . $this->getTotal() . ' | Saldo: ' . $simbolo . $this->getSaldo();
    }

    public function getComprobanteCtaCtePendienteTxt() {
        if ($this->getCobro()) {
            $fecha = $this->getCobro()->getFechaCobro()->format('d/m/Y');
            $simbolo = $this->getCobro()->getMoneda()->getSimbolo();
            $saldo = $this->getSaldo();
        }
        else {
            $fecha = $this->getNotaDebCred()->getFecha()->format('d/m/Y');
            $simbolo = $this->getNotaDebCred()->getMoneda()->getSimbolo();
            $saldo = ($this->getNotaDebCred()->getSigno() == '-') ? $this->getTotal() * -1 : $this->getSaldo();
        }
        return $this->getTipoComprobante()->getValor() . ' ' . $this->getNroComprobanteTxt() .
            ' | ' . $fecha . ' | ' . $simbolo . $saldo;
    }

    public function getCodigoComprobante() {
        return intval($this->getTipoComprobante()->getCodigo());
    }
    
    public function getPercepcionRentas(){
        if($this->getCobro()){
            return $this->getCobro()->getVenta()->getPercepcionRentas();
        }elseif($this->getNotaDebCred()){
            return $this->getNotaDebCred()->getPercepcionRentas();
        }
        return null;
    }

    public function getLetra() {
        return substr($this->getTipoComprobante()->getValor(), 4, 1);
    }

    public function getTipo() {
        return substr($this->getTipoComprobante()->getValor(), 0, 3);
    }

    public function getTituloPdf() {
        $tipo = substr($this->getTipoComprobante()->getValor(), 0, 3);
        switch ($tipo) {
            case 'FAC':
                return 'FACTURA';
            case 'TIC':
                return 'TICKET';
            case 'DEB':
                return 'NOTA DE DEBITO';
            case 'CRE':
                return 'NOTA DE CREDITO';
            case 'FCE':
                return 'FACTURA DE CREDITO MiPyME';
            case 'NDE':
                return 'NOTA DE DEBITO MiPyME';
            case 'NCE':
                return 'NOTA DE CREDITO MiPyME';
        }
        return false;
    }

    public function getCbteFchFormatted($format = 'Y-m-d') {
        $cbteFch = new \DateTime($this->getCbteFch());
        return $cbteFch->format($format);
    }

    public function getFchVtoPagoFormatted($format = 'Y-m-d') {
        $fecha = new \DateTime($this->getFchVtoPago());
        return $fecha->format($format);
    }

    public function getOpcionalesById($id) {
        $opcionales = json_decode($this->getOpcionales());
        if ($opcionales) {
            foreach ($opcionales as $op) {
                if ($op->Id == $id) {
                    return $op->Valor;
                }
            }
        }
        return null;
    }
    /**
     * Returns the transferencia option name based on the value in the opciones field
     *
     * @return string
     */
    public function getOpcionTransferencia() {
        $opTransferencia = $this->getOpcionalesById(27);
        $transferenciaOptionName = $opTransferencia === 'SCA'
            ? 'Sistema de Circulacion Abierta'
            : 'Agente de Depósito Colectivo';
        return $transferenciaOptionName;
    }

    public function getPeriodoFacturado() {
        $cbteFch = new \DateTime($this->getCbteFch());
        return array('desde' => $cbteFch->format('Y-m-01'), 'hasta' => $cbteFch->format('Y-m-t'));
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
     * Set puntoVenta
     *
     * @param integer $puntoVenta
     * @return FacturaElectronica
     */
    public function setPuntoVenta($puntoVenta) {
        $this->puntoVenta = $puntoVenta;

        return $this;
    }

    /**
     * Get puntoVenta
     *
     * @return integer
     */
    public function getPuntoVenta() {
        return $this->puntoVenta;
    }

    /**
     * Set nroComprobante
     *
     * @param integer $nroComprobante
     * @return FacturaElectronica
     */
    public function setNroComprobante($nroComprobante) {
        $this->nroComprobante = $nroComprobante;

        return $this;
    }

    /**
     * Get nroComprobante
     *
     * @return integer
     */
    public function getNroComprobante() {
        return $this->nroComprobante;
    }

    /**
     * Set cae
     *
     * @param string $cae
     * @return FacturaElectronica
     */
    public function setCae($cae) {
        $this->cae = $cae;

        return $this;
    }

    /**
     * Get cae
     *
     * @return string
     */
    public function getCae() {
        return $this->cae;
    }

    /**
     * Set caeVto
     *
     * @param string $caeVto
     * @return FacturaElectronica
     */
    public function setCaeVto($caeVto) {
        $this->caeVto = $caeVto;

        return $this;
    }

    /**
     * Get caeVto
     *
     * @return string
     */
    public function getCaeVto() {
        return $this->caeVto;
    }

    /**
     * Set cobro
     *
     * @param \VentasBundle\Entity\Cobro $cobro
     * @return FacturaElectronica
     */
    public function setCobro(\VentasBundle\Entity\Cobro $cobro = null) {
        $this->cobro = $cobro;

        return $this;
    }

    /**
     * Get cobro
     *
     * @return \VentasBundle\Entity\Cobro
     */
    public function getCobro() {
        return $this->cobro;
    }

    /**
     * Set notaDebCred
     *
     * @param \VentasBundle\Entity\NotaDebCred $notaDebCred
     * @return FacturaElectronica
     */
    public function setNotaDebCred(\VentasBundle\Entity\NotaDebCred $notaDebCred = null) {
        $this->notaDebCred = $notaDebCred;

        return $this;
    }

    /**
     * Get notaDebCred
     *
     * @return \VentasBundle\Entity\NotaDebCred
     */
    public function getNotaDebCred() {
        return $this->notaDebCred;
    }

    /**
     * Set tipoComprobante
     *
     * @param \ConfigBundle\Entity\AfipComprobante $tipoComprobante
     * @return FacturaElectronica
     */
    public function setTipoComprobante(\ConfigBundle\Entity\AfipComprobante $tipoComprobante = null) {
        $this->tipoComprobante = $tipoComprobante;

        return $this;
    }

    /**
     * Get tipoComprobante
     *
     * @return \ConfigBundle\Entity\AfipComprobante
     */
    public function getTipoComprobante() {
        return $this->tipoComprobante;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return FacturaElectronica
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
     * Set saldo
     *
     * @param string $saldo
     * @return FacturaElectronica
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
     * Constructor
     */
    public function __construct() {
        $this->pagos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add pagos
     *
     * @param \VentasBundle\Entity\PagoCliente $pagos
     * @return FacturaElectronica
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
     * Set concepto
     *
     * @param integer $concepto
     * @return FacturaElectronica
     */
    public function setConcepto($concepto) {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return integer
     */
    public function getConcepto() {
        return $this->concepto;
    }

    /**
     * Set docTipo
     *
     * @param integer $docTipo
     * @return FacturaElectronica
     */
    public function setDocTipo($docTipo) {
        $this->docTipo = $docTipo;

        return $this;
    }

    /**
     * Get docTipo
     *
     * @return integer
     */
    public function getDocTipo() {
        return $this->docTipo;
    }

    /**
     * Set docNro
     *
     * @param string $docNro
     * @return FacturaElectronica
     */
    public function setDocNro($docNro) {
        $this->docNro = $docNro;

        return $this;
    }

    /**
     * Get docNro
     *
     * @return string
     */
    public function getDocNro() {
        return $this->docNro;
    }

    /**
     * Set nombreCliente
     *
     * @param string $nombreCliente
     * @return FacturaElectronica
     */
    public function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;

        return $this;
    }

    /**
     * Get nombreCliente
     *
     * @return string
     */
    public function getNombreCliente() {
        return $this->nombreCliente;
    }

    /**
     * Set impTotConc
     *
     * @param string $impTotConc
     * @return FacturaElectronica
     */
    public function setImpTotConc($impTotConc) {
        $this->impTotConc = $impTotConc;

        return $this;
    }

    /**
     * Get impTotConc
     *
     * @return string
     */
    public function getImpTotConc() {
        return $this->impTotConc;
    }

    /**
     * Set impNeto
     *
     * @param string $impNeto
     * @return FacturaElectronica
     */
    public function setImpNeto($impNeto) {
        $this->impNeto = $impNeto;

        return $this;
    }

    /**
     * Get impNeto
     *
     * @return string
     */
    public function getImpNeto() {
        return $this->impNeto;
    }

    /**
     * Set impOpEx
     *
     * @param string $impOpEx
     * @return FacturaElectronica
     */
    public function setImpOpEx($impOpEx) {
        $this->impOpEx = $impOpEx;

        return $this;
    }

    /**
     * Get impOpEx
     *
     * @return string
     */
    public function getImpOpEx() {
        return $this->impOpEx;
    }

    /**
     * Set impIva
     *
     * @param string $impIva
     * @return FacturaElectronica
     */
    public function setImpIva($impIva) {
        $this->impIva = $impIva;

        return $this;
    }

    /**
     * Get impIva
     *
     * @return string
     */
    public function getImpIva() {
        return $this->impIva;
    }

    /**
     * Set impTrib
     *
     * @param string $impTrib
     * @return FacturaElectronica
     */
    public function setImpTrib($impTrib) {
        $this->impTrib = $impTrib;

        return $this;
    }

    /**
     * Get impTrib
     *
     * @return string
     */
    public function getImpTrib() {
        return $this->impTrib;
    }

    /**
     * Set monId
     *
     * @param string $monId
     * @return FacturaElectronica
     */
    public function setMonId($monId) {
        $this->monId = $monId;

        return $this;
    }

    /**
     * Get monId
     *
     * @return string
     */
    public function getMonId() {
        return $this->monId;
    }

    /**
     * Set monCotiz
     *
     * @param string $monCotiz
     * @return FacturaElectronica
     */
    public function setMonCotiz($monCotiz) {
        $this->monCotiz = $monCotiz;

        return $this;
    }

    /**
     * Get monCotiz
     *
     * @return string
     */
    public function getMonCotiz() {
        return $this->monCotiz;
    }

    /**
     * Set tributos
     *
     * @param string $tributos
     * @return FacturaElectronica
     */
    public function setTributos($tributos) {
        $this->tributos = $tributos;

        return $this;
    }

    /**
     * Get tributos
     *
     * @return string
     */
    public function getTributos() {
        return $this->tributos;
    }

    /**
     * Set cbtesAsoc
     *
     * @param string $cbtesAsoc
     * @return FacturaElectronica
     */
    public function setCbtesAsoc($cbtesAsoc) {
        $this->cbtesAsoc = $cbtesAsoc;

        return $this;
    }

    /**
     * Get cbtesAsoc
     *
     * @return string
     */
    public function getCbtesAsoc() {
        return $this->cbtesAsoc;
    }

    /**
     * Set periodoAsoc
     *
     * @param string $periodoAsoc
     * @return FacturaElectronica
     */
    public function setPeriodoAsoc($periodoAsoc) {
        $this->periodoAsoc = $periodoAsoc;

        return $this;
    }

    /**
     * Get periodoAsoc
     *
     * @return string
     */
    public function getPeriodoAsoc() {
        return $this->periodoAsoc;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return FacturaElectronica
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
     * Set cbteFch
     *
     * @param integer $cbteFch
     * @return FacturaElectronica
     */
    public function setCbteFch($cbteFch) {
        $this->cbteFch = $cbteFch;

        return $this;
    }

    /**
     * Get cbteFch
     *
     * @return integer
     */
    public function getCbteFch() {
        return $this->cbteFch;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return FacturaElectronica
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return FacturaElectronica
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
     * Set unidadNegocio
     *
     * @param \ConfigBundle\Entity\UnidadNegocio $unidadNegocio
     * @return FacturaElectronica
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
     * Set cliente
     *
     * @param \VentasBundle\Entity\Cliente $cliente
     * @return FacturaElectronica
     */
    public function setCliente(\VentasBundle\Entity\Cliente $cliente = null) {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return \VentasBundle\Entity\Cliente
     */
    public function getCliente() {
        return $this->cliente;
    }

    /**
     * Set opcionales
     *
     * @param string $opcionales
     * @return FacturaElectronica
     */
    public function setOpcionales($opcionales) {
        $this->opcionales = $opcionales;

        return $this;
    }

    /**
     * Get opcionales
     *
     * @return string
     */
    public function getOpcionales() {
        return $this->opcionales;
    }

    /**
     * Set fchVtoPago
     *
     * @param integer $fchVtoPago
     * @return FacturaElectronica
     */
    public function setFchVtoPago($fchVtoPago) {
        $this->fchVtoPago = $fchVtoPago;

        return $this;
    }

    /**
     * Get fchVtoPago
     *
     * @return integer
     */
    public function getFchVtoPago() {
        return $this->fchVtoPago;
    }

}