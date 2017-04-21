<?php

namespace WarehouseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * BookingProduct
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_booking_product")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingProductRepository")
 */
class BookingProduct
{
	//region Constants

	//status
	const STATUS_DELETED=0;
	const STATUS_PENDING=1;
	const STATUS_IN_PROGRESS=2;
	const STATUS_PICKED=3;

	//endregion


    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One BookingProduct has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many BookingProduct have One Booking
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="products")
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     */
    private $booking;

    /**
     * Many BookingProduct have One Product (Unidirectional)
     * @ORM\ManyToOne(targetEntity="Product",cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ORM\Column(name="picked_date", type="datetime", nullable=true)
     */
    private $pickedDate;

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
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="status", type="smallint")
     */
    private $status = 1;

    /**
     * Constructor and set defaults.
     */
    public function __construct() {
        # Defaults
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
     * @return BookingProduct
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
     * Set pickedDate
     *
     * @param \DateTime $pickedDate
     *
     * @return BookingProduct
     */
    public function setPickedDate($pickedDate)
    {
        $this->pickedDate = $pickedDate;

        return $this;
    }

    /**
     * Get pickedDate
     *
     * @return \DateTime
     */
    public function getPickedDate()
    {
        return $this->pickedDate;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BookingProduct
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
     * @return BookingProduct
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
     * Set status
     *
     * @param integer $status
     *
     * @return BookingProduct
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set booking
     *
     * @param \WarehouseBundle\Entity\Booking $booking
     *
     * @return BookingProduct
     */
    public function setBooking(\WarehouseBundle\Entity\Booking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \WarehouseBundle\Entity\Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * Set product
     *
     * @param \WarehouseBundle\Entity\Product $product
     *
     * @return BookingProduct
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
     * @return BookingProduct
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

    public function __toString() {
        return 'BookingProduct';
    }
}
