<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\BillingCenter;
use App\Entity\City;
use App\Entity\CustomerService;
use App\Entity\Region;
use App\Entity\StreetType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class Laboratory implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populateData(object $object, array $data): object
    {
        $object->setUuid($data['uuid']);

        if (array_key_exists('user', $data)) {
            if (is_array($data['user']) && array_key_exists('uuid', $data['user'])) {
                $object->setUser(
                    $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $data['user']['uuid']])
                );
            } else {
                $object->setUser(null);
            }
        }

        if (array_key_exists('region', $data)) {
            if (is_array($data['region']) && array_key_exists('uuid', $data['region'])) {
                $object->setRegion(
                    $this->entityManager->getRepository(Region::class)->findOneBy(['uuid' => $data['region']['uuid']])
                );
            } else {
                $object->setRegion(null);
            }
        }

        if (array_key_exists('billingCenter', $data)) {
            if (is_array($data['billingCenter']) && array_key_exists('uuid', $data['billingCenter'])) {
                $object->setBillingCenter(
                    $this->entityManager->getRepository(BillingCenter::class)->findOneBy(
                        ['uuid' => $data['billingCenter']['uuid']]
                    )
                );
            } else {
                $object->setBillingCenter(null);
            }
        }

        if (array_key_exists('customerService', $data)) {
            if (is_array($data['customerService']) && array_key_exists('uuid', $data['customerService'])) {
                $object->setCustomerService(
                    $this->entityManager->getRepository(CustomerService::class)->findOneBy(
                        ['uuid' => $data['customerService']['uuid']]
                    )
                );
            } else {
                $object->setCustomerService(null);
            }
        }

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('symbol', $data)) {
            $object->setSymbol($data['symbol']);
        }

        if (array_key_exists('prefix', $data)) {
            $object->setPrefix($data['prefix']);
        }

        if (array_key_exists('mpk', $data)) {
            $object->setMpk($data['mpk']);
        }

        if (array_key_exists('postalCode', $data) && $data['postalCode']) {
            $object->setPostalCode(preg_replace('/\s/', '', $data['postalCode']));
        }

        if (array_key_exists('street', $data)) {
            $object->setStreet($data['street']);
        }

        if (array_key_exists('streetType', $data)) {
            if (is_array($data['streetType']) && array_key_exists('uuid', $data['streetType'])) {
                $object->setStreetType(
                    $this->entityManager->getRepository(StreetType::class)->findOneBy(
                        ['uuid' => $data['streetType']['uuid']]
                    )
                );
            } else {
                $object->setStreetType(null);
            }
        }

        if (array_key_exists('city', $data)) {
            if (is_array($data['city']) && array_key_exists('uuid', $data['city'])) {
                $object->setCity(
                    $this->entityManager->getRepository(City::class)->findOneBy(['uuid' => $data['city']['uuid']])
                );
            } else {
                $object->setCity(null);
            }
        }

        if (array_key_exists('isHospital', $data)) {
            $object->setIsHospital((bool)$data['isHospital']);
        }

        if (array_key_exists('isCollectionPoint', $data)) {
            $object->setIsCollectionPoint((bool)$data['isCollectionPoint']);
        }

        if (array_key_exists('isActive', $data)) {
            $object->setIsActive((bool)$data['isActive']);
        }

        if (array_key_exists('isOpenInSunday', $data)) {
            $object->setIsOpenInSunday((bool)$data['isOpenInSunday']);
        }

        if (array_key_exists('isOpenInSaturday', $data)) {
            $object->setIsOpenInSaturday((bool)$data['isOpenInSaturday']);
        }

        if (array_key_exists('isOpenInHoliday', $data)) {
            $object->setIsOpenInHoliday((bool)$data['isOpenInHoliday']);
        }

        if (array_key_exists('email', $data)) {
            $object->setEmail($data['email']);
        }

        if (array_key_exists('teryt', $data)) {
            $object->setTeryt((int)$data['teryt']);
        }

        if (array_key_exists('organizationalUnit', $data)) {
            $object->setOrganizationalUnit($data['organizationalUnit']);
        }

        if (array_key_exists('registerBook', $data)) {
            $object->setRegisterBook($data['registerBook']);
        }

        return $object;
    }
}
