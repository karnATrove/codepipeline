<?php

namespace WarehouseBundle\Workflow;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\DTO\Booking\BulkAction;
use WarehouseBundle\Manager\BookingManager;

class BookingWorkflow
{
	private $bookingManager;
	private $em;

	/**
	 * BookingWorkFlow constructor.
	 *
	 * @param BookingManager         $bookingManager
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(BookingManager $bookingManager, EntityManagerInterface $entityManager)
	{
		$this->bookingManager = $bookingManager;
		$this->em = $entityManager;
	}

	/**
	 * handle bulk action on booking page
	 *
	 * @param BulkAction $bulkAction
	 *
	 * @throws \Exception
	 */
	public function bulkAction(BulkAction $bulkAction)
	{
		$bookingIds = $bulkAction->getOrderIds();
		if (empty($bookingIds)) {
			return;
		}
		switch ($bulkAction->getAction()) {
			case BulkAction::ACTION_DELETE:
				$this->handleBulkDelete($bookingIds, $this->em);
				$this->em->flush();
				break;
			case BulkAction::ACTION_PICKING_ON:
				$this->handleBulkPicking($bookingIds, true, $this->em);
				$this->em->flush();
				break;
			case BulkAction::ACTION_PICKING_OFF:
				$this->handleBulkPicking($bookingIds, false, $this->em);
				$this->em->flush();
				break;
			default:
				throw new \Exception('Not implemented');
				break;
		}
	}

	/**
	 * @param                        $bookingIds
	 * @param EntityManagerInterface $entityManager
	 */
	private function handleBulkDelete($bookingIds, EntityManagerInterface $entityManager)
	{
		foreach ($bookingIds as $bookingId) {
			$this->bookingManager->deleteBookingById($bookingId, $entityManager, false);
		}
	}

	/**
	 * @param                        $bookingIds
	 * @param                        $isOn
	 * @param EntityManagerInterface $entityManager
	 */
	private function handleBulkPicking($bookingIds, $isOn, EntityManagerInterface $entityManager)
	{
		foreach ($bookingIds as $bookingId) {
			$booking = $this->bookingManager->getBookingById($bookingId);
			$isOn = $isOn ? 1 : 0;
			$booking->setPickingFlag($isOn);
			$this->bookingManager->updateBooking($booking, $entityManager, false);
		}
	}

	public function generatePickSummaryDTO(array $bookings)
	{
		$skuList = $this->bookingManager->getDistinctSkuQuantityByBookingList($bookings);
	}
}