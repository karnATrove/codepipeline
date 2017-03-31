<?php
namespace WarehouseBundle\Model;

/**
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */

//interface BookingInterface extends \Serializable
interface BookingInterface
{
    const BOOKING_STATUS_DEFAULT = 2;
    const BOOKING_STATUS_CANCELLED = 0;
    const BOOKING_STATUS_AWAITING_FORWARD = 1;
    const BOOKING_STATUS_ACCEPTED = 2;
    const BOOKING_STATUS_PICKED = 3;
    const BOOKING_STATUS_SHIPPED = 5;

    /**
     * Returns the booking unique id.
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the Order Number.
     *
     * @param string $orderNumber
     *
     * @return self
     */
    public function setOrderNumber($orderNumber);

    /**
     * Gets the Order Number.
     *
     * @return string
     */
    public function getOrderNumber();

    /**
     * Sets the Order Reference.
     *
     * @param string $orderReference
     *
     * @return self
     */
    public function setOrderReference($orderReference);

    /**
     * Gets the Order Reference.
     *
     * @return string
     */
    public function getOrderReference();

    /**
     * Sets the Order Type.
     *
     * @param integer $orderType
     *
     * @return self
     */
    public function setOrderType($orderType);

    /**
     * Gets the Order Type.
     *
     * @return int
     */
    public function getOrderType();

    /**
     * Sets the Carrier ID.
     *
     * @param integer $carrierId
     *
     * @return self
     */
    public function setCarrierId($carrierId);

    /**
     * Gets the Carrier ID.
     *
     * @return int
     */
    public function getCarrierId();

    /**
     * Sets the Skid Count.
     *
     * @param integer $skidCount
     *
     * @return self
     */
    public function setSkidCount($skidCount);

    /**
     * Gets the Skid Count.
     *
     * @return int
     */
    public function getSkidCount();

    /**
     * Sets the Picking Flag.
     *
     * @param integer $pickingFlag
     *
     * @return self
     */
    public function setPickingFlag($pickingFlag);

    /**
     * Gets the Picking Flag.
     *
     * @return int
     */
    public function getPickingFlag();

    /**
     * Sets the Booking Status.
     *
     * @param integer $status
     *
     * @return self
     */
    public function setStatus($status);

    /**
     * Gets the Booking Status.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Sets the Future Ship Date.
     *
     * @param \DateTime $futureship
     *
     * @return self
     */
    public function setFutureship($futureship);

    /**
     * Gets the Future Ship Date.
     *
     * @return \DateTime
     */
    public function getFutureship();

    /**
     * Sets the Shipped Date.
     *
     * @param \DateTime $shipped
     *
     * @return self
     */
    public function setShipped($shipped);

    /**
     * Gets the Shipped Date.
     *
     * @return \DateTime
     */
    public function getShipped();

    /**
     * Sets the Created Date.
     *
     * @param \DateTime $created
     *
     * @return self
     */
    public function setCreated($created);

    /**
     * Gets the Created Date.
     *
     * @return \DateTime
     */
    public function getCreated();

    /**
     * Sets the Modified Date.
     *
     * @param \DateTime $modified
     *
     * @return self
     */
    public function setModified($modified);

    /**
     * Gets the Modified Date.
     *
     * @return \DateTime
     */
    public function getModified();

    /**
     * Sets the Contact.
     *
     * @param \WarehouseBundle\Entity\BookingContact $contact
     *
     * @return self
     */
    public function setContact(\WarehouseBundle\Entity\BookingContact $contact = null);

    /**
     * Gets the Contact.
     *
     * @return \WarehouseBundle\Entity\BookingContact
     */
    public function getContact();

    /**
     * Adds a File.
     *
     * @param \WarehouseBundle\Entity\BookingFile $file
     *
     * @return self
     */
    public function addFile(\WarehouseBundle\Entity\BookingFile $file);

    /**
     * Removes a File.
     *
     * @param \WarehouseBundle\Entity\BookingFile $file
     */
    public function removeFile(\WarehouseBundle\Entity\BookingFile $file);

    /**
     * Gets the Files.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles();

    /**
     * Adds a Comment.
     *
     * @param \WarehouseBundle\Entity\BookingComment $comment
     *
     * @return self
     */
    public function addComment(\WarehouseBundle\Entity\BookingComment $comment);

    /**
     * Removes a Comment.
     *
     * @param \WarehouseBundle\Entity\BookingComment $comment
     */
    public function removeComment(\WarehouseBundle\Entity\BookingComment $comment);

    /**
     * Gets the Comments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments();

    /**
     * Adds a Product.
     *
     * @param \WarehouseBundle\Entity\BookingProduct $product
     *
     * @return self
     */
    public function addProduct(\WarehouseBundle\Entity\BookingProduct $product);

    /**
     * Removes a Product.
     *
     * @param \WarehouseBundle\Entity\BookingProduct $product
     */
    public function removeProduct(\WarehouseBundle\Entity\BookingProduct $product);

    /**
     * Gets all Products.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts();

    /**
     * Adds a Return.
     *
     * @param \WarehouseBundle\Entity\BookingReturn $return
     *
     * @return self
     */
    public function addReturn(\WarehouseBundle\Entity\BookingReturn $return);

    /**
     * Removes a Return.
     *
     * @param \WarehouseBundle\Entity\BookingReturn $return
     */
    public function removeReturn(\WarehouseBundle\Entity\BookingReturn $return);

    /**
     * Gets Returns.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReturns();

    /**
     * Set the user who created/modified.
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return self
     */
    public function setUser(\WarehouseBundle\Entity\User $user = null);

    /**
     * Get the user who created/modified.
     *
     * @return \WarehouseBundle\Entity\User
     */
    public function getUser();

}
