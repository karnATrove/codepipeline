<?php

namespace WarehouseBundle\Exception\WorkflowException;

use Exception;
use Throwable;

class WorkflowAPIException extends Exception
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}