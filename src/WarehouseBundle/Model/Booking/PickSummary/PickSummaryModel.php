<?php

namespace WarehouseBundle\Model\Booking\PickSummary;


class PickSummaryModel
{
	/** @var PickSummaryItemModel[] $items */
	private $items;

	/**
	 * PickSummaryModel constructor.
	 *
	 * @param PickSummaryItemModel[] $items
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}

	/**
	 * @return PickSummaryItemModel[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param PickSummaryItemModel[]|Object $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}

}