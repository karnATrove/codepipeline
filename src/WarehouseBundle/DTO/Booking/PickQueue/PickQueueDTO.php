<?php

/**
 * 
 */

namespace WarehouseBundle\DTO\Booking\PickQueue;


#use WarehouseBundle\Entity\Booking;

class PickQueueDTO
{
	/** @var PickQueueItemDTO[] $items */
	private $items;

	/**
	 * PickQueueDTO constructor.
	 *
	 * @param PickQueueItemDTO[] $items
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}

	/**
	 * @return PickQueueItemDTO[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param PickQueueItemDTO[] $items
	 */
	public function setItems(array $items)
	{
		$this->items = $items;
	}
}