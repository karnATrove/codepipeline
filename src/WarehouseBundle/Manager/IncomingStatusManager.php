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

}