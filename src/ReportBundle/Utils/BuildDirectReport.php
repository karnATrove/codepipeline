<?php

namespace ReportBundle\Utils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\Container;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Form\IncomingType;

class BuildDirectReport
{
	/**
	 * Container aware.
	 */
	private $container;

	/**
	 * Make this utility container-aware (adding availability of doctrine for example)
	 *
	 * @param      <type>  $container  The container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param $start
	 * @param $end
	 * @return mixed
	 */
	public function getOceanFrightContainerCount($start, $end)
	{
		/** @var EntityManager $em */
		$em = $this->container->get("doctrine")->getManager();
		$query = $em->createQuery(
			'SELECT count(i.id)
				FROM WarehouseBundle:Incoming i 
				WHERE i.type=:incomingType AND i.arrived>=:startTime AND i.arrived<= :endTime'
		)
			->setParameter('incomingType', IncomingType::OCEAN_FREIGHT)
			->setParameter('startTime', $start)
			->setParameter('endTime', $end);
		try {
			$count = $query->getSingleScalarResult();
		} catch (NoResultException $exception) {
			$count = 0;
		}
		return $count;
	}

	public function getOrdersChangedToShip($start, $end)
	{
		/** @var EntityManager $em */
		$em = $this->container->get("doctrine")->getManager();
		$query = $em->createQuery(
			'SELECT count(DISTINCT s.booking)
				FROM WarehouseBundle:Shipment s
				WHERE s.created>=:startTime AND s.created<= :endTime'
		)
			->setParameter('startTime', $start)
			->setParameter('endTime', $end);
		try {
			$count = $query->getSingleScalarResult();
		} catch (NoResultException $exception) {
			$count = 0;
		}
		return $count;
	}

	public function getPickUpOrdersChangedToShip($start, $end)
	{
		/** @var EntityManager $em */
		$em = $this->container->get("doctrine")->getManager();
		$query = $em->createQuery(
			'SELECT count(DISTINCT s.booking)
				FROM WarehouseBundle:Shipment s INNER JOIN WarehouseBundle:Booking b WITH s.booking=b
				WHERE s.created>=:startTime AND s.created<= :endTime AND b.orderType=:orderType'
		)
			->setParameter('startTime', $start)
			->setParameter('endTime', $end)
			->setParameter('orderType', Booking::TYPE_PICKUP_ORDER);
		try {
			$count = $query->getSingleScalarResult();
		} catch (NoResultException $exception) {
			$count = 0;
		}
		return $count;
	}

	public function getCartonsOrdersChangedToShip($start, $end)
	{
		/** @var EntityManager $em */
		$em = $this->container->get("doctrine")->getManager();
//		$query = $queryBuilder->getQuery();
		$connection = $em->getConnection();
		$statement = $connection->prepare("SELECT FORMAT(bp.qty/p.qty_per_carton,0) as qty
				FROM warehouse_shipment s INNER JOIN warehouse_booking b ON s.booking_id = b.id
				  INNER JOIN warehouse_booking_product bp ON bp.booking_id = b.id
				  INNER JOIN warehouse_product p ON bp.product_id = p.id
				WHERE s.created>=:startTime AND s.created<=:endTime
				GROUP BY s.booking_id");
		$statement->bindValue('startTime', $start);
		$statement->bindValue('endTime', $end);
		$statement->execute();
		$results = $statement->fetchAll();
		$count = 0;
		foreach ($results as $result) {
			$count += $result['qty'];
		}
		return $count;
	}
}