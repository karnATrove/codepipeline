<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LocationProduct
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_location_product")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\LocationProductRepository")
 */
class LocationProduct implements IEntity
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
	 * One LocationProduct has One User
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	private $user;

	/**
	 * Many Locations have One Product
	 * @ORM\ManyToOne(targetEntity="Product", inversedBy="locations")
	 * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
	 */
	private $product;

	/**
	 * Many LocationProduct have One location.
	 * @Gedmo\Versioned
	 * @ORM\ManyToOne(targetEntity="Location", inversedBy="products")
	 * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
	 */
	private $location;

	/**
	 * @var int
	 * @Gedmo\Versioned
	 * @ORM\Column(name="on_hand", type="integer")
	 */
	private $onHand;

	/**
	 * @var int
	 * @Gedmo\Versioned
	 * @ORM\Column(name="staged", type="integer")
	 */
	private $staged;

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
	 * Set onHand
	 *
	 * @param integer $onHand
	 *
	 * @return LocationProduct
	 */
	public function setOnHand($onHand)
	{
		$this->onHand = $onHand;

		return $this;
	}

	/**
	 * Get onHand
	 *
	 * @return int
	 */
	public function getOnHand()
	{
		return $this->onHand;
	}

	/**
	 * Set staged
	 *
	 * @param integer $staged
	 *
	 * @return LocationProduct
	 */
	public function setStaged($staged)
	{
		$this->staged = $staged;

		return $this;
	}

	/**
	 * Get staged
	 *
	 * @return int
	 */
	public function getStaged()
	{
		return $this->staged;
	}

	/**
	 * Set created
	 *
	 * @param \DateTime $created
	 *
	 * @return LocationProduct
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
	 * @return LocationProduct
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
	 * Set product
	 *
	 * @param Product $product
	 *
	 * @return LocationProduct
	 */
	public function setProduct(Product $product = null)
	{
		$this->product = $product;
		return $this;
	}

	/**
	 * Get product
	 *
	 * @return Product
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * Set location
	 *
	 * @param Location $location
	 *
	 * @return LocationProduct
	 */
	public function setLocation(Location $location = null)
	{
		$this->location = $location;
		return $this;
	}

	/**
	 * Get location
	 *
	 * @return Location
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * Set user
	 *
	 * @param User $user
	 *
	 * @return LocationProduct
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
