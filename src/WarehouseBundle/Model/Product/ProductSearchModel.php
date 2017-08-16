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
    private $criteria;
    private $orderBy;
    private $limit;
    private $offset;

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param mixed $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
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
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
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
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
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
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }



}