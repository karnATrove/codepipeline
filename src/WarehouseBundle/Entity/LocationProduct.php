<?php

namespace WarehouseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * LocationProduct
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_location_product")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\LocationProductRepository")
 */
class LocationProduct
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
     * One LocationProduct has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many Locations have One Product
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="locations")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * Many LocationProduct have One location.
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="products")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    private $location;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="on_hand", type="integer")
     */
    private $onHand;

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
     * Set onHand
     *
     * @param integer $onHand
     *
     * @return LocationProduct
     */
    public function setOnHand($onHand)
    {
        $this->onHand = $onHand;

        return $this;
    }

    /**
     * Get onHand
     *
     * @return int
     */
    public function getOnHand()
    {
        return $this->onHand;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return LocationProduct
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
     * @return LocationProduct
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
     * Set product
     *
     * @param \WarehouseBundle\Entity\Product $product
     *
     * @return Location
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
     * Set location
     *
     * @param \WarehouseBundle\Entity\Location $location
     *
     * @return Location
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
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return LocationProduct
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
