<?php
namespace WarehouseBundle\Model;

use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Entity\LocationProduct;

/**
 * Abstract Booking Manager implementation which can be used as base class for your
 * concrete manager.
 *
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
abstract class BookingManager implements BookingManagerInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createBooking()
    {
        $booking = new \WarehouseBundle\Entity\Booking();

        return $booking;
    }

    /**
     * {@inheritdoc}
     */
    public function findBookingById($id)
    {
        return $this->findBookingBy(array('id' => $id));
    }

    /**
     * {@inheritdoc}
     */
    public function findBookingByOrderReference($orderReference)
    {
        return $this->findBookingBy(array('orderReference' => $orderReference));
    }

    /**
     * {@inheritdoc}
     */
    public function findBookingByOrderNumber($orderNumber)
    {
        return $this->findBookingBy(array('orderNumber' => $orderNumber));
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByOrderNumberOrReference($numberOrReference)
    {
        if (is_numeric($numberOrReference)) {
            return $this->findBookingByOrderNumber($numberOrReference);
        }

        return $this->findBookingByOrderReference($numberOrReference);
    }

}
