<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-14
 * Time: 4:54 PM
 */

namespace RoveSiteRestApiBundle\Manager;


use Guzzle\Http\Client;
use RoveSiteRestApiBundle\Utils\ApiSignatureGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseManager
{
	protected $roveSiteUrl;
	protected $roveSiteApiKey;
	protected $roveClient;

	/**
	 * OrderCommentManager constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->roveSiteUrl = $container->getParameter('rove_site.api.url.base');
		$this->roveSiteApiKey = $container->getParameter('rove_site.api.key');
		$this->roveClient = new Client();
	}

	/**
	 * @param $url
	 * @param $timestamp
	 *
	 * @return array
	 */
	public function generateAuthHeaders($url, $timestamp, string $method)
	{
		$signature = ApiSignatureGenerator::generateSignature($this->roveSiteApiKey, $url, $method, $timestamp);
		$headers = [
			'Content-Type' => 'application/json',
			'X-Rove-Requested' => $timestamp,
			'X-Rove-Signature' => $signature
		];
		return $headers;
	}
}