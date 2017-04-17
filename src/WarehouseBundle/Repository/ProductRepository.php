<?php

namespace WarehouseBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr;
//use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Entity\Product;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Find a group of matching Products by model.
	 *
	 * @param      string  $model  The model
	 * @param      <type>  $limit  The limit
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function findByModel($model,$limit=null) {
		$query = $this->createQueryBuilder('p')
           ->where(Expr::like('p.model',':model'))
           ->setParameter('model', $model.'%')
           ->orderBy('p.model','ASC');
        if (!is_null($limit) && is_integer($limit))
           $query->setMaxResults(10);
        return $query->getQuery()->getResult();
	}

	/**
	 * Gets the picked qty by booking product.
	 *
	 * @param      \WarehouseBundle\Entity\Product  $product  The product
	 * @param      boolean  $active_only Only include active orders (non shipped)
	 *
	 * @return     <type>                                  The picked qty by product.
	 */
	public function getPickedQtyByProduct(\WarehouseBundle\Entity\Product $product, $active_only = FALSE) {
		$query = $this->createQueryBuilder('p')
			->andWhere('p.id = :product')
			->join('WarehouseBundle:BookingProduct','bp','WITH','bp.product = p.id')
			->join('WarehouseBundle:BookingProductLocation','bpl','WITH','bp.id = bpl.bookingProduct')
			->join('WarehouseBundle:Booking','b','WITH','b.id = bp.booking')
            ->setParameter('product', $product)
            ->select('COALESCE(SUM(bpl.qty),0) as quantity')
            ->setMaxResults(1)
           	->orderBy('bpl.created','ASC');
        if ($active_only) {
        	$query->andWhere('b.status IN (:statuses)');
        	$query->setParameter('statuses',array_values(\WarehouseBundle\Utils\Booking::bookingStatusList(TRUE,TRUE)));
        }
        return $query
            ->getQuery()
            ->getSingleScalarResult();
	}
	
}
