<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BookingReturn
 *
 * @ORM\Table(name="warehouse_booking_return")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingReturnRepository")
 */
class BookingReturn
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
     * One BookingReturn has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many BookingReturn have One Booking
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="returns")
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     */
    private $booking;

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
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * One BookingReturn has Many BookingReturnProduct.
     * @ORM\OneToMany(targetEntity="BookingReturnProduct", mappedBy="bookingReturn")
     */
    private $products;

    /**
     * One BookingReturn has Many BookingReturnComment.
     * @ORM\OneToMany(targetEntity="BookingReturnComment", mappedBy="return")
     */
    private $comments;

    public function __construct() {
        $this->comments = new ArrayCollection();
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BookingReturn
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
     * @return BookingReturn
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
     * @return BookingReturn
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
     * @return BookingReturn
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
     * Add product
     *
     * @param \WarehouseBundle\Entity\BookingReturnProduct $returnProduct
     *
     * @return BookingContact
     */
    public function addProduct(\WarehouseBundle\Entity\BookingReturnProduct $returnProduct)
    {
        $this->products[] = $returnProduct;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \WarehouseBundle\Entity\BookingContactCommunication $returnProduct
     */
    public function removeProduct(\WarehouseBundle\Entity\BookingReturnProduct $returnProduct)
    {
        $this->products->removeElement($returnProduct);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add comment
     *
     * @param \WarehouseBundle\Entity\BookingReturnComment $comment
     *
     * @return BookingReturn
     */
    public function addComment(\WarehouseBundle\Entity\BookingReturnComment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \WarehouseBundle\Entity\BookingReturnComment $comment
     */
    public function removeComment(\WarehouseBundle\Entity\BookingReturnComment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return BookingReturn
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
