<?php

namespace VentasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * VentasBundle\Entity\CobroDetalleTarjeta
 * @ORM\Table(name="ventas_cobro_detalle_tarjeta")
 * @ORM\Entity()
 * @Gedmo\Loggable()
 */
class CobroDetalleTarjeta {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Tarjeta")
     * @ORM\JoinColumn(name="tarjeta_id", referencedColumnName="id")
     * @ORM\OrderBy({"nombre" = "ASC"})
     * @Gedmo\Versioned()
     */
    protected $tarjeta;

    /**
     * @var string $cupon
     * @ORM\Column(name="cupon", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $cupon;

    /**
     * @var string $cuota
     * @ORM\Column(name="cuota", type="integer", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $cuota;

    /**
     * @var string $numero
     * @ORM\Column(name="numero", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $numero;

    /**
     * @var string $firmante
     * @ORM\Column(name="firmante", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $firmante;

    /**
     * @ORM\Column(name="presentar_al_facturar", type="boolean",nullable=true)
     * @Gedmo\Versioned()
     */
    protected $presentarAlFacturar = false;

    /**
     * @var date $presentar
     * @ORM\Column(name="presentar", type="date", nullable=false)
     * @Gedmo\Versioned()
     */
    protected $presentar;

    /**
     * @var string $codigoAutorizacion
     * @ORM\Column(name="codigo_autorizacion", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $codigoAutorizacion;

    /**
     * Constructor
     */
    public function __construct() {
        $this->presentar = new \DateTime();
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
     * Set cupon
     *
     * @param string $cupon
     * @return CobroDetalleTarjeta
     */
    public function setCupon($cupon) {
        $this->cupon = $cupon;

        return $this;
    }

    /**
     * Get cupon
     *
     * @return string
     */
    public function getCupon() {
        return $this->cupon;
    }

    /**
     * Set cuota
     *
     * @param integer $cuota
     * @return CobroDetalleTarjeta
     */
    public function setCuota($cuota) {
        $this->cuota = $cuota;

        return $this;
    }

    /**
     * Get cuota
     *
     * @return integer
     */
    public function getCuota() {
        return $this->cuota;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return CobroDetalleTarjeta
     */
    public function setNumero($numero) {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * Set firmante
     *
     * @param string $firmante
     * @return CobroDetalleTarjeta
     */
    public function setFirmante($firmante) {
        $this->firmante = $firmante;

        return $this;
    }

    /**
     * Get firmante
     *
     * @return string
     */
    public function getFirmante() {
        return $this->firmante;
    }

    /**
     * Set presentarAlFacturar
     *
     * @param boolean $presentarAlFacturar
     * @return CobroDetalleTarjeta
     */
    public function setPresentarAlFacturar($presentarAlFacturar) {
        $this->presentarAlFacturar = $presentarAlFacturar;

        return $this;
    }

    /**
     * Get presentarAlFacturar
     *
     * @return boolean
     */
    public function getPresentarAlFacturar() {
        return $this->presentarAlFacturar;
    }

    /**
     * Set presentar
     *
     * @param \DateTime $presentar
     * @return CobroDetalleTarjeta
     */
    public function setPresentar($presentar) {
        $this->presentar = $presentar;

        return $this;
    }

    /**
     * Get presentar
     *
     * @return \DateTime
     */
    public function getPresentar() {
        return $this->presentar;
    }

    /**
     * Set codigoAutorizacion
     *
     * @param string $codigoAutorizacion
     * @return CobroDetalleTarjeta
     */
    public function setCodigoAutorizacion($codigoAutorizacion) {
        $this->codigoAutorizacion = $codigoAutorizacion;

        return $this;
    }

    /**
     * Get codigoAutorizacion
     *
     * @return string
     */
    public function getCodigoAutorizacion() {
        return $this->codigoAutorizacion;
    }

    /**
     * Set tarjeta
     *
     * @param \ConfigBundle\Entity\Tarjeta $tarjeta
     * @return CobroDetalleTarjeta
     */
    public function setTarjeta(\ConfigBundle\Entity\Tarjeta $tarjeta = null) {
        $this->tarjeta = $tarjeta;

        return $this;
    }

    /**
     * Get tarjeta
     *
     * @return \ConfigBundle\Entity\Tarjeta
     */
    public function getTarjeta() {
        return $this->tarjeta;
    }

}