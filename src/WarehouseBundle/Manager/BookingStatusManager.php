<?php

namespace WarehouseBundle\Manager;


use Rove\CanonicalDto\Booking\BookingDto;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Exception\Manager\ManagerException;

class BookingStatusManager
{

	/**
	 * get bookingDto status code from internal booking status id
	 *
	 * @param int $statusId
	 *
	 * @return string
	 * @throws ManagerException
	 */
	public static function getCode(int $statusId)
	{
		$mapping = static::getStatusMapper('id');
		if (!isset($mapping[$statusId])) {
			throw new ManagerException("Can't find match booking status code by {$statusId}");
		}
		return $mapping[$statusId];
	}

	/**
	 * Get internal booking status by
	 *
	 * @param string $statusCode
	 *
	 * @return int
	 *
	 * @throws ManagerException
	 */
	public static function getStatusByCode(string $statusCode)
	{
		$mapping = static::getStatusMapper('code');
		if (!isset($mapping[$statusCode])) {
			throw new ManagerException("Can't find match booking status by {$statusCode}");
		}
		return $mapping[$statusCode];
	}

	/**
	 * Get the mapping array of booking dto status and internal booking status
	 *
	 * @param string $key define dto/entity is the key of mapping array
	 *
	 * @return array
	 * @throws ManagerException
	 */
	private static function getStatusMapper(string $key = 'id')
	{
		$mapper = [
			Booking::STATUS_DELETED => BookingDto::STATUS_CODE_DELETED,
			Booking::STATUS_AWAITING_FORWARD => BookingDto::STATUS_CODE_AWAITING_FORWARD,
			Booking::STATUS_ACCEPTED => BookingDto::STATUS_CODE_ACCEPTED,
			Booking::STATUS_PICKED => BookingDto::STATUS_CODE_PICKED,
			Booking::STATUS_PACKED => BookingDto::STATUS_CODE_PACKED,
			Booking::STATUS_SHIPPED => BookingDto::STATUS_CODE_SHIPPED,
		];
		if ($key == 'id') {
			return $mapper;
		} elseif ($key == 'code') {
			return array_flip($mapper);
		}
		throw new ManagerException("Booking Status Mapper {!key} not found");

	}
}