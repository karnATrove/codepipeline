<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BookingFile
 *
 * @ORM\Table(name="warehouse_booking_file")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingFileRepository")
 */
class BookingFile implements IEntity
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
     * One BookingFile has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many BookingFile have One Booking
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="files")
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     */
    private $booking;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=45)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="filepath", type="string", length=75)
     *
     * @Assert\NotBlank(message="Please, upload the booking file.")
     */
    private $filepath;

    /**
     * @var int
     *
     * @ORM\Column(name="fid", type="integer", nullable=true)
     */
    private $fid;

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
     * Set type
     *
     * @param string $type
     *
     * @return BookingFile
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set filepath
     *
     * @param string $filepath
     *
     * @return BookingFile
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set fid
     *
     * @param integer $fid
     *
     * @return BookingFile
     */
    public function setFid($fid)
    {
        $this->fid = $fid;

        return $this;
    }

    /**
     * Get fid
     *
     * @return int
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return BookingFile
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
     * @return BookingFile
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
     * @return BookingFile
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
