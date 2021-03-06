<?php

namespace WarehouseBundle\Utils;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use WarehouseBundle\Entity\Booking as BookingEntity;
use WarehouseBundle\Entity\BookingContact;
use WarehouseBundle\Entity\BookingProduct as BookingProductEntity;
use WarehouseBundle\Manager\CarrierManager;
use WarehouseBundle\Model\BookingInterface;
use WarehouseBundle\Model\BookingManagerInterface;

define('BOOKING_STATE_INACTIVE', 0);
define('BOOKING_STATE_ACTIVE', 1);
define('BOOKING_STATE_COMPLETE', 0);

class Booking
{

	const BASE_WEBSITE_ADDRESS = "https://www.roveconcepts.com/remote/c9c078d09dbfa23992cb150ccadc238f/carrier/downloads/";
	private $container;

	/**
	 * Booking manager.
	 *
	 * @var BookingManagerInterface
	 */
	private $bookingManager;

	/**
	 * @var RequestStack
	 */
	private $requestStack;

	/**
	 * Make the utility container-aware. (giving access to doctrine for example)
	 *
	 * @param \Symfony\Component\DependencyInjection\Container $container The container
	 * @param \WarehouseBundle\Model\BookingManagerInterface   $bookingManager
	 * @param \Symfony\Component\HttpFoundation\RequestStack   $requestStack
	 */
	public function __construct(Container $container, BookingManagerInterface $bookingManager, RequestStack $requestStack)
	{
		$this->container = $container;
		$this->bookingManager = $bookingManager;
		$this->requestStack = $requestStack;
	}

	/**
	 * Return a human readable format of the booking status.
	 *
	 * @param      integer $statusId The status id
	 *
	 * @return     string  Human readable text.
	 */
	public static function bookingStatusName($statusId)
	{
		return isset(self::bookingStatusList()[$statusId]) ? self::bookingStatusList()[$statusId] : 'Unknown';
	}

	/**
	 * Listing of available booking statuses.
	 * 0 = Deleted/Invisible/Cancelled
	 * 1 = Awaiting Forward
	 * 2 = Accepted
	 * 3 = Picked --- Changed when all item qty satisfied (auto)
	 * 4 = Packed --- Scan order on pick list - confirmed to packed....
	 * 5 = Shipped --- Scanned when loading to carrier
	 *
	 * - Cancelling can only occur status IN (1,2) - Rove system deletes it
	 * - Cancelling in 'Picked' or 'Packed' - Email change request to BD then they can go in and cancel an item or order
	 * @return     array  Available booking statuses.
	 */
	public static function bookingStatusList($active_only = FALSE, $not_closed = FALSE)
	{
		$statusList = [
			BookingEntity::STATUS_AWAITING_FORWARD => 'Awaiting Forward',
			BookingEntity::STATUS_ACCEPTED => 'Accepted',
			BookingEntity::STATUS_PICKED => 'Picked',
			BookingEntity::STATUS_PACKED => 'Packed',
		];
		if ($active_only && $not_closed) {
			return $statusList;
		}
		if ($active_only && !$not_closed) {
			$statusList[BookingEntity::STATUS_SHIPPED] = 'Shipped';
			return $statusList;
		}
		$statusList[BookingEntity::STATUS_DELETED] = 'Deleted/Invisible/Cancelled';
		$statusList[BookingEntity::STATUS_SHIPPED] = 'Shipped';
		return $statusList;
	}

	/**
	 * Determine the state of a booking (active, inactive, complete).
	 *
	 * @param      integer $statusId The status id
	 *
	 * @return     integer  (active, inactive, complete)
	 */
	public static function bookingStatusState($statusId)
	{
		switch ($statusId) {
			case BookingEntity::STATUS_DELETED:
				return BOOKING_STATE_INACTIVE;
			case BookingEntity::STATUS_SHIPPED:
				return BOOKING_STATE_COMPLETE;
			case BookingEntity::STATUS_AWAITING_FORWARD:
			case BookingEntity::STATUS_ACCEPTED:
			case BookingEntity::STATUS_PICKED:
			case BookingEntity::STATUS_PACKED:
			default:
				return BOOKING_STATE_ACTIVE;
		}
	}

	/**
	 * Return a human readable format of the carrier of a booking.
	 *
	 * @param      integer $carrierId The carrier identifier
	 *
	 * @return     string  Human readable text.
	 */
	public function bookingCarrierName($carrierId)
	{
		$list = $this->bookingCarrierList();
		return isset($list[$carrierId]) ? $list[$carrierId] : 'Unknown';
	}

	/**
	 * Listing of available carriers.
	 * @return  array  ['carrierId'=>'name']
	 */
	public function bookingCarrierList()
	{
		return $this->container->get(CarrierManager::class)->getCarrierList('name');
	}

	/**
	 * Return a human readable format of the order type of the booking.
	 *
	 * @param      integer $orderTypeId The order type identifier
	 *
	 * @return     string  Human readable text.
	 */
	public static function bookingOrderTypeName($orderTypeId)
	{
		return isset(self::bookingOrderTypeList()[$orderTypeId]) ? self::bookingOrderTypeList()[$orderTypeId] : 'Unknown';
	}

	/**
	 * Listing of available order types.
	 *
	 * @return     array  Available order types.
	 */
	public static function bookingOrderTypeList()
	{
		return [
			BookingEntity::TYPE_CARRIER_ORDER => 'Carrier Order',
			BookingEntity::TYPE_PICKUP_ORDER => 'Pickup Order',
			BookingEntity::TYPE_TRANSFER => 'Transfer (Forward)',
			BookingEntity::TYPE_PICK_ONLY => 'Pick Only',
		];
	}

	/**
	 * Creates a booking and returns it.
	 *
	 * @param string    $orderNumber
	 * @param string    $orderReference
	 * @param int       $orderType
	 * @param int       $carrierId
	 * @param \DateTime $futureShip
	 *
	 * @return BookingInterface
	 */
	public function create($orderNumber, $orderReference, $orderType, $carrierId, $futureShip = NULL)
	{
		#$manipulator = $this->getContainer()->get('app.booking');

		$booking = $this->bookingManager->createBooking();
		$booking->setOrderNumber($orderNumber);
		$booking->setOrderReference($orderReference);
		$booking->setOrderType($orderType);
		$booking->setCarrier($this->container->get(CarrierManager::class)->find($carrierId));
		$booking->setSkidCount(NULL);
		$booking->setStatus(BookingEntity::STATUS_ACCEPTED);
		$booking->setFutureShip($futureShip);
		$booking->setShipped(NULL);
		$booking->setCreated(new \DateTime('now'));
		$booking->setModified(NULL);
		$booking->setPickingFlag(0);
		$booking->setUser(NULL);

		$this->bookingManager->updateBooking($booking);

		# Use below if we ever use EventDispatching
		#$event = new BookingEvent($booking, $this->getRequest());
		#$this->dispatcher->dispatch(WarehouseBookingEvents::BOOKING_CREATED, $event);

		return $booking;
	}

	/**
	 * Get the total number picked from a booking product.
	 *
	 * @param      BookingProductEntity $bookingProduct The booking product
	 *
	 * @return     <type>                                  ( description_of_the_return_value )
	 */
	public function bookingProductPickedQty(BookingProductEntity $bookingProduct)
	{
		return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getPickedQtyByBookingProduct($bookingProduct);
	}

	/**
	 * Determine if the order is fillable or not.
	 *
	 * @param      <type>   $booking  The booking
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function bookingIsFillable(BookingEntity $booking)
	{
		$fillable_products = 0;
		foreach ($booking->getProducts() as $bookingProduct) {
			if ($this->container->get('app.product')->getAvailableInternal($bookingProduct->getProduct()) >= $bookingProduct->getQty()) {
				$fillable_products++;
			}
		}

		return $fillable_products == count($booking->getProducts()) ? TRUE : FALSE;
	}

	/**
	 * Gets the total quantity count of products on a booking.
	 *
	 * @param      <type>   $booking  The booking
	 *
	 * @return     integer  ( description_of_the_return_value )
	 */
	public function bookingProductQuantityTotal(\WarehouseBundle\Entity\Booking $booking)
	{
		$qty = 0;
		foreach ($booking->getProducts() as $bookingProduct) {
			$qty += $bookingProduct->getQty();
		}
		return $qty;
	}

	/**
	 * Help format an address from a BookingContact.
	 *
	 * @param      <type>  $contact  The contact
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function formatContactAddress(BookingContact $contact)
	{
		return ($contact->getCompany() ? $contact->getCompany() . "\n" . 'c/o ' .
				$contact->getName() . "\n" : 'c/o ' . $contact->getName() . "\n") .
			$contact->getStreet() . " " . $contact->getStreet2() . "\n" .
			$contact->getCity() . ", " . $contact->getState() . "\n" .
			$contact->getZip() . " " . $contact->getCountry();
	}

	/**
	 * Gets the human readable format of the booking product status.
	 *
	 * @param      <type>  $statusId  The status identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function bookingProductStatusName($statusId)
	{
		return isset($this->bookingProductStatusList()[$statusId]) ? $this->bookingProductStatusList()[$statusId] : 'Unknown';
	}

	/**
	 * Listing of available order types.
	 *
	 * @return     array  Available order types.
	 */
	public static function bookingProductStatusList()
	{
		return [
			BookingProductEntity::STATUS_DELETED => 'Cancelled/Deleted/Invisible',
			BookingProductEntity::STATUS_PENDING => 'Pending',
			BookingProductEntity::STATUS_IN_PROGRESS => 'In Progress',
			BookingProductEntity::STATUS_PICKED => 'Picked',
			//4 => 'Closed',
		];
	}

	/**
	 * @return Request
	 */
	private function getRequest()
	{
		return $this->requestStack->getCurrentRequest();
	}
}