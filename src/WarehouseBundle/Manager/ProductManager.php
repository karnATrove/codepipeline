<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Rove\CanonicalDto\Product\ProductItemDimensionDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Exception\Manager\ManagerException;
use WarehouseBundle\Model\Product\ProductSearchModel;
use WarehouseBundle\Repository\ProductRepository;

class ProductManager extends BaseManager
{
	/**
	 * ProductManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, Product::class);
	}

	/**
	 * Get product item dimension
	 *
	 * @param ProductItemDto $productItemDto
	 *
	 * @return null|ProductItemDimensionDto
	 */
	public static function getProductItemDimensionFromProductItemDto(ProductItemDto $productItemDto)
	{
		$dimensions = $productItemDto->getDimensions();
		if (empty($dimensions)) {
			return null;
		}
		foreach ($dimensions as $dimension) {
			if ($dimension->getType() == ProductItemDimensionDto::TYPE_PRODUCT) {
				return $dimension;
			}
		}
		return $dimensions[0];
	}

	/**
	 * @param string $sku
	 *
	 * @return int
	 * @throws ManagerException
	 */
	public function getProductAvailableQuantity(string $sku): int
	{
		if (empty($sku)) {
			throw new ManagerException('Empty SKU', "Sku you provided is empty");
		}
		/** @var ProductRepository $repo */
		$repo = $this->entityRepository;
		return $repo->findProductAvailableQuantity($sku);
	}

	/**
	 * @param string $sku
	 *
	 * @return null|object|\WarehouseBundle\Entity\Product
	 */
	public function getOneBySku(string $sku)
	{
		return $this->entityRepository->findOneBy(['model' => $sku]);
	}

	/**
	 * @param null $sku
	 * @param null $user
	 *
	 * @return Product
	 */
	public function createNewProductWithDefaultInfo($sku = null, $user = null)
	{
		$product = new Product();
		$product->setCreated(new \DateTime());
		$product->setStatus(Product::PRODUCT_STATUS_ACTIVE);
		$product->setDescription('No product description');
		$product->setQtyPerCarton(1);
		$product->setDimUnits('in');
		$product->setWeightUnits('lbs');
		$product->setUser($user);
		$product->setModel($sku);
		return $product;
	}

	/**
	 * @param Product $product
	 * @param null    $entityManager
	 * @param null    $user
	 */
	public function updateProduct(Product $product, $entityManager = null, $user = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		if ($user) {
			$product->setUser($user);
		}
		$entityManager->persist($product);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * Count products
	 *
	 * @param ProductSearchModel $productSearchModel
	 *
	 * @return int
	 */
	public function countStockProduct(ProductSearchModel $productSearchModel)
	{
		/** @var ProductRepository $repo */
		$repo = $this->entityRepository;
		return $repo->countStockProduct($productSearchModel);
	}

	/**
	 * @param ProductSearchModel $searchModel
	 * @param bool               $returnQuery
	 *
	 * @return array|Query
	 */
	public function searchProducts(ProductSearchModel $searchModel, $returnQuery = false)
	{
		/** @var ProductRepository $repo */
		$repo = $this->entityRepository;
		return $repo->searchProducts($searchModel, $returnQuery);
	}

	/**
	 * @param Product $product
	 *
	 * @return int
	 */
	public static function quantityOnHand(Product $product)
	{
		$onHand = 0;
		foreach ($product->getLocations() as $location) {
			$onHand += $location->getOnHand();
		}
		return $onHand;
	}
}