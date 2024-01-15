<?php

namespace VentasBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * VentasBundle\Entity\PagoClienteComprobante
 * @ORM\Table(name="ventas_pago_cliente_comprobante")
 * @ORM\Entity(repositoryClass="VentasBundle\Entity\ClienteRepository")
 */
class PagoClienteComprobante {
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
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\PagoCliente", inversedBy="comprobantes")
     * @ORM\JoinColumn(name="pago_cliente_id", referencedColumnName="id")
     */
    protected $pago;

    /**
     * @ORM\ManyToOne(targetEntity="VentasBundle\Entity\FacturaElectronica", inversedBy="pagos")
     * @ORM\JoinColumn(name="ventas_factura_electronica_id", referencedColumnName="id")
     */
    protected $comprobante;

    public function getComprobanteTxt(){
      $comp = $this->getComprobante();
      $simbolo = $comp->getCobro()
        ? $comp->getCobro()->getMoneda()->getSimbolo()
        : $comp->getNotaDebCred()->getMoneda()->getSimbolo();
        return $comp->getTipoComprobante()->getValor() . ' ' . $comp->getNroComprobanteTxt()  . ' | Monto: ' . $simbolo . $this->getMonto();
    }

    public function getComprobanteCtaCtePendienteTxt(){
      return $this->getComprobante()->getComprobanteCtaCtePendienteTxt();
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
     * @return PagoClienteComprobante
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
     * @return PagoClienteComprobante
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
     * Set comprobante
     *
     * @param \VentasBundle\Entity\FacturaElectronica $comprobante
     * @return PagoClienteComprobante
     */
    public function setComprobante(\VentasBundle\Entity\FacturaElectronica $comprobante = null)
    {
        $this->comprobante = $comprobante;

        return $this;
    }

    /**
     * Get comprobante
     *
     * @return \VentasBundle\Entity\FacturaElectronica
     */
    public function getComprobante()
    {
        return $this->comprobante;
    }
}
