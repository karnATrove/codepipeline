<?php

namespace WarehouseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Model\Product\ProductSearchModel;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends EntityRepository
{
	/**
	 * Find a group of matching Products by model
	 *
	 * @param string   $model
	 * @param int|null $limit
	 *
	 * @return array
	 */
	public function findByModel(string $model, int $limit = null)
	{
		$query = $this->createQueryBuilder('p');
		$query->where($query->expr()->like('p.model', ':model'))
			->setParameter('model', $model . '%')
			->orderBy('p.model', 'ASC');
		if (!is_null($limit) && is_integer($limit))
			$query->setMaxResults(10);
		return $query->getQuery()->getResult();
	}

	/**
	 * Gets the picked qty by booking product
	 *
	 * @param Product $product
	 * @param bool    $active_only
	 *
	 * @return mixed
	 */
	public function getPickedQtyByProduct(Product $product, $active_only = FALSE)
	{
		$query = $this->createQueryBuilder('p')
			->andWhere('p.id = :product')
			->join('WarehouseBundle:BookingProduct', 'bp', 'WITH', 'bp.product = p.id')
			->join('WarehouseBundle:BookingProductLocation', 'bpl', 'WITH', 'bp.id = bpl.bookingProduct')
			->join('WarehouseBundle:Booking', 'b', 'WITH', 'b.id = bp.booking')
			->setParameter('product', $product)
			->select('COALESCE(SUM(bpl.qty),0) as quantity')
			->setMaxResults(1)
			->orderBy('bpl.created', 'ASC');
		if ($active_only) {
			$query->andWhere('b.status IN (:statuses)');
			$query->setParameter('statuses',
				array_values(\WarehouseBundle\Utils\Booking::bookingStatusList(TRUE, TRUE)));
		}
		return $query
			->getQuery()
			->getSingleScalarResult();
	}


	/**
	 * @param string $sku
	 *
	 * @return int
	 */
	public function findProductAvailableQuantity(string $sku): int
	{
		$query = $this->getEntityManager()->createQuery(
			'SELECT
				  sum(lp.onHand) AS quantity
				FROM WarehouseBundle:Product p INNER JOIN WarehouseBundle:LocationProduct lp WITH p = lp.product
				WHERE p.model = :sku
				GROUP BY p.model'
		)->setParameter('sku', $sku);
		$results = $query->getResult();
		return isset($results[0]) ? $results[0]['quantity'] : 0;
	}

	/**
	 * Identify if there is an 'onHand' amount greater then 'Staged'.
	 * This is bad and can be caused by transferring stock from one location to another.
	 */
	public function isDefunct(Product $product) {
		$query = $this->getEntityManager()->createQuery(
			'SELECT
			  SUM(lp.onHand) AS hand,
			  (SELECT SUM(lpp.staged) FROM WarehouseBundle:LocationProduct lpp WHERE lpp.product = lp.product) as x
			 FROM WarehouseBundle:LocationProduct lp 
			 INNER JOIN WarehouseBundle:Location l WITH l = lp.location
			 INNER JOIN WarehouseBundle:Product p WITH p = lp.product

			 WHERE p.id = :product
			 GROUP BY lp.product
			 HAVING x < hand'
		)->setParameter('product',$product);

		$results = $query->getResult();
		return isset($results[0]) ? TRUE : FALSE;
	}

    /**
     * Count products
     * @param ProductSearchModel $productSearchModel
     */
    public function countStockProduct(ProductSearchModel $productSearchModel)
    {
        $products = $this->getStockProduct($productSearchModel);
        return is_array($products) ? count($products) : 0;
    }

    /**
     * @param ProductSearchModel $productSearchModel
     * @return array
     */
    public function getStockProduct(ProductSearchModel $productSearchModel){
        $criteria = $productSearchModel->getCriteria();
        $orderBy = $productSearchModel->getOrderBy();
        $offset = $productSearchModel->getOffset();
        $limit = $productSearchModel->getLimit();

        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.model AS model')
            ->select('SUM(lp.onHand) AS on_hand')
            ->leftJoin('WarehouseBundle:LocationProduct', 'lp', 'WITH', 'p = lp.product')
            ->groupBy('p.model')
            ->andHaving('on_hand > 0');

        if(!empty($criteria)){
            foreach ($criteria as $param => $value) {
                $queryBuilder->andWhere("p.{$param} = '{$value}'");
            }
        }
        if(!empty($orderBy)){
            foreach ($orderBy as $param => $value) {
                $queryBuilder->orderBy("p.{$value}");
            }
        }
        if(!empty($limit)){
            $queryBuilder->setMaxResults($limit);
            if(!empty($offset)) $queryBuilder->setFirstResult($offset);
        }

        $query = $queryBuilder->getQuery();
        try{
            return $query->getResult();
        }catch (NoResultException $noResultException){
            return [];
        }

    }

}
