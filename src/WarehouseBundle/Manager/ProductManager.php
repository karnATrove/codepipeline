<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-25
 * Time: 3:08 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
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
}