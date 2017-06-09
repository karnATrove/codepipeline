<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 4:30 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\Entity\Product;

class LocationProductManager extends BaseManager
{
	private $locationProductRepository;

	/**
	 * LocationProductManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->locationProductRepository = $entityManager->getRepository('WarehouseBundle:LocationProduct');
	}

	/**
	 * @param Product  $product
	 * @param Location $location
	 *
	 * @return mixed
	 */
	public function findOneByProductAndLocation(Product $product, Location $location)
	{
		return $this->locationProductRepository->findOneByProductAndLocation($product, $location);
	}

	/**
	 * @param IncomingProductScan $incomingProductScan
	 * @param null                $entityManager
	 */
	public function createLocationProductByIncomingProductScan(IncomingProductScan $incomingProductScan, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$locationProduct = new LocationProduct();
		$locationProduct->setProduct($incomingProductScan->getProduct());
		$locationProduct->setLocation($incomingProductScan->getLocation());
		$locationProduct->setOnHand($incomingProductScan->getQtyOnScan());
		$locationProduct->setCreated(new \DateTime('now'));
		$entityManager->persist($locationProduct);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * @param LocationProduct $locationProduct
	 * @param null            $entityManager
	 */
	public function updateLocationProduct(LocationProduct $locationProduct, $entityManager = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$entityManager->persist($locationProduct);
		if ($flush) {
			$entityManager->flush();
		}
	}
}