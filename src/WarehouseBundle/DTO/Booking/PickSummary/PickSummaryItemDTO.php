<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-23
 * Time: 4:33 PM
 */

namespace WarehouseBundle\DTO\Booking\PickSummary;


class PickSummaryItemDTO
{
	/** @var string $sku */
	private $sku;
	/** @var string $description */
	private $description;
	/** @var int $orderedQuantity */
	private $orderedQuantity;
	/** @var int $boxCount */
	private $boxCount;
	/** @var PickSummaryItemLocationDTO[] $itemLocations */
	private $itemLocations;
	/** @var PickSummaryItemOrder[] $orders */
	private $orders;

	/**
	 * @return string
	 */
	public function getSku(): ?string
	{
		return $this->sku;
	}

	/**
	 * @param string $sku
	 */
	public function setSku($sku)
	{
		$this->sku = $sku;
	}

	/**
	 * @return int
	 */
	public function getBoxCount(): ?int
	{
		return $this->boxCount;
	}

	/**
	 * @param int $boxCount
	 */
	public function setBoxCount($boxCount)
	{
		$this->boxCount = $boxCount;
	}

	/**
	 * @return PickSummaryItemLocationDTO[]
	 */
	public function getItemLocations(): array
	{
		return $this->itemLocations;
	}

	/**
	 * @param PickSummaryItemLocationDTO[] $itemLocations
	 */
	public function setItemLocations(array $itemLocations)
	{
		$this->itemLocations = $itemLocations;
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
	 * @return string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return PickSummaryItemOrder[]
	 */
	public function getOrders(): array
	{
		return $this->orders;
	}

	/**
	 * @param PickSummaryItemOrder[] $orders
	 */
	public function setOrders(array $orders)
	{
		$this->orders = $orders;
	}


}