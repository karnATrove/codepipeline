<?php

namespace WarehouseBundle\DTO\AjaxResponse;

class AjaxCommandDTO
{
	const OP_REDIRECT = "redirect";
	const OP_MODAL = "modal";
	const OP_HIDE = "hide";
	const OP_HTML = "html";
	const OP_APPEND = "append";
	const OP_REMOVE = "remove";
	const OP_NOTY = "noty";

	private $selector;
	private $op;
	private $value;
	private $params;

	public function __construct($selector = null, $operator = null, $value = null, $params = [])
	{
		$this->selector = $selector;
		$this->op = $operator;
		$this->value = $value;
		$this->params = $params;
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

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @param array $params
	 */
	public function setParams(array $params)
	{
		$this->params = $params;
	}

}