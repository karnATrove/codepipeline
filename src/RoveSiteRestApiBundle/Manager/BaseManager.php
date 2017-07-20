<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-14
 * Time: 4:54 PM
 */

namespace RoveSiteRestApiBundle\Manager;


use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
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

    /**
     * Return the total count of record.
     * @param \Guzzle\Http\Message\Response $response
     *
     * @return int
     */
    static public function ResponseDataCountGet(Response $response) {
	    if ($response->hasHeader('TOTAL_COUNT')) {
            return $response->getHeader('TOTAL_COUNT')->parseParams()[0];
        } else{
	        $data = $response->json();
	        if (is_object($data)) {
	            return 1;
            } elseif (is_array($data)) {
	            return count($data);
            }
        }
        return 0;
    }
}