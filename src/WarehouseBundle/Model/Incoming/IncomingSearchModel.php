<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-21
 * Time: 10:29 AM
 */

namespace WarehouseBundle\Model\Incoming;


class IncomingSearchModel
{
	/** @var string|null $searchString */
	private $searchString;
	/** @var bool|null $isComplete */
	private $isComplete;
	/** @var array $criteria */
	private $criteria;
	/** @var array $orderBy */
	private $orderBy;
	/** @var int|null $limit */
	private $limit;
	/** @var int|null $offset */
	private $offset;
	/** @var string|null $etaStartDate */
	private $etaStartDate;
	/** @var string|null $etaEndDate */
	private $etaEndDate;
	/** @var string|null $etaStartDate */
	private $scheduledStartDate;
	/** @var string|null $etaEndDate */
	private $scheduledEndDate;

	public function __construct()
	{
		$this->criteria = [];
		$this->orderBy = [];
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
	 */
	public function setSearchString($searchString)
	{
		$this->searchString = $searchString;
	}

	/**
	 * @return bool|null
	 */
	public function getIsComplete()
	{
		return $this->isComplete;
	}

	/**
	 * @param bool|null $isComplete
	 */
	public function setIsComplete($isComplete)
	{
		$this->isComplete = $isComplete;
	}

	/**
	 * @return array
	 */
	public function getCriteria()
	{
		return $this->criteria;
	}

	/**
	 * @param array $criteria
	 */
	public function setCriteria($criteria)
	{
		$this->criteria = $criteria;
	}

	/**
	 * @return array
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}

	/**
	 * @param array $orderBy
	 */
	public function setOrderBy($orderBy)
	{
		$this->orderBy = $orderBy;
	}

	/**
	 * @return int|null
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param int|null $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}

	/**
	 * @return int|null
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param int|null $offset
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
	}

	/**
	 * @return null|string
	 */
	public function getEtaStartDate()
	{
		return $this->etaStartDate;
	}

	/**
	 * @param null|string $etaStartDate
	 */
	public function setEtaStartDate($etaStartDate)
	{
		$this->etaStartDate = $etaStartDate;
	}

	/**
	 * @return null|string
	 */
	public function getEtaEndDate()
	{
		return $this->etaEndDate;
	}

	/**
	 * @param null|string $etaEndDate
	 */
	public function setEtaEndDate($etaEndDate)
	{
		$this->etaEndDate = $etaEndDate;
	}

	/**
	 * @return null|string
	 */
	public function getScheduledStartDate()
	{
		return $this->scheduledStartDate;
	}

	/**
	 * @param null|string $scheduledStartDate
	 */
	public function setScheduledStartDate($scheduledStartDate)
	{
		$this->scheduledStartDate = $scheduledStartDate;
	}

	/**
	 * @return null|string
	 */
	public function getScheduledEndDate()
	{
		return $this->scheduledEndDate;
	}

	/**
	 * @param null|string $scheduledEndDate
	 */
	public function setScheduledEndDate($scheduledEndDate)
	{
		$this->scheduledEndDate = $scheduledEndDate;
	}


}