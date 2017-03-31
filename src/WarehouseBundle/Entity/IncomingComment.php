<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IncomingComment
 *
 * @ORM\Table(name="warehouse_incoming_comment")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingCommentRepository")
 */
class IncomingComment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One IncomingComment has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many IncomingComment have One Incoming
     * @ORM\ManyToOne(targetEntity="Incoming", inversedBy="comments")
     * @ORM\JoinColumn(name="incoming_id", referencedColumnName="id")
     */
    private $incoming;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


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
     * Set comment
     *
     * @param string $comment
     *
     * @return IncomingComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return IncomingComment
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set incoming
     *
     * @param \WarehouseBundle\Entity\Incoming $incoming
     *
     * @return IncomingComment
     */
    public function setIncoming(\WarehouseBundle\Entity\Incoming $incoming = null)
    {
        $this->incoming = $incoming;

        return $this;
    }

    /**
     * Get incoming
     *
     * @return \WarehouseBundle\Entity\Incoming
     */
    public function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return IncomingComment
     */
    public function setUser(\WarehouseBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \WarehouseBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
