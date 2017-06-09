<?php

namespace WarehouseBundle\Utils;

use Symfony\Component\DependencyInjection\Container;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\Incoming as IncomingEntity;
use WarehouseBundle\Entity\Product as ProductEntity;

class Incoming
{
	/**
	 * Container aware.
	 */
	private $container;

	/**
	 * Make this utility container-aware (adding availability of doctrine for example)
	 *
	 * @param      <type>  $container  The container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Return a human readable format of the product status.
	 *
	 * @param      integer $status The product status identifier
	 *
	 * @return     string  Human readable text.
	 */
//	public function incomingTypeName($type)
//	{
//		return isset($this->incomingTypeList()[$type]) ? $this->incomingTypeList()[$type] : 'Unknown';
//	}

	/**
	 * Listing of available incoming container statuses.
	 *
	 * @return     array  Available incoming statuses.
	 */
//	public static function incomingTypeList()
//	{
//		return array(
//			intval(IncomingEntity::TYPE_OCEAN_FREIGHT) => 'Ocean Freight',
//			intval(IncomingEntity::TYPE_FORWARD) => 'Forward',
//		);
//	}

	/**
	 * Get BookingProduct's based on Product entity.
	 *
	 * @param  ProductEntity $product [description]
	 * @return     integer  Allocated quantity.
	 */
	public function getIncomingByProduct(ProductEntity $product, $limit = NULL)
	{
		return $this->container->get("doctrine")->getRepository('WarehouseBundle:IncomingProduct')->getIncomingByProduct($product, $limit);
	}

	/**
	 * Get picked BookingProduct's based on Product entity.
	 *
	 * @param  ProductEntity $product [description]
	 * @param   integer Number of results to return
	 *
	 * @return     integer  Allocated quantity.
	 */
	public function getRecentPickedProducts(ProductEntity $product, $limit = 10)
	{
		return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getPickedRecentByProduct($product, $limit);
	}

	/**
	 * Gets the incoming product scan count by model.
	 *
	 * @param      \WarehouseBundle\Entity\Incoming $incoming The incoming
	 * @param      <type>                            $model     The model
	 */
	public function getIncomingProductScanCountByModel(\WarehouseBundle\Entity\Incoming $incoming, $model)
	{
		return $this->container->get("doctrine")->getRepository('WarehouseBundle:IncomingProductScan')->findCountScannedByModel($incoming, $model);
	}

	/**
	 * Return a human readable format of the product status.
	 *
	 * @param      integer $status The product status identifier
	 *
	 * @return     string  Human readable text.
	 */
//	public function incomingStatusName($status)
//	{
//		return isset($this->incomingStatusList()[$status]) ? $this->incomingStatusList()[$status] : 'Unknown';
//	}

	/**
	 * Listing of available incoming container statuses.
	 *
	 * @return     array  Available incoming statuses.
	 */
//	public static function incomingStatusList()
//	{
//		return array(
//			IncomingEntity::STATUS_DELETED => 'Deleted',
//			IncomingEntity::STATUS_INBOUND => 'Inbound',
//			IncomingEntity::STATUS_ARRIVED => 'Arrived',
//			IncomingEntity::STATUS_COMPLETED => 'Completed',
//		);
//	}
}