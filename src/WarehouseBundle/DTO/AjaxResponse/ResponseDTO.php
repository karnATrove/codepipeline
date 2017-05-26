<?php

namespace WarehouseBundle\DTO\AjaxResponse;

use Doctrine\Common\Collections\ArrayCollection;

class ResponseDTO
{
	/** @var AjaxCommandDTO[] $ajaxCommand */
	private $ajaxCommand;

	public function __construct()
	{
		$this->ajaxCommand = new ArrayCollection();
	}

	/**
	 * @return AjaxCommandDTO[]
	 */
	public function getAjaxCommand(): array
	{
		return $this->ajaxCommand;
	}

	/**
	 * @param AjaxCommandDTO[] $ajaxCommand
	 */
	public function setAjaxCommand(array $ajaxCommand)
	{
		$this->ajaxCommand = $ajaxCommand;
	}

	/**
	 * @param AjaxCommandDTO $command
	 */
	public function addAjaxCommand(AjaxCommandDTO $command)
	{
		$this->ajaxCommand->add($command);
	}

	/**
	 * @param AjaxCommandDTO $command
	 */
	public function removeAjaxCommand(AjaxCommandDTO $command)
	{
		$this->ajaxCommand->remove($command);
	}
}