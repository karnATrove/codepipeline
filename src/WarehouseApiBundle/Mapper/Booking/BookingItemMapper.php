<?php

namespace WarehouseApiBundle\Mapper\Booking;

use Rove\CanonicalDto\Booking\BookingCommentDto;
use Rove\CanonicalDto\Booking\BookingItemDto;
use WarehouseBundle\Entity\BookingComment;
use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Manager\BookingProductManager;

class BookingItemMapper
{
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
	 * @param BookingProduct $bookingProduct
	 *
	 * @return BookingItemDto
	 */
	public static function mapToDto(BookingProduct $bookingProduct)
	{
		$bookingItemDto = new BookingItemDto();
		$bookingItemDto->setQuantity($bookingProduct->getQty());
		$bookingItemDto->setPickedDate($bookingProduct->getPickedDate());
		$bookingItemDto->setCreatedAt($bookingProduct->getCreated());
		$bookingItemDto->setUpdatedAt($bookingProduct->getModified());
		$bookingItemDto->setStatusCode(BookingProductManager::getCode($bookingProduct->getStatus()));
		$bookingItemDto->setSku($bookingProduct->getProduct()->getModel());
		return $bookingItemDto;
	}

}