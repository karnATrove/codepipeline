<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Location
 *
 * @ORM\Table(name="warehouse_location")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\LocationRepository")
 */
class Location
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
     * One Location has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many Locations have One Product
     * @ORM\OneToMany(targetEntity="LocationProduct",mappedBy="location")
     */
    private $products;

    /**
     * @var string
     *
     * @ORM\Column(name="aisle", type="string", length=10)
     */
    private $aisle;

    /**
     * @var string
     *
     * @ORM\Column(name="row", type="string", length=10)
     */
    private $row;

    /**
     * @var boolean
     *
     * @ORM\Column(name="staging", type="boolean", options={"default":0})
     */
    private $staging;

    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", length=10)
     */
    private $level;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;


    public function __construct() {
        $this->products = new ArrayCollection();
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
     * Set aisle
     *
     * @param string $aisle
     *
     * @return Location
     */
    public function setAisle($aisle)
    {
        $this->aisle = $aisle;

        return $this;
    }

    /**
     * Get aisle
     *
     * @return string
     */
    public function getAisle()
    {
        return $this->aisle;
    }

    /**
     * Set row
     *
     * @param string $row
     *
     * @return Location
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * Get row
     *
     * @return string
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Set level
     *
     * @param string $level
     *
     * @return Location
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set staging
     *
     * @param boolean $staging
     *
     * @return boolean
     */
    public function setStaging($staging)
    {
        $this->staging = $staging;

        return $this;
    }

    /**
     * Get staging
     *
     * @return boolean
     */
    public function getStaging()
    {
        return $this->staging;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Location
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
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Location
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Add location product
     *
     * @param \WarehouseBundle\Entity\LocationProduct $product
     *
     * @return Location
     */
    public function addProduct(\WarehouseBundle\Entity\LocationProduct $product)
    {
        $product->setBooking($this);    # Helpful for cascading
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove location product
     *
     * @param \WarehouseBundle\Entity\LocationProduct $product
     */
    public function removeProduct(\WarehouseBundle\Entity\LocationProduct $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get location products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return Location
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

    public function printLocation(){
	    return $this->getAisle(). ' - '. $this->getRow(). ' - '. $this->getLevel();
    }
}
