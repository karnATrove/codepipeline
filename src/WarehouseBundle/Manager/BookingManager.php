<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WarehouseBundle\DTO\Booking\PickSummary\PickSummaryDTO;
use WarehouseBundle\DTO\Booking\PickSummary\PickSummaryItemDTO;
use WarehouseBundle\DTO\Booking\PickSummary\PickSummaryItemLocationDTO;
use WarehouseBundle\DTO\Booking\PickSummary\PickSummaryItemOrder;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\BookingManagerException;
use WarehouseBundle\Exception\Utils\UrlFileNotFoundException;
use WarehouseBundle\Model\Booking\PickSummary\PickSummaryItemModel;
use WarehouseBundle\Model\Booking\PickSummary\PickSummaryModel;
use WarehouseBundle\Utils\DownloadUtility;
use WarehouseBundle\Utils\FileUtility;

class BookingManager
{
	const BASE_WEBSITE_ADDRESS = "https://www.roveconcepts.com/remote/c9c078d09dbfa23992cb150ccadc238f/carrier/downloads/";

	private $bookingRepository;
	private $em;

	/**
	 * BookingManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->em = $entityManager;
		$this->bookingRepository = $this->em->getRepository('WarehouseBundle:Booking');
	}

	public static function formatPickSummaryForView(PickSummaryDTO $pickSummaryDTO)
	{
		$resp = [];
		foreach ($pickSummaryDTO->getItems() as $item) {
			$sku = $item->getSku();
			$sizeOfOrders = sizeof($item->getOrders());
			$sizeOfLocations = sizeof($item->getItemLocations());
			$loop = $sizeOfOrders > $sizeOfLocations ? $sizeOfOrders : $sizeOfLocations;

			$orderList = [];
			foreach ($item->getOrders() as $order){
				$orderList[]=$order;
			}

			$locationList = [];
			foreach ($item->getItemLocations() as $location){
				$locationList[]=$location;
			}


			for ($i = 0; $i < $loop; $i++) {
				$order = isset($orderList[$i])?$orderList[$i]:null;
				$location = isset($locationList[$i])?$locationList[$i]:null;
				if ($i == 0) {
					$resp[] = ['sku' => $sku,
						'description' => $item->getDescription(),
						'qty' => $item->getOrderedQuantity() . "({$item->getBoxCount()})",
						'detail' => $order->getOrderNumber() . " ordered {$order->getQuantity()}",
						'location' => $location->printLocation(),
						'stockLevel' => $location->getQuantity(),
					];
				} else {
					if ($order) {
						$resp[$i] = ['detail' => $order->getOrderNumber() . " ordered {$order->getQuantity()}"];
					}
					if ($location) {
						$resp[$i] = [
							'location' => $location->printLocation(),
							'stockLevel' => $location->getQuantity(),];
					}
				}
			}
		}
		return $resp;
	}

	/**
	 * @param $status
	 *
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getAllBookingByStatusQueryBuilder($status)
	{
		$queryBuilder = $this->bookingRepository->createQueryBuilder('e');
		$queryBuilder->andWhere('e.status <> :status');
		$queryBuilder->setParameter('status', $status);
		return $queryBuilder;
	}

	/**
	 * @param string                 $id
	 * @param EntityManagerInterface $em
	 * @param bool                   $flush
	 */
	public function deleteBookingById($id, $em = null, $flush = true)
	{
		$booking = $this->getBookingById($id);
		if (!$booking) {
			throw new Exception("Booking not found");
		}
		$booking->setStatus(Booking::STATUS_DELETED);
		$this->updateBooking($booking, $em, $flush);
	}

	/**
	 * @param $id
	 *
	 * @return null|object|Booking
	 */
	public function getBookingById($id)
	{
		return $this->bookingRepository->find($id);
	}

	/**
	 * @param Booking                $booking
	 * @param EntityManagerInterface $em
	 * @param bool                   $flush
	 */
	public function updateBooking(Booking $booking, $em = null, $flush = true)
	{
		$em = $em ? $em : $this->em;
		$em->persist($booking);
		if ($flush) {
			$em->flush();
		}
	}

	/**
	 * @param Booking[] $bookings
	 *
	 * @return array
	 */
	public function getDistinctSkuQuantityByBookingList(array $bookings)
	{
		$skuList = [];
		foreach ($bookings as $booking) {
			foreach ($booking->getProducts() as $bookingProduct) {
				$sku = $bookingProduct->getProduct()->getModel();
				if (!isset($skuList[$sku])) {
					$skuList[$sku] = $bookingProduct->getQty();
				} else {
					$skuList[$sku] += $bookingProduct->getQty();
				}
			}
		}
		return $skuList;
	}

	/**
	 * @param array $bookingIds
	 *
	 * @return PickSummaryModel
	 */
	public function getPickSummaryModel(array $bookingIds): PickSummaryModel
	{
		$data = $this->bookingRepository->pickingSummaryByBookingIds($bookingIds);
		$pickSummary = $this->denormalizePickSummaryModel($data);
		return $pickSummary;
	}

	/**
	 * @param array $data
	 *
	 * @return PickSummaryModel
	 */
	private function denormalizePickSummaryModel(array $data): PickSummaryModel
	{
		$serializer = new Serializer([new ObjectNormalizer(), new ArrayDenormalizer()]);
		/** @var PickSummaryItemModel[] $pickSummaryItems */
		$pickSummaryItems = $serializer->denormalize($data, PickSummaryItemModel::class . "[]");
		$pickSummary = new PickSummaryModel($pickSummaryItems);
		return $pickSummary;
	}

	/**
	 * @param PickSummaryModel $pickSummaryModel
	 *
	 * @return null|PickSummaryDTO
	 */
	public function getPickSummaryDTO(PickSummaryModel $pickSummaryModel)
	{
		$pickSummaryItems = [];
		$processedLog = [];
		foreach ($pickSummaryModel->getItems() as $pickSummaryItemModel) {
			$bookingProductId = $pickSummaryItemModel->getBookingProductId();
			$sku = $pickSummaryItemModel->getSku();
			if (!in_array($bookingProductId, $processedLog)) {
				$processedLog[] = $bookingProductId;
				$orderedQuantity = $pickSummaryItemModel->getOrderedQuantity();
				if ($orderedQuantity <= 0) {
					continue;
				}
				$boxNeeded = ceil($pickSummaryItemModel->getQuantityPerCarton() / $orderedQuantity);
				if (key_exists($sku, $pickSummaryItems)) {
					$pickSummaryItems[$sku]['orderedQuantity'] += $orderedQuantity;
					$pickSummaryItems[$sku]['boxCount'] += $boxNeeded;
				} else {
					$pickSummaryItems[$sku]['orderedQuantity'] = $orderedQuantity;
					$pickSummaryItems[$sku]['boxCount'] = $boxNeeded;
					$pickSummaryItems[$sku]['sku'] = $sku;
					$pickSummaryItems[$sku]['description'] = $pickSummaryItemModel->getDescription();
				}
				$pickSummaryItems[$sku]['orders'][] = [
					'orderNumber' => $pickSummaryItemModel->getOrderNumber(),
					'quantity' => $orderedQuantity
				];
			}

			if (!isset($pickSummaryItems[$sku]['itemLocations'])) {
				$pickSummaryItems[$sku]['itemLocations'] = [];
			}

			$locationId = $pickSummaryItemModel->getLocationId();
			if (!key_exists($locationId, $pickSummaryItems[$sku]['itemLocations'])) {
				$pickSummaryItems[$sku]['itemLocations'][$locationId] = [
					'aisle' => $pickSummaryItemModel->getAisle(),
					'row' => $pickSummaryItemModel->getRow(),
					'level' => $pickSummaryItemModel->getLevel(),
					'quantity' => $pickSummaryItemModel->getQuantityLevel()
				];
			}
		}
		return $this->denormalizePickSummaryDTO($pickSummaryItems);
	}


	public static function validatePickSummaryDto(PickSummaryDTO $pickSummaryDTO){
		$error='';
		foreach ($pickSummaryDTO->getItems() as $pickSummaryItemDTO){
			$orderNumbers="";
			foreach ($pickSummaryItemDTO->getOrders() as $order){
				$orderNumbers.="#{$order->getOrderNumber()} ";
			}
			$totalOrderedQuantity = $pickSummaryItemDTO->getOrderedQuantity();
			$stockCount=0;
			foreach ($pickSummaryItemDTO->getItemLocations() as $location){
				$stockCount+=$location->getQuantity();
			}
			if ($totalOrderedQuantity>$stockCount){
				$error.="Not enough quantity for order {$orderNumbers}";
			}
		}
		if (!empty($error)){
			throw new BookingManagerException('Error',$error);
		}
	}

	/**
	 * @param array $pickSummaryItemsArray
	 *
	 * @return PickSummaryDTO
	 */
	private function denormalizePickSummaryDTO(array $pickSummaryItemsArray): PickSummaryDTO
	{
		$serializer = new Serializer([new ObjectNormalizer(), new ArrayDenormalizer()]);
		$pickSummaryItems = [];
		foreach ($pickSummaryItemsArray as $sku => $itemArray) {
			/** @var PickSummaryItemLocationDTO[] $itemLocations */
			$itemLocations = $serializer->denormalize($itemArray['itemLocations'], PickSummaryItemLocationDTO::class . "[]");
			/** @var PickSummaryItemOrder[] $itemOrders */
			$itemOrders = $serializer->denormalize($itemArray['orders'], PickSummaryItemOrder::class . "[]");
			/** @var PickSummaryItemDTO $pickSummaryItem */
			$pickSummaryItem = $serializer->denormalize($itemArray, PickSummaryItemDTO::class);
			$pickSummaryItem->setItemLocations($itemLocations);
			$pickSummaryItem->setOrders($itemOrders);
			$pickSummaryItems[] = $pickSummaryItem;
		}
		$pickSummaryDTO = new PickSummaryDTO($pickSummaryItems);
		return $pickSummaryDTO;
	}

	/**
	 * @param array $bookingIds
	 * @param       $dir
	 */
	public function downloadBookingDocs(array $bookingIds, $dir, $zipDir = null)
	{
		if (empty($bookingIds)) {
			throw new Exception('No booking ids provided');
		}

		$bookings = $this->getBookingByIdList($bookingIds);
		foreach ($bookings as $booking) {
			$bolUrl = self::getDefaultBookingLabel($booking);
			$labelUrl = self::getDefaultBookingBol($booking);
			if (!empty($bolUrl)) {
				try {
					DownloadUtility::downloadFileFromUrl($bolUrl, $dir);
				} catch (UrlFileNotFoundException $exception) {
				}
			}
			if (!empty($labelUrl)) {
				try {
					DownloadUtility::downloadFileFromUrl($labelUrl, $dir);
				} catch (UrlFileNotFoundException $exception) {
				}
			}
		}
		if (FileUtility::isDirEmpty($dir)) {
			throw new Exception('No file need to be download');
		}
		$zipDir = $zipDir ?? $dir . '.zip';
		FileUtility::zip($dir, $zipDir);
	}

	/**
	 * @param array $bookingIds
	 *
	 * @return Booking[]
	 */
	public function getBookingByIdList(array $bookingIds)
	{
		return $this->bookingRepository->findBookingByIds($bookingIds);
	}

	/**
	 * booking label from rove
	 *
	 * @param Booking $booking
	 *
	 * @return null|string
	 */
	public static function getDefaultBookingLabel(Booking $booking)
	{
		$url = self::BASE_WEBSITE_ADDRESS . $booking->getCarrier()->getId() . "/{$booking->getOrderReference()}/label";
		return DownloadUtility::isLinkExist($url) ? $url : null;
	}

	/**
	 * booking bol from rove
	 *
	 * @param Booking $booking
	 *
	 * @return null|string
	 */
	public static function getDefaultBookingBol(Booking $booking)
	{
		$url = self::BASE_WEBSITE_ADDRESS . $booking->getCarrier()->getId() . "/{$booking->getOrderReference()}/bol";
		return DownloadUtility::isLinkExist($url) ? $url : null;
	}
}