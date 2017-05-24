<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WarehouseBundle\Entity\Booking;
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
	public function getPickSummaryData(array $orderIds)
	{
		$data['items'] = $this->bookingRepository->pickingSummaryByBookingIds($orderIds);

		$serializer = new Serializer([new GetSetMethodNormalizer()]);
		/** @var PickSummaryModel $pickSummary */
		$pickSummary = $serializer->denormalize($data, PickSummaryModel::class);
		return $pickSummary;
	}

}