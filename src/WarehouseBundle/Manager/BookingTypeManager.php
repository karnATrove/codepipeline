<?php

namespace WarehouseBundle\Manager;


use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingTypeManager extends BaseManager
{
	const TYPE_CODE_CARRIER_ORDER = "CRAR";
	const TYPE_CODE_PICKUP_ORDER = "PKOR";
	const TYPE_CODE_TRANSFER = "TRAN";
	const TYPE_CODE_PICK_ONLY = "PICK";

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
				return self::TYPE_CODE_CARRIER_ORDER;
				break;
			case Booking::TYPE_PICKUP_ORDER:
				return self::TYPE_CODE_PICKUP_ORDER;
				break;
			case Booking::TYPE_TRANSFER:
				return self::TYPE_CODE_TRANSFER;
				break;
			case Booking::TYPE_PICK_ONLY:
				return self::TYPE_CODE_PICK_ONLY;
				break;
			default:
				throw new ManagerException("Can't find match type code");
				break;
		}
	}
}