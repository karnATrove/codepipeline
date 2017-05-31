<?php
namespace WarehouseBundle\Model;

/**
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */

//interface ProductInterface extends \Serializable
interface ProductInterface
{
    const PRODUCT_STATUS_DEFAULT = 1;
    const PRODUCT_STATUS_ACTIVE = 1;
    const PRODUCT_STATUS_DELETED = 0;

    /**
     * Returns the product unique id.
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the model.
     *
     * @param string $model
     *
     * @return self
     */
    public function setModel($model);

    /**
     * Gets the Model.
     *
     * @return string
     */
    public function getModel();

    /**
     * Sets the Description.
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description);

    /**
     * Gets the Order Reference.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the Quantity Per Carton.
     *
     * @param integer $qtyPerCarton
     *
     * @return self
     */
    public function setQtyPerCarton($qtyPerCarton);

    /**
     * Gets the Quantity Per Carton.
     *
     * @return int
     */
    public function getQtyPerCarton();

    /**
     * Sets the Length.
     *
     * @param integer $length
     *
     * @return self
     */
    public function setLength($length);

    /**
     * Gets the Length.
     *
     * @return int
     */
    public function getLength();

    /**
     * Sets the Width.
     *
     * @param integer $width
     *
     * @return self
     */
    public function setWidth($width);

    /**
     * Gets the Width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Sets the Height.
     *
     * @param integer $height
     *
     * @return self
     */
    public function setHeight($height);

    /**
     * Gets the Height.
     *
     * @return int
     */
    public function getHeight();

    /**
     * Sets the Dimension Units.
     *
     * @param integer $dimUnits
     *
     * @return self
     */
    public function setDimUnits($dimUnits);

    /**
     * Gets the Dimension Units.
     *
     * @return int
     */
    public function getDimUnits();

    /**
     * Sets the Weight.
     *
     * @param integer $weight
     *
     * @return self
     */
    public function setWeight($weight);

    /**
     * Gets the Weight.
     *
     * @return int
     */
    public function getWeight();

    /**
     * Sets the Weight Units.
     *
     * @param integer $weightUnits
     *
     * @return self
     */
    public function setWeightUnits($weightUnits);

    /**
     * Gets the Weight Units.
     *
     * @return integer
     */
    public function getWeightUnits();

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
     * Sets the Status.
     *
     * @param integer $status
     *
     * @return self
     */
    public function setStatus($status);

    /**
     * Gets the Status.
     *
     * @return int
     */
    public function getStatus();

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
