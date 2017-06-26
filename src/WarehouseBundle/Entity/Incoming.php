<?php

namespace WarehouseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Incoming
 * @Gedmo\Loggable
 * @ORM\Table(name="warehouse_incoming")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingRepository")
 */
class Incoming
{
	//region Constants

	//Status
//	const STATUS_DELETED = 0;
//	const STATUS_INBOUND = 1;
//	const STATUS_ARRIVED = 2;
//	const STATUS_COMPLETED = 3;
//
//	//Type
//	const TYPE_OCEAN_FREIGHT = 1;
//	const TYPE_FORWARD = 2;

	//endregion

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * One Incoming has One User
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	private $user;

	/**
	 * @var string
	 * @Gedmo\Versioned
	 * @ORM\Column(name="name", type="string", length=50)
	 */
	private $name;

	/**
	 * @var \DateTime
	 * @Gedmo\Versioned
	 * @ORM\Column(name="eta", type="date", nullable=true)
	 */
	private $eta;

	/**
	 * @var \DateTime
	 * @Gedmo\Versioned
	 * @ORM\Column(name="scheduled", type="datetime", nullable=true)
	 */
	private $scheduled;

	/**
	 * @var \DateTime
	 * @Gedmo\Versioned
	 * @ORM\Column(name="arrived", type="datetime", nullable=true)
	 */
	private $arrived;

	/**
	 * @var \DateTime
	 * @Gedmo\Versioned
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
	 * One Incoming has Many IncomingFile.
	 * @ORM\OneToMany(targetEntity="IncomingFile", mappedBy="incoming")
	 */
	private $files;

	/**
	 * @var ArrayCollection
	 *
	 * One Incoming has Many IncomingComment.
	 * @ORM\OneToMany(targetEntity="IncomingComment", mappedBy="incoming")
	 */
	private $comments;

	/**
	 * One Incoming has Many IncomingProduct.
	 * @ORM\OneToMany(targetEntity="IncomingProduct", mappedBy="incoming", cascade={"persist","remove"})
	 */
	private $incoming_products;

	/**
	 * @var IncomingType $type
	 * @ORM\ManyToOne(targetEntity="WarehouseBundle\Entity\IncomingType", inversedBy="incoming")
	 */
	private $type;

	/**
	 * @var IncomingStatus $status
	 * @ORM\ManyToOne(targetEntity="WarehouseBundle\Entity\IncomingStatus", inversedBy="incoming")
	 */
	private $status;

	/**
	 * @var IncomingProductScan[]
	 *
	 * One Incoming has Many IncomingProductScan.
	 * @ORM\OneToMany(targetEntity="IncomingProductScan", mappedBy="incoming", cascade={"persist","remove"})
	 * @ORM\OrderBy({"id" = "DESC", "modified" = "DESC"})
	 */
	private $incoming_scanned_products;

	public function __construct()
	{
		$this->files = new ArrayCollection();
		$this->incoming_products = new ArrayCollection();
		$this->incoming_scanned_products = new ArrayCollection();
		$this->comments = new ArrayCollection();
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
	 * Get type
	 *
	 * @return IncomingType
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param IncomingType $type
	 *
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Incoming
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get eta
	 *
	 * @return \DateTime
	 */
	public function getEta()
	{
		return $this->eta;
	}

	/**
	 * Set eta
	 *
	 * @param \DateTime $eta
	 *
	 * @return Incoming
	 */
	public function setEta($eta)
	{
		$this->eta = $eta;

		return $this;
	}

	/**
	 * Get scheduled
	 *
	 * @return \DateTime
	 */
	public function getScheduled()
	{
		return $this->scheduled;
	}

	/**
	 * Set scheduled
	 *
	 * @param \DateTime $scheduled
	 *
	 * @return Incoming
	 */
	public function setScheduled($scheduled)
	{
		$this->scheduled = $scheduled;

		return $this;
	}

	/**
	 * Get arrived
	 *
	 * @return \DateTime
	 */
	public function getArrived()
	{
		return $this->arrived;
	}

	/**
	 * Set arrived
	 *
	 * @param \DateTime $arrived
	 *
	 * @return Incoming
	 */
	public function setArrived($arrived)
	{
		$this->arrived = $arrived;

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
	 * @return Incoming
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
	 * @return Incoming
	 */
	public function setModified($modified)
	{
		$this->modified = $modified;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return IncomingStatus
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Set status
	 *
	 * @param IncomingStatus $status
	 *
	 * @return Incoming
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Add file
	 *
	 * @param IncomingFile $file
	 *
	 * @return Incoming
	 */
	public function addFile(IncomingFile $file)
	{
		$file->setIncoming($this);
		$this->files[] = $file;

		return $this;
	}

	/**
	 * Remove file
	 *
	 * @param IncomingFile $file
	 */
	public function removeFile(IncomingFile $file)
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
	 * @param IncomingComment $comment
	 *
	 * @return Incoming
	 */
	public function addComment(IncomingComment $comment)
	{
		$comment->setIncoming($this);
		$this->comments[] = $comment;
		return $this;
	}

	/**
	 * Remove comment
	 *
	 * @param IncomingComment $comment
	 */
	public function removeComment(IncomingComment $comment)
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
	 * Add incomingProduct
	 *
	 * @param IncomingProduct $incomingProduct
	 *
	 * @return Incoming
	 */
	public function addIncomingProduct(IncomingProduct $incomingProduct)
	{
		$incomingProduct->setIncoming($this);
		$this->incoming_products[] = $incomingProduct;

		return $this;
	}

	/**
	 * Remove incomingProduct
	 *
	 * @param IncomingProduct $incomingProduct
	 */
	public function removeIncomingProduct(IncomingProduct $incomingProduct)
	{
		$this->incoming_products->removeElement($incomingProduct);
	}

	/**
	 * Get incomingProducts
	 *
	 * @return IncomingProduct[]|ArrayCollection
	 */
	public function getIncomingProducts()
	{
		return $this->incoming_products;
	}

	/**
	 * Add incomingProduct
	 *
	 * @param IncomingProductScan $incomingScannedProduct
	 *
	 * @return Incoming
	 */
	public function addIncomingScannedProduct(IncomingProductScan $incomingScannedProduct)
	{
		$incomingScannedProduct->setIncoming($this);
		$this->incoming_scanned_products[] = $incomingScannedProduct;

		return $this;
	}

	/**
	 * Remove incomingScannedProduct
	 *
	 * @param IncomingProductScan $incomingScannedProduct
	 */
	public function removeIncomingScannedProduct(IncomingProductScan $incomingScannedProduct)
	{
		$this->incoming_scanned_products->removeElement($incomingScannedProduct);
	}

	/**
	 * @return IncomingProductScan[]
	 */
	public function getIncomingScannedProducts()
	{
		return $this->incoming_scanned_products;
	}

	/**
	 * @param IncomingProductScan[] $incomingScannedProducts
	 *
	 * @return $this
	 */
	public function setIncomingScannedProducts($incomingScannedProducts)
	{
		$this->incoming_scanned_products = $incomingScannedProducts;

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

	/**
	 * Set user
	 *
	 * @param User $user
	 *
	 * @return Incoming
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;

		return $this;
	}
}
