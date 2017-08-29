<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingStatusLog
 *
 * @ORM\Table(name="warehouse_booking_status_log")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingStatusLogRepository")
 */
class BookingStatusLog implements IEntity
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
	 * @ORM\ManyToOne(targetEntity="Booking", inversedBy="bookingStatusLogs")
	 * @ORM\JoinColumn(onDelete="cascade")
	 */
	private $booking;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="old_status", type="smallint")
	 */
	private $oldBookingStatus;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="new_status", type="smallint")
	 */
	private $newBookingStatus;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;

	/**
	 * Construct and set some defaults.
	 */
	public function __construct()
	{
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
	 * Get created
	 *
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * Set created
	 *
	 * @param \DateTime $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
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
	 * @param mixed $booking
	 */
	public function setBooking($booking)
	{
		$this->booking = $booking;
	}

	/**
	 * @return int
	 */
	public function getOldBookingStatus()
	{
		return $this->oldBookingStatus;
	}

	/**
	 * @param int $oldBookingStatus
	 */
	public function setOldBookingStatus($oldBookingStatus)
	{
		$this->oldBookingStatus = $oldBookingStatus;
	}

	/**
	 * @return int
	 */
	public function getNewBookingStatus()
	{
		return $this->newBookingStatus;
	}

	/**
	 * @param int $newBookingStatus
	 */
	public function setNewBookingStatus($newBookingStatus)
	{
		$this->newBookingStatus = $newBookingStatus;
	}
}
