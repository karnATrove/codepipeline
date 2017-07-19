<?php

namespace RoveSiteRestApiBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use JMS\Serializer\SerializerBuilder;
use Rove\CanonicalDto\Product\ProductDto;
use Rove\CanonicalDto\Product\ProductItemDto;
use RoveSiteRestApiBundle\Exception\RoveSiteApiException;
use RoveSiteRestApiBundle\Utils\SerializeHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductManager extends BaseManager
{
	private $PRODUCT_URL;

	private $PRODUCT_ITEM_URL;

	/**
	 * ContainerManager constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->PRODUCT_URL = $this->roveSiteUrl . $container->getParameter('rove_site.api.url.product');
		$this->PRODUCT_ITEM_URL = $this->roveSiteUrl . $container->getParameter('rove_site.api.url.productItem');
	}

    /**
     * Fetch product info by searching style code
     *
     * @param string $style_code
     * @see https://roveconcepts.atlassian.net/wiki/display/APIS/[GET]+Product+Read
     * @return \Rove\CanonicalDto\Product\ProductDto
     * @throws \RoveSiteRestApiBundle\Exception\RoveSiteApiException
     *
     */
	public function getByStyleCode(string $style_code)
	{
		$timestamp = time();
		$url = $this->PRODUCT_URL . "/{$style_code}";
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
            $statusCode = $response->getStatusCode();
			throw new RoveSiteApiException("Failed to fetch product from Rove. Detail: "
				. $responseErrorDto->getMessage(), $statusCode, $clientErrorResponseException);
		} catch (ServerErrorResponseException $serverErrorResponseException) {
			$response = $serverErrorResponseException->getResponse();
			$body = $response->getBody(true);
            $statusCode = $response->getStatusCode();
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to fetch product from Rove. Detail: "
				. $responseErrorDto->getMessage(),$statusCode, $serverErrorResponseException);
		} catch (\Exception $exception) {
			throw new RoveSiteApiException($exception->getMessage());
		}
	}

    /**
     * Fetch product info by searching any SKU of product.
     * NOTE: this WILL NOT work on custom sku
     * @see https://roveconcepts.atlassian.net/wiki/display/APIS/[GET]+Product+Read
     * @param string $SKU
     *
     * @return ProductDto
     */
    public function getBySku(string $SKU)
    {
        $timestamp = time();
        $url = $this->PRODUCT_URL;
        $queryString = ['filter'=>['sku'=>['eq'=>$SKU]]];
        $headers = parent::generateAuthHeaders($url, $timestamp, 'GET');
        $request = $this->roveClient->get($url, $headers, ['query'=>$queryString]);

        try {
            $request->send();
            $response = $request->getResponse();
            $count = self::ResponseDataCountGet($response);
            ## return empty array if sku not found.
            if ($count < 1) {
                throw new RoveSiteApiException('Failed to fetch product from Rove. Detail: SKU not found', 404);
            }

            $body = $response->getBody(TRUE);
            $serializer = SerializerBuilder::create()->build();

            /** @var ProductDto[] $products */
            $products = $serializer->deserialize($body, 'array<'.ProductDto::class.'>', 'json');
            if (count($products) < 1) {
                throw new RoveSiteApiException('Failed to fetch product from Rove. Detail: SKU not found', 404);
            }
            return $products[0];//only return the first record

        } catch (ClientErrorResponseException $clientErrorResponseException) {
            $response = $clientErrorResponseException->getResponse();
            $body = $response->getBody(true);
            $statusCode = $response->getStatusCode();
            $responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
            throw new RoveSiteApiException("Failed to fetch product from Rove. Detail: "
                . $responseErrorDto->getMessage(),$statusCode, $clientErrorResponseException);
        } catch (ServerErrorResponseException $serverErrorResponseException) {
            $response = $serverErrorResponseException->getResponse();
            $body = $response->getBody(true);
            $statusCode = $response->getStatusCode();
            $responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
            throw new RoveSiteApiException("Failed to fetch product from Rove. Detail: "
                . $responseErrorDto->getMessage(), $statusCode, $serverErrorResponseException);
        } catch (\Exception $exception) {
            throw new RoveSiteApiException($exception->getMessage());
        }
    }

    /**
     * Get the product item by sku
     * @see https://roveconcepts.atlassian.net/wiki/display/APIS/[GET]+Product+Item+Read
     * @param string $SKU
     *
     * @return \Rove\CanonicalDto\Product\ProductItemDto
     */
    public function getItemBySku(string $SKU) {
        $timestamp = time();
        $url = $this->PRODUCT_ITEM_URL . '/'.$SKU;

        $headers = parent::generateAuthHeaders($url, $timestamp, 'GET');
        $request = $this->roveClient->get($url, $headers);
        try {
            $request->send();
            $response = $request->getResponse()->getBody(true);
            $serializer = SerializerBuilder::create()->build();
            $productItemDto = $serializer->deserialize($response, ProductItemDto::class, 'json');
            return $productItemDto;
        } catch (ClientErrorResponseException $clientErrorResponseException) {
            $response = $clientErrorResponseException->getResponse();
            $body = $response->getBody(true);
            $responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
            $statusCode = $response->getStatusCode();
            throw new RoveSiteApiException("Failed to fetch product from Rove. Detail: "
                . $responseErrorDto->getMessage(), $statusCode, $clientErrorResponseException);
        } catch (ServerErrorResponseException $serverErrorResponseException) {
            $response = $serverErrorResponseException->getResponse();
            $body = $response->getBody(true);
            $statusCode = $response->getStatusCode();
            $responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
            throw new RoveSiteApiException("Failed to fetch product from Rove. Detail: "
                . $responseErrorDto->getMessage(),$statusCode, $serverErrorResponseException);
        } catch (\Exception $exception) {
            throw new RoveSiteApiException($exception->getMessage());
        }
    }
}