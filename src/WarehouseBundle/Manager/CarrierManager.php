<?php
/**
 * Created by PhpStorm.
 * User: rovedev
 * Date: 2017-07-21
 * Time: 11:08 AM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Carrier;

class CarrierManager extends BaseManager {

    private $carrierRepository;

    /**
     * CarrierManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct($entityManager);
        $this->carrierRepository = $this->entityManager->getRepository(Carrier::class);
    }

    /**
     * Return all carriers
     *
     * @return array|Carrier[]
     */
    public function findAll() {
        return $this->carrierRepository->findAll();
    }

    /**
     * Find carrier by carrier id
     * @param $carrierId
     * @return Carrier
     */
    public function find($carrierId) {
        return $this->carrierRepository->find($carrierId);
    }

    /**
     * find one carrier by criteria
     *
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return null|Carrier
     */
    public function findOneBy($criteria, $orderBy = NULL) {
        return $this->carrierRepository->findOneBy($criteria, $orderBy);
    }

    /**
     * Return list of carrier ['id'=>(name|code)]
     *
     * @param string $type
     * @param bool $flip
     *
     * @return array
     */
    public function getCarrierList(string $type = 'name', bool $flip = FALSE) {
        $carrierList = [];
        /** @var Carrier $carrier */
        foreach ($this->findAll() as $carrier) {
            $value = $type == 'name' ? $carrier->getName() : $carrier->getCode();
            $carrierList[$carrier->getId()] = $value;
        }

        if ($flip) {
            return array_flip($carrierList);
        }
        return $carrierList;
    }
}