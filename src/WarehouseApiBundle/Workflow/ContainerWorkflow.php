<?php

namespace WarehouseApiBundle\Workflow;


use Rove\CanonicalDto\Container\ContainerDto;
use Rove\CanonicalDto\Product\ProductDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use WarehouseApiBundle\Mapper\Container\ContainerMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use WarehouseApiBundle\Exception\ApiException;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingComment;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\Product;

class ContainerWorkflow extends BaseWorkflow
{
	private $productManager;
	private $incomingManager;
	private $incomingCommentManager;
	private $incomingProductManager;

	/**
	 * ContainerWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->productManager = $container->get('warehouse.manager.product_manager');
		$this->incomingManager = $container->get('warehouse.manager.incoming_manager');
		$this->incomingCommentManager = $container->get('warehouse.manager.incoming_comment_manager');
		$this->incomingProductManager = $container->get('warehouse.manager.incoming_product_manager');
	}

	/**
	 * @param ContainerDto $containerDto
	 */
	public function createContainer(ContainerDto $containerDto)
	{
		if ($this->incomingManager->findOneBy(['name' => $containerDto->getName()])) {
			throw new ApiException("Container with same name already exist", Response::HTTP_BAD_REQUEST);
		}
		//create container
		$incoming = $this->createIncoming($containerDto);
		$this->incomingManager->updateIncoming($incoming, $this->entityManager);
		//create products
		if (!empty($containerDto->getContainerProducts())) {
			$existProductList = [];

			foreach ($containerDto->getContainerProducts() as $containerProduct) {
				$sku = $containerProduct->getSku();

				//check product existence, create if not exist
				$product = $this->productManager->getOneBySku($sku);
				if (!$product) {
					$product = $this->createMissingProduct($sku);
					$this->productManager->updateProduct($product, $this->entityManager);
				}

				if (array_key_exists($sku, $existProductList)) {
					/** @var IncomingProduct $incomingProduct */
					$incomingProduct = $existProductList[$sku];
					$qty = $incomingProduct->getQty() + $containerProduct->getQuantity();
					$incomingProduct->setQty($qty);
				} else {
					$incomingProduct = $this->createIncomingProduct($product, $incoming, $containerProduct->getQuantity());
					$existProductList[$sku] = $incomingProduct;
				}
				$this->incomingProductManager->update($incomingProduct, $this->entityManager);
			}
		}

		//create comments
		if (!empty($containerDto->getComments())) {
			foreach ($containerDto->getComments() as $commentDto) {
				$incomingComment = new IncomingComment();
				$incomingComment->setComment($commentDto->getComment());
				$incomingComment->setCreated(new \DateTime());
				$incomingComment->setIncoming($incoming);
				$this->incomingCommentManager->update($incomingComment, $this->entityManager);
			}
		}
		$this->entityManager->flush();
	}

	/**
	 * @param ContainerDto $containerDto
	 *
	 * @return Incoming
	 */
	private function createIncoming(ContainerDto $containerDto)
	{
		$incomingEntity = ContainerMapper::mapDtoToEntity($containerDto, $this->entityManager);
		$incomingEntity->setCreated(new \DateTime('now'));
		$incomingEntity->setModified(new \DateTime('now'));
		return $incomingEntity;
	}

	/**
	 * @param ProductDto $productDto
	 * @param string     $sku
	 *
	 * @return ProductItemDto
	 * @throws ApiException
	 */
	private function getProductItemDtoFromProductDto(ProductDto $productDto, string $sku)
	{
		foreach ($productDto->getItems() as $productItemDto) {
			if ($productItemDto->getSku() == $sku) {
				return $productItemDto;
			}
		}
		throw new ApiException("Failed to get product data.", Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param Product  $product
	 * @param Incoming $incoming
	 * @param          $quantity
	 *
	 * @return IncomingProduct
	 */
	private function createIncomingProduct(Product $product, Incoming $incoming, $quantity)
	{
		$incomingProduct = new IncomingProduct();
		$incomingProduct->setQty($quantity);
		$incomingProduct->setIncoming($incoming);
		$incomingProduct->setProduct($product);
		$incomingProduct->setModel($product->getModel());
		$incomingProduct->setCreated(new \DateTime());
		$incomingProduct->setModified(new \DateTime());
		return $incomingProduct;
	}
}