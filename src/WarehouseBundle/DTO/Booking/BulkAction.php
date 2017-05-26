<?php

namespace WarehouseBundle\DTO\Booking;


class BulkAction
{
	const ACTION_DELETE = 'delete';
	const ACTION_PICKING_ON = 'pickingOn';
	const ACTION_PICKING_OFF = 'pickingOff';
	const ACTION_PICK_SUMMARY = 'pickSummary';
	const ACTION_PRINT_WITH_DOCUMENTS = 'printWithDocuments';


	/** @var array $orderIds */
	private $orderIds;
	/** @var string $action */
	private $action;

	/**
	 * @return array
	 */
	public function getOrderIds(): array
	{
		return $this->orderIds;
	}

	/**
	 * @param array $orderIds
	 */
	public function setOrderIds(array $orderIds)
	{
		$this->orderIds = $orderIds;
	}

	/**
	 * @return string
	 */
	public function getAction(): string
	{
		return $this->action;
	}

	/**
	 * @param string $action
	 */
	public function setAction(string $action)
	{
		$this->action = $action;
	}

}