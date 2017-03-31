<?php

namespace WarehouseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * IncomingProductScan
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_incoming_product_scan")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingProductScanRepository")
 */
class IncomingProductScan
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One IncomingProductScan has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many IncomingProductScan have One IncomingProduct
     * @ORM\ManyToOne(targetEntity="IncomingProduct")
     * @ORM\JoinColumn(name="incoming_product_id", referencedColumnName="id")
     */
    private $incomingProduct;

    /**
     * Many IncomingProductScans have One Incoming
     * @ORM\ManyToOne(targetEntity="Incoming", inversedBy="incoming_scanned_products")
     * @ORM\JoinColumn(name="incoming_id", referencedColumnName="id")
     */
    private $incoming;

    /**
     * Many IncomingProductScans have One Location
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    private $location;

    /**
     * Many IncomingProductScans have One Product
     * @ORM\ManyToOne(targetEntity="Product",)
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="qty_on_scan", type="integer")
     */
    private $qtyOnScan;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set qtyOnScan
     *
     * @param integer $qtyOnScan
     *
     * @return IncomingProductScan
     */
    public function setQtyOnScan($qtyOnScan)
    {
        $this->qtyOnScan = $qtyOnScan;

        return $this;
    }

    /**
     * Get qtyOnScan
     *
     * @return int
     */
    public function getQtyOnScan()
    {
        return $this->qtyOnScan;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return IncomingProductScan
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return IncomingProductScan
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set incomingProduct
     *
     * @param \WarehouseBundle\Entity\IncomingProduct $incomingProduct
     *
     * @return IncomingProductScan
     */
    public function setIncomingProduct(\WarehouseBundle\Entity\IncomingProduct $incomingProduct = null)
    {
        $this->incomingProduct = $incomingProduct;

        return $this;
    }

    /**
     * Get incomingProduct
     *
     * @return \WarehouseBundle\Entity\IncomingProduct
     */
    public function getIncomingProduct()
    {
        return $this->incomingProduct;
    }

    /**
     * Set incoming
     *
     * @param \WarehouseBundle\Entity\Incoming $incoming
     *
     * @return IncomingProductScan
     */
    public function setIncoming(\WarehouseBundle\Entity\Incoming $incoming = null)
    {
        $this->incoming = $incoming;

        return $this;
    }

    /**
     * Get incoming
     *
     * @return \WarehouseBundle\Entity\Incoming
     */
    public function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * Set location
     *
     * @param \WarehouseBundle\Entity\Location $location
     *
     * @return IncomingProductScan
     */
    public function setLocation(\WarehouseBundle\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \WarehouseBundle\Entity\Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set product
     *
     * @param \WarehouseBundle\Entity\Product $product
     *
     * @return IncomingProductScan
     */
    public function setProduct(\WarehouseBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \WarehouseBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return IncomingProductScan
     */
    public function setUser(\WarehouseBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \WarehouseBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
