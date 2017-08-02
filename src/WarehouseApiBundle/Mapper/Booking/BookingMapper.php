<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-22
 * Time: 3:42 PM
 */

namespace WarehouseApiBundle\Mapper\Booking;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Rove\CanonicalDto\Booking\BookingDto;
use WarehouseApiBundle\Exception\MapperException;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Manager\BookingStatusManager;
use WarehouseBundle\Manager\BookingTypeManager;
use WarehouseBundle\Manager\CarrierManager;

class BookingMapper
{
	/**
	 * @param Booking $booking
	 *
	 * @return BookingDto
	 */
	public static function mapToDto(Booking $booking)
	{
		$serializer = SerializerBuilder::create()->build();
		$bookingJson = $serializer->serialize($booking, 'json');
		/** @var BookingDto $bookingDto */
		$bookingDto = $serializer->deserialize($bookingJson, BookingDto::class, 'json');
		$bookingDto->setCarrierCode($booking->getCarrier()->getCode());
		$bookingDto->setOrderType(BookingTypeManager::getCode($booking->getOrderType()));
		$bookingDto->setSkidCount($booking->getSkidCount());
		$bookingDto->setShippedDate($booking->getShipped());
		$bookingDto->setStatusCode(BookingStatusManager::getCode($booking->getStatus()));
		$bookingDto->setFutureShipDate($booking->getFutureship());
		$bookingDto->setCreatedAt($booking->getCreated());
		$bookingDto->setUpdatedAt($booking->getModified());
		$bookingDto->setBookingComments(BookingCommentMapper::mapToDtoList($booking->getComments()));
		$bookingDto->setBookingContacts([BookingContactMapper::mapToDto($booking->getContact())]);
		$bookingDto->setBookingItems(BookingItemMapper::mapToDtoList($booking->getProducts()));
		return $bookingDto;
	}

	/**
	 * @param $list
	 *
	 * @return array
	 */
	public static function mapToDtoList($list)
	{
		$dtoList = [];
		foreach ($list as $item) {
			$dtoList[] = self::mapToDto($item);
		}
		return $dtoList;
	}

    /**
     *
     * @param BookingDto $bookingDto
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     *
     * @return \WarehouseBundle\Entity\Booking
     * @throws \WarehouseApiBundle\Exception\MapperException
     */
    public static function mapDtoToEntity(BookingDto $bookingDto, EntityManagerInterface $entityManager)
    {
        $booking = new Booking();
        $booking->setOrderNumber($bookingDto->getOrderNumber());
        $booking->setOrderType(BookingTypeManager::getTypeIdByCode($bookingDto->getOrderType()));
        $booking->setOrderReference($bookingDto->getOrderReference());
        $carrierManager = new CarrierManager($entityManager);
        $carrier = $carrierManager->findOneBy(['code'=>$bookingDto->getCarrierCode()]);
        if (!$carrier) {
            throw new MapperException("Failed to match carrier with code {$bookingDto->getCarrierCode()}");
        }
        $booking->setCarrier($carrier);
        $booking->setSkidCount($bookingDto->getSkidCount());
        $booking->setFutureship($bookingDto->getFutureShipDate());
        $booking->setShipped($bookingDto->getShippedDate());
        $booking->setStatus(BookingStatusManager::getStatusByCode($bookingDto->getStatusCode()));

        return $booking;
    }
}