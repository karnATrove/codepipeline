<?php

namespace WarehouseBundle\Repository;

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
}
