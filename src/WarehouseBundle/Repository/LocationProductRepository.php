<?php

namespace WarehouseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\Product;

/**
 * LocationProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LocationProductRepository extends EntityRepository
{
	/**
	 * @param Product  $product
	 * @param Location $location
	 *
	 * @return mixed
	 */
	public function findOneByProductAndLocation(Product $product, Location $location)
	{
		return $this->createQueryBuilder('pl')
			->andWhere('pl.product = :product')
			->andWhere('pl.location = :location')
			->setParameter('product', $product)
			->setParameter('location', $location)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Find one staging area by product (existing).
	 * @param  Product $product [description]
	 * @return [type]           [description]
	 */
	public function findOneStagingByProduct(Product $product) {
		return $this->createQueryBuilder('pl')
			->innerJoin('pl.location','l')
			->andWhere('pl.product = :product')
			->andWhere('l.staging = :staging')
			->setParameter('product', $product)
			->setParameter('staging', 1)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Find one staging area by product (existing).
	 * @param  Product $product [description]
	 * @return [type]           [description]
	 */
	public function findByStagedProduct(Product $product) {
		return $this->createQueryBuilder('pl')
			->innerJoin('pl.location','l')
			->andWhere('pl.product = :product')
			->andWhere('l.staging = :staging')
			->andWhere('pl.staged > 0')
			->setParameter('product', $product)
			->setParameter('staging', 0)
			->orderBy('pl.staged','ASC')
			->getQuery()
			->getResult();
	}
}