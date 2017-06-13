<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-25
 * Time: 3:08 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Exception\Manager\ManagerException;

class ProductManager
{
	private $productRepository;
	private $em;

	/**
	 * ProductManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->em = $entityManager;
		$this->productRepository = $entityManager->getRepository('WarehouseBundle:Product');
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
		return $this->productRepository->findProductAvailableQuantity($sku);
	}

	/**
	 * @param string $sku
	 *
	 * @return null|object|\WarehouseBundle\Entity\Product
	 */
	public function getOneBySku(string $sku)
	{
		return $this->productRepository->findOneBy(['model' => $sku]);
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
		$entityManager = $entityManager ? $entityManager : $this->em;
		if ($user) {
			$product->setUser($user);
		}
		$entityManager->persist($product);
		if ($flush) {
			$entityManager->flush();
		}
	}
}