<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-14
 * Time: 2:38 PM
 */

namespace RoveSiteRestApiBundle\Utils;


use Rove\CanonicalDto\Response\ResponseErrorDto;
use Rove\CanonicalDto\Response\ResponseErrorItemDto;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializeHelper
{
	/**
	 * @param string $responseErrorDto
	 *
	 * @return ResponseErrorDto
	 */
	public static function deserializeResponseErrorDto($responseErrorDto)
	{
		$serializer = new Serializer(
			[new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),  new ArrayDenormalizer()],
			[new JsonEncoder()]);
		/** @var ResponseErrorDto $responseErrorDto */
		$responseErrorDto = $serializer->deserialize($responseErrorDto, ResponseErrorDto::class, 'json');
		$errorItems = $serializer->denormalize($responseErrorDto->getErrorItems(),
			ResponseErrorItemDto::class . '[]');
		$responseErrorDto->setErrorItems($errorItems);
		return $responseErrorDto;
	}
}