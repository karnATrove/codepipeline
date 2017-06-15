<?php
/**
 * 
 */

namespace WarehouseBundle\DTO\Booking\PickQueue;


class PickQueueItemDTO
{
	/** @var string $sku */
	private $sku;
	/** @var int $orderedQuantity */
	private $orderedQuantity;
	/** @var int $boxCount */
	private $boxCount;
	/** @var int $quantityStaged */
	private $quantityStaged;
	/** @var PickQueueItemLocationDTO[] $itemLocations */
	private $itemLocations;

	/**
	 * @return string
	 */
	public function getSku()
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
	public function getBoxCount()
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
	 * @return PickQueueItemLocationDTO[]
	 */
	public function getItemLocations(): array
	{
		return $this->itemLocations;
	}

	/**
	 * @param PickQueueItemLocationDTO[] $itemLocations
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
	 * @return int
	 */
	public function getQuantityStaged()
	{
		return $this->quantityStaged;
	}

	/**
	 * @param int $quantityStaged
	 */
	public function setQuantityStaged($quantityStaged)
	{
		$this->quantityStaged = $quantityStaged;
	}

}