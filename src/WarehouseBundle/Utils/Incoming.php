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
	public function incomingTypeName($type)
	{
		return isset($this->incomingTypeList()[$type]) ? $this->incomingTypeList()[$type] : 'Unknown';
	}

	/**
	 * Listing of available incoming container statuses.
	 *
	 * @return     array  Available incoming statuses.
	 */
	public static function incomingTypeList()
	{
		return array(
			intval(IncomingEntity::TYPE_OCEAN_FREIGHT) => 'Ocean Freight',
			intval(IncomingEntity::TYPE_FORWARD) => 'Forward',
		);
	}

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
	 * Returns a boolean if the incoming container is completed.
	 */
	public function isComplete(\WarehouseBundle\Entity\Incoming $incoming)
	{
		if ($this->incomingStatusName($incoming->getStatus()->getCode()) == 'COMPLETED') return TRUE;
		return FALSE;
	}

	/**
	 * Return a human readable format of the product status.
	 *
	 * @param      integer $status The product status identifier
	 *
	 * @return     string  Human readable text.
	 */
	public function incomingStatusName($status)
	{
		return isset($this->incomingStatusList()[$status]) ? $this->incomingStatusList()[$status] : 'Unknown';
	}

	/**
	 * Listing of available incoming container statuses.
	 *
	 * @return     array  Available incoming statuses.
	 */
	public static function incomingStatusList()
	{
		return array(
			IncomingEntity::STATUS_DELETED => 'Deleted',
			IncomingEntity::STATUS_INBOUND => 'Inbound',
			IncomingEntity::STATUS_ARRIVED => 'Arrived',
			IncomingEntity::STATUS_COMPLETED => 'Completed',
		);
	}

	public function preloadScannedProducts(\WarehouseBundle\Entity\Incoming $incoming)
	{
		$em = $this->container->get("doctrine")->getManager();
		/** @var IncomingProduct[] $incomingProducts */
		$incomingProducts = $incoming->getIncomingProducts();
		/** @var IncomingProductScan[] $incomingProductScan */
		$incomeScannedProducts = $incoming->getIncomingScannedProducts();
		$productIds = $this->getIncomingProductIds($incomeScannedProducts);
		foreach ($incomingProducts as $incomingProduct) {
			if (!in_array($incomingProduct->getId(), $productIds)) {
				$item = (new IncomingProductScan())
					->setIncoming($incoming)
					->setIncomingProduct($incomingProduct)
					->setQtyOnScan(0)
					->setProduct($incomingProduct->getProduct())
					->setCreated(new \DateTime('now'));
				$item->setUser($this->container->get('security.token_storage')->getToken()->getUser());
				$em->persist($item);
			}
		}
		$em->flush();
		$this->container->get('session')->getFlashBag()->add('success', 'Successfully loaded products.');

		return TRUE;
	}

	/**
	 * @param IncomingProductScan[] $incomeScannedProducts
	 * @return array
	 */
	private function getIncomingProductIds($incomeScannedProducts)
	{
		$ids = [];
		foreach ($incomeScannedProducts as $incomeScannedProduct) {
			$ids[] = $incomeScannedProduct->getProduct()->getId();
		}
		return $ids;
	}

	/**
	 * Sets the Incoming as complete.
	 */
	public function setComplete(\WarehouseBundle\Entity\Incoming $incoming)
	{
		$em = $this->container->get("doctrine")->getManager();

		if (in_array($incoming->getStatus()->getCode(), array('INBOUND', 'ARRIVED'))) {
			# Add items to inventory - We use this loop because some items scanned may not be in the container
			foreach ($incoming->getIncomingScannedProducts() as $incomingScannedProduct) {
				# Look for an existing location with this specific product then modifiy it, otherwise make new.
				$locationProduct = $this->container->get("doctrine")->getRepository('WarehouseBundle:LocationProduct')->findOneByProductAndLocation($incomingScannedProduct->getProduct(), $incomingScannedProduct->getLocation());
				if (!$locationProduct) {
					$locationProduct = (new \WarehouseBundle\Entity\LocationProduct())
						->setProduct($incomingScannedProduct->getProduct())
						->setLocation($incomingScannedProduct->getLocation())
						->setOnHand($incomingScannedProduct->getQtyOnScan())
						->setCreated(new \DateTime('now'));
				} else {
					$locationProduct->setModified(new \DateTime('now'))
						->setOnHand($locationProduct->getOnHand());
				}
				$em->persist($locationProduct);
			}

			# Change the Incoming status to completed.
			$incoming->setStatus(3);
			$em->persist($incoming);
			$em->flush();

			$this->container->get('session')->getFlashBag()->add('success', 'Successfully set Incoming container to Completed.');

			return TRUE;
		} else {
			$this->container->get('session')->getFlashBag()->add('error', 'Unable to mark the container as completed because it is not in an active status.');

			return FALSE;
		}
	}
}