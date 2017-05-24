<?php

namespace WarehouseBundle\DTO\Booking\PickSummary;


use WarehouseBundle\Entity\Booking;

class Summary
{
	/** @var Item[] $items */
	private $items;
	/** @var Booking[] $bookings */
	private $bookings;

	/**
	 * @return Item[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param Item[] $items
	 */
	public function setItems(array $items)
	{
		$this->items = $items;
	}

	/**
	 * @return Booking[]
	 */
	public function getBookings(): array
	{
		return $this->bookings;
	}

	/**
	 * @param Booking[] $bookings
	 */
	public function setBookings(array $bookings)
	{
		$this->bookings = $bookings;
	}
}