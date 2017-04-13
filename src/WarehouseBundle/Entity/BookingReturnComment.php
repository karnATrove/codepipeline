<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingReturnComment
 *
 * @ORM\Table(name="warehouse_booking_return_comment")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingReturnCommentRepository")
 */
class BookingReturnComment
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
     * One BookingReturnComment has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many BookingReturnComment have One BookingReturn
     * @ORM\ManyToOne(targetEntity="BookingReturn", inversedBy="comments")
     * @ORM\JoinColumn(name="return_id", referencedColumnName="id")
     */
    private $return;

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
     * @return BookingReturnComment
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
     * @return BookingReturnComment
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
     * @param \WarehouseBundle\Entity\BookingReturn $booking
     *
     * @return BookingReturnComment
     */
    public function setBooking(\WarehouseBundle\Entity\BookingReturn $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \WarehouseBundle\Entity\BookingReturn
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
     * @return BookingReturnComment
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

    /**
     * Set return
     *
     * @param \WarehouseBundle\Entity\BookingReturn $return
     *
     * @return BookingReturnComment
     */
    public function setReturn(\WarehouseBundle\Entity\BookingReturn $return = null)
    {
        $this->return = $return;

        return $this;
    }

    /**
     * Get return
     *
     * @return \WarehouseBundle\Entity\BookingReturn
     */
    public function getReturn()
    {
        return $this->return;
    }
}
