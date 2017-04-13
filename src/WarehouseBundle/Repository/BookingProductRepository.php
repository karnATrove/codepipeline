<?php

namespace WarehouseBundle\Repository;

use Doctrine\ORM\Query\Expr;

/**
 * BookingProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BookingProductRepository extends \Doctrine\ORM\EntityRepository
{
	/**
	 * Gets the allocated quantity by product
	 *
	 * @param      \WarehouseBundle\Entity\Product  $product  The product
	 *
	 * @return     <type>                           The allocated by product.
	 */
	public function getAllocatedQuantityByProduct(\WarehouseBundle\Entity\Product $product) {
		return $this->createQueryBuilder('op')
			->andWhere('op.product = :product')
			->andWhere('op.status IN (:status)')
			->join('WarehouseBundle:Booking','b','WITH','op.booking = b.id')
			->andWhere('b.status IN (:booking_status)')
            ->setParameter('product', $product)
            ->setParameter('status', array(1,2,3,4)) # Allow all except deleted (closed is okay since booking status below will filter out shipped)
            ->setParameter('booking_status', array(1,2,3,4))	# TODO: Verify with art these statuses (All excepted deleted and shipped (0 & 5))
            ->select('COALESCE(SUM(op.qty),0) as quantity')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
	}

	/**
	 * Gets the allocated booking products by product
	 *
	 * @param      \WarehouseBundle\Entity\Product  $product  The product
	 *
	 * @return     <type>                           The allocated by product.
	 */
	public function getAllocatedByProduct(\WarehouseBundle\Entity\Product $product) {
		return $this->createQueryBuilder('op')
			->andWhere('op.product = :product')
			->andWhere('op.status IN (:status)')
			->join('WarehouseBundle:Booking','b','WITH','op.booking = b.id')
			->andWhere('b.status IN (:booking_status)')
            ->setParameter('product', $product)
            ->setParameter('status', array(1,2,3,4)) # Allow all except deleted (closed is okay since booking status below will filter out shipped)
            ->setParameter('booking_status', array(1,2,3,4))	# TODO: Verify with art these statuses (All excepted deleted and shipped (0 & 5))
            ->getQuery()
            ->getResult();
	}

	/**
	 * Gets the previously picked booking products by product
	 *
	 * @param      \WarehouseBundle\Entity\Product  $product  The product
	 * @param      integer $limit  The number of results to return
	 *
	 * @return     <type>                           The allocated by product.
	 */
	public function getPickedRecentByProduct(\WarehouseBundle\Entity\Product $product, $limit = 5) {
		return $this->createQueryBuilder('op')
			->andWhere('op.product = :product')
			->andWhere('op.status IN (:status)')
			->join('WarehouseBundle:Booking','b','WITH','op.booking = b.id')
			->andWhere('b.status IN (:booking_status)')
            ->setParameter('product', $product)
            ->setParameter('status', array(2,3,4)) # Picked & 'In Progress' & Closed
            ->setParameter('booking_status', array(1,2,3,4,5))	# TODO: Verify with art these statuses (This is all statuses except deleted)
           	->orderBy('op.pickedDate','DESC')
           	->setMaxResults($limit)
            ->getQuery()
            ->getResult();
	}

	/**
	 * Gets the previously shipping booking products by product
	 *
	 * @param      \WarehouseBundle\Entity\Product  $product  The product
	 * @param      integer $limit  The number of results to return
	 *
	 * @return     <type>                           The allocated by product.
	 */
	public function getShippedRecentByProduct(\WarehouseBundle\Entity\Product $product, $limit = 5) {
		return $this->createQueryBuilder('op')
			->andWhere('op.product = :product')
			->andWhere('op.status IN (:status)')
			->join('WarehouseBundle:Booking','b','WITH','op.booking = b.id')
			->andWhere('b.status IN (:booking_status)')
            ->setParameter('product', $product)
            ->setParameter('status', array(1,2,3,4)) # TODO: This should be only 4 (Closed)
            ->setParameter('booking_status', array(5))
           	->orderBy('op.pickedDate','DESC')
           	->setMaxResults($limit)
            ->getQuery()
            ->getResult();
	}

	/**
	 * Gets the picked qty by booking product.
	 *
	 * @param      \WarehouseBundle\Entity\BookingProduct  $bookingProduct  The booking product
	 * @param      boolean  $active_only Only include active orders (non shipped).
	 *
	 * @return     <type>                                  The picked qty by booking product.
	 */
	public function getPickedQtyByBookingProduct(\WarehouseBundle\Entity\BookingProduct $bookingProduct, $active_only = FALSE) {
		$query = $this->createQueryBuilder('bp')
			->andWhere('bp.id = :bookingProduct')
			->join('WarehouseBundle:BookingProductLocation','bpl','WITH','bp.id = bpl.bookingProduct')
			->join('WarehouseBundle:Booking','b','WITH','b.id = bp.booking')
            ->setParameter('bookingProduct', $bookingProduct)
            ->select('COALESCE(SUM(bpl.qty),0) as quantity')
            ->setMaxResults(1)
           	->orderBy('bpl.created','ASC');
        if ($active_only) {
        	$query->andWhere('b.status IN (:statuses)');
        	$query->setParameter('statuses',array_values($this->get('app.booking')->bookingStatusList(TRUE,TRUE)));
        }
        return $query
            ->getQuery()
            ->getSingleScalarResult();
	}

	/**
	 * Find a group of matching BookingProducts by model.
	 *
	 * @param      string  $searchString  The search string
	 * @param      <type>  $limit  The limit
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function findByModelOrOrder($searchString,$limit=null) {
		$query = $this->createQueryBuilder('bp')
           ->join('WarehouseBundle:Booking','b','WITH','b.id = bp.booking')
           ->join('WarehouseBundle:Product','p','WITH','p.id = bp.product')
           ->andWhere(Expr::orX(
           		Expr::like('p.model',':searchString'),
           		Expr::like('b.orderNumber',':searchString'),
           		Expr::like('b.orderReference',':searchString')
           	))
           ->setParameter('searchString', $searchString.'%')
           ->orderBy('b.id','ASC');
        if (!is_null($limit) && is_integer($limit))
           $query->setMaxResults(10);
        return $query->getQuery()->getResult();
	}

}