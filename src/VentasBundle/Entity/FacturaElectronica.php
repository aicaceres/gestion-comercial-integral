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
     * @var integer $puntoVenta
     * @ORM\Column(name="punto_venta", type="integer")
     */
    protected $puntoVenta;
    /**
     * @var integer $tipoComprobante
     * @ORM\Column(name="tipo_comprobante", type="string")
     */
    protected $tipoComprobante;
    /**
     * @var integer $nroComprobante
     * voucher_number
     * @ORM\Column(name="nro_comprobante", type="integer")
     */
    protected $nroComprobante;
    /**
     * @var integer $cae
     * @ORM\Column(name="cae", type="string", length=14)
     */
    protected $cae;
    /**
     * @var integer $caeVto
     * CAEFchVto
     * @ORM\Column(name="cae_vto", type="string", length=10)
     */
    protected $caeVto;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set puntoVenta
     *
     * @param integer $puntoVenta
     * @return FacturaElectronica
     */
    public function setPuntoVenta($puntoVenta)
    {
        $this->puntoVenta = $puntoVenta;

        return $this;
    }

    /**
     * Get puntoVenta
     *
     * @return integer
     */
    public function getPuntoVenta()
    {
        return $this->puntoVenta;
    }

    /**
     * Set tipoComprobante
     *
     * @param string $tipoComprobante
     * @return FacturaElectronica
     */
    public function setTipoComprobante($tipoComprobante)
    {
        $this->tipoComprobante = $tipoComprobante;

        return $this;
    }

    /**
     * Get tipoComprobante
     *
     * @return string
     */
    public function getTipoComprobante()
    {
        return $this->tipoComprobante;
    }

    /**
     * Set nroComprobante
     *
     * @param integer $nroComprobante
     * @return FacturaElectronica
     */
    public function setNroComprobante($nroComprobante)
    {
        $this->nroComprobante = $nroComprobante;

        return $this;
    }

    /**
     * Get nroComprobante
     *
     * @return integer
     */
    public function getNroComprobante()
    {
        return $this->nroComprobante;
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
     * Set cobro
     *
     * @param \VentasBundle\Entity\Cobro $cobro
     * @return FacturaElectronica
     */
    public function setCobro(\VentasBundle\Entity\Cobro $cobro = null)
    {
        $this->cobro = $cobro;

        return $this;
    }

    /**
     * Get cobro
     *
     * @return \VentasBundle\Entity\Cobro
     */
    public function getCobro()
    {
        return $this->cobro;
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
}
