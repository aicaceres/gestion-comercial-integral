<?php

namespace ComprasBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ComprasBundle\Entity\NotaDebCredDetalle
 * @ORM\Table(name="compras_nota_debcred_detalle")
 * @ORM\Entity()
 */
class NotaDebCredDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $orden
     * @ORM\Column(name="orden", type="integer",nullable=true)
     */
    protected $orden;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto")
     * @ORM\JoinColumn(name="producto_id", referencedColumnName="id")
     */
    protected $producto;

    /**
     * @var integer $textoProducto
     * @ORM\Column(name="texto_producto", type="string",nullable=true)
     */
    protected $textoProducto;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", precision=20, scale=2)
     */
    protected $cantidad;

    /**
     * @ORM\Column(name="bulto", type="boolean", nullable=true)
     */
    protected $bulto = false;

    /**
     * @var integer $cantidadxBulto
     * @ORM\Column(name="cantidad_x_bulto", type="integer", nullable=true )
     */
    protected $cantidadxBulto;

    /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", precision=20, scale=2 )
     */
    protected $precio;

    /**
     * @var integer $iva
     * @ORM\Column(name="iva", type="decimal", precision=20, scale=2 )
     */
    protected $iva = 21;

    /**
     * @var integer $descuento
     * @ORM\Column(name="descuento", type="decimal", precision=20, scale=2 )
     */
    protected $descuento = 0;

    /**
     * @ORM\ManyToOne(targetEntity="ComprasBundle\Entity\NotaDebCred", inversedBy="detalles")
     * @ORM\JoinColumn(name="compras_nota_debcred_id", referencedColumnName="id")
     */
    protected $notaDebCred;

    /*
     * DATOS PARA AFIP
     */

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\AfipAlicuota")
     * @ORM\JoinColumn(name="afip_alicuota_id", referencedColumnName="id")
     * */
    protected $afipAlicuota;

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

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return NotaDebCredDetalle
     */
    public function setOrden($orden) {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer
     */
    public function getOrden() {
        return $this->orden;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return NotaDebCredDetalle
     */
    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer
     */
    public function getCantidad() {
        return $this->cantidad;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return NotaDebCredDetalle
     */
    public function setPrecio($precio) {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio() {
        return $this->precio;
    }

    /**
     * Set iva
     *
     * @param string $iva
     * @return NotaDebCredDetalle
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
     * Set descuento
     *
     * @param string $descuento
     * @return NotaDebCredDetalle
     */
    public function setDescuento($descuento) {
        $this->descuento = $descuento;

        return $this;
    }

    /**
     * Get descuento
     *
     * @return string
     */
    public function getDescuento() {
        return $this->descuento;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     * @return NotaDebCredDetalle
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null) {
        $this->producto = $producto;
        return $this;
    }

    /**
     * Get producto
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto() {
        return $this->producto;
    }

    /**
     * Set notaDebCred
     *
     * @param \ComprasBundle\Entity\NotaDebCred $notaDebCred
     * @return NotaDebCredDetalle
     */
    public function setNotaDebCred(\ComprasBundle\Entity\NotaDebCred $notaDebCred = null) {
        $this->notaDebCred = $notaDebCred;

        return $this;
    }

    /**
     * Get notaDebCred
     *
     * @return \ComprasBundle\Entity\NotaDebCred
     */
    public function getNotaDebCred() {
        return $this->notaDebCred;
    }

    /** Calculos * */
    public function getSubTotal() {
        return $this->precio * $this->cantidad;
    }

    public function getMontoDescuento() {
        return $this->getSubTotal() * ($this->descuento / 100);
    }

    public function getMontoIva() {
        $alic = $this->getAfipAlicuota()->getValor();
        return ( $this->getSubTotal() - $this->getMontoDescuento() ) * ($alic / 100);
    }

    public function getMontoIvaItem() {
        $alic = $this->getAfipAlicuota()->getValor();
        return $this->getMontoNetoItem() * ($alic / 100);
    }

    public function getMontoNetoItem() {
        $porcBonif = ($this->getNotaDebCred()->getTotalBonificado() * 100 ) / $this->getNotaDebCred()->getSubtotalNeto();
        $neto = $this->getSubTotal() * (1 - ($porcBonif / 100));
        return $neto;
    }

    public function getTotal() {
        return ($this->getSubTotal() - $this->getMontoDescuento()) + $this->getMontoIva();
    }

    // Cantidad total de items
    public function getCantidadTotal() {
        if ($this->bulto) {
            return $this->cantidad * $this->cantidadxBulto;
        }
        else {
            return $this->cantidad;
        }
    }

    // Cantidad para mostrar como texto
    public function getCantidadTxt() {
        if ($this->bulto) {
            return $this->getCantidad() . ' x ' . $this->getCantidadxBulto() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
        else {
            return $this->getCantidad() . ' ' . $this->getProducto()->getUnidadMedida()->getNombre();
        }
    }

    /**
     * Set bulto
     *
     * @param boolean $bulto
     * @return NotaDebCredDetalle
     */
    public function setBulto($bulto) {
        $this->bulto = $bulto;

        return $this;
    }

    /**
     * Get bulto
     *
     * @return boolean
     */
    public function getBulto() {
        return $this->bulto;
    }

    /**
     * Set cantidadxBulto
     *
     * @param integer $cantidadxBulto
     * @return NotaDebCredDetalle
     */
    public function setCantidadxBulto($cantidadxBulto) {
        $this->cantidadxBulto = $cantidadxBulto;

        return $this;
    }

    /**
     * Get cantidadxBulto
     *
     * @return integer
     */
    public function getCantidadxBulto() {
        return $this->cantidadxBulto;
    }

    /**
     * Set textoProducto
     *
     * @param string $textoProducto
     * @return NotaDebCredDetalle
     */
    public function setTextoProducto($textoProducto) {
        $this->textoProducto = $textoProducto;

        return $this;
    }

    /**
     * Get textoProducto
     *
     * @return string
     */
    public function getTextoProducto() {
        return $this->textoProducto;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return NotaDebCredDetalle
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
     * @return NotaDebCredDetalle
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return NotaDebCredDetalle
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
     * @return NotaDebCredDetalle
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
     * Set afipAlicuota
     *
     * @param \ConfigBundle\Entity\AfipAlicuota $afipAlicuota
     * @return NotaDebCredDetalle
     */
    public function setAfipAlicuota(\ConfigBundle\Entity\AfipAlicuota $afipAlicuota = null) {
        $this->afipAlicuota = $afipAlicuota;

        return $this;
    }

    /**
     * Get afipAlicuota
     *
     * @return \ConfigBundle\Entity\AfipAlicuota
     */
    public function getAfipAlicuota() {
        return $this->afipAlicuota;
    }

}