<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-25
 * Time: 2:53 PM
 */

namespace WarehouseBundle\Exception\Manager;


use Throwable;

class ManagerException extends \Exception
{
	protected $detail;

	/**
	 * ManagerException constructor.
	 *
	 * @param string         $message
	 * @param string         $detail
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $detail = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->detail = $detail;
	}

	/**
	 * @return mixed
	 */
	public function getDetail()
	{
		return $this->detail;
	}

	/**
	 * @param mixed $detail
	 */
	public function setDetail($detail)
	{
		$this->detail = $detail;
	}
}