<?php

namespace WarehouseBundle\DTO\AjaxResponse;

class AjaxCommandDTO
{
	const OP_REDIRECT = "redirect";
	const OP_MODAL = "modal";

	private $selector;
	private $op;
	private $value;

	public function __construct($selector = null, $operator = null, $value = null)
	{
		$this->selector = $selector;
		$this->op = $operator;
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getSelector()
	{
		return $this->selector;
	}

	/**
	 * @param mixed $selector
	 */
	public function setSelector($selector)
	{
		$this->selector = $selector;
	}

	/**
	 * @return mixed
	 */
	public function getOp()
	{
		return $this->op;
	}

	/**
	 * @param mixed $op
	 */
	public function setOp($op)
	{
		$this->op = $op;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

}