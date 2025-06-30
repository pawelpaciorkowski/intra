<?php

declare(strict_types=1);

namespace App\Services\CollectionPoint;

use App\Entity\City;
use App\Entity\CollectionPoint;
use App\Entity\CollectionPointClassification;
use App\Entity\CollectionPointLocation;
use App\Entity\CollectionPointPartner;
use App\Entity\CollectionPointType;
use App\Entity\DayOfWeek;
use App\Entity\Laboratory;
use App\Entity\Period;
use App\Entity\Phone;
use App\Entity\Team;
use App\Entity\User;
use App\Services\CollectionPoint\Data\Row;
use App\Services\CollectionPoint\Exception\TranslatorException;
use App\Services\PeriodService;
use Doctrine\ORM\EntityManagerInterface;

class TranslatorService
{
    public $collectionPoint;
    public $row;
    private $entityManager;
    private $periodService;

    public function __construct(EntityManagerInterface $entityManager, PeriodService $periodService)
    {
        $this->entityManager = $entityManager;
        $this->periodService = $periodService;
    }

    public function getCollectionPoint(): ?CollectionPoint
    {
        return $this->collectionPoint;
    }

    public function setCollectionPoint(CollectionPoint $collectionPoint): self
    {
        $this->collectionPoint = $collectionPoint;

        return $this;
    }

    public function getRow(): Row
    {
        return $this->row;
    }

    public function setRow(Row $row): self
    {
        $this->row = $row;

        return $this;
    }

    /**
     * @throws TranslatorException
     */
    public function translate(): void
    {
        $this
            ->setSymbol()
            ->setName()
            ->setCollectionPointClassification()
            ->setCoordinator()
            ->setStreet()
            ->setModel()
            ->setCity()
            ->setPostalCode()
            ->setPhone()
            ->setLaboratory()
            ->setIsInternet()
            ->setIsParking()
            ->setIsForDisabled()
            ->setCollectionPointLocation()
            ->setCollectionPointPartner()
            ->setIsChildren()
            ->setIsGynecology()
            ->setIsDermatofit()
            ->setIsSwab()
            ->setOpenSunday()
            ->setOpenMonday()
            ->setOpenTuesday()
            ->setOpenWednesday()
            ->setOpenThursday()
            ->setOpenFriday()
            ->setOpenSaturday()
            ->setAdditionalInfo()
            ->setCollectionPointType()
            ->setEmail()
            ->setPriceList()
            ->setMpk();
    }

    private function setMpk(): void
    {
        $this->collectionPoint->setMpk($this->row->getMpk());
    }

    private function setPriceList(): self
    {
        $this->collectionPoint->setPriceList($this->row->getPriceList());

        return $this;
    }

    private function setEmail(): self
    {
        $this->collectionPoint->setEmail($this->row->getEmail());

        return $this;
    }

    private function setCollectionPointType(): self
    {
        $collectionPointType = null;

        if ($this->row->getCollectionPointType()) {
            $map = [
                'h0' => 'HL7',
                'hl7' => 'HL7',
                'icentrum' => 'Icentrum',
                'mail' => 'Mail',
                'materiał niestabilny' => 'Materiał niestabilny',
                'ogólny' => 'Podstawowy',
                'ogolny' => 'Podstawowy',
                'wewnętrzny' => 'Wewnętrzny',
            ];

            if (!array_key_exists(mb_strtolower($this->row->getCollectionPointType()), $map)) {
                throw new TranslatorException(
                    sprintf(
                        'CollectionPointType \'%s\' for collection point \'%s\' not found',
                        $this->row->getCollectionPointType(),
                        $this->row->getSymbol()
                    )
                );
            }

            $collectionPointType = $this
                ->entityManager
                ->getRepository(CollectionPointType::class)
                ->findOneBy(['name' => $map[mb_strtolower($this->row->getCollectionPointType())]]);

            if (!$collectionPointType) {
                throw new TranslatorException(
                    sprintf(
                        'CollectionPointType \'%s\' for collection point \'%s\' not found',
                        $this->row->getCollectionPointType(),
                        $this->row->getSymbol()
                    )
                );
            }
        }

        $this->collectionPoint->setCollectionPointType($collectionPointType);

        return $this;
    }

    private function setAdditionalInfo(): self
    {
        $this->collectionPoint->setAdditionalInfo($this->row->getAdditionalInfo());

        return $this;
    }

    private function setOpenSaturday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenSaturday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_SATURDAY_ID),
        );

        return $this;
    }

    private function setOpenFriday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenFriday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_FRIDAY_ID),
        );

        return $this;
    }

    private function setOpenThursday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenThursday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_THURSDAY_ID),
        );

        return $this;
    }

    private function setOpenWednesday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenWednesday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_WEDNESDAY_ID),
        );

        return $this;
    }

    private function setOpenTuesday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenTuesday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_TUESDAY_ID),
        );

        return $this;
    }

    private function setOpenMonday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenMonday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_MONDAY_ID),
        );

        return $this;
    }

    private function setOpenSunday(): self
    {
        $this->periodService->setPeriodsForCollectionPointAndDay(
            $this->row->getOpenSunday(),
            Period::TYPE_WORK,
            $this->collectionPoint,
            $this->entityManager->getRepository(DayOfWeek::class)->find(DayOfWeek::DAY_OF_WEEK_SUNDAY_ID),
        );

        return $this;
    }

    private function setIsSwab(): self
    {
        $this->collectionPoint->setIsSwab($this->row->getIsSwab());

        return $this;
    }

    private function setIsDermatofit(): self
    {
        $this->collectionPoint->setIsDermatofit($this->row->getIsDermatofit());

        return $this;
    }

    private function setIsGynecology(): self
    {
        $this->collectionPoint->setIsGynecology($this->row->getIsGynecology());

        return $this;
    }

    private function setIsChildren(): self
    {
        $this->collectionPoint->setIsChildren($this->row->getIsChildren());

        return $this;
    }

    private function setCollectionPointPartner(): self
    {
        $collectionPointPartner = null;

        if ($this->row->getCollectionPointPartner()) {
            $map = [
                'laboratoria medyczne' => 'Laboratoria Medyczne',
                'pp alab' => 'ALAB laboratoria',
                'pp aalb' => 'ALAB laboratoria',
                'pp alab pkp' => 'ALAB laboratoria',
                'pp bcam' => 'PP BCAM',
                'pp bio' => 'BioDiagnostyka',
                'pp laboratoria medyczne' => 'Laboratoria Medyczne',
                'pp lś' => 'Laboratorium Świętokrzyskie',
                'pp przygoda' => 'Laboratoria Medyczne',
            ];

            if (!array_key_exists(mb_strtolower($this->row->getCollectionPointPartner()), $map)) {
                throw new TranslatorException(
                    sprintf(
                        'CollectionPointPartner \'%s\' for collection point \'%s\' not found',
                        $this->row->getCollectionPointPartner(),
                        $this->row->getSymbol()
                    )
                );
            }

            $collectionPointPartner = $this
                ->entityManager
                ->getRepository(CollectionPointPartner::class)
                ->findOneBy(['name' => $map[mb_strtolower($this->row->getCollectionPointPartner())]]);

            if (!$collectionPointPartner) {
                throw new TranslatorException(
                    sprintf(
                        'CollectionPointPartner \'%s\' for collection point \'%s\' not found',
                        $this->row->getCollectionPointPartner(),
                        $this->row->getSymbol()
                    )
                );
            }
        }

        $this->collectionPoint->setCollectionPointPartner($collectionPointPartner);

        return $this;
    }

    private function setCollectionPointLocation(): self
    {
        $collectionPointLocation = null;

        if ($this->row->getCollectionPointLocation()) {
            $map = [
                'laboratorium' => 'Przy laboratorium',
                'nzoz' => 'NZOZ',
                'partnerski' => 'Partnerski',
                'pkp' => 'Partnerski',
                'przy laboratorium' => 'Przy laboratorium',
                'przy nzoz' => 'NZOZ',
                'przy spzoz' => 'NZOZ',
                'wolnostojący' => 'Wolnostojący',
            ];

            if (!array_key_exists(mb_strtolower($this->row->getCollectionPointLocation()), $map)) {
                throw new TranslatorException(
                    sprintf(
                        'CollectionPointLocation \'%s\' for collection point \'%s\' not found',
                        $this->row->getCollectionPointLocation(),
                        $this->row->getSymbol()
                    )
                );
            }

            $collectionPointLocation = $this
                ->entityManager
                ->getRepository(CollectionPointLocation::class)
                ->findOneBy(['name' => $map[mb_strtolower($this->row->getCollectionPointLocation())]]);

            if (!$collectionPointLocation) {
                throw new TranslatorException(
                    sprintf(
                        'CollectionPointLocation \'%s\' for collection point \'%s\' not found',
                        $this->row->getCollectionPointLocation(),
                        $this->row->getSymbol()
                    )
                );
            }
        }

        $this->collectionPoint->setCollectionPointLocation($collectionPointLocation);

        return $this;
    }

    private function setIsForDisabled(): self
    {
        $this->collectionPoint->setIsForDisabled($this->row->getIsForDisabled());

        return $this;
    }

    private function setIsParking(): self
    {
        $this->collectionPoint->setIsParking($this->row->getIsParking());

        return $this;
    }

    private function setIsInternet(): self
    {
        $this->collectionPoint->setIsInternet($this->row->getIsInternet());

        return $this;
    }

    private function setLaboratory(): self
    {
        if (!$this->row->getMpk()) {
            throw new TranslatorException(
                sprintf(
                    'Cannot search for Laboratory \'%s\' for collection point \'%s\' because MPK is empty',
                    $this->row->getMpk(),
                    $this->row->getSymbol()
                )
            );
        }

        $laboratory = $this
            ->entityManager
            ->getRepository(Laboratory::class)
            ->findOneBy(['mpk' => substr($this->row->getMpk(), 0, 3)]);

        if (!$laboratory) {
            throw new TranslatorException(
                sprintf(
                    'Laboratory \'%s\' for collection point \'%s\' not found',
                    $this->row->getMpk(),
                    $this->row->getSymbol()
                )
            );
        }

        $this->collectionPoint->setLaboratory($laboratory);

        return $this;
    }

    private function setPhone(): self
    {
        $phones = preg_replace('/[,;\n]+/', ',', $this->row->getPhone());
        $phones = preg_replace('/[^\d,]+/', '', $phones);

        $phones = preg_split('/,+/', $phones, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($phones as $phone) {
            if (!preg_match('/^\d{9}$/', $phone)) {
                throw new TranslatorException(
                    sprintf(
                        'Phone number has wrong format \'%s\' for collection point \'%s\'',
                        $phone,
                        $this->row->getSymbol()
                    )
                );
            }
        }

        foreach ($phones as $phone) {
            $phoneObject = new Phone();
            $phoneObject
                ->setCollectionPoint($this->collectionPoint)
                ->setNumber($phone);

            $this->collectionPoint->addPhone($phoneObject);
            $this->entityManager->persist($phoneObject);
        }

        return $this;
    }

    private function setPostalCode(): self
    {
        $postalCode = trim((string)$this->row->getPostalCode());

        if ($postalCode && !preg_match('/^\d{2}\-\d{3}$/', $postalCode)) {
            throw new TranslatorException(
                sprintf(
                    'Postal code has wrong format \'%s\' for collection point \'%s\'',
                    $postalCode,
                    $this->row->getSymbol()
                )
            );
        }

        $this->collectionPoint->setPostalCode($postalCode ?? null);

        return $this;
    }

    private function setCity(): self
    {
        $city = $this
            ->entityManager
            ->getRepository(City::class)
            ->findOneBy(['name' => $this->row->getCity()]);

        if (!$city) {
            throw new TranslatorException(
                sprintf(
                    'City \'%s\' for collection point \'%s\' not found',
                    $this->row->getCity(),
                    $this->row->getSymbol()
                )
            );
        }

        $this->collectionPoint->setCity($city);

        return $this;
    }

    private function setModel(): self
    {
        switch ($this->row->getModel()) {
            case '2 osoby pobierające':
            case 'Dwie pobierające':
                $this->collectionPoint->setTakingSamples(2);
                $this->collectionPoint->setRegistrants(0);
                break;
            case 'Cztery pobierajace i dwie rejestratorki':
                $this->collectionPoint->setTakingSamples(4);
                $this->collectionPoint->setRegistrants(2);
                break;
            case 'Dwie pobierające i dwie rejestratorki':
                $this->collectionPoint->setTakingSamples(2);
                $this->collectionPoint->setRegistrants(2);
                break;
            case 'Dwie pobierające i rejestratorka':
                $this->collectionPoint->setTakingSamples(2);
                $this->collectionPoint->setRegistrants(1);
                break;
            case 'Pobierająca i rejestratorka':
                $this->collectionPoint->setTakingSamples(1);
                $this->collectionPoint->setRegistrants(1);
                break;
            case 'Rejestratorka i 3 pobierające':
                $this->collectionPoint->setTakingSamples(3);
                $this->collectionPoint->setRegistrants(1);
                break;
            case 'Tylko pobierająca':
                $this->collectionPoint->setTakingSamples(1);
                $this->collectionPoint->setRegistrants(0);
                break;
            default:
                $this->collectionPoint->setTakingSamples(0);
                $this->collectionPoint->setRegistrants(0);
                break;
        }

        return $this;
    }

    private function setStreet(): self
    {
        $this->collectionPoint->setStreet($this->row->getStreet());

        return $this;
    }

    private function setCoordinator(): self
    {
        if (!$this->row->getCoordinator()) {
            return $this;
        }

        $names = preg_split('/\s/', $this->row->getCoordinator());
        $name = $names[0];
        $surname = $names[1];

        if (!$name || !$surname) {
            throw new TranslatorException(
                sprintf(
                    'Coordinator \'%s\' for collection point \'%s\' is invalid',
                    $this->row->getCoordinator(),
                    $this->row->getSymbol()
                )
            );
        }

        $coordinator = $this->entityManager->getRepository(User::class)->findOneBy([
            'name' => $name,
            'surname' => $surname,
            'team' => Team::COLLECTION_POINT_COORDINATOR_ID,
        ]);

        if ($coordinator) {
            $this->collectionPoint->setUser($coordinator);
        }

        return $this;
    }

    private function setCollectionPointClassification(): self
    {
        $collectionPointClassification = $this
            ->entityManager
            ->getRepository(CollectionPointClassification::class)
            ->findOneBy(['name' => $this->row->getCollectionPointClassification()]);

        $this->collectionPoint->setCollectionPointClassification($collectionPointClassification);

        return $this;
    }

    private function setName(): self
    {
        $this->collectionPoint->setName($this->row->getStreet());

        return $this;
    }

    private function setSymbol(): self
    {
        if (!$this->row->getSymbol()) {
            throw new TranslatorException(
                sprintf('Symbol \'%s\' form MPK \'%s\' cannot be empty', $this->row->getSymbol(), $this->row->getMpk())
            );
        }

        $this->collectionPoint->setMarcel($this->row->getSymbol());

        return $this;
    }
}
