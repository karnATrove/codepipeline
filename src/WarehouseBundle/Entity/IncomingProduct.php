<?php

namespace WarehouseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * IncomingProduct
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_incoming_product")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingProductRepository")
 */
class IncomingProduct
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
     * One IncomingProduct has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many IncomingProducts have One Incoming
     * @ORM\ManyToOne(targetEntity="Incoming", inversedBy="incoming_products")
     * @ORM\JoinColumn(name="incoming_id", referencedColumnName="id")
     */
    private $incoming;

    /**
     * Many IncomingProducts have One Product
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="incoming")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var int
     * 
     * @ORM\Column(name="container_id", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $containerId;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="model", type="string", length=45)
     */
    private $model;

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
     * Set containerId
     *
     * @param integer $containerId
     *
     * @return IncomingProduct
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get containerId
     *
     * @return int
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set qty
     *
     * @param integer $qty
     *
     * @return IncomingProduct
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty
     *
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set model
     *
     * @param string $model
     *
     * @return IncomingProduct
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set incoming
     *
     * @param \WarehouseBundle\Entity\Incoming $incoming
     *
     * @return IncomingProduct
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
     * Set product
     *
     * @param \WarehouseBundle\Entity\Product $product
     *
     * @return IncomingProduct
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return IncomingProduct
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
     * @return IncomingProduct
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
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return IncomingProduct
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
