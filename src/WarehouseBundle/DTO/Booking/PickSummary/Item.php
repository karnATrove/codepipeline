<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-23
 * Time: 4:33 PM
 */

namespace WarehouseBundle\DTO\Booking\PickSummary;


class Item
{
	/** @var string $sku */
	private $sku;
	/** @var int $boxCount */
	private $boxCount;
	/** @var ItemLocation[] $itemLocations */
	private $itemLocations;

	/**
	 * @return string
	 */
	public function getSku(): string
	{
		return $this->sku;
	}

	/**
	 * @param string $sku
	 */
	public function setSku(string $sku)
	{
		$this->sku = $sku;
	}

	/**
	 * @return int
	 */
	public function getBoxCount(): int
	{
		return $this->boxCount;
	}

	/**
	 * @param int $boxCount
	 */
	public function setBoxCount(int $boxCount)
	{
		$this->boxCount = $boxCount;
	}

	/**
	 * @return ItemLocation[]
	 */
	public function getItemLocations(): array
	{
		return $this->itemLocations;
	}

	/**
	 * @param ItemLocation[] $itemLocations
	 */
	public function setItemLocations(array $itemLocations)
	{
		$this->itemLocations = $itemLocations;
	}


}