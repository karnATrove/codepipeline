<?php
/**
 * Created by PhpStorm.
 * User: Sam Li
 * Date: 6/14/2017
 * Time: 12:01 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

class DashboardManager extends BaseManager {

	/**
	 * Get the booking count based on criteria
	 *
	 * @param array $criteria
	 *
	 * @return int|null
	 *
	 */
	public function getBookingCount(array $criteria) {
		$qb= $this->entityManager->createQueryBuilder();
		$qb->select('COUNT(b.orderNumber) AS cnt')
			->from('WarehouseBundle:Booking', 'b')
			->setMaxResults(1);
		foreach ($criteria as $field => $c) {
			$qb->andWhere($c['statement']);
			$qb->setParameter($field,$c['value']);
		}

		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * Get the product count based on criteria.
	 * @param array $criteria
	 *
	 * @return int|null
	 */
	public function getProductCount(array $criteria) {
		$qb = $this->entityManager->createQueryBuilder();
		$qb->select('COUNT(DISTINCT p.id) AS cnt')
			->from('WarehouseBundle:Product', 'p')
			->join('WarehouseBundle:LocationProduct', 'lp', 'p.id = lp.product_id');
		foreach ($criteria as $field => $c) {
			$qb->andWhere($c['statement']);
			$qb->setParameter($field,$c['value']);
		}
		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * Get the booking product count based on criteria.
	 * @param array $criteria
	 *
	 * @return int|null
	 */
//	public function getBookingProductCount(array $criteria) {
//
//		$qb = $this->entityManager->createQueryBuilder();
//		$qb->select('COUNT(DISTINCT p.id) AS cnt')
//			->from('WarehouseBundle:Product', 'p')
//			->join('WarehouseBundle:LocationProduct', 'lp', 'p.id = lp.product_id');
//		foreach ($criteria as $field => $c) {
//			$qb->andWhere($c['statement']);
//			$qb->setParameter($field,$c['value']);
//		}
//		return $qb->getQuery()->getSingleScalarResult();
//	}



}