<?php

namespace WarehouseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * BookingProductLocation
 *
 * Represents booking product pick history.
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_booking_product_location")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingProductLocationRepository")
 */
class BookingProductLocation
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
     * One BookingProductLocation has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many BookingProductLocation have One BookingProduct.
     * @ORM\ManyToOne(targetEntity="BookingProduct")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $bookingProduct;

    /**
     * Many BookingProductLocation have One Location.
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $location;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * Construct and setup defaults.
     */
    public function __construct() {
        $this->created = new \DateTime('now');
    }

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
     * Set qty
     *
     * @param integer $qty
     *
     * @return BookingProductLocation
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BookingProductLocation
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
     * Set bookingProduct
     *
     * @param \WarehouseBundle\Entity\BookingProduct $bookingProduct
     *
     * @return BookingProductLocation
     */
    public function setBookingProduct(\WarehouseBundle\Entity\BookingProduct $bookingProduct = null)
    {
        $this->bookingProduct = $bookingProduct;

        return $this;
    }

    /**
     * Get bookingProduct
     *
     * @return \WarehouseBundle\Entity\BookingProduct
     */
    public function getBookingProduct()
    {
        return $this->bookingProduct;
    }

    /**
     * Set location
     *
     * @param \WarehouseBundle\Entity\Location $location
     *
     * @return BookingProductLocation
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
     * @return BookingLog
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
