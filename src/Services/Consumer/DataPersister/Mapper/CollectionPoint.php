<?php

declare(strict_types=1);

namespace App\Services\Consumer\DataPersister\Mapper;

use App\Entity\City;
use App\Entity\CollectionPointClassification as CollectionPointClassificationEntity;
use App\Entity\CollectionPointLocation as CollectionPointLocationEntity;
use App\Entity\CollectionPointPartner as CollectionPointPartnerEntity;
use App\Entity\CollectionPointType as CollectionPointTypeEntity;
use App\Entity\Laboratory;
use App\Entity\StreetType;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CollectionPoint implements ConsumerMapperInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Exception
     */
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

        if (array_key_exists('user2', $data)) {
            if (is_array($data['user2']) && array_key_exists('uuid', $data['user2'])) {
                $object->setUser2(
                    $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $data['user2']['uuid']])
                );
            } else {
                $object->setUser2(null);
            }
        }

        if (array_key_exists('name', $data)) {
            $object->setName($data['name']);
        }

        if (array_key_exists('isActive', $data)) {
            $object->setIsActive((bool)$data['isActive']);
        }

        if (array_key_exists('marcel', $data)) {
            $object->setMarcel($data['marcel']);
        }

        if (array_key_exists('mpk', $data)) {
            $object->setMpk($data['mpk']);
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

        if (array_key_exists('addressInfo', $data)) {
            $object->setAddressInfo($data['addressInfo']);
        }

        if (array_key_exists('isCard', $data)) {
            $object->setIsCard((bool)$data['isCard']);
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

        if (array_key_exists('postalCode', $data)) {
            $postalCode = $data['postalCode'];
            if (is_string($data['postalCode'])) {
                $postalCode = preg_replace('/\s/', '', $data['postalCode']);
            }
            $object->setPostalCode($postalCode);
        }

        if (array_key_exists('isInternet', $data)) {
            $object->setIsInternet((bool)$data['isInternet']);
        }

        if (array_key_exists('isParking', $data)) {
            $object->setIsParking((bool)$data['isParking']);
        }

        if (array_key_exists('isForDisabled', $data)) {
            $object->setIsForDisabled((bool)$data['isForDisabled']);
        }

        if (array_key_exists('isChildren', $data)) {
            $object->setIsChildren((bool)$data['isChildren']);
        }

        if (array_key_exists('isChildrenConfirm', $data)) {
            $object->setIsChildrenConfirm((bool)$data['isChildrenConfirm']);
        }

        if (array_key_exists('isChildrenAge', $data)) {
            $object->setIsChildrenAge((int)$data['isChildrenAge']);
        }

        if (array_key_exists('isDermatofit', $data)) {
            $object->setIsDermatofit((bool)$data['isDermatofit']);
        }

        if (array_key_exists('isDermatofitConfirm', $data)) {
            $object->setIsDermatofitConfirm((bool)$data['isDermatofitConfirm']);
        }

        if (array_key_exists('isSwab', $data)) {
            $object->setIsSwab((bool)$data['isSwab']);
        }

        if (array_key_exists('isGynecology', $data)) {
            $object->setIsGynecology((bool)$data['isGynecology']);
        }

        if (array_key_exists('isGynecologyConfirm', $data)) {
            $object->setIsGynecologyConfirm((bool)$data['isGynecologyConfirm']);
        }

        if (array_key_exists('additionalInfo', $data)) {
            $object->setAdditionalInfo($data['additionalInfo']);
        }

        if (array_key_exists('internalInfo', $data)) {
            $object->setInternalInfo($data['internalInfo']);
        }

        if (array_key_exists('latitude', $data)) {
            $object->setLatitude($data['latitude']);
        }

        if (array_key_exists('longitude', $data)) {
            $object->setLongitude($data['longitude']);
        }

        if (array_key_exists('email', $data)) {
            $object->setEmail($data['email']);
        }

        if (array_key_exists('laboratory', $data)) {
            if (is_array($data['laboratory']) && array_key_exists('uuid', $data['laboratory'])) {
                $object->setLaboratory(
                    $this->entityManager->getRepository(Laboratory::class)->findOneBy(
                        ['uuid' => $data['laboratory']['uuid']]
                    )
                );
            } else {
                $object->setLaboratory(null);
            }
        }

        if (array_key_exists('collectionPointClassification', $data)) {
            if (is_array($data['collectionPointClassification']) && array_key_exists(
                'uuid',
                $data['collectionPointClassification']
            )) {
                $object->setCollectionPointClassification(
                    $this->entityManager->getRepository(CollectionPointClassificationEntity::class)->findOneBy(
                        ['uuid' => $data['collectionPointClassification']['uuid']]
                    )
                );
            } else {
                $object->setCollectionPointClassification(null);
            }
        }

        if (array_key_exists('collectionPointLocation', $data)) {
            if (is_array($data['collectionPointLocation']) && array_key_exists(
                'uuid',
                $data['collectionPointLocation']
            )) {
                $object->setCollectionPointLocation(
                    $this->entityManager->getRepository(CollectionPointLocationEntity::class)->findOneBy(
                        ['uuid' => $data['collectionPointLocation']['uuid']]
                    )
                );
            } else {
                $object->setCollectionPointLocation(null);
            }
        }

        if (array_key_exists('collectionPointPartner', $data)) {
            if (is_array($data['collectionPointPartner']) && array_key_exists(
                'uuid',
                $data['collectionPointPartner']
            )) {
                $object->setCollectionPointPartner(
                    $this->entityManager->getRepository(CollectionPointPartnerEntity::class)->findOneBy(
                        ['uuid' => $data['collectionPointPartner']['uuid']]
                    )
                );
            } else {
                $object->setCollectionPointPartner(null);
            }
        }

        if (array_key_exists('collectionPointType', $data)) {
            if (is_array($data['collectionPointType']) && array_key_exists('uuid', $data['collectionPointType'])) {
                $object->setCollectionPointType(
                    $this->entityManager->getRepository(CollectionPointTypeEntity::class)->findOneBy(
                        ['uuid' => $data['collectionPointType']['uuid']]
                    )
                );
            } else {
                $object->setCollectionPointType(null);
            }
        }

        if (array_key_exists('takingSamples', $data)) {
            $object->setTakingSamples((int)$data['takingSamples']);
        }

        if (array_key_exists('registrants', $data)) {
            $object->setRegistrants((int)$data['registrants']);
        }

        if (array_key_exists('priceList', $data)) {
            $object->setPriceList($data['priceList']);
        }

        if (array_key_exists('isContest', $data)) {
            $object->setIsContest((bool)$data['isContest']);
        }

        if (array_key_exists('isCovidPrivate', $data)) {
            $object->setIsCovidPrivate((bool)$data['isCovidPrivate']);
        }

        if (array_key_exists('is40Plus', $data)) {
            $object->setIs40Plus((bool)$data['is40Plus']);
        }

        if (array_key_exists('openAt', $data)) {
            $object->setOpenAt($data['openAt'] ? new DateTime($data['openAt']) : null);
        }

        if (array_key_exists('signedAt', $data)) {
            $object->setSignedAt($data['signedAt'] ? new DateTime($data['signedAt']) : null);
        }

        if (array_key_exists('closeAt', $data)) {
            $object->setCloseAt($data['closeAt'] ? new DateTime($data['closeAt']) : null);
        }

        return $object;
    }
}
