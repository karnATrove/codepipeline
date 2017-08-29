<?php

namespace WarehouseApiBundle\Mapper\Booking;

use Rove\CanonicalDto\Booking\BookingItemDto;
use WarehouseApiBundle\Exception\MapperException;
use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Manager\BookingProductManager;

class BookingItemMapper
{
	/**
	 * @param $list
	 *
	 * @return array
	 */
	public static function mapToDtoList($list)
	{
		$dtoList = [];
		foreach ($list as $item) {
			$dtoList[] = self::mapToDto($item);
		}
		return $dtoList;
	}

	/**
	 * @param BookingProduct $bookingProduct
	 *
	 * @return BookingItemDto
	 */
	public static function mapToDto(BookingProduct $bookingProduct)
	{
		$bookingItemDto = new BookingItemDto();
		$bookingItemDto->setQuantity($bookingProduct->getQty());
		$bookingItemDto->setPickedDate($bookingProduct->getPickedDate());
		$bookingItemDto->setCreatedAt($bookingProduct->getCreated());
		$bookingItemDto->setUpdatedAt($bookingProduct->getModified());
		$bookingItemDto->setStatusCode(BookingProductManager::getCode($bookingProduct->getStatus()));
		$bookingItemDto->setSku($bookingProduct->getProduct()->getModel());
		return $bookingItemDto;
	}

    /**
     * @param \Rove\CanonicalDto\Booking\BookingItemDto $bookingItemDto
     * @param \WarehouseBundle\Manager\ProductManager   $productManager
     *
     * @throws \WarehouseApiBundle\Exception\MapperException
     */
    public static function mapDtoToEntity(BookingItemDto $bookingItemDto, $productManager)
    {
        $bookingProduct = new BookingProduct();
        $product = $productManager->getOneBySku($bookingItemDto->getSku());
        if (!$product) {
            throw new MapperException("Cannot find sku:{$bookingItemDto->getSku()}");
        }
        $bookingProduct->setProduct($product);
        $bookingProduct->setQty($bookingItemDto->getQuantity());
        $bookingProduct->setPickedDate($bookingItemDto->getPickedDate());
        $bookingProduct->setStatus(BookingProductManager::getStatusIdByCode($bookingItemDto->getStatusCode()));

        return $bookingProduct;
    }
}