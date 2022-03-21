<?php
namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\Tarjeta
 * @ORM\Table(name="tarjeta")
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ParametroRepository")
 * @UniqueEntity(
 *     fields={"nombre"},
 *     errorPath="nombre",
 *     message="Registro duplicado."
 * )
 */
class Tarjeta {
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
     * @var integer $maximoCuotas
     * @ORM\Column(name="maximo_cuotas", type="integer", nullable=true)
     */
    protected $maximoCuotas;
   /**
     * @var float $limiteSinAutorizacion
     * @ORM\Column(name="limite_sin_autorizacion", type="decimal", scale=2, nullable=true)
     */
    protected $limiteSinAutorizacion;
    /**
     * @var boolean $presentarAlFacturar
     * @ORM\Column(name="presentar_al_facturar", type="boolean", nullable=true)
     */
    protected $presentarAlFacturar = false;
    /**
     * @var string $presentar
     * @ORM\Column(name="presentar", type="string", nullable=true)
     */
    protected $presentar;
   /**
     * @var integer $diaPresentar
     * @ORM\Column(name="dia_presentar", type="integer", nullable=true)
     */
    protected $diaPresentar;
    /**
     * @var string $tipoCobro
     * @ORM\Column(name="tipo_cobro", type="string", nullable=true)
     */
    protected $tipoCobro;
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Banco")
     * @ORM\JoinColumn(name="banco_id", referencedColumnName="id")
     */
    protected $banco;
    /**
     * @var string $cuenta
     * @ORM\Column(name="cuenta", type="string", nullable=true)
     */
    protected $cuenta;
   /**
     * @var integer $diaParaCobrar
     * @ORM\Column(name="dia_para_cobrar", type="integer", nullable=true)
     */
    protected $diaParaCobrar;
   /**
     * @var integer $comisionTarjeta
     * @ORM\Column(name="comision_tarjeta", type="integer", nullable=true)
     */
    protected $comisionTarjeta;

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
     * @return Tarjeta
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
     * Set maximoCuotas
     *
     * @param integer $maximoCuotas
     * @return Tarjeta
     */
    public function setMaximoCuotas($maximoCuotas)
    {
        $this->maximoCuotas = $maximoCuotas;

        return $this;
    }

    /**
     * Get maximoCuotas
     *
     * @return integer 
     */
    public function getMaximoCuotas()
    {
        return $this->maximoCuotas;
    }

    /**
     * Set limiteSinAutorizacion
     *
     * @param string $limiteSinAutorizacion
     * @return Tarjeta
     */
    public function setLimiteSinAutorizacion($limiteSinAutorizacion)
    {
        $this->limiteSinAutorizacion = $limiteSinAutorizacion;

        return $this;
    }

    /**
     * Get limiteSinAutorizacion
     *
     * @return string 
     */
    public function getLimiteSinAutorizacion()
    {
        return $this->limiteSinAutorizacion;
    }

    /**
     * Set presentarAlFacturar
     *
     * @param boolean $presentarAlFacturar
     * @return Tarjeta
     */
    public function setPresentarAlFacturar($presentarAlFacturar)
    {
        $this->presentarAlFacturar = $presentarAlFacturar;

        return $this;
    }

    /**
     * Get presentarAlFacturar
     *
     * @return boolean 
     */
    public function getPresentarAlFacturar()
    {
        return $this->presentarAlFacturar;
    }

    /**
     * Set presentar
     *
     * @param string $presentar
     * @return Tarjeta
     */
    public function setPresentar($presentar)
    {
        $this->presentar = $presentar;

        return $this;
    }

    /**
     * Get presentar
     *
     * @return string 
     */
    public function getPresentar()
    {
        return $this->presentar;
    }

    /**
     * Set diaPresentar
     *
     * @param integer $diaPresentar
     * @return Tarjeta
     */
    public function setDiaPresentar($diaPresentar)
    {
        $this->diaPresentar = $diaPresentar;

        return $this;
    }

    /**
     * Get diaPresentar
     *
     * @return integer 
     */
    public function getDiaPresentar()
    {
        return $this->diaPresentar;
    }

    /**
     * Set tipoCobro
     *
     * @param string $tipoCobro
     * @return Tarjeta
     */
    public function setTipoCobro($tipoCobro)
    {
        $this->tipoCobro = $tipoCobro;

        return $this;
    }

    /**
     * Get tipoCobro
     *
     * @return string 
     */
    public function getTipoCobro()
    {
        return $this->tipoCobro;
    }

    /**
     * Set cuenta
     *
     * @param string $cuenta
     * @return Tarjeta
     */
    public function setCuenta($cuenta)
    {
        $this->cuenta = $cuenta;

        return $this;
    }

    /**
     * Get cuenta
     *
     * @return string 
     */
    public function getCuenta()
    {
        return $this->cuenta;
    }

    /**
     * Set diaParaCobrar
     *
     * @param integer $diaParaCobrar
     * @return Tarjeta
     */
    public function setDiaParaCobrar($diaParaCobrar)
    {
        $this->diaParaCobrar = $diaParaCobrar;

        return $this;
    }

    /**
     * Get diaParaCobrar
     *
     * @return integer 
     */
    public function getDiaParaCobrar()
    {
        return $this->diaParaCobrar;
    }

    /**
     * Set comisionTarjeta
     *
     * @param integer $comisionTarjeta
     * @return Tarjeta
     */
    public function setComisionTarjeta($comisionTarjeta)
    {
        $this->comisionTarjeta = $comisionTarjeta;

        return $this;
    }

    /**
     * Get comisionTarjeta
     *
     * @return integer 
     */
    public function getComisionTarjeta()
    {
        return $this->comisionTarjeta;
    }

    /**
     * Set banco
     *
     * @param \ConfigBundle\Entity\Banco $banco
     * @return Tarjeta
     */
    public function setBanco(\ConfigBundle\Entity\Banco $banco = null)
    {
        $this->banco = $banco;

        return $this;
    }

    /**
     * Get banco
     *
     * @return \ConfigBundle\Entity\Banco 
     */
    public function getBanco()
    {
        return $this->banco;
    }
}
