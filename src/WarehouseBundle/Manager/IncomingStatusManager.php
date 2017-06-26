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

class IncomingStatusManager extends BaseManager
{

	private $incomingStatusRepository;

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->incomingStatusRepository = $entityManager->getRepository('WarehouseBundle:IncomingStatus');
	}

	/**
	 * @param $id
	 *
	 * @return null|object|IncomingStatus
	 */
	public function find($id)
	{
		return $this->incomingStatusRepository->find($id);
	}

	/**
	 * @param Incoming $incoming
	 * @param          $statusList
	 *
	 * @return bool
	 */
	public static function haveStatus(Incoming $incoming, $statusList){
		return in_array($incoming->getStatus()->getId(),$statusList);
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

	/**
	 * @param array $criteria
	 *
	 * @return null|object|IncomingStatus
	 */
	public function findOneBy(array $criteria)
	{
		return $this->incomingStatusRepository->findOneBy($criteria);
	}
}