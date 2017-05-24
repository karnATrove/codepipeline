<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-23
 * Time: 4:37 PM
 */

namespace WarehouseBundle\DTO\Booking\PickSummary;


class ItemLocation
{
	private $aisle;
	private $row;
	private $level;
	private $quantity;

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

}