<?php

namespace WarehouseBundle\Manager;


use Rove\CanonicalDto\Booking\BookingDto;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingStatusManager extends BaseManager
{
	/**
	 * get status code from status id
	 *
	 * @param $statusId
	 *
	 * @return string
	 * @throws ManagerException
	 */
	public static function getCode($statusId)
	{
		switch ($statusId) {
			case Booking::STATUS_DELETED:
				return BookingDto::STATUS_CODE_DELETED;
				break;
			case Booking::STATUS_AWAITING_FORWARD:
				return BookingDto::STATUS_CODE_AWAITING_FORWARD;
				break;
			case Booking::STATUS_ACCEPTED:
				return BookingDto::STATUS_CODE_ACCEPTED;
				break;
			case Booking::STATUS_PICKED:
				return BookingDto::STATUS_CODE_PICKED;
				break;
			case Booking::STATUS_PACKED:
				return BookingDto::STATUS_CODE_PACKED;
				break;
			case Booking::STATUS_SHIPPED:
				return BookingDto::STATUS_CODE_SHIPPED;
				break;
			default:
				throw new ManagerException("Can't find match status code");
				break;
		}
	}
}