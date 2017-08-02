<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-23
 * Time: 12:52 PM
 */

namespace WarehouseApiBundle\Workflow;


use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use RoveSiteRestApiBundle\Exception\RoveSiteApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseApiBundle\Exception\ApiException;
use WarehouseApiBundle\Mapper\Product\ProductMapper;
use WarehouseBundle\Entity\Product;

class BaseWorkflow
{
	protected $container;
	/** @var EntityManagerInterface $entityManager  */
	protected $entityManager;
	protected $roveApiProductManager;

	/**
	 * BaseWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->entityManager = $container->get('doctrine.orm.entity_manager');
		$this->roveApiProductManager = $container->get('rove_site_rest_api.manager.product_manager');
	}

    /**
     * @param string $sku
     *
     * @return Product
     */
    protected function createMissingProduct(string $sku)
    {
        //create product
        try{
            $productItemDto = $this->roveApiProductManager->getItemBySku($sku);
        }catch (RoveSiteApiException $exception) {
            throw new ApiException("Can not create missing sku $sku Detail: ".$exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        // only need product item to do mapping,
        // put product title into $productItemDto->attributes
        $productEntity = ProductMapper::mapDtoToEntity($productItemDto);
        $productEntity->setCreated(new \DateTime());
        $productEntity->setStatus(Product::PRODUCT_STATUS_DEFAULT);
        return $productEntity;
    }
}