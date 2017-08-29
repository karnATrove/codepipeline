<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingType;

class IncomingTypeManager extends BaseManager
{

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, IncomingType::class);
	}

	/**
	 * can change to read from db
	 *
	 * @return array
	 */
	public static function incomingTypeList()
	{
		return [
			IncomingType::OCEAN_FREIGHT => 'Ocean Freight',
			IncomingType::FORWARD => 'Forward',
		];
	}
}