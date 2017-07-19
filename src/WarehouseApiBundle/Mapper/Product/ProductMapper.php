<?php

namespace WarehouseApiBundle\Mapper\Product;


use Rove\CanonicalDto\Product\ProductDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Manager\ProductManager;

class ProductMapper
{
	/**
     * Map product to production
	 * @param ProductItemDto $productItemDto
	 *
	 * @return Product
	 */
	public static function mapDtoToEntity(ProductItemDto $productItemDto)
	{
		$product = new Product();
		$product->setModel($productItemDto->getSku());
		// get product title from $productItemDto->attributes
		$productAttributes = $productItemDto->getAttributes();
        $productTitle = '';
        foreach ($productAttributes as $productAttribute) {
            if ($productAttribute->getKey() == 'title') {
                $productTitle = $productAttribute->getValue();
                break;
            }
        }
		$product->setDescription(empty($productTitle)?$productItemDto->getSku():$productTitle);
		$product->setQtyPerCarton($productItemDto->getPackageQuantity());
		$dimension = ProductManager::getProductItemDimensionFromProductItemDto($productItemDto);
		if ($dimension === NULL) {
		    return $product;
        }
		$product->setLength($dimension->getLength());
		$product->setWidth($dimension->getWidth());
		$product->setHeight($dimension->getHeight());
		//use length unit as dimension unit for now
		$product->setDimUnits($dimension->getLengthUnit());
		$product->setWeight($dimension->getWeight());
		$product->setWeightUnits($dimension->getWeightUnit());
		return $product;
	}
}