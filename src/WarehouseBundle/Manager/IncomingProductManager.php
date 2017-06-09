<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 3:22 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;

class IncomingProductManager extends BaseManager
{

	private $incomingProductRepository;

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->incomingProductRepository = $entityManager->getRepository('WarehouseBundle:IncomingProduct');
	}

	/**
	 * @param IncomingProductScan[] $incomingProductScans
	 *
	 * @return array
	 */
	public static function getIncomingProductIdsByIncomingProductScan($incomingProductScans)
	{
		$ids = [];
		foreach ($incomingProductScans as $incomingProductScan) {
			$ids[] = $incomingProductScan->getProduct()->getId();
		}
		return $ids;
	}

	/**
	 * @param IncomingProduct $incomingProduct
	 * @param null     $entityManager
	 */
	public function update(IncomingProduct $incomingProduct, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($incomingProduct);
		if ($flush) {
			$entityManager->flush();
		}
	}

}