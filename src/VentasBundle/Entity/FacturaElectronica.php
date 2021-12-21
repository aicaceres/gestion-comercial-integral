<?php
namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VentasBundle\Entity\FacturaElectronica
 * @ORM\Table(name="ventas_factura_electronica")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\FacturaRepository")
 */
class FacturaElectronica
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $tipoComp
     * @ORM\Column(name="tipo_comp", type="string",nullable=true)
     */    
    protected $tipoComp;    
    /**
     * @var integer $nroComp
     * @ORM\Column(name="nro_comp", type="string",nullable=true)
     */    
    protected $nroComp;    
    /**
     * @var integer $cae
     * @ORM\Column(name="cae", type="string",nullable=true)
     */    
    protected $cae;    
    /**
     * @var integer $caeVto
     * @ORM\Column(name="cae_vto", type="string",nullable=true)
     */    
    protected $caeVto;    
    /**
     * @var integer $facturadoDesde
     * @ORM\Column(name="facturado_desde", type="string",nullable=true)
     */    
    protected $facturadoDesde;    
    /**
     * @var integer $facturadoHasta
     * @ORM\Column(name="facturado_hasta", type="string",nullable=true)
     */    
    protected $facturadoHasta;    
    /**
     * @var integer $pagoVto
     * @ORM\Column(name="pago_vto", type="string",nullable=true)
     */    
    protected $pagoVto;    
    
    /**
     * @var integer $resultado
     * @ORM\Column(name="resultado", type="string",nullable=true)
     */    
    protected $resultado;            
    /**
     * @var integer $motivoObs
     * @ORM\Column(name="motivo_obs", type="string",nullable=true)
     */    
    protected $motivoObs;       
    /**
     * @var integer $mensajeError
     * @ORM\Column(name="mensaje_error", type="string",nullable=true)
     */    
    protected $mensajeError;       
    /**
     * @var integer $reproceso
     * @ORM\Column(name="reproceso", type="string",nullable=true)
     */    
    protected $reproceso;       

     /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=3 )
     */
    protected $total=0;    
     /**
     * @var integer $neto
     * @ORM\Column(name="neto", type="decimal", scale=3 )
     */
    protected $neto=0;    
     /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", scale=3 )
     */
    protected $iva=0;    

    /**
    * @ORM\OneToOne(targetEntity="VentasBundle\Entity\Factura", inversedBy="facturaElectronica")
    * @ORM\JoinColumn(name="ventas_factura_id", referencedColumnName="id")
    */
    protected $factura;
    /**
     * @ORM\OneToOne(targetEntity="VentasBundle\Entity\NotaDebCred", inversedBy="notaElectronica")
     * @ORM\JoinColumn(name="ventas_nota_debcred_id", referencedColumnName="id")
     */
    protected $notaDebCred;

    /** 
     * @var string $prefijoCompAsoc
     * @ORM\Column(name="prefijo_comp_asoc", type="string", nullable=true)
     */
    protected $prefijoCompAsoc;
    /** 
     * @var string $tipoCompAsoc
     * @ORM\Column(name="tipo_comp_asoc", type="string", nullable=true)
     */
    protected $tipoCompAsoc;
    /** 
     * @var string $nroCompAsoc
     * @ORM\Column(name="nro_comp_asoc", type="string", nullable=true)
     */
    protected $nroCompAsoc;    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tipoComp
     *
     * @param string $tipoComp
     * @return FacturaElectronica
     */
    public function setTipoComp($tipoComp)
    {
        $this->tipoComp = $tipoComp;
    
        return $this;
    }

    /**
     * Get tipoComp
     *
     * @return string 
     */
    public function getTipoComp()
    {
        return $this->tipoComp;
    }

    /**
     * Set nroComp
     *
     * @param string $nroComp
     * @return FacturaElectronica
     */
    public function setNroComp($nroComp)
    {
        $this->nroComp = $nroComp;
    
        return $this;
    }

    /**
     * Get nroComp
     *
     * @return string 
     */
    public function getNroComp()
    {
        return $this->nroComp;
    }

    /**
     * Set cae
     *
     * @param string $cae
     * @return FacturaElectronica
     */
    public function setCae($cae)
    {
        $this->cae = $cae;
    
        return $this;
    }

    /**
     * Get cae
     *
     * @return string 
     */
    public function getCae()
    {
        return $this->cae;
    }

    /**
     * Set caeVto
     *
     * @param string $caeVto
     * @return FacturaElectronica
     */
    public function setCaeVto($caeVto)
    {
        $this->caeVto = $caeVto;
    
        return $this;
    }

    /**
     * Get caeVto
     *
     * @return string 
     */
    public function getCaeVto()
    {
        return $this->caeVto;
    }

    /**
     * Set resultado
     *
     * @param string $resultado
     * @return FacturaElectronica
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;
    
        return $this;
    }

    /**
     * Get resultado
     *
     * @return string 
     */
    public function getResultado()
    {
        return $this->resultado;
    }

    /**
     * Set motivoObs
     *
     * @param string $motivoObs
     * @return FacturaElectronica
     */
    public function setMotivoObs($motivoObs)
    {
        $this->motivoObs = $motivoObs;
    
        return $this;
    }

    /**
     * Get motivoObs
     *
     * @return string 
     */
    public function getMotivoObs()
    {
        return $this->motivoObs;
    }

    /**
     * Set mensajeError
     *
     * @param string $mensajeError
     * @return FacturaElectronica
     */
    public function setMensajeError($mensajeError)
    {
        $this->mensajeError = $mensajeError;
    
        return $this;
    }

    /**
     * Get mensajeError
     *
     * @return string 
     */
    public function getMensajeError()
    {
        return $this->mensajeError;
    }

    /**
     * Set reproceso
     *
     * @param string $reproceso
     * @return FacturaElectronica
     */
    public function setReproceso($reproceso)
    {
        $this->reproceso = $reproceso;
    
        return $this;
    }

    /**
     * Get reproceso
     *
     * @return string 
     */
    public function getReproceso()
    {
        return $this->reproceso;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return FacturaElectronica
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return string 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set neto
     *
     * @param string $neto
     * @return FacturaElectronica
     */
    public function setNeto($neto)
    {
        $this->neto = $neto;
    
        return $this;
    }

    /**
     * Get neto
     *
     * @return string 
     */
    public function getNeto()
    {
        return $this->neto;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return FacturaElectronica
     */
    public function setIva($iva)
    {
        $this->iva = $iva;
    
        return $this;
    }

    /**
     * Get iva
     *
     * @return string 
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * Set factura
     *
     * @param \VentasBundle\Entity\Factura $factura
     * @return FacturaElectronica
     */
    public function setFactura(\VentasBundle\Entity\Factura $factura = null)
    {
        $this->factura = $factura;
    
        return $this;
    }

    /**
     * Get factura
     *
     * @return \VentasBundle\Entity\Factura 
     */
    public function getFactura()
    {
        return $this->factura;
    }

    /**
     * Set notaDebCred
     *
     * @param \VentasBundle\Entity\NotaDebCred $notaDebCred
     * @return FacturaElectronica
     */
    public function setNotaDebCred(\VentasBundle\Entity\NotaDebCred $notaDebCred = null)
    {
        $this->notaDebCred = $notaDebCred;
    
        return $this;
    }

    /**
     * Get notaDebCred
     *
     * @return \VentasBundle\Entity\NotaDebCred 
     */
    public function getNotaDebCred()
    {
        return $this->notaDebCred;
    }

    /**
     * Set prefijoCompAsoc
     *
     * @param string $prefijoCompAsoc
     * @return FacturaElectronica
     */
    public function setPrefijoCompAsoc($prefijoCompAsoc)
    {
        $this->prefijoCompAsoc = $prefijoCompAsoc;
    
        return $this;
    }

    /**
     * Get prefijoCompAsoc
     *
     * @return string 
     */
    public function getPrefijoCompAsoc()
    {
        return $this->prefijoCompAsoc;
    }

    /**
     * Set tipoCompAsoc
     *
     * @param string $tipoCompAsoc
     * @return FacturaElectronica
     */
    public function setTipoCompAsoc($tipoCompAsoc)
    {
        $this->tipoCompAsoc = $tipoCompAsoc;
    
        return $this;
    }

    /**
     * Get tipoCompAsoc
     *
     * @return string 
     */
    public function getTipoCompAsoc()
    {
        return $this->tipoCompAsoc;
    }

    /**
     * Set nroCompAsoc
     *
     * @param string $nroCompAsoc
     * @return FacturaElectronica
     */
    public function setNroCompAsoc($nroCompAsoc)
    {
        $this->nroCompAsoc = $nroCompAsoc;
    
        return $this;
    }

    /**
     * Get nroCompAsoc
     *
     * @return string 
     */
    public function getNroCompAsoc()
    {
        return $this->nroCompAsoc;
    }

    /**
     * Set facturadoDesde
     *
     * @param string $facturadoDesde
     * @return FacturaElectronica
     */
    public function setFacturadoDesde($facturadoDesde)
    {
        $this->facturadoDesde = $facturadoDesde;

        return $this;
    }

    /**
     * Get facturadoDesde
     *
     * @return string 
     */
    public function getFacturadoDesde()
    {
        return $this->facturadoDesde;
    }

    /**
     * Set facturadoHasta
     *
     * @param string $facturadoHasta
     * @return FacturaElectronica
     */
    public function setFacturadoHasta($facturadoHasta)
    {
        $this->facturadoHasta = $facturadoHasta;

        return $this;
    }

    /**
     * Get facturadoHasta
     *
     * @return string 
     */
    public function getFacturadoHasta()
    {
        return $this->facturadoHasta;
    }

    /**
     * Set pagoVto
     *
     * @param string $pagoVto
     * @return FacturaElectronica
     */
    public function setPagoVto($pagoVto)
    {
        $this->pagoVto = $pagoVto;

        return $this;
    }

    /**
     * Get pagoVto
     *
     * @return string 
     */
    public function getPagoVto()
    {
        return $this->pagoVto;
    }
}
