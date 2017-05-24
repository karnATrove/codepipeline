<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-24
 * Time: 1:30 PM
 */

namespace WarehouseBundle\Denormalizer;


use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use WarehouseBundle\Model\Booking\PickSummary\PickSummaryModel;

class PickSummaryModelDenormalizer implements DenormalizableInterface
{

	public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = [])
	{
//		$pickSummaryModel = new PickSummaryModel();
//		$itemList = [];
//		foreach ($data->items as $item){
//			$pickSummaryItemModel = $this->denormalize(
//				$item,
//			)
//		}
	}
}