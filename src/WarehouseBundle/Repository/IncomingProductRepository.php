<?php

namespace WarehouseBundle\Repository;

use Doctrine\ORM\Query\Expr;

/**
 * IncomingProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class IncomingProductRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Gets the previously picked booking products by product
	 *
	 * @param      \WarehouseBundle\Entity\Product  $product  The product
	 * @param      integer $limit  The number of results to return
	 *
	 * @return     <type>                           The allocated by product.
	 */
	public function getIncomingByProduct(\WarehouseBundle\Entity\Product $product, $limit = NULL) {
		return $this->createQueryBuilder('ip')
			->andWhere('ip.product = :product')
			->join('WarehouseBundle:Incoming','i')
			->andWhere('i.status IN (:incoming_status)')
            ->setParameter('product', $product)
            ->setParameter('incoming_status', array(1,2))	# TODO: Verify with art these statuses (This is all statuses)
            ->addSelect('(
            	CASE WHEN i.arrived IS NOT NULL THEN 1
            		 ELSE (
		            	CASE WHEN i.scheduled IS NOT NULL THEN i.scheduled
		            		 ELSE i.eta
		            	END
		             )
            	END
            ) AS HIDDEN ORD')
            ->orderBy('ORD','DESC')
           	->setMaxResults($limit?$limit:10000)
            ->getQuery()
            ->getResult();
	}

	/**
	 * Find a group of matching Products by model.
	 *
	 * @param      string  $searchString  The search string
	 * @param      <type>  $limit  The limit
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function findByModelOrName($searchString,$limit=null) {
		$query = $this->createQueryBuilder('ip')
           ->innerJoin('WarehouseBundle:Product','p','WITH','p.id = ip.product')
           ->innerJoin('WarehouseBundle:Incoming','i','WITH','ip.incoming = i.id')
           ->where('i.status IN (:status)')
           ->andWhere(Expr::orX(
           		Expr::like('p.model',':searchString'),
           		Expr::like('i.name',':searchString')
           	))
           ->setParameter('searchString', $searchString.'%')
           ->setParameter('status', array(1,2))
           ->orderBy('i.id','ASC');
        if (!is_null($limit) && is_integer($limit))
           $query->setMaxResults(10);
        return $query->getQuery()->getResult();
	}

	/**
	 * Find IncomingProduct by Incoming and model.
	 *
	 * @param      \WarehouseBundle\Entity\Incoming  $incoming  The incoming
	 * @param      <type>                            $model     The model
	 *
	 * @return     <type>                            ( description_of_the_return_value )
	 */
	function findOneByModel(\WarehouseBundle\Entity\Incoming $incoming, $model) {
		return $this->createQueryBuilder('ip')
            ->join('WarehouseBundle:Product','p','WITH','ip.product = p.id')
			->andWhere('ip.incoming = :incoming')
			->andWhere('p.model = :model')
            ->setParameter('incoming', $incoming)
            ->setParameter('model', $model)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
	}
}