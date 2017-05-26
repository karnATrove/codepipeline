<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-25
 * Time: 11:02 AM
 */

namespace WarehouseBundle\DTO\Booking\PickSummary;


class PickSummaryItemOrder
{
	private $orderNumber;
	private $quantity;

	/**
	 * @return mixed
	 */
	public function getOrderNumber()
	{
		return $this->orderNumber;
	}

	/**
	 * @param mixed $orderNumber
	 */
	public function setOrderNumber($orderNumber)
	{
		$this->orderNumber = $orderNumber;
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