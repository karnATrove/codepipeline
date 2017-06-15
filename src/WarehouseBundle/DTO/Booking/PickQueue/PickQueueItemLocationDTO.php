<?php
/**
 * 
 */

namespace WarehouseBundle\DTO\Booking\PickQueue;


class PickQueueItemLocationDTO
{
	private $id;
	private $locationId;
	private $aisle;
	private $row;
	private $level;
	private $quantity;
	private $quantityStaged;
	private $staging;
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
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param mixed $quantity
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
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
	public function getModified()
	{
		return $this->modified;
	}

	/**
	 * @param mixed $staging
	 */
	public function setModified($modified)
	{
		$this->modified = $modified;
	}

	public function printLocation(){
		return $this->getAisle(). ' - '. $this->getRow(). ' - '. $this->getLevel();
	}


	/**
	 * Convert to string.
	 * @return string [description]
	 */
	public function __toString()
	{
		return $this->getAisle() . '-' . $this->getRow() . '-' . $this->getLevel();
	}
}