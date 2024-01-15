<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\PagoClienteRecibo
 * @ORM\Table(name="ventas_pago_cliente_recibo")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\ClienteRepository")
 */
class PagoClienteRecibo {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="monto", type="decimal", precision=20, scale=2 )
     */
    protected $monto = 0;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\PagoCliente", inversedBy="recibos")
     * @ORM\JoinColumn(name="pago_cliente_id", referencedColumnName="id")
     */
    protected $pago;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\PagoCliente", inversedBy="pagos")
     * @ORM\JoinColumn(name="recibo_id", referencedColumnName="id")
     */
    protected $recibo;

    public function getComprobanteTxt(){
      $recibo = $this->getRecibo();
      $simbolo = $recibo->getMoneda()->getSimbolo();
        return $recibo->getComprobanteNro() . ' | Monto: ' . $simbolo . $this->getMonto();
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
     * Set monto
     *
     * @param string $monto
     * @return PagoClienteRecibo
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * Get monto
     *
     * @return string
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * Set pago
     *
     * @param \VentasBundle\Entity\PagoCliente $pago
     * @return PagoClienteRecibo
     */
    public function setPago(\VentasBundle\Entity\PagoCliente $pago = null)
    {
        $this->pago = $pago;

        return $this;
    }

    /**
     * Get pago
     *
     * @return \VentasBundle\Entity\PagoCliente
     */
    public function getPago()
    {
        return $this->pago;
    }

    /**
     * Set recibo
     *
     * @param \VentasBundle\Entity\PagoCliente $recibo
     * @return PagoClienteRecibo
     */
    public function setRecibo(\VentasBundle\Entity\PagoCliente $recibo = null)
    {
        $this->recibo = $recibo;

        return $this;
    }

    /**
     * Get recibo
     *
     * @return \VentasBundle\Entity\PagoCliente
     */
    public function getRecibo()
    {
        return $this->recibo;
    }
}
