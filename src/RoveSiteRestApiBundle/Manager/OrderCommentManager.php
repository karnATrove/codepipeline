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

class OrderCommentManager extends BaseManager
{
	private $roveSiteUrlComment;

	/**
	 * ContainerManager constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->roveSiteUrlComment = $container->getParameter('rove_site.api.url.comment');
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
		$headers = parent::generateAuthHeaders($url, $timestamp,'POST');
		$request = $this->roveClient->post($url, $headers, $postData);
		try {
			$request->send();
		} catch (ClientErrorResponseException $clientErrorResponseException) {
			$response = $clientErrorResponseException->getResponse();
			$body = $response->getBody(true);
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to notify customer service. Detail: " .
				$responseErrorDto->getMessage());
		} catch (ServerErrorResponseException $serverErrorResponseException) {
			$response = $serverErrorResponseException->getResponse();
			$body = $response->getBody(true);
			$responseErrorDto = SerializeHelper::deserializeResponseErrorDto($body);
			throw new RoveSiteApiException("Failed to notify customer service. Detail: " .
				$responseErrorDto->getMessage());
		}
	}
}