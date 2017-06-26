<?php

namespace WarehouseApiBundle\Workflow;


use Rove\CanonicalDto\Container\ContainerDto;
use Rove\CanonicalDto\Product\ProductDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use RoveSiteRestApiBundle\Mapper\Container\ContainerMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use WarehouseApiBundle\Exception\ApiException;
use WarehouseApiBundle\Mapper\Product\ProductMapper;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingComment;
use WarehouseBundle\Entity\Product;

class ContainerWorkflow extends BaseWorkflow
{
	private $productManager;
	private $roveApiProductManager;
	private $incomingManager;
	private $incomingCommentManager;

	/**
	 * ContainerWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->productManager = $container->get('warehouse.manager.product_manager');
		$this->roveApiProductManager = $container->get('rove_site_rest_api.manager.product_manager');
		$this->incomingManager = $container->get('warehouse.manager.incoming_manager');
		$this->incomingCommentManager = $container->get('warehouse.manager.incoming_comment_manager');
	}


	public function createContainer(ContainerDto $containerDto)
	{
		//create container
		$incoming = $this->createIncoming($containerDto);

		//create products
		if (!empty($containerDto->getContainerProducts())) {
			$existProductList=[];

			foreach ($containerDto->getContainerProducts() as $containerProduct) {
				$sku = $containerProduct->getSku();

				//check product existence, create if not exist
				$product = $this->productManager->getOneBySku($sku);
				if (!$product) {
					$product = $this->createMissingProduct($sku);
				}

				if (array_key_exists($sku,$existProductList)){
					$incomingProduct = $existProductList[$sku];
					$incomingProduct->
				}else{
					$incomingProduct = $this->createIncomingProduct($product, $incoming);
					$existProductList[$sku] = $incomingProduct;
				}

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

	}

	/**
	 * @param ContainerDto $containerDto
	 *
	 * @return Incoming
	 */
	private function createIncoming(ContainerDto $containerDto)
	{
		$incomingEntity = ContainerMapper::mapDtoToEntity($containerDto, $this->entityManager);
		$incomingEntity->setCreated(new \DateTime());
		$incomingEntity->setModified(new \DateTime());
		$this->incomingManager->updateIncoming($incomingEntity, $this->entityManager);
		return $incomingEntity;
	}

	/**
	 * @param string $sku
	 *
	 * @return Product
	 */
	private function createMissingProduct(string $sku)
	{
		//create product
		$productDto = $this->roveApiProductManager->get($sku);
		$productItemDto = $this->getProductItemDtoFromProductDto($productDto, $sku);
		$productEntity = ProductMapper::mapDtoToEntity($productDto, $productItemDto);
		$productEntity->setCreated(new \DateTime());
		$productEntity->setStatus(Product::PRODUCT_STATUS_DEFAULT);
		$this->productManager->updateProduct($productEntity, $this->entityManager);
		return $productEntity;
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

	private function createIncomingProduct(Product $product, Incoming $incoming)
	{

	}
}