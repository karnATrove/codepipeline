<?php

namespace WarehouseApiBundle\Mapper\Product;


use Rove\CanonicalDto\Product\ProductDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Manager\ProductManager;

class ProductMapper
{
	/**
	 * @param ProductDto     $productDto
	 * @param ProductItemDto $productItemDto
	 *
	 * @return Product
	 */
	public static function mapDtoToEntity(ProductDto $productDto, ProductItemDto $productItemDto)
	{
		$product = new Product();
		$product->setModel($productItemDto->getSku());
		$product->setDescription($productDto->getDescription());
		$product->setQtyPerCarton($productItemDto->getPackageQuantity());
		$dimension = ProductManager::getProductItemDimensionFromProductItemDto($productItemDto);
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