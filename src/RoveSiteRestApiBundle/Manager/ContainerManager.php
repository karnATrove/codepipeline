<?php

namespace RoveSiteRestApiBundle\Manager;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Rove\CanonicalDto\Container\ContainerUpdateDto;
use Rove\CanonicalDto\Order\OrderCommentCreateDto;
use RoveSiteRestApiBundle\Exception\RoveSiteApiException;
use RoveSiteRestApiBundle\Utils\ApiSignatureGenerator;
use RoveSiteRestApiBundle\Utils\SerializeHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WarehouseBundle\Serializer\NullExcludedObjectNormalizer;

class ContainerManager extends BaseManager
{
	private $containerUpdateUrl;

	/**
	 * ContainerManager constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->containerUpdateUrl = $this->roveSiteUrl . $container->getParameter('rove_site.api.url.container');
	}

	/**
	 * @param ContainerUpdateDto $containerUpdateDto
	 * @param string             $containerName
	 *
	 * @throws RoveSiteApiException
	 */
	public function update(ContainerUpdateDto $containerUpdateDto,string $containerName)
	{
		$serializer = new Serializer(
			[new NullExcludedObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())],
			[new JsonEncoder()]);
		$data = $serializer->serialize($containerUpdateDto, 'json');
		$timestamp = time();
		$url = $this->containerUpdateUrl."/{$containerName}";
		$headers = parent::generateAuthHeaders($url, $timestamp,'PUT');
		$request = $this->roveClient->put($url, $headers, $data,['allow_redirects'=>true]);
//		$request->getParams()->set('redirect.strict', true);
		try {
			$request->send();
		} catch (ClientErrorResponseException $clientErrorResponseException) {
			$response = $clientErrorResponseException->getResponse();
			$body = $response->getBody(true);
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to update container. Detail: "
				. $responseErrorDto->getMessage());
		} catch (ServerErrorResponseException $serverErrorResponseException) {
			$response = $serverErrorResponseException->getResponse();
			$body = $response->getBody(true);
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to update container. Detail: "
				. $responseErrorDto->getMessage());
		}
	}
}