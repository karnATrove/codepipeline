<?php

namespace WarehouseBundle\Model\Booking\PickSummary;


class PickSummaryModel
{
	/** @var PickSummaryItemModel[] $items */
	private $items;

	/**
	 * @return PickSummaryItemModel[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param PickSummaryItemModel[] $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}

}