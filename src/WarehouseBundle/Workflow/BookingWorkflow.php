<?php

namespace WarehouseBundle\Workflow;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use WarehouseBundle\DTO\Booking\BulkAction;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\BookingManagerException;
use WarehouseBundle\Manager\BookingManager;
use WarehouseBundle\Utils\FileUtility;

class BookingWorkflow
{
	const BULK_ACTION_TYPE_RENDER_PDF = "RENDER_PDF";
	const BULK_ACTION_TYPE_RETURN = "RETURN";

	private $bookingManager;
	/** @var EntityManagerInterface $em */
	private $em;
	private $templating;
	private $container;

	/**
	 * BookingWorkFlow constructor.
	 *
	 * @param BookingManager         $bookingManager
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->bookingManager = $container->get('warehouse.manager.booking_manager');
		$this->em = $container->get('doctrine')->getManager();
		$this->templating = $container->get('templating');
		$this->container = $container;
	}

	/**
	 * handle bulk action on booking page
	 *
	 * @param BulkAction $bulkAction
	 *
	 * @throws \Exception
	 */
	public function bulkAction(BulkAction $bulkAction, &$responseData = null)
	{
		$action = null;
		$bookingIds = $bulkAction->getOrderIds();
		if (empty($bookingIds)) {
			throw new \Exception('No booking selected');
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
			case BulkAction::ACTION_PICK_SUMMARY:
				$responseData = $this->handlePickSummary($bookingIds);
				$action = self::BULK_ACTION_TYPE_RENDER_PDF;
				break;
			case BulkAction::ACTION_PRINT_WITH_DOCUMENTS:
				$responseData = $this->downloadDocuments($bookingIds);
				$action = self::BULK_ACTION_TYPE_RETURN;
				break;
			default:
				throw new \Exception('Not implemented');
				break;
		}
		return $action;
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

	/**
	 * @param array $bookingIds
	 *
	 * @return mixed
	 */
	private function handlePickSummary(array $bookingIds)
	{
		$bookings = $this->bookingManager->getBookingByIdList($bookingIds);
		$unavailableBookings = $this->verifyBookingAvailability($bookings);
		if (!empty($unavailableBookings)) {
			$msg = "Few of your booking item are unavailable.";
			foreach ($unavailableBookings as $orderNumber => $unavailableBooking) {
				$msg .= " #{$orderNumber}";
			}
			throw new BookingManagerException('Booking Error', $msg);
		}
		$pickSummaryModel = $this->bookingManager->getPickSummaryModel($bookingIds);
		$pickSummaryDTO = $this->bookingManager->getPickSummaryDTO($pickSummaryModel);
		BookingManager::validatePickSummaryDto($pickSummaryDTO);
		$viewData = BookingManager::formatPickSummaryForView($pickSummaryDTO);
		return $this->templating->render('booking/pdf/pick_summary.html.twig', [
			'bookings' => $bookings,
			'viewData' => $viewData
		]);
	}

	/**
	 * can only validate for each order, not working for two order ordered same item
	 *
	 * @param Booking[] $bookings
	 *
	 * @return array
	 */
	public function verifyBookingAvailability(array $bookings): array
	{
		$unavailableBooking = [];
		foreach ($bookings as $booking) {
			foreach ($booking->getProducts() as $bookingProduct) {
				$sku = $bookingProduct->getProduct()->getModel();
				$availableQuantity = $this->container->get('warehouse.manager.product_manager')
					->getProductAvailableQuantity($sku);
				if ($availableQuantity < $bookingProduct->getQty()) {
					$unavailableBooking[$booking->getOrderNumber()][] = ['sku' => $sku, '
					availableQuantity' => $availableQuantity];
				}
			}
		}
		return $unavailableBooking;
	}

	/**
	 * @param $bookingIdList
	 *
	 * @return Response
	 */
	public function downloadDocuments($bookingIdList)
	{
		$dir = "temp/booking_documents_" . $this->container->get('security.token_storage')->getToken()->getUsername()
			. "_" . date('YmdHis');
		mkdir($dir, 0777, true);
		$zipDir = $dir . '.zip';
		$this->bookingManager->downloadBookingDocs($bookingIdList, $dir, $zipDir);
		$content = file_get_contents($zipDir);
		$response = new Response();
		//set headers
		$response->headers->set('Content-Type', 'application/zip');
		$response->headers->set('Content-Disposition', 'attachment;filename="' . basename($zipDir));
		$response->setContent($content);
		FileUtility::recursiveRemoveDirectory($dir);
		unlink($zipDir);
		return $response;
	}

}