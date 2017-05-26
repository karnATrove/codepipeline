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
use WarehouseBundle\Model\Booking\PickSummary\PickSummaryItemModel;
use WarehouseBundle\Model\Booking\PickSummary\PickSummaryModel;

class BookingManager
{
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

	public function getAllBookingByStatusQueryBuilder($status)
	{
		$queryBuilder = $this->bookingRepository->createQueryBuilder('e');
		$queryBuilder->andWhere('e.status <> :status');
		$queryBuilder->setParameter('status', $status);
		return $queryBuilder;
	}

	public function getQueryBuilderForBooking()
	{

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
	 * @param array $idList
	 *
	 * @return Booking[]
	 */
	public function getBookingByIdList(array $idList)
	{
		return $this->bookingRepository->findBookingByIds($idList);
	}

	/**
	 * @param Booking[] $bookingList
	 *
	 * @return array
	 */
	public function getDistinctSkuQuantityByBookingList(array $bookingList)
	{
		$skuList = [];
		foreach ($bookingList as $booking) {
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
	 * @param array $orderIds
	 *
	 * @return PickSummaryModel
	 */
	public function getPickSummaryModel(array $orderIds): PickSummaryModel
	{
		$data = $this->bookingRepository->pickingSummaryByBookingIds($orderIds);
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
	public function getPickSummaryDTO(PickSummaryModel $pickSummaryModel): ?PickSummaryDTO
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
}