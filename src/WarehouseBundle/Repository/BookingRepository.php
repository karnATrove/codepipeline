<?php

namespace WarehouseBundle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use WarehouseBundle\Entity\Booking;

class BookingRepository extends EntityRepository
{
	/**
	 * @param string $searchString
	 * @param null   $limit
	 *
	 * @return array
	 */
	public function findByModelOrOrder($searchString, $limit = null)
	{
		$queryBuilder = $this->createQueryBuilder('b');
		$queryBuilder->join('WarehouseBundle:BookingProduct', 'bp', 'WITH', 'b.id = bp.booking')
			->join('WarehouseBundle:Product', 'p', 'WITH', 'p.id = bp.product')
			->andWhere($queryBuilder->expr()->orX(
				$queryBuilder->expr()->like('p.model', ':searchString'),
				$queryBuilder->expr()->like('b.orderNumber', ':searchString'),
				$queryBuilder->expr()->like('b.orderReference', ':searchString')
			))
			->setParameter('searchString', $searchString . '%')
			->orderBy('b.orderNumber', 'ASC');
		if (!is_null($limit) && is_integer($limit))
			$queryBuilder->setMaxResults(10);
		return $queryBuilder->getQuery()->getResult();
	}

	/**
	 * @param array $ids
	 *
	 * @return array
	 */
	public function findBookingByIds(array $ids)
	{
		$queryBuilder = $this->createQueryBuilder('b');
		$queryBuilder->andWhere('b.id IN (:ids)')
			->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);
		return $queryBuilder->getQuery()->getResult();
	}

	/**
	 * @param array $bookingIds
	 *
	 * @return array
	 */
	public function pickingSummaryByBookingIds(array $bookingIds)
	{
		$query = $this->getEntityManager()->createQuery(
			'SELECT
				  b.id       AS bookingId,
				  bp.id      AS bookingProductId,
				  bp.qty     AS orderedQuantity,
				  p.id       AS productId,
				  p.model    AS sku,
				  l.id       AS locationId,
				  l.aisle    AS aile,
				  l.row      AS row,
				  l.level    AS level,
				  lp.onHand AS quantityLevel,
				  p.qtyPerCarton AS quantityPerCarton
				FROM WarehouseBundle:Booking b INNER JOIN WarehouseBundle:BookingProduct bp WITH bp.booking=b AND b.id IN (:ids)
				  INNER JOIN WarehouseBundle:Product p WITH bp.product = p
				  INNER JOIN WarehouseBundle:LocationProduct lp WITH lp.product=p
				  INNER JOIN WarehouseBundle:Location l WITH lp.location = l'
		)->setParameter('ids', $bookingIds, Connection::PARAM_STR_ARRAY);
		$results = $query->getResult();
		return $results;
	}
}
