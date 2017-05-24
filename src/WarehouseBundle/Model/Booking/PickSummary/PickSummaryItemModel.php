<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-24
 * Time: 12:04 PM
 */

namespace WarehouseBundle\Model\Booking\PickSummary;


class PickSummaryItemModel
{
	private $bookingId;
	private $bookingProductId;
	private $orderedQuantity;
	private $productId;
	private $sku;
	private $locationId;
	private $aile;
	private $row;
	private $level;
	private $quantityLevel;
	private $quantityPerCarton;

	/**
	 * @return mixed
	 */
	public function getBookingId()
	{
		return $this->bookingId;
	}

	/**
	 * @param mixed $bookingId
	 */
	public function setBookingId($bookingId)
	{
		$this->bookingId = $bookingId;
	}

	/**
	 * @return mixed
	 */
	public function getBookingProductId()
	{
		return $this->bookingProductId;
	}

	/**
	 * @param mixed $bookingProductId
	 */
	public function setBookingProductId($bookingProductId)
	{
		$this->bookingProductId = $bookingProductId;
	}

	/**
	 * @return mixed
	 */
	public function getOrderedQuantity()
	{
		return $this->orderedQuantity;
	}

	/**
	 * @param mixed $orderedQuantity
	 */
	public function setOrderedQuantity($orderedQuantity)
	{
		$this->orderedQuantity = $orderedQuantity;
	}

	/**
	 * @return mixed
	 */
	public function getProductId()
	{
		return $this->productId;
	}

	/**
	 * @param mixed $productId
	 */
	public function setProductId($productId)
	{
		$this->productId = $productId;
	}

	/**
	 * @return mixed
	 */
	public function getSku()
	{
		return $this->sku;
	}

	/**
	 * @param mixed $sku
	 */
	public function setSku($sku)
	{
		$this->sku = $sku;
	}

	/**
	 * @return mixed
	 */
	public function getLocationId()
	{
		return $this->locationId;
	}

	/**
	 * @param mixed $locationId
	 */
	public function setLocationId($locationId)
	{
		$this->locationId = $locationId;
	}

	/**
	 * @return mixed
	 */
	public function getAile()
	{
		return $this->aile;
	}

	/**
	 * @param mixed $aile
	 */
	public function setAile($aile)
	{
		$this->aile = $aile;
	}

	/**
	 * @return mixed
	 */
	public function getRow()
	{
		return $this->row;
	}

	/**
	 * @param mixed $row
	 */
	public function setRow($row)
	{
		$this->row = $row;
	}

	/**
	 * @return mixed
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * @param mixed $level
	 */
	public function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * @return mixed
	 */
	public function getQuantityLevel()
	{
		return $this->quantityLevel;
	}

	/**
	 * @param mixed $quantityLevel
	 */
	public function setQuantityLevel($quantityLevel)
	{
		$this->quantityLevel = $quantityLevel;
	}

	/**
	 * @return mixed
	 */
	public function getQuantityPerCarton()
	{
		return $this->quantityPerCarton;
	}

	/**
	 * @param mixed $quantityPerCarton
	 */
	public function setQuantityPerCarton($quantityPerCarton)
	{
		$this->quantityPerCarton = $quantityPerCarton;
	}

}