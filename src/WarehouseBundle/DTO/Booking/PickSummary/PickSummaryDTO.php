<?php

namespace WarehouseBundle\DTO\Booking\PickSummary;


use WarehouseBundle\Entity\Booking;

class PickSummaryDTO
{
	/** @var PickSummaryItemDTO[] $items */
	private $items;

	/**
	 * PickSummaryDTO constructor.
	 *
	 * @param PickSummaryItemDTO[] $items
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}

	/**
	 * @return PickSummaryItemDTO[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param PickSummaryItemDTO[] $items
	 */
	public function setItems(array $items)
	{
		$this->items = $items;
	}
}