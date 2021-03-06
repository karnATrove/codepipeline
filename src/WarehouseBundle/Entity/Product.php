<?php

namespace WarehouseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use WarehouseBundle\Manager\ProductManager;
use WarehouseBundle\Model\ProductInterface;

/**
 * Product
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_product")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\ProductRepository")
 */
class Product implements ProductInterface, IEntity
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
	 * One Product has One User
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	private $user;

	/**
	 * @var string
	 * @Gedmo\Versioned
	 * @ORM\Column(name="model", type="string", length=45, unique=true)
	 */
	private $model;

	/**
	 * @var int
	 * @Gedmo\Versioned
	 * @ORM\Column(name="status", type="smallint")
	 */
	private $status;

	/**
	 * @var string
	 * @Gedmo\Versioned
	 * @ORM\Column(name="description", type="string", length=75)
	 */
	private $description;

	/**
	 * @var int
	 * @Gedmo\Versioned
	 * @ORM\Column(name="qty_per_carton", type="integer")
	 */
	private $qtyPerCarton;

	/**
	 * @var float
	 * @Gedmo\Versioned
	 * @ORM\Column(name="length", type="float", nullable=true)
	 */
	private $length;

	/**
	 * @var float
	 * @Gedmo\Versioned
	 * @ORM\Column(name="width", type="float", nullable=true)
	 */
	private $width;

	/**
	 * @var float
	 * @Gedmo\Versioned
	 * @ORM\Column(name="height", type="float", nullable=true)
	 */
	private $height;

	/**
	 * @var string
	 * @Gedmo\Versioned
	 * @ORM\Column(name="dim_units", type="string", length=10)
	 */
	private $dimUnits;

	/**
	 * @var float
	 * @Gedmo\Versioned
	 * @ORM\Column(name="weight", type="float", nullable=true)
	 */
	private $weight;

	/**
	 * @var string
	 * @Gedmo\Versioned
	 * @ORM\Column(name="weight_units", type="string", length=10)
	 */
	private $weightUnits;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;

	/**
	 * One Product has Many LocationProduct.
	 * @ORM\OneToMany(targetEntity="LocationProduct", mappedBy="product", cascade={"persist","remove"})
	 */
	private $locations;

	/**
	 * One Product has Many Locations.
	 * @ORM\OneToMany(targetEntity="IncomingProduct", mappedBy="product")
	 */
	private $incoming;

	public function __construct()
	{
		$this->locations = new ArrayCollection();
		$this->incoming = new ArrayCollection();
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
	 * Get model
	 *
	 * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Set model
	 *
	 * @param string $model
	 *
	 * @return Product
	 */
	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set status
	 *
	 * @param integer $status
	 *
	 * @return Product
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Product
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Get qtyPerCarton
	 *
	 * @return int
	 */
	public function getQtyPerCarton()
	{
		return $this->qtyPerCarton;
	}

	/**
	 * Set qtyPerCarton
	 *
	 * @param integer $qtyPerCarton
	 *
	 * @return Product
	 */
	public function setQtyPerCarton($qtyPerCarton)
	{
		$this->qtyPerCarton = $qtyPerCarton;

		return $this;
	}

	/**
	 * Get length
	 *
	 * @return float
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * Set length
	 *
	 * @param float $length
	 *
	 * @return Product
	 */
	public function setLength($length)
	{
		$this->length = $length;

		return $this;
	}

	/**
	 * Get width
	 *
	 * @return float
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * Set width
	 *
	 * @param float $width
	 *
	 * @return Product
	 */
	public function setWidth($width)
	{
		$this->width = $width;

		return $this;
	}

	/**
	 * Get height
	 *
	 * @return float
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * Set height
	 *
	 * @param float $height
	 *
	 * @return Product
	 */
	public function setHeight($height)
	{
		$this->height = $height;

		return $this;
	}

	/**
	 * Get dimUnits
	 *
	 * @return string
	 */
	public function getDimUnits()
	{
		return $this->dimUnits;
	}

	/**
	 * Set dimUnits
	 *
	 * @param string $dimUnits
	 *
	 * @return Product
	 */
	public function setDimUnits($dimUnits)
	{
		$this->dimUnits = $dimUnits;

		return $this;
	}

	/**
	 * Get weight
	 *
	 * @return float
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Set weight
	 *
	 * @param float $weight
	 *
	 * @return Product
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;

		return $this;
	}

	/**
	 * Get weightUnits
	 *
	 * @return string
	 */
	public function getWeightUnits()
	{
		return $this->weightUnits;
	}

	/**
	 * Set weightUnits
	 *
	 * @param string $weightUnits
	 *
	 * @return Product
	 */
	public function setWeightUnits($weightUnits)
	{
		$this->weightUnits = $weightUnits;

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
	 * Set created
	 *
	 * @param \DateTime $created
	 *
	 * @return Product
	 */
	public function setCreated($created)
	{
		$this->created = $created;

		return $this;
	}

	/**
	 * Add location
	 *
	 * @param Location $location
	 *
	 * @return Product
	 */
	public function addLocation(Location $location)
	{
		$this->locations[] = $location;

		return $this;
	}

	/**
	 * Remove location
	 *
	 * @param Location $location
	 */
	public function removeLocation(Location $location)
	{
		$this->locations->removeElement($location);
	}

	/**
	 * Get locations
	 *
	 * @return Collection|LocationProduct[]
	 */
	public function getLocations()
	{
		return $this->locations;
	}

	/**
	 * Add incoming
	 *
	 * @param IncomingProduct $incoming
	 *
	 * @return Product
	 */
	public function addIncoming(IncomingProduct $incoming)
	{
		$this->incoming[] = $incoming;

		return $this;
	}

	/**
	 * Remove incoming
	 *
	 * @param IncomingProduct $incoming
	 */
	public function removeIncoming(IncomingProduct $incoming)
	{
		$this->incoming->removeElement($incoming);
	}

	/**
	 * Get incoming
	 *
	 * @return Collection
	 */
	public function getIncoming()
	{
		return $this->incoming;
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

	/**
	 * Set user
	 *
	 * @param User $user
	 *
	 * @return Product
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;

		return $this;
	}

	public function __toString()
	{
		return 'Product';
	}

	/**
	 * @return int
	 */
	public function getQuantityOnHand()
	{
		return ProductManager::quantityOnHand($this);
	}
}
