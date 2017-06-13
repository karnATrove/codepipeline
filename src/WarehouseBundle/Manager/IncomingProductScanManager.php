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
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public static function orderByModified($a, $b)
	{
		if ($a[0]->getModified() < $b[0]->getModified()) {
			return 1;
		}
		return -1;
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
	public function getByIncoming(Incoming $incoming)
	{
		return $this->incomingProductScanRepository->findBy(['incoming' => $incoming]);
	}

	/**
	 * @param array      $criteria
	 * @param array|null $orderBy
	 * @param null       $limit
	 * @param null       $offset
	 *
	 * @return array|IncomingProductScan[]
	 */
	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
		return $this->incomingProductScanRepository->findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @param $id
	 *
	 * @return null|object|IncomingProductScan
	 */
	public function find($id){
		return $this->incomingProductScanRepository->find($id);
	}

	/**
	 * @param Incoming $incoming
	 * @param string   $sku
	 *
	 * @return mixed
	 */
	public function getOneByIncomingAndSku(Incoming $incoming, string $sku, $locked = null)
	{
		return $this->incomingProductScanRepository->findOneByModel($incoming, $sku, $locked);
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
		usort($temp, "self::orderByModified");
		$resp = [];
		foreach ($temp as $t) {
			foreach ($t as $scan) {
				$resp[] = $scan;
			}
		}
		return $resp;
	}
}

