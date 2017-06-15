<?php
/**
 * 
 */
namespace WarehouseBundle\Model\Booking\PickQueue;


class PickQueueModel
{
	/** @var PickQueueItemModel[] $items */
	private $items;

	/**
	 * PickQueueModel constructor.
	 *
	 * @param PickSummaryItemModel[] $items
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}

	/**
	 * @return PickQueueItemModel[]
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param PickQueueItemModel[]|Object $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}

}