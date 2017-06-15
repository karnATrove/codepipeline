<?php

namespace WarehouseBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class NullExcludedObjectNormalizer extends ObjectNormalizer
{
	/**
	 * This normalizer exclude unset variables
	 * {@inheritdoc}
	 */
	public function normalize($object, $format = null, array $context = [])
	{
		$data = parent::normalize($object, $format, $context);

		return array_filter($data, function ($value) {
			return null !== $value;
		});
	}
}