<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-23
 * Time: 1:11 PM
 */

namespace WarehouseApiBundle\Exception;


use Throwable;

class ApiException extends \Exception
{
	private $httpCode;

	public function __construct($message = "", $httpCode = 0, $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->httpCode = $httpCode;
	}

	/**
	 * @return mixed
	 */
	public function getHttpCode()
	{
		return $this->httpCode;
	}

	/**
	 * @param mixed $httpCode
	 */
	public function setHttpCode($httpCode)
	{
		$this->httpCode = $httpCode;
	}
}