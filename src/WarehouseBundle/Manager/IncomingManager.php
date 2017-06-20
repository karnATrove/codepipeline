<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 3:22 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingStatus;

class IncomingManager extends BaseManager
{

	private $incomingRepository;

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->incomingRepository = $entityManager->getRepository('WarehouseBundle:Incoming');
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
	 * @param Incoming $incoming
	 */
	public function refresh(Incoming $incoming)
	{
		$this->entityManager->refresh($incoming);
	}

	/**
	 * Finds entities by a set of criteria.
	 *
	 * @param array      $criteria
	 * @param array|null $orderBy
	 * @param int|null   $limit
	 * @param int|null   $offset
	 *
	 * @return Incoming[]
	 */
	public function findBy($criteria, $orderBy = null, $limit = null, $offset = null)
	{
		return $this->incomingRepository->findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @return array|Incoming[]
	 */
	public function findAll()
	{
		return $this->incomingRepository->findAll();
	}

	/**
	 * @param string|null $keyword
	 * @param null        $complete
	 * @param array       $criteria
	 * @param null        $orderBy
	 * @param null        $limit
	 * @param null        $offset
	 * @param bool        $queryOnly
	 *
	 * @return array|\Doctrine\ORM\Query
	 */
	public function searchContainers(string $keyword = null, $complete = null, $criteria = [],
	                                 $orderBy = null, $limit = null, $offset = null, $queryOnly = false)
	{
		return $this->incomingRepository->searchContainers($keyword, $complete, $criteria, $orderBy,
			$limit, $offset, $queryOnly);
	}
}