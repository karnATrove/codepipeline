<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Model\Incoming\IncomingSearchModel;
use WarehouseBundle\Repository\IncomingRepository;

class IncomingManager extends BaseManager
{

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, Incoming::class);
	}

	/**
	 * True if allow container to delete
	 *
	 * @param Incoming $incoming
	 *
	 * @return bool
	 */
	public static function allowDelete(Incoming $incoming)
	{
		return ($incoming->getStatus()->getId() == IncomingStatus::INBOUND ||
			$incoming->getStatus()->getId() == IncomingStatus::ARRIVED);
	}

	/**
	 * Check if container is complete
	 *
	 * @param Incoming $incoming
	 *
	 * @return bool
	 */
	public static function isComplete(Incoming $incoming)
	{
		return $incoming->getStatus()->getId() == IncomingStatus::COMPLETED;
	}

	/**
	 * Check if container is active
	 *
	 * @param Incoming $incoming
	 *
	 * @return bool
	 */
	public static function isActive(Incoming $incoming)
	{
		return $incoming->getStatus()->getId() == IncomingStatus::INBOUND
			|| $incoming->getStatus()->getId() == IncomingStatus::ARRIVED;
	}

	/**
	 * @param Incoming $incoming
	 * @param null     $entityManager
	 */
	public function updateIncoming(Incoming $incoming, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($incoming);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * @param IncomingSearchModel $incomingSearchModel
	 * @param bool                $returnQuery
	 *
	 * @return Query|Incoming[]
	 */
	public function searchContainers(IncomingSearchModel $incomingSearchModel, $returnQuery = false)
	{
		/** @var IncomingRepository $repo */
		$repo = $this->entityRepository;
		return $repo->searchContainers($incomingSearchModel, $returnQuery);
	}
}