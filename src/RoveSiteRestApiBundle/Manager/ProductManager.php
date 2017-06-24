<?php

namespace RoveSiteRestApiBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use JMS\Serializer\SerializerBuilder;
use Rove\CanonicalDto\Product\ProductDto;
use RoveSiteRestApiBundle\Exception\RoveSiteApiException;
use RoveSiteRestApiBundle\Utils\SerializeHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductManager extends BaseManager
{
	private $PRODUCT_URL;

	/**
	 * ContainerManager constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->PRODUCT_URL = $this->roveSiteUrl . $container->getParameter('rove_site.api.url.product');
	}


	public function get(string $sku)
	{
		$timestamp = time();
		$url = $this->PRODUCT_URL . "/{$sku}";
		$headers = parent::generateAuthHeaders($url, $timestamp, 'GET');
		$request = $this->roveClient->get($url, $headers);
		try {
			$request->send();
			$response = $request->getResponse()->getBody(true);
			$serializer = SerializerBuilder::create()->build();
			$productDto = $serializer->deserialize($response, ProductDto::class, 'json');
			return $productDto;
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
		} catch (\Exception $exception) {
			throw new RoveSiteApiException($exception->getMessage());
		}
	}
}