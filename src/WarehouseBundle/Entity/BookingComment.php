<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingComment
 *
 * @ORM\Table(name="warehouse_booking_comment")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingCommentRepository")
 */
class BookingComment implements IEntity
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
     * One BookingComment has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many BookingFile have One Booking
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="comments")
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     */
    private $booking;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * Constructor to initiate defaults.
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
     * Set comment
     *
     * @param string $comment
     *
     * @return BookingComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BookingComment
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
     * Set booking
     *
     * @param \WarehouseBundle\Entity\Booking $booking
     *
     * @return BookingComment
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
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return BookingComment
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
