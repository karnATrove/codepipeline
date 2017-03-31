<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingReturnProduct
 *
 * @ORM\Table(name="warehouse_booking_return_product")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingReturnProductRepository")
 */
class BookingReturnProduct
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
     * Many BookingReturnProduct have One BookingReturn
     * @ORM\ManyToOne(targetEntity="BookingReturn", inversedBy="products")
     * @ORM\JoinColumn(name="return_id", referencedColumnName="id")
     */
    private $bookingReturn;

    /**
     * Many BookingReturnProduct have One Product (Unidirectional)
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


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
     * @return BookingReturnProduct
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
     * Set status
     *
     * @param integer $status
     *
     * @return BookingReturnProduct
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BookingReturnProduct
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
     * Set bookingReturn
     *
     * @param \WarehouseBundle\Entity\BookingReturn $bookingReturn
     *
     * @return BookingReturnProduct
     */
    public function setBookingReturn(\WarehouseBundle\Entity\BookingReturn $bookingReturn = null)
    {
        $this->bookingReturn = $bookingReturn;

        return $this;
    }

    /**
     * Get bookingReturn
     *
     * @return \WarehouseBundle\Entity\BookingReturn
     */
    public function getBookingReturn()
    {
        return $this->bookingReturn;
    }

    /**
     * Set product
     *
     * @param \WarehouseBundle\Entity\Product $product
     *
     * @return BookingReturnProduct
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
}
