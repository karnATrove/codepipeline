<?php

namespace WarehouseBundle\Utils;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use WarehouseBundle\Entity\BookingContact;
use WarehouseBundle\Model\BookingInterface;
use WarehouseBundle\Model\BookingManagerInterface;

define('BOOKING_STATE_INACTIVE',0);
define('BOOKING_STATE_ACTIVE',1);
define('BOOKING_STATE_COMPLETE',0);

class Booking 
{

    private $container;

    /**
     * User manager.
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
     * @param      <type>  $container  The container
     */
    public function __construct(Container $container, BookingManagerInterface $bookingManager, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->bookingManager = $bookingManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string $orderNumber
     * @param string $orderReference
     * @param int $orderType
     * @param int 	 $carrierId
     * @param \DateTime   $futureShip
     *
     * @return BookingInterface
     */
    public function create($orderNumber, $orderReference, $orderType, $carrierId, $futureShip=NULL) {
        #$manipulator = $this->getContainer()->get('app.booking');

        $booking = $this->bookingManager->createBooking();
        $booking->setOrderNumber($orderNumber);
        $booking->setOrderReference($orderReference);
        $booking->setOrderType($orderType);
        $booking->setCarrierId($carrierId);
        $booking->setSkidCount(NULL);
        $booking->setStatus(2); # Accepted
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
     * @param      \WarehouseBundle\Entity\BookingProduct  $bookingProduct  The booking product
     *
     * @return     <type>                                  ( description_of_the_return_value )
     */
    public function bookingProductPickedQty(\WarehouseBundle\Entity\BookingProduct $bookingProduct) {
        return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getPickedQtyByBookingProduct($bookingProduct);
    }

    /**
     * Listing of available booking statuses.
    0 = Deleted/Invisible/Cancelled
    1 = Awaiting Forward
    2 = Accepted
    3 = Picked --- Changed when all item qty satisfied (auto)
    4 = Packed --- Scan order on pick list - confirmed to packed....
    5 = Shipped --- Scanned when loading to carrier

    - Cancelling can only occur status IN (1,2) - Rove system deletes it
    - Cancelling in 'Picked' or 'Packed' - Email change request to BD then they can go in and cancel an item or order
     * @return     array  Available booking statuses.
     */
    public static function bookingStatusList($active_only=FALSE,$not_closed=FALSE) {
        if ($active_only && !$not_closed)
            return array(
                1 => 'Awaiting Forward',
                2 => 'Accepted',
                3 => 'Picked',
                4 => 'Packed',
                5 => 'Shipped',
            );
        elseif ($active_only && $not_closed)
            return array(
                1 => 'Awaiting Forward',
                2 => 'Accepted',
                3 => 'Picked',
                4 => 'Packed',
            );
        return array(
            0 => 'Deleted/Invisible/Cancelled',
            1 => 'Awaiting Forward',
            2 => 'Accepted',
            3 => 'Picked',
            4 => 'Packed',
            5 => 'Shipped',
        );
    }

    /**
     * Return a human readable format of the booking status.
     *
     * @param      integer  $statusId  The status id
     *
     * @return     string  Human readable text.
     */
    public function bookingStatusName($statusId) {
        return isset($this->bookingStatusList()[$statusId]) ? $this->bookingStatusList()[$statusId] : 'Unknown';
    }

    /**
     * Determine the state of a booking (active, inactive, complete).
     *
     * @param      integer  $statusId  The status id
     *
     * @return     integer  (active, inactive, complete)
     */
    public static function bookingStatusState($statusId) {
        switch($statusId) {
            case 0:
                return BOOKING_STATE_INACTIVE;
            case 5:
                return BOOKING_STATE_COMPLETE;
            case 1:
            case 2:
            case 3:
            case 4:
            default:
                return BOOKING_STATE_ACTIVE;
        }
    }

    /**
     * Listing of available order types.
     *
     * @return     array  Available order types.
     */
    public static function bookingOrderTypeList() {
        return array(
            1 => 'Carrier Order',
            2 => 'Pickup Order',
            3 => 'Transfer (Forward)',
        );
    }

    /**
     * Return a human readable format of the order type of the booking.
     *
     * @param      integer  $orderTypeId  The order type identifier
     *
     * @return     string  Human readable text.
     */
    public function bookingOrderTypeName($orderTypeId) {
        return isset($this->bookingOrderTypeList()[$orderTypeId]) ? $this->bookingOrderTypeList()[$orderTypeId] : 'Unknown';
    }

    /**
     * Listing of available carriers.
     * TODO: Move this to database.
     *
     * @return     array  Available carriers.
     */
    public static function bookingCarrierList() {
        return array(
            1 => 'XPO Logistics',
            2 => 'Non Stop Delivery',
            3 => 'UPS',
            4 => 'FedEx',
            5 => 'Home Direct',
            6 => 'VITRAN',
            7 => 'MACTRAN',
            8 => 'CEVA Logistics',
            9 => 'AGS Logistics',
            10 => 'SEKO Logistics',
            11 => 'Manna Logistics',
            12 => 'Pilot Logistics',
            13 => 'TEST Logistics',
            14 => 'Propack Shipping',
            16 => 'DWS Pickup',
            17 => 'Sunshine',
            18 => 'Customer Pickup',
            19 => 'ATS',
            20 => 'Wayfair Carrier',
            21 => 'Amazon Carrier',
        );
    }

    /**
     * Return a human readable format of the carrier of a booking.
     *
     * @param      integer  $carrierId  The carrier identifier
     *
     * @return     string  Human readable text.
     */
    public function bookingCarrierName($carrierId) {
        return isset($this->bookingCarrierList()[$carrierId]) ? $this->bookingCarrierList()[$carrierId] : 'Unknown';
    }


    /**
     * Determine if the order is fillable or not.
     *
     * @param      <type>   $booking  The booking
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public function bookingIsFillable(\WarehouseBundle\Entity\Booking $booking)
    {
        $fillable_products = 0;
        foreach($booking->getProducts() as $bookingProduct) {
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
        foreach($booking->getProducts() as $bookingProduct) {
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
        return ($contact->getCompany()?$contact->getCompany()."\n".'c/o '. $contact->getName()."\n":'c/o '. $contact->getName()."\n").
            $contact->getStreet()." ".$contact->getStreet2()."\n".
            $contact->getCity().", ".$contact->getState()."\n".
            $contact->getZip()." ".$contact->getCountry();
    }


    /**
     * Listing of available order types.
     *
     * @return     array  Available order types.
     */
    public static function bookingProductStatusList() {
        return array(
            0 => 'Cancelled/Deleted/Invisible',
            1 => 'Pending',
            2 => 'In Progress',
            3 => 'Picked',
            //4 => 'Closed',
        );
    }

    /**
     * Gets the human readable format of the booking product status.
     *
     * @param      <type>  $statusId  The status identifier
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function bookingProductStatusName($statusId) {
        return isset($this->bookingProductStatusList()[$statusId]) ? $this->bookingProductStatusList()[$statusId] : 'Unknown';
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}