<?php

namespace RoveSiteRestApiBundle\Exception;

use Throwable;

class RoveSiteApiException extends \Exception
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}