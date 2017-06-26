<?php

namespace WarehouseApiBundle\Mapper\Product;


use Rove\CanonicalDto\Product\ProductDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use WarehouseBundle\Entity\Product;

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
		$quantityPerCarton = $productItemDto->getPackageQuantity()??$productDto->getPackageQuantity();
		$product->setQtyPerCarton($quantityPerCarton);
		$length = $productItemDto->getLength()??$productDto->getLength();
		$product->setLength($length);
		$width = $productItemDto->getWidth()??$productDto->getWidth();
		$product->setWidth($width);
		$height = $productItemDto->getHeight()??$productDto->getHeight();
		$product->setHeight($height);
		//use length unit as dimension unit for now
		$lengthUnit = $productItemDto->getLengthUnit()??$productDto->getLengthUnit();
		$product->setDimUnits($lengthUnit);
		$weight = $productItemDto->getWeight()??$productDto->getWeight();
		$product->setWeight($weight);
		$weightUnit = $productItemDto->getWidthUnit()??$productDto->getWeightUnit();
		$product->setWeightUnits($weightUnit);
		return $product;
	}
}