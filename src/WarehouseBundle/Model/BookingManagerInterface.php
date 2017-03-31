<?php
namespace WarehouseBundle\Model;

/**
 * Interface to be implemented by booking managers. This adds an additional level
 * of abstraction between your application, and the actual repository.
 *
 * All changes to bookings should happen through this interface.
 *
 * The class also contains ACL annotations which will only work if you have the
 * SecurityExtraBundle installed, otherwise they will simply be ignored.
 *
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
interface BookingManagerInterface
{
    /**
     * Creates an empty booking instance.
     *
     * @param string $note
     */
    public function logEntry($note);

    /**
     * Creates an empty booking instance.
     *
     * @return BookingInterface
     */
    public function createBooking();

    /**
     * Creates an empty booking product for a booking instance.
     *
     * @return BookingProduct
     */
    public function createBookingProduct();

    /**
     * Deletes a booking.
     *
     * @param BookingInterface $booking
     */
    public function deleteBooking(BookingInterface $booking);

    /**
     * Deletes a booking product.
     *
     * @param BookingProduct $bookingProduct
     */
    public function deleteBookingProduct(\WarehouseBundle\Entity\BookingProduct $bookingProduct);

    /**
     * Finds one booking by the given criteria.
     *
     * @param array $criteria
     *
     * @return BookingInterface
     */
    public function findBookingBy(array $criteria);

    /**
     * Find a booking by its internal id.
     *
     * @param string $id
     *
     * @return BookingInterface or null if booking does not exist
     */
    public function findBookingById($id);

    /**
     * Find a booking by its order reference.
     *
     * @param string $orderReference
     *
     * @return BookingInterface or null if booking does not exist
     */
    public function findBookingByOrderReference($orderReference);

    /**
     * Finds a user by its order number.
     *
     * @param string $orderNumber
     *
     * @return BookingInterface or null if booking does not exist
     */
    public function findBookingByOrderNumber($orderNumber);

    /**
     * Finds a booking by its order number or order reference.
     *
     * @param string $numberOrReference
     *
     * @return BookingInterface or null if booking does not exist
     */
    public function findUserByOrderNumberOrReference($numberOrReference);

    /**
     * Returns a collection with all booking instances.
     *
     * @return \Traversable
     */
    public function findBookings();

    /**
     * Reloads a booking.
     *
     * @param BookingInterface $booking
     */
    public function reloadBooking(BookingInterface $booking);

    /**
     * Updates a booking.
     *
     * @param BookingInterface $booking
     */
    public function updateBooking(BookingInterface $booking);

    /**
     * Updates a booking product.
     *
     * @param BookingProduct $bookingProduct
     */
    public function updateBookingProduct(\WarehouseBundle\Entity\BookingProduct $bookingProduct);

    /**
     * Updates a booking file.
     *
     * @param BookingFile $bookingFile
     */
    public function updateFile(\WarehouseBundle\Entity\BookingFile $bookingFile);

    /**
     * Updates a booking file.
     *
     * @param BookingFile $bookingFile
     */
    public function updateComment(\WarehouseBundle\Entity\BookingComment $bookingComment);
}
