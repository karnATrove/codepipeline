<?php

namespace WarehouseBundle\Repository;

/**
 * LocationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LocationRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Find one staging area.
	 * @param  Product $product [description]
	 * @return [type]           [description]
	 */
	public function findOneStaging() {
		return $this->createQueryBuilder('l')
			->andWhere('l.staging = :staging')
			->setParameter('staging', 1)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}
}
