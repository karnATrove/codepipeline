<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingStatus;

class IncomingStatusManager extends BaseManager
{

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, IncomingStatus::class);
	}

	/**
	 * @param Incoming $incoming
	 * @param          $statusList
	 *
	 * @return bool
	 */
	public static function haveStatus(Incoming $incoming, $statusList)
	{
		return in_array($incoming->getStatus()->getId(), $statusList);
	}

	/**
	 * can change to read from db
	 *
	 * @return array
	 */
	public static function incomingStatusList()
	{
		return [
			IncomingStatus::DELETED => 'Deleted',
			IncomingStatus::INBOUND => 'Inbound',
			IncomingStatus::ARRIVED => 'Arrived',
			IncomingStatus::COMPLETED => 'Completed',
		];
	}
}