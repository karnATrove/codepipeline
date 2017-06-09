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
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\IncomingStatus;

class IncomingProductScanManager extends BaseManager
{

	private $incomingProductScanRepository;

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->incomingProductScanRepository = $entityManager->getRepository('WarehouseBundle:IncomingProductScan');
	}


	/**
	 * @param IncomingProductScan $incomingProductScan
	 * @param null                $entityManager
	 */
	public function update(IncomingProductScan $incomingProductScan, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($incomingProductScan);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * @param IncomingProductScan $incomingProductScan
	 */
	public function refresh(IncomingProductScan $incomingProductScan)
	{
		$this->entityManager->refresh($incomingProductScan);
	}

	/**
	 * @param Incoming $incoming
	 *
	 * @return array|IncomingProductScan[]
	 */
	public function getByIncoming(Incoming $incoming){
		return $this->incomingProductScanRepository->findBy(['incoming' => $incoming]);
	}

}