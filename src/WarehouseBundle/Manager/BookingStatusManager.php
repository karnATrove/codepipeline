<?php

namespace WarehouseBundle\Manager;


use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingStatusManager extends BaseManager
{
	const STATUS_CODE_DELETED = "DEL";
	const STATUS_CODE_AWAITING_FORWARD = "AWAT";
	const STATUS_CODE_ACCEPTED = "ACPT";
	const STATUS_CODE_PICKED = "PICK";
	const STATUS_CODE_PACKED = "PACK";
	const STATUS_CODE_SHIPPED = "SHIP";

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
				return self::STATUS_CODE_DELETED;
				break;
			case Booking::STATUS_AWAITING_FORWARD:
				return self::STATUS_CODE_AWAITING_FORWARD;
				break;
			case Booking::STATUS_ACCEPTED:
				return self::STATUS_CODE_ACCEPTED;
				break;
			case Booking::STATUS_PICKED:
				return self::STATUS_CODE_PICKED;
				break;
			case Booking::STATUS_PACKED:
				return self::STATUS_CODE_PACKED;
				break;
			case Booking::STATUS_SHIPPED:
				return self::STATUS_CODE_SHIPPED;
				break;
			default:
				throw new ManagerException("Can't find match status code");
				break;
		}
	}
}