<?php

namespace RoveSiteRestApiBundle\Manager;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Rove\CanonicalDto\Order\OrderCommentCreateDto;
use RoveSiteRestApiBundle\Exception\RoveSiteApiException;
use RoveSiteRestApiBundle\Utils\ApiSignatureGenerator;
use RoveSiteRestApiBundle\Utils\SerializeHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class OrderCommentManager
{
	private $roveSiteUrl;
	private $roveSiteApiKey;
	private $roveSiteUrlComment;
	private $roveClient;

	/**
	 * OrderCommentManager constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->roveSiteUrl = $container->getParameter('rove_site.api.url.base');
		$this->roveSiteUrlComment = $container->getParameter('rove_site.api.url.comment');
		$this->roveSiteApiKey = $container->getParameter('rove_site.api.key');
		$this->roveClient = new Client();
	}

	/**
	 * Create Order Comment
	 *
	 * @param OrderCommentCreateDto $commentCreateDto
	 *
	 * @throws RoveSiteApiException
	 */
	public function create(OrderCommentCreateDto $commentCreateDto)
	{
		$serializer = new Serializer(
			[new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())],
			[new JsonEncoder()]);
		$postData = $serializer->serialize($commentCreateDto, 'json');
		$url = $this->roveSiteUrl . $this->roveSiteUrlComment;
		$timestamp = time();
		$signature = ApiSignatureGenerator::generateSignature($this->roveSiteApiKey, $url, 'POST', $timestamp);
		$headers = [
			'Content-Type' => 'application/json',
			'X-Rove-Requested' => $timestamp,
			'X-Rove-Signature' => $signature,
			'Authorization'=>'Basic cm92ZXRlYW06cm92ZXJvY2tz'
		];
		$request = $this->roveClient->post($url, $headers, $postData);
		try {
			$request->send();
		} catch (ClientErrorResponseException $clientErrorResponseException) {
			$response = $clientErrorResponseException->getResponse();
			$body = $response->getBody();
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to notify customer service. Detail: ".$responseErrorDto->getMessage());
		}catch (ServerErrorResponseException $serverErrorResponseException){
			$response = $serverErrorResponseException->getResponse();
			$body = $response->getBody();
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to notify customer service. Detail: ".$responseErrorDto->getMessage());
		}
	}
}