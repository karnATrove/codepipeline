<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Repository\IncomingProductScanRepository;

class IncomingProductScanManager extends BaseManager
{

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, IncomingProductScan::class);
	}

	/**
	 * @param Incoming $incoming
	 *
	 * @return array|IncomingProductScan[]
	 */
	public function getByIncoming(Incoming $incoming)
	{
		return $this->entityRepository->findBy(['incoming' => $incoming]);
	}

	/**
	 * @param Incoming $incoming
	 * @param string   $sku
	 *
	 * @return mixed
	 */
	public function getOneByIncomingAndSku(Incoming $incoming, string $sku, $locked = null)
	{
		/** @var IncomingProductScanRepository $repo */
		$repo = $this->entityRepository;
		return $repo->findOneByModel($incoming, $sku, $locked);
	}

	/**
	 * @param IncomingProductScan         $incomingProductScan
	 * @param EntityManagerInterface|null $entityManager
	 */
	public function delete(IncomingProductScan $incomingProductScan, EntityManagerInterface $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->remove($incomingProductScan);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * order incomingProductScan by modified and group by sku
	 *
	 * @param IncomingProductScan[] $incomingProductScans
	 *
	 * @return array
	 */
	public function orderIncomingProductScanBySkuAndModified($incomingProductScans)
	{
		$temp = [];
		foreach ($incomingProductScans as $incomingProductScan) {
			$productId = $incomingProductScan->getProduct()->getId();
			if (!key_exists($productId, $temp)) {
				$temp[$productId][] = $incomingProductScan;
			} else {
				if ($temp[$productId][0]->getModified() >= $incomingProductScan->getModified()) {
					$temp[$productId][] = $incomingProductScan;
				} else {
					array_unshift($temp[$productId], $incomingProductScan);
				}
			}
		}
		usort($temp, function($a, $b){
			if ($a[0]->getModified() < $b[0]->getModified()) {
				return 1;
			}
			return -1;
		});
		$resp = [];
		foreach ($temp as $t) {
			foreach ($t as $scan) {
				$resp[] = $scan;
			}
		}
		return $resp;
	}
}

