<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\FormaPago
 * @ORM\Table(name="forma_pago")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ParametroRepository")
 * @UniqueEntity(
 *     fields={"nombre"},
 *     errorPath="nombre",
 *     message="Registro duplicado."
 * )
 */
class FormaPago {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false, unique=true)
     */
    protected $nombre;
    /**
     * @var string $cuentaCorriente
     * @ORM\Column(name="cuenta_corriente", type="boolean", nullable=true)
     */
    protected $cuentaCorriente = false;
    /**
     * @var string $tarjeta
     * @ORM\Column(name="tarjeta", type="boolean", nullable=true)
     */
    protected $tarjeta = false;
    /**
     * @var string $contado
     * @ORM\Column(name="contado", type="boolean", nullable=true)
     */
    protected $contado = false;
    /**
     * @var integer $cuotas
     * @ORM\Column(name="cuotas", type="integer", nullable=true)
     */
    protected $cuotas;
    /**
     * @var string $vencimiento
     * @ORM\Column(name="vencimiento", type="string", nullable=false)
     */
    protected $vencimiento = 'M';
    /**
     * @var integer $plazo
     * @ORM\Column(name="plazo", type="integer", nullable=true)
     */
    protected $plazo;
    /**
     * @var integer $tipoRecargo
     * @ORM\Column(name="tipo_recargo", type="string", nullable=true)
     */
    protected $tipoRecargo;
    /**
     * @var integer $porcentajeRecargo
     * @ORM\Column(name="porcentaje_recargo", type="decimal",scale=2, nullable=true)
     */
    protected $porcentajeRecargo;
    /**
     * @var integer $descuentoPagoAnticipado
     * @ORM\Column(name="descuento_pago_anticipado", type="integer", nullable=true)
     */
    protected $descuentoPagoAnticipado;
    /**
     * @var integer $mora
     * @ORM\Column(name="mora", type="decimal",scale=2,nullable=true)
     */
    protected $mora;
    /**
     * @var integer $diasMora
     * @ORM\Column(name="dias_mora", type="integer", nullable=true)
     */
    protected $diasMora;
    /**
     * @var integer $copiasComprobante
     * @ORM\Column(name="copias_comprobante", type="integer", nullable=true)
     */
    protected $copiasComprobante;

    public function getTipoPago(){
        $tipo = 'X';
        if( $this->cuentaCorriente ){
            $tipo = 'CTACTE';
        }elseif( $this->tarjeta ){
            $tipo = 'TARJETA';
        }elseif( $this->contado ){
            $tipo = 'EFECTIVO';
        }
        return $tipo;
    }

    public function __toString() {
        return $this->nombre;
    }

    public function getTextSelect(){
        return $this->nombre.' [%'.$this->porcentajeRecargo.']';
    }

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
     * Set nombre
     *
     * @param string $nombre
     * @return FormaPago
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set cuentaCorriente
     *
     * @param boolean $cuentaCorriente
     * @return FormaPago
     */
    public function setCuentaCorriente($cuentaCorriente)
    {
        $this->cuentaCorriente = $cuentaCorriente;

        return $this;
    }

    /**
     * Get cuentaCorriente
     *
     * @return boolean
     */
    public function getCuentaCorriente()
    {
        return $this->cuentaCorriente;
    }

    /**
     * Set tarjeta
     *
     * @param boolean $tarjeta
     * @return FormaPago
     */
    public function setTarjeta($tarjeta)
    {
        $this->tarjeta = $tarjeta;

        return $this;
    }

    /**
     * Get tarjeta
     *
     * @return boolean
     */
    public function getTarjeta()
    {
        return $this->tarjeta;
    }

    /**
     * Set cuotas
     *
     * @param integer $cuotas
     * @return FormaPago
     */
    public function setCuotas($cuotas)
    {
        $this->cuotas = $cuotas;

        return $this;
    }

    /**
     * Get cuotas
     *
     * @return integer
     */
    public function getCuotas()
    {
        return $this->cuotas;
    }

    /**
     * Set vencimiento
     *
     * @param string $vencimiento
     * @return FormaPago
     */
    public function setVencimiento($vencimiento)
    {
        $this->vencimiento = $vencimiento;

        return $this;
    }

    /**
     * Get vencimiento
     *
     * @return string
     */
    public function getVencimiento()
    {
        return $this->vencimiento;
    }

    /**
     * Set plazo
     *
     * @param integer $plazo
     * @return FormaPago
     */
    public function setPlazo($plazo)
    {
        $this->plazo = $plazo;

        return $this;
    }

    /**
     * Get plazo
     *
     * @return integer
     */
    public function getPlazo()
    {
        return $this->plazo;
    }

    /**
     * Set tipoRecargo
     *
     * @param string $tipoRecargo
     * @return FormaPago
     */
    public function setTipoRecargo($tipoRecargo)
    {
        $this->tipoRecargo = $tipoRecargo;

        return $this;
    }

    /**
     * Get tipoRecargo
     *
     * @return string
     */
    public function getTipoRecargo()
    {
        return $this->tipoRecargo;
    }

    /**
     * Set porcentajeRecargo
     *
     * @param string $porcentajeRecargo
     * @return FormaPago
     */
    public function setPorcentajeRecargo($porcentajeRecargo)
    {
        $this->porcentajeRecargo = $porcentajeRecargo;

        return $this;
    }

    /**
     * Get porcentajeRecargo
     *
     * @return string
     */
    public function getPorcentajeRecargo()
    {
        return $this->porcentajeRecargo;
    }

    /**
     * Set descuentoPagoAnticipado
     *
     * @param integer $descuentoPagoAnticipado
     * @return FormaPago
     */
    public function setDescuentoPagoAnticipado($descuentoPagoAnticipado)
    {
        $this->descuentoPagoAnticipado = $descuentoPagoAnticipado;

        return $this;
    }

    /**
     * Get descuentoPagoAnticipado
     *
     * @return integer
     */
    public function getDescuentoPagoAnticipado()
    {
        return $this->descuentoPagoAnticipado;
    }

    /**
     * Set mora
     *
     * @param string $mora
     * @return FormaPago
     */
    public function setMora($mora)
    {
        $this->mora = $mora;

        return $this;
    }

    /**
     * Get mora
     *
     * @return string
     */
    public function getMora()
    {
        return $this->mora;
    }

    /**
     * Set diasMora
     *
     * @param integer $diasMora
     * @return FormaPago
     */
    public function setDiasMora($diasMora)
    {
        $this->diasMora = $diasMora;

        return $this;
    }

    /**
     * Get diasMora
     *
     * @return integer
     */
    public function getDiasMora()
    {
        return $this->diasMora;
    }

    /**
     * Set copiasComprobante
     *
     * @param integer $copiasComprobante
     * @return FormaPago
     */
    public function setCopiasComprobante($copiasComprobante)
    {
        $this->copiasComprobante = $copiasComprobante;

        return $this;
    }

    /**
     * Get copiasComprobante
     *
     * @return integer
     */
    public function getCopiasComprobante()
    {
        return $this->copiasComprobante;
    }

    /**
     * Set contado
     *
     * @param boolean $contado
     * @return FormaPago
     */
    public function setContado($contado)
    {
        $this->contado = $contado;

        return $this;
    }

    /**
     * Get contado
     *
     * @return boolean
     */
    public function getContado()
    {
        return $this->contado;
    }
}
