<?php

namespace RoveSiteRestApiBundle\Utils;

class ApiSignatureGenerator
{
	/**
	 * Generate the signature to grant api access.
	 *
	 * @param string $apiKey private api key
	 * @param string $url url user requested
	 * @param string $method http method
	 * @param int    $timestamp
	 *
	 * @return string
	 */
	public static function generateSignature($apiKey, $url, $method, $timestamp) {
		$data = $method.'::'.$url.':'.$timestamp;
		$hash_val = hash_hmac('sha256', $data, $apiKey);
		return base64_encode($hash_val);
	}
}