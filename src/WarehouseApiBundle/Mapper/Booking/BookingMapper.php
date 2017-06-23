<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-22
 * Time: 3:42 PM
 */

namespace WarehouseApiBundle\Mapper\Booking;

use JMS\Serializer\SerializerBuilder;
use Rove\CanonicalDto\Booking\BookingDto;
use Rove\CanonicalDto\Booking\BookingLogDto;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingLog;
use WarehouseBundle\Manager\BookingStatusManager;
use WarehouseBundle\Manager\BookingTypeManager;

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
}