<?php
/*
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */
namespace WarehouseBundle\Entity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 * @ORM\Table(name="app_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=75, nullable=true)
     */
    protected $name;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 *
	 * @Assert\Length(
	 *     min=3,
	 *     max=255,
	 *     minMessage="The name is too short.",
	 *     maxMessage="The name is too long.",
	 *     groups={"Registration", "Profile"}
	 * )
	 */
	protected $company;

	/**
	 * @ORM\ManyToMany(targetEntity="WarehouseBundle\Entity\UserGroup")
	 * @ORM\JoinTable(name="user_group_mapper",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
	 * )
	 */
	protected $groups;

	/**
	 * @ORM\OneToOne(targetEntity="Invitation")
	 * @ORM\JoinColumn(referencedColumnName="code")
	 * @Assert\NotNull(message="Your invitation is wrong", groups={"Registration"})
	 */
	protected $invitation;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set model
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getInvitation()
	{
		return $this->invitation;
	}

	/**
	 * @param mixed $invitation
	 */
	public function setInvitation($invitation)
	{
		$this->invitation = $invitation;
	}

	/**
	 * @return mixed
	 */
	public function getCompany()
	{
		return $this->company;
	}

	/**
	 * @param mixed $company
	 */
	public function setCompany($company)
	{
		$this->company = $company;
	}


}
