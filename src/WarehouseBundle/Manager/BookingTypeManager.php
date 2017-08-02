<?php

namespace WarehouseBundle\Manager;


use Rove\CanonicalDto\Booking\BookingDto;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingTypeManager extends BaseManager
{
	/**
	 * get type code by type id
	 *
	 * @param $typeId
	 *
	 * @return string
	 * @throws ManagerException
	 */
	public static function getCode($typeId)
	{
		$mapper = self::getStatusMapper('id');
		if (!isset($mapper[$typeId])) {
		    throw new ManagerException("Booking Type Code not found by {$typeId}");
        }
        return $mapper[$typeId];
	}

    /**
     * get type id by type code
     * @param $typeCode
     *
     * @return mixed
     * @throws ManagerException
     */
    public static function getTypeIdByCode($typeCode)
    {
        $mapper = self::getStatusMapper('code');
        if (!isset($mapper[$typeCode])) {
            throw new ManagerException("Booking Type ID not found by {$typeCode}");
        }
        return $mapper[$typeCode];
    }

    /**
     * Return the booking type mapper [id=>code] or [code=>id]
     * @param string $key
     *
     * @return array
     * @throws \WarehouseBundle\Exception\Manager\ManagerException
     */
    private static function getStatusMapper(string $key = 'id')
    {
        $mapper = array(
            Booking::TYPE_CARRIER_ORDER => BookingDto::TYPE_CODE_CARRIER_ORDER,
            Booking::TYPE_PICKUP_ORDER  => BookingDto::TYPE_CODE_PICKUP_ORDER,
            Booking::TYPE_TRANSFER      => BookingDto::TYPE_CODE_TRANSFER,
            Booking::TYPE_PICK_ONLY     => BookingDto::TYPE_CODE_PICK_ONLY,
        );
        if ($key == 'id') {
            return $mapper;
        }elseif ($key == 'code') {
            return array_flip($mapper);
        }
        throw new ManagerException("Booking Type Mapper {$key} not found");

    }
}