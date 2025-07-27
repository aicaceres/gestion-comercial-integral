<?php

namespace ConfigBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigBundle\Entity\AfipCondicionIvaReceptor
 * @ORM\Table(name="afip_condicion_iva_receptor")
 * @ORM\Entity()
 */
class AfipCondicionIvaReceptor {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * ARCA Id
     * @var string $codigo
     * @ORM\Column(name="codigo", type="string", nullable=false)
     */
    protected $codigo;

    /**
     * ARCA Desc
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     */
    protected $nombre;

    /**
     * ARCA Cmp_Clase
     * @var string $claseComprobante
     * @ORM\Column(name="clase_comprobante", type="string", nullable=false)
     */
    protected $claseComprobante;

    /**
     * Equivalencia con sit-impositiva
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Parametro")
     * @ORM\JoinColumn(name="categoria_iva_id", referencedColumnName="id", onDelete="SET NULL")
     * */
    protected $categoriaIva;

    /**
     * TICKET - Responsabilidad ante IVA
     * @var string $ticket
     * @ORM\Column(name="ticket", type="string", nullable=false)
     */
    protected $ticket;

    /**
     * @var string $activo
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    protected $activo = true;

    public function esConsumidorFinal(){
      return $this->codigo === 'C';
    }

    public function tieneCondicion($letra){
      return $this->codigo === $letra;
    }

    public function esExento(){
      return $this->codigo === 'E';
    }

    public function esInscripto(){
      return $this->codigo === 'I';
    }

    public function esMonotributo(){
      return $this->codigo === 'I';
    }

    public function emiteFacturaTipo($tipo){
      return strpos($this->claseComprobante, $tipo) !== false;
    }

    public function __toString() {
        return $this->nombre;
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
     * Set codigo
     *
     * @param string $codigo
     * @return AfipCondicionIvaReceptor
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return AfipCondicionIvaReceptor
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
     * Set claseComprobante
     *
     * @param string $claseComprobante
     * @return AfipCondicionIvaReceptor
     */
    public function setClaseComprobante($claseComprobante)
    {
        $this->claseComprobante = $claseComprobante;

        return $this;
    }

    /**
     * Get claseComprobante
     *
     * @return string
     */
    public function getClaseComprobante()
    {
        return $this->claseComprobante;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return AfipCondicionIvaReceptor
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set categoriaIva
     *
     * @param \ConfigBundle\Entity\Parametro $categoriaIva
     * @return AfipCondicionIvaReceptor
     */
    public function setCategoriaIva(\ConfigBundle\Entity\Parametro $categoriaIva = null)
    {
        $this->categoriaIva = $categoriaIva;

        return $this;
    }

    /**
     * Get categoriaIva
     *
     * @return \ConfigBundle\Entity\Parametro
     */
    public function getCategoriaIva()
    {
        return $this->categoriaIva;
    }

    /**
     * Set ticket
     *
     * @param string $ticket
     * @return AfipCondicionIvaReceptor
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return string 
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
