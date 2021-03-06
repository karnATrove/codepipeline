<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IncomingStatus
 *
 * @ORM\Table(name="warehouse_incoming_status")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingStatusRepository")
 */
class IncomingStatus implements IEntity
{
	//Status
	const DELETED = 0;
	const INBOUND = 1;
	const ARRIVED = 2;
	const COMPLETED = 3;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="code", type="string", length=20)
	 */
	private $code;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="detail", type="string", length=255)
	 */
	private $detail;

	/**
	 * @ORM\OneToMany(targetEntity="WarehouseBundle\Entity\Incoming", mappedBy="status")
	 */
	private $incoming;

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get code
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Set code
	 *
	 * @param string $code
	 *
	 * @return IncomingStatus
	 */
	public function setCode($code)
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * Get detail
	 *
	 * @return string
	 */
	public function getDetail()
	{
		return $this->detail;
	}

	/**
	 * Set detail
	 *
	 * @param string $detail
	 *
	 * @return IncomingStatus
	 */
	public function setDetail($detail)
	{
		$this->detail = $detail;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIncoming()
	{
		return $this->incoming;
	}

	/**
	 * @param mixed $incoming
	 */
	public function setIncoming($incoming)
	{
		$this->incoming = $incoming;
	}
}

