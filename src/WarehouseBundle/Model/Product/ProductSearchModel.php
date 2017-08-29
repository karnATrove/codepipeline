<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017-08-15
 * Time: 11:55 AM
 */

namespace WarehouseBundle\Model\Product;


class ProductSearchModel
{
	/** @var string|null $searchString */
	private $searchString;
	private $status;
	/** @var bool $productHaveQuantityOnly */
	private $productHaveQuantityOnly;
	private $criteria;
	private $orderBy;
	private $limit;
	private $offset;

	/**
	 * ProductSearchModel constructor.
	 */
	public function __construct()
	{
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getSearchString()
	{
		return $this->searchString;
	}

	/**
	 * @param null|string $searchString
	 *
	 * @return ProductSearchModel
	 */
	public function setSearchString($searchString)
	{
		$this->searchString = $searchString;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param mixed $status
	 *
	 * @return ProductSearchModel
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCriteria()
	{
		return $this->criteria;
	}

	/**
	 * @param mixed $criteria
	 *
	 * @return ProductSearchModel
	 */
	public function setCriteria($criteria)
	{
		$this->criteria = $criteria;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}

	/**
	 * @param mixed $orderBy
	 *
	 * @return ProductSearchModel
	 */
	public function setOrderBy($orderBy)
	{
		$this->orderBy = $orderBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param mixed $limit
	 *
	 * @return ProductSearchModel
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param mixed $offset
	 *
	 * @return ProductSearchModel
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * @param bool $productHaveQuantityOnly
	 *
	 * @return ProductSearchModel
	 */
	public function setProductHaveQuantityOnly(bool $productHaveQuantityOnly)
	{
		$this->productHaveQuantityOnly = $productHaveQuantityOnly;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isProductHaveQuantityOnly(): bool
	{
		return $this->productHaveQuantityOnly;
	}

}