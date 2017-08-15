<?php

namespace WarehouseBundle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Model\Booking\BookingSearchModel;

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
				  b.orderNumber AS orderNumber,
				  bp.id      AS bookingProductId,
				  bp.qty     AS orderedQuantity,
				  p.id       AS productId,
				  p.description AS description,
				  p.model    AS sku,
				  l.id       AS locationId,
				  l.aisle    AS aisle,
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

	/**
	 * We group within the ProductManager. (Most efficient way?)
	 *
	 * @return array
	 */
	public function getPickableProducts()
	{
		$pickableStatuses = $this->getBookingPickableStatuses();
		$productStatuses = $this->getBookingProductPickableStatuses();
		return $this->getEntityManager()->createQuery(
			'SELECT
				  bp.id      	AS bookingProductId,
				  bp.qty 		AS orderedQuantity,
				  p.id       	AS productId,
				  p.model    	AS sku,
				  l.id       	AS locationId,
				  l.aisle    	AS aisle,
				  l.row      	AS row,
				  l.level    	AS level,
				  l.staging 	AS staging,
				  lp.id 		AS id,
				  lp.staged 	AS quantityStaged,
				  lp.onHand 	AS quantityLevel,
				  lp.modified 	AS modified,
				  p.qtyPerCarton AS quantityPerCarton
				FROM WarehouseBundle:Booking b INNER JOIN WarehouseBundle:BookingProduct bp WITH bp.booking=b AND b.status IN (:bookingStatus)
				  INNER JOIN WarehouseBundle:Product p WITH bp.product = p
				  INNER JOIN WarehouseBundle:LocationProduct lp WITH lp.product=p
				  INNER JOIN WarehouseBundle:Location l WITH lp.location = l AND l.staging = 0
				WHERE bp.status IN (:productStatus)
				  AND b.pickingFlag = 1
				ORDER BY p.model'
		)
			->setParameter('bookingStatus', $pickableStatuses, Connection::PARAM_STR_ARRAY)
			->setParameter('productStatus', $productStatuses, Connection::PARAM_STR_ARRAY)
			->getResult();
	}

	public function getBookingPickableStatuses()
	{
		return [Booking::STATUS_ACCEPTED];
	}

	public function getBookingProductPickableStatuses()
	{
		return [BookingProduct::STATUS_PENDING];
	}

	/**
	 * We group within the ProductManager. (Most efficient way?)
	 *
	 * @return array
	 */
	public function getBookingQuantityAskedByProduct(Product $product)
	{
		$pickableStatuses = $this->getBookingPickableStatuses();
		$productStatuses = $this->getBookingProductPickableStatuses();
		return $this->getEntityManager()->createQuery(
			'SELECT
				  SUM(bp.qty) AS asked
				FROM WarehouseBundle:Booking b 
				  INNER JOIN WarehouseBundle:BookingProduct bp WITH bp.booking=b AND b.status IN (:bookingStatus)
				  INNER JOIN WarehouseBundle:Product p WITH bp.product = p
				WHERE bp.product = :product 
					AND bp.status IN (:productStatus)
					AND b.pickingFlag = 1
				GROUP BY bp.product
				ORDER BY p.model'
		)
			->setParameter('bookingStatus', $pickableStatuses, Connection::PARAM_STR_ARRAY)
			->setParameter('productStatus', $productStatuses, Connection::PARAM_STR_ARRAY)
			->setParameter('product', $product)
			->getSingleScalarResult();
	}

	/**
	 * @param BookingSearchModel $bookingSearchModel
	 *
	 * @return int|mixed
	 */
	public function count($bookingSearchModel)
	{
		$queryBuilder = $this->createQueryBuilder('b');
		$queryBuilder->select($queryBuilder->expr()->count('b'));
		$criteria = $bookingSearchModel->getCriteria();
		$criteriaStartDate = $bookingSearchModel->getCriteriaStartDate();
		$criteriaEndDate = $bookingSearchModel->getCriteriaEndDate();
        $orderBy = $bookingSearchModel->getOrderBy();
        $offset = $bookingSearchModel->getOffset();
        $limit = $bookingSearchModel->getLimit();
		if(!empty($criteria)){
            foreach ($criteria as $param => $value) {
                $queryBuilder->andWhere("b.{$param} = '{$value}'");
            }
        }
        if(!empty($criteriaStartDate)){
            foreach ($criteriaStartDate as $param => $value) {
                $queryBuilder->andWhere("b.{$param} >= '{$value}'");
            }
        }
        if(!empty($criteriaEndDate)){
            foreach ($criteriaEndDate as $param => $value) {
                $queryBuilder->andWhere("b.{$param} <= '{$value}'");
            }
        }
        if(!empty($orderBy)){
            foreach ($orderBy as $param => $value) {
                $queryBuilder->orderBy("b.{$value}");
            }
        }
        if(!empty($limit)){
            $queryBuilder->setMaxResults($limit);
            if(!empty($offset)) $queryBuilder->setFirstResult($offset);
        }

		$query = $queryBuilder->getQuery();
		try{
			return $query->getSingleScalarResult();
		}catch (NoResultException $noResultException){
			return 0;
		}
	}

    /**
     * Search bookings
     *
     * @param array $criteria
     * @param array $sorting
     * @param bool  $query      true return query builder; false return result.
     *
     * @return mixed
     */
    public function searchBookings($criteria, $sorting, $query = FALSE) {
	    $queryBuilder = $this->createQueryBuilder('b');
	    $queryBuilder->leftJoin('b.carrier', 'c');

	    foreach ($criteria as $param => $value) {
	        if (is_scalar($value)) {
	            if ($value === '') {
	                unset($criteria[$param]);
                }
            } elseif (is_array($value)) {
	            if (!isset($value['op'])) {
                    $criteria[$param]['op'] = '=';
                }
                if (!isset($value['value']) || is_null($value['value']) || $value['value'] === '') {
                    unset($criteria[$param]);
                }
            } elseif(is_null($value)) {
                unset($criteria[$param]);
            }
        }
        foreach ($criteria as $param => $value) {
            if (is_scalar($value) || is_object($value)) {
                $queryBuilder->andWhere("b.{$param} = :{$param}");
                $queryBuilder->setParameter("{$param}", $value);
            } elseif(is_array($value)) {
                $queryBuilder->andWhere("b.{$param} {$value['op']} :{$param}");
                $queryBuilder->setParameter("{$param}", $value['value']);
            }
        }

        foreach($sorting as $sort => $direction) {
            $queryBuilder->orderBy("b.{$sort}", $direction);
        }

        if ($query) {
            return $queryBuilder;
        } else {
            return $queryBuilder->getQuery()->getResult();
        }
    }
}
