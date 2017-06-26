<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-23
 * Time: 9:51 AM
 */

namespace WarehouseBundle\Manager;


use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingProductManager
{
	const STATUS_CODE_DELETED = "DELT";
	const STATUS_CODE_PENDING = "PEND";
	const STATUS_CODE_IN_PROGRESS = "PROG";
	const STATUS_CODE_PICKED = "PICK";
	const STATUS_CODE_CLOSED = "CLOS";

	/**
	 * @param $statusId
	 *
	 * @return string
	 * @throws ManagerException
	 */
	public static function getCode($statusId)
	{
		switch ($statusId) {
			case BookingProduct::STATUS_DELETED:
				return self::STATUS_CODE_DELETED;
				break;
			case BookingProduct::STATUS_PENDING:
				return self::STATUS_CODE_PENDING;
				break;
				break;
			case BookingProduct::STATUS_PICKED:
				return self::STATUS_CODE_PICKED;
				break;
			case BookingProduct::STATUS_IN_PROGRESS:
				return self::STATUS_CODE_IN_PROGRESS;
				break;
			case BookingProduct::STATUS_CLOSED:
				return self::STATUS_CODE_CLOSED;
				break;
			default:
				throw new ManagerException("Can't find match item status code");
				break;
		}
	}
}