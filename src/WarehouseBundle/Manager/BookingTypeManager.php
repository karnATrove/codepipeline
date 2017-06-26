<?php

namespace WarehouseBundle\Manager;


use Rove\CanonicalDto\Booking\BookingDto;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingTypeManager extends BaseManager
{
	/**
	 * get type code from type id
	 *
	 * @param $typeId
	 *
	 * @return string
	 * @throws ManagerException
	 */
	public static function getCode($typeId)
	{
		switch ($typeId) {
			case Booking::TYPE_CARRIER_ORDER:
				return BookingDto::TYPE_CODE_CARRIER_ORDER;
				break;
			case Booking::TYPE_PICKUP_ORDER:
				return BookingDto::TYPE_CODE_PICKUP_ORDER;
				break;
			case Booking::TYPE_TRANSFER:
				return BookingDto::TYPE_CODE_TRANSFER;
				break;
			case Booking::TYPE_PICK_ONLY:
				return BookingDto::TYPE_CODE_PICK_ONLY;
				break;
			default:
				throw new ManagerException("Can't find match type code");
				break;
		}
	}
}