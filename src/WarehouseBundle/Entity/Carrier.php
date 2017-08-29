<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shipment
 *
 * @ORM\Table(name="warehouse_carrier")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\CarrierRepository")
 */
class Carrier implements IEntity
{

	//region Constant

	//carrier
	const CARRIER_XPO_LOGISTICS = 1;
	const CARRIER_NON_STOP_DELIVERY = 2;
	const CARRIER_UPS = 3;
	const CARRIER_FEDEX = 4;
	const CARRIER_HOME_DIRECT = 5;
	const CARRIER_VITRAN = 6;
	const CARRIER_MACTRAN = 7;
	const CARRIER_CEVA_LOGISTICS = 8;
	const CARRIER_AGS_LOGISTICS = 9;
	const CARRIER_SEKO_LOGISTICS = 10;
	const CARRIER_MANNA_LOGISTICS = 11;
	const CARRIER_PILOT_LOGISTICS = 12;
	const CARRIER_TEST_LOGISTICS = 13;
	const CARRIER_PROPACK_SHIPPING = 14;
	const CARRIER_DWS_PICKUP = 16;
	const CARRIER_SUNSHINE = 17;
	const CARRIER_CUSTOMER_PICKUP = 18;
	const CARRIER_ATS = 19;
	const CARRIER_WAYFAIR_CARRIER = 20;
	const CARRIER_AMAZON_CARRIER = 21;
	const CARRIER_BUILDDIRECT_CARRIER = 22;
	const CARRIER_HAYNEEDLE_CARRIER = 23;
	const CARRIER_LOCAL_CARRIER = 24;

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
	 * @var string
	 * @ORM\Column(name="name", type="string", length=50)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(name="code", type="string", length=10)
	 */
	private $code;

    /**
     * @ORM\OneToMany(targetEntity="WarehouseBundle\Entity\Booking", mappedBy="carrier")
     */
	private $bookings;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

    /**
     * @return \WarehouseBundle\Entity\Booking[]
     */
    public function getBookings() {
        return $this->bookings;
    }

    /**
     * @param \WarehouseBundle\Entity\Booking[] $booking
     */
    public function setBookings($bookings) {
        $this->bookings = $bookings;
    }
}

