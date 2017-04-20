<?php

namespace WarehouseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

use WarehouseBundle\Model\BookingInterface;

/**
 * Booking
 *
 * @ORM\Table(name="warehouse_booking")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingRepository")
 * @Gedmo\Loggable
 * @ExclusionPolicy("all")
 */
class Booking implements BookingInterface
{
	const STATUS_DELETED = 0;
	const STATUS_AWAITING_FORWARD = 1;
	const STATUS_ACCEPTED = 2;
	const STATUS_PICKED = 3;
	const STATUS_PACKED = 4;
	const STATUS_SHIPPED = 5;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 *
	 * @Expose
	 */
	private $id;

	/**
	 * One Booking has One User
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	private $user;

	/**
	 * @var string
	 * @Gedmo\Versioned
	 * @ORM\Column(name="order_number", type="string", length=45)
	 *
	 * @Expose
	 */
	private $orderNumber;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="order_reference", type="string", length=45)
	 * @Gedmo\Versioned
	 * @Expose
	 */
	private $orderReference;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="order_type", type="smallint")
	 * @Gedmo\Versioned
	 * @Expose
	 */
	private $orderType;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="carrier_id", type="integer", nullable=true)
	 * @Gedmo\Versioned
	 * @Expose
	 */
	private $carrierId;

	/**
	 * @var int
	 * @Gedmo\Versioned
	 * @ORM\Column(name="skid_count", type="integer", nullable=true)
	 */
	private $skidCount;

	/**
	 * @var int
	 * @Gedmo\Versioned
	 * @ORM\Column(name="picking_flag", type="smallint", nullable=true)
	 */
	private $pickingFlag = 0;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="status", type="smallint")
	 * @Gedmo\Versioned
	 * @Expose
	 */
	private $status;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="futureship", type="date", nullable=true)
	 * @Gedmo\Versioned
	 * @Expose
	 */
	private $futureship;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="shipped", type="datetime", nullable=true)
	 * @Gedmo\Versioned
	 * @Expose
	 */
	private $shipped;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime")
	 *
	 * @Expose
	 */
	private $created;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="modified", type="datetime", nullable=true)
	 *
	 * @Expose
	 */
	private $modified;

	/**
	 * One Booking has One BookingContact
	 * @ORM\OneToOne(targetEntity="BookingContact", mappedBy="booking", cascade={"persist","remove"})
	 */
	private $contact;

	/**
	 * One Booking has Many BookingFile.
	 * @ORM\OneToMany(targetEntity="BookingFile", mappedBy="booking", cascade={"persist","remove"})
	 */
	private $files;

	/**
	 * One Booking has Many BookingComment.
	 * @ORM\OneToMany(targetEntity="BookingComment", mappedBy="booking", cascade={"persist","remove"})
	 */
	private $comments;

	/**
	 * One Booking has Many BookingProduct.
	 * @ORM\OneToMany(targetEntity="BookingProduct", mappedBy="booking", cascade={"persist","remove"})
	 */
	private $products;

	/**
	 * One Booking has Many BookingReturn.
	 * @ORM\OneToMany(targetEntity="BookingReturn", mappedBy="booking")
	 */
	private $returns;

	/**
	 * Contruct and set some defaults.
	 */
	public function __construct()
	{
		$this->files = new ArrayCollection();
		$this->products = new ArrayCollection();
		$this->returns = new ArrayCollection();

		$this->created = new \DateTime('now');
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
	 * Get orderNumber
	 *
	 * @return string
	 */
	public function getOrderNumber()
	{
		return $this->orderNumber;
	}

	/**
	 * Set orderNumber
	 *
	 * @param string $orderNumber
	 *
	 * @return Booking
	 */
	public function setOrderNumber($orderNumber)
	{
		$this->orderNumber = $orderNumber;

		return $this;
	}

	/**
	 * Get orderReference
	 *
	 * @return string
	 */
	public function getOrderReference()
	{
		return $this->orderReference;
	}

	/**
	 * Set orderReference
	 *
	 * @param string $orderReference
	 *
	 * @return Booking
	 */
	public function setOrderReference($orderReference)
	{
		$this->orderReference = $orderReference;

		return $this;
	}

	/**
	 * Get orderType
	 *
	 * @return int
	 */
	public function getOrderType()
	{
		return $this->orderType;
	}

	/**
	 * Set orderType
	 *
	 * @param integer $orderType
	 *
	 * @return Booking
	 */
	public function setOrderType($orderType)
	{
		$this->orderType = $orderType;

		return $this;
	}

	/**
	 * Get carrierId
	 *
	 * @return int
	 */
	public function getCarrierId()
	{
		return $this->carrierId;
	}

	/**
	 * Set carrierId
	 *
	 * @param integer $carrierId
	 *
	 * @return Booking
	 */
	public function setCarrierId($carrierId)
	{
		$this->carrierId = $carrierId;

		return $this;
	}

	/**
	 * Get skidCount
	 *
	 * @return int
	 */
	public function getSkidCount()
	{
		return $this->skidCount;
	}

	/**
	 * Set skidCount
	 *
	 * @param integer $skidCount
	 *
	 * @return Booking
	 */
	public function setSkidCount($skidCount)
	{
		$this->skidCount = $skidCount;

		return $this;
	}

	/**
	 * Get pickingFlag
	 *
	 * @return int
	 */
	public function getPickingFlag()
	{
		return $this->pickingFlag;
	}

	/**
	 * Set pickingFlag
	 *
	 * @param integer $pickingFlag
	 *
	 * @return Booking
	 */
	public function setPickingFlag($pickingFlag)
	{
		$this->pickingFlag = $pickingFlag;

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
	 * @return Booking
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Get futureship
	 *
	 * @return \DateTime
	 */
	public function getFutureship()
	{
		return $this->futureship;
	}

	/**
	 * Set futureship
	 *
	 * @param \DateTime $futureship
	 *
	 * @return Booking
	 */
	public function setFutureship($futureship)
	{
		$this->futureship = $futureship;

		return $this;
	}

	/**
	 * Get shipped
	 *
	 * @return \DateTime
	 */
	public function getShipped()
	{
		return $this->shipped;
	}

	/**
	 * Set shipped
	 *
	 * @param \DateTime $shipped
	 *
	 * @return Booking
	 */
	public function setShipped($shipped)
	{
		$this->shipped = $shipped;

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
	 * @return Booking
	 */
	public function setCreated($created)
	{
		$this->created = $created;

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
	 * Set modified
	 *
	 * @param \DateTime $modified
	 *
	 * @return Booking
	 */
	public function setModified($modified)
	{
		$this->modified = $modified;

		return $this;
	}

	/**
	 * Get contact
	 *
	 * @return \WarehouseBundle\Entity\BookingContact
	 */
	public function getContact()
	{
		return $this->contact;
	}

	/**
	 * Set contact
	 *
	 * @param \WarehouseBundle\Entity\BookingContact $contact
	 *
	 * @return Booking
	 */
	public function setContact(\WarehouseBundle\Entity\BookingContact $contact = null)
	{
		if (!is_null($contact)) $contact->setBooking($this);
		$this->contact = $contact;

		return $this;
	}

	/**
	 * Add file
	 *
	 * @param \WarehouseBundle\Entity\BookingFile $file
	 *
	 * @return Booking
	 */
	public function addFile(\WarehouseBundle\Entity\BookingFile $file)
	{
		$file->setBooking($this);
		$this->files[] = $file;

		return $this;
	}

	/**
	 * Remove file
	 *
	 * @param \WarehouseBundle\Entity\BookingFile $file
	 */
	public function removeFile(\WarehouseBundle\Entity\BookingFile $file)
	{
		$this->files->removeElement($file);
	}

	/**
	 * Get files
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getFiles()
	{
		return $this->files;
	}

	/**
	 * Add comment
	 *
	 * @param \WarehouseBundle\Entity\BookingComment $comment
	 *
	 * @return Booking
	 */
	public function addComment(\WarehouseBundle\Entity\BookingComment $comment)
	{
		$comment->setBooking($this);
		$this->comments[] = $comment;

		return $this;
	}

	/**
	 * Remove comment
	 *
	 * @param \WarehouseBundle\Entity\BookingComment $comment
	 */
	public function removeComment(\WarehouseBundle\Entity\BookingComment $comment)
	{
		$this->comments->removeElement($comment);
	}

	/**
	 * Get comments
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getComments()
	{
		return $this->comments;
	}

	/**
	 * Add product
	 *
	 * @param \WarehouseBundle\Entity\BookingProduct $product
	 *
	 * @return Booking
	 */
	public function addProduct(\WarehouseBundle\Entity\BookingProduct $product)
	{
		$product->setBooking($this);    # Helpful for cascading
		$this->products[] = $product;

		return $this;
	}

	/**
	 * Remove product
	 *
	 * @param \WarehouseBundle\Entity\BookingProduct $product
	 */
	public function removeProduct(\WarehouseBundle\Entity\BookingProduct $product)
	{
		$this->products->removeElement($product);
	}

	/**
	 * Get products
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getProducts()
	{
		return $this->products;
	}

	/**
	 * Add return
	 *
	 * @param \WarehouseBundle\Entity\BookingReturn $return
	 *
	 * @return Booking
	 */
	public function addReturn(\WarehouseBundle\Entity\BookingReturn $return)
	{
		$return->setBooking($this);
		$this->returns[] = $return;

		return $this;
	}

	/**
	 * Remove return
	 *
	 * @param \WarehouseBundle\Entity\BookingReturn $return
	 */
	public function removeReturn(\WarehouseBundle\Entity\BookingReturn $return)
	{
		$this->returns->removeElement($return);
	}

	/**
	 * Get returns
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getReturns()
	{
		return $this->returns;
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

	/**
	 * Set user
	 *
	 * @param \WarehouseBundle\Entity\User $user
	 *
	 * @return Booking
	 */
	public function setUser(\WarehouseBundle\Entity\User $user = null)
	{
		$this->user = $user;

		return $this;
	}
}
