<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Repository\IncomingProductRepository;

class IncomingProductManager extends BaseManager
{

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, IncomingProduct::class);
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
	 * @param Incoming $incoming
	 * @param string   $sku
	 *
	 * @return IncomingProduct
	 */
	public function getOneByIncomingAndSku(Incoming $incoming, string $sku)
	{
		/** @var IncomingProductRepository $repo */
		$repo = $this->entityRepository;
		return $repo->findOneByModel($incoming, $sku);
	}

}