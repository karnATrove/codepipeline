<?php
/**
 * 
 */

namespace WarehouseBundle\Model\Booking\PickQueue;


class PickQueueItemModel
{
	private $id;
	private $bookingProductId;
	private $orderedQuantity;
	private $productId;
	private $sku;
	private $locationId;
	private $aisle;
	private $row;
	private $level;
	private $staging;
	private $quantityLevel;
	private $quantityPerCarton;
	private $quantityStaged;
	private $modified;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getBookingProductId()
	{
		return $this->bookingProductId;
	}

	/**
	 * @param mixed $bookingProductId
	 */
	public function setBookingProductId($bookingProductId)
	{
		$this->bookingProductId = $bookingProductId;
	}

	/**
	 * @return mixed
	 */
	public function getOrderedQuantity()
	{
		return $this->orderedQuantity;
	}

	/**
	 * @param mixed $orderedQuantity
	 */
	public function setOrderedQuantity($orderedQuantity)
	{
		$this->orderedQuantity = $orderedQuantity;
	}

	/**
	 * @return mixed
	 */
	public function getProductId()
	{
		return $this->productId;
	}

	/**
	 * @param mixed $productId
	 */
	public function setProductId($productId)
	{
		$this->productId = $productId;
	}

	/**
	 * @return mixed
	 */
	public function getSku()
	{
		return $this->sku;
	}

	/**
	 * @param mixed $sku
	 */
	public function setSku($sku)
	{
		$this->sku = $sku;
	}

	/**
	 * @return mixed
	 */
	public function getLocationId()
	{
		return $this->locationId;
	}

	/**
	 * @param mixed $locationId
	 */
	public function setLocationId($locationId)
	{
		$this->locationId = $locationId;
	}

	/**
	 * @return mixed
	 */
	public function getRow()
	{
		return $this->row;
	}

	/**
	 * @param mixed $row
	 */
	public function setRow($row)
	{
		$this->row = $row;
	}

	/**
	 * @return mixed
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * @param mixed $level
	 */
	public function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * @return mixed
	 */
	public function getStaging()
	{
		return $this->staging;
	}

	/**
	 * @param mixed $staging
	 */
	public function setStaging($staging)
	{
		$this->staging = $staging;
	}

	/**
	 * @return mixed
	 */
	public function getQuantityLevel()
	{
		return $this->quantityLevel;
	}

	/**
	 * @param mixed $quantityLevel
	 */
	public function setQuantityLevel($quantityLevel)
	{
		$this->quantityLevel = $quantityLevel;
	}

	/**
	 * @return mixed
	 */
	public function getQuantityPerCarton()
	{
		return $this->quantityPerCarton;
	}

	/**
	 * @param mixed $quantityPerCarton
	 */
	public function setQuantityPerCarton($quantityPerCarton)
	{
		$this->quantityPerCarton = $quantityPerCarton;
	}

	/**
	 * @return mixed
	 */
	public function getQuantityStaged()
	{
		return $this->quantityStaged;
	}

	/**
	 * @param mixed $quantityStaged
	 */
	public function setQuantityStaged($quantityStaged)
	{
		$this->quantityStaged = $quantityStaged;
	}

	/**
	 * @return mixed
	 */
	public function getAisle()
	{
		return $this->aisle;
	}

	/**
	 * @param mixed $aisle
	 */
	public function setAisle($aisle)
	{
		$this->aisle = $aisle;
	}

	/**
	 * @return mixed
	 */
	public function getModified()
	{
		return $this->modified;
	}

	/**
	 * @param mixed $modified
	 */
	public function setModified($modified)
	{
		$this->modified = $modified;
	}

}