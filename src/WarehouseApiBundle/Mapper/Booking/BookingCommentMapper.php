<?php

namespace WarehouseApiBundle\Mapper\Booking;

use Rove\CanonicalDto\Booking\BookingCommentDto;
use WarehouseBundle\Entity\BookingComment;

class BookingCommentMapper
{
	/**
	 * @param $bookingCommentList
	 *
	 * @return array
	 */
	public static function mapToDtoList($bookingCommentList)
	{
		$bookingCommentDtoList = [];
		foreach ($bookingCommentList as $bookingComment) {
			$bookingCommentDtoList[] = self::mapToDto($bookingComment);
		}
		return $bookingCommentDtoList;
	}

	/**
	 * @param BookingComment $bookingComment
	 *
	 * @return BookingCommentDto
	 */
	public static function mapToDto(BookingComment $bookingComment)
	{
		$bookingCommentDto = new BookingCommentDto();
		$bookingCommentDto->setComment($bookingComment->getComment());
		$bookingCommentDto->setCreatedAt($bookingComment->getCreated());
		return $bookingCommentDto;
	}
}