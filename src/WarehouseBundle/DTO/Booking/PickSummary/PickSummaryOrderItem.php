<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-23
 * Time: 4:43 PM
 */

namespace WarehouseBundle\DTO\Booking;


class PickSummaryOrderItem
{
	private $orderId;
	private $sku;
	private $quantity;
	private $boxCount;

	/**
	 * @return mixed
	 */
	public function getOrderId()
	{
		return $this->orderId;
	}

	/**
	 * @param mixed $orderId
	 */
	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
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
	public function getBoxCount()
	{
		return $this->boxCount;
	}

	/**
	 * @param mixed $boxCount
	 */
	public function setBoxCount($boxCount)
	{
		$this->boxCount = $boxCount;
	}

}