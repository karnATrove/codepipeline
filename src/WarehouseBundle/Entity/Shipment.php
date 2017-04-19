<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shipment
 *
 * @ORM\Table(name="warehouse_shipment")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\ShipmentRepository")
 */
class Shipment
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
	 * @var Booking
	 *
	 * Many Shipment has One Booking
	 * @ORM\ManyToOne(targetEntity="booking")
	 */
	private $booking;

	/**
	 * @var User
	 *
	 * Many Shipment has One User
	 * @ORM\ManyToOne(targetEntity="user")
	 */
	private $user;

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
	 * @return Booking
	 */
	public function getBooking()
	{
		return $this->booking;
	}

	/**
	 * @param Booking $booking
	 */
	public function setBooking($booking)
	{
		$this->booking = $booking;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param \DateTime $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
	}
}

