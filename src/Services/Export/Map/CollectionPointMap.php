<?php

declare(strict_types=1);

namespace App\Services\Export\Map;

use App\Entity\CollectionPoint;
use App\Entity\Period;

final class CollectionPointMap implements MapInterface
{
    private $columns;
    private $periodWork;
    private $periodCollect;

    public function getColumnNames(): array
    {
        $names = [];

        foreach ($this->columns as $column) {
            $names[] = $column->getName();
        }

        return $names;
    }

    private function getName($object): ?string
    {
        return $object->getName();
    }

    public function setColumns(?array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function getRow($object): array
    {
        $row = [];

        $this->periodWork = [];
        $this->periodCollect = [];
        foreach ($object->getPeriods() as $period) {
            if (Period::TYPE_WORK === $period->getType()) {
                $this->periodWork[$period->getDayOfWeek()->getId()][] = $period->getIsAllDay() ? 'całodobowo' : sprintf(
                    '%s-%s',
                    $period->getStartAt()->format('H:i'),
                    $period->getEndAt()->format('H:i')
                );
            } elseif (Period::TYPE_COLLECT === $period->getType()) {
                $this->periodCollect[$period->getDayOfWeek()->getId()][] = $period->getIsAllDay(
                ) ? 'całodobowo' : sprintf(
                    '%s-%s',
                    $period->getStartAt()->format('H:i'),
                    $period->getEndAt()->format('H:i')
                );
            }
        }

        foreach ($this->columns as $column) {
            $method = sprintf('get%s', ucfirst($column->getKey()));

            if (method_exists($this, $method)) {
                $row[] = $this->$method($object);
            } else {
                $row[] = 'error';
            }
        }

        return $row;
    }

    private function getId($object): ?int
    {
        return $object->getId();
    }

    private function getIsActive($object): ?string
    {
        return $object->getIsActive() ? 'tak' : 'nie';
    }

    private function getIsContest($object): ?string
    {
        return $object->getIsContest() ? 'tak' : 'nie';
    }

    private function getIsCovidPrivate($object): ?string
    {
        return $object->getIsCovidPrivate() ? 'tak' : 'nie';
    }

    private function getIs40Plus($object): ?string
    {
        return $object->getIs40Plus() ? 'tak' : 'nie';
    }

    private function getLaboratory($object): ?string
    {
        return $object->getLaboratory() ? $object->getLaboratory()->getName() : '';
    }

    private function getCoordinator($object): ?string
    {
        return $object->getUser() ? $object->getUser()->getFullname() : '';
    }

    private function getRegional($object): ?string
    {
        return $object->getUser2() ? $object->getUser2()->getFullname() : '';
    }

    private function getMarcel($object): ?string
    {
        return $object->getMarcel();
    }

    private function getMpk($object): ?string
    {
        return $object->getMpk();
    }

    private function getIsForDisabled($object): ?string
    {
        return $object->getIsForDisabled() ? 'tak' : 'nie';
    }

    private function getCollectionPointClassification($object): ?string
    {
        return $object->getCollectionPointClassification() ? $object->getCollectionPointClassification()->getName(
        ) : '';
    }

    private function getCollectionPointLocation($object): ?string
    {
        return $object->getCollectionPointLocation() ? $object->getCollectionPointLocation()->getName() : '';
    }

    private function getCollectionPointPartner($object): ?string
    {
        return $object->getCollectionPointPartner() ? $object->getCollectionPointPartner()->getName() : '';
    }

    private function getPriceList($object): ?string
    {
        return $object->getPriceList();
    }

    private function getTakingSamples($object): ?int
    {
        return $object->getTakingSamples();
    }

    private function getRegistrants($object): ?int
    {
        return $object->getRegistrants();
    }

    private function getIsInternet($object): ?string
    {
        return $object->getIsInternet() ? 'tak' : 'nie';
    }

    private function getIsChildren($object): ?string
    {
        return $object->getIsChildren() ? 'tak' : 'nie';
    }

    private function getIsChildrenConfirm($object): ?string
    {
        return $object->getIsChildrenConfirm() ? 'tak' : 'nie';
    }

    private function getIsGynecology($object): ?string
    {
        return $object->getIsGynecology() ? 'tak' : 'nie';
    }

    private function getIsGynecologyConfirm($object): ?string
    {
        return $object->getIsGynecologyConfirm() ? 'tak' : 'nie';
    }

    private function getIsDermatofit($object): ?string
    {
        return $object->getIsDermatofit() ? 'tak' : 'nie';
    }

    private function getIsDermatofitConfirm($object): ?string
    {
        return $object->getIsDermatofitConfirm() ? 'tak' : 'nie';
    }

    private function getStreetType($object): ?string
    {
        return $object->getStreetType() ? $object->getStreetType()->getName() : '';
    }

    private function getStreet($object): ?string
    {
        return $object->getStreet();
    }

    private function getAddressInfo($object): ?string
    {
        return $object->getAddressInfo();
    }

    private function getIsCard($object): ?string
    {
        return $object->getIsCard() ? 'tak' : 'nie';
    }

    private function getProvince($object): ?string
    {
        return $object->getCity() ? $object->getCity()->getProvince()->getName() : '';
    }

    private function getCity($object): ?string
    {
        return $object->getCity() ? $object->getCity()->getName() : '';
    }

    private function getPostalCode($object): ?string
    {
        return $object->getPostalCode();
    }

    private function getLongitude($object): ?string
    {
        return $object->getLongitude();
    }

    private function getLatitude($object): ?string
    {
        return $object->getLatitude();
    }

    private function getEmail($object): ?string
    {
        return $object->getEmail();
    }

    private function getAdditionalInfo($object): ?string
    {
        return $object->getAdditionalInfo();
    }

    private function getInternalInfo($object): ?string
    {
        return $object->getInternalInfo();
    }

    private function getIsChildrenAge($object): ?string
    {
        $isChildrenAge = '';
        if ($object->getIsChildrenAge() > 0) {
            if (array_key_exists($object->getIsChildrenAge(), CollectionPoint::IS_CHILDREN_AGE_OPTIONS)) {
                $isChildrenAge = CollectionPoint::IS_CHILDREN_AGE_OPTIONS[$object->getIsChildrenAge()];
            } else {
                $isChildrenAge = $object->getIsChildrenAge() . ' mieś.';
            }
        }

        return $isChildrenAge;
    }

    private function getPhones($object): ?string
    {
        $phones = [];
        foreach ($object->getPhones() as $phone) {
            $phones[] = $phone->getNumber();
        }

        return implode(', ', $phones);
    }

    private function getOpenAt($object): ?string
    {
        return $object->getOpenAt() ? $object->getOpenAt()->format('d.m.Y') : '';
    }

    private function getSignedAt($object): ?string
    {
        return $object->getSignedAt() ? $object->getSignedAt()->format('d.m.Y') : '';
    }

    private function getCloseAt($object): ?string
    {
        return $object->getCloseAt() ? $object->getCloseAt()->format('d.m.Y') : '';
    }

    private function getPeriodWork1($object): string
    {
        return array_key_exists('1', $this->periodWork) ? implode(', ', $this->periodWork[1]) : '';
    }

    private function getPeriodWork2($object): string
    {
        return array_key_exists('2', $this->periodWork) ? implode(', ', $this->periodWork[2]) : '';
    }

    private function getPeriodWork3($object): string
    {
        return array_key_exists('3', $this->periodWork) ? implode(', ', $this->periodWork[3]) : '';
    }

    private function getPeriodWork4($object): string
    {
        return array_key_exists('4', $this->periodWork) ? implode(', ', $this->periodWork[4]) : '';
    }

    private function getPeriodWork5($object): string
    {
        return array_key_exists('5', $this->periodWork) ? implode(', ', $this->periodWork[5]) : '';
    }

    private function getPeriodWork6($object): string
    {
        return array_key_exists('6', $this->periodWork) ? implode(', ', $this->periodWork[6]) : '';
    }

    private function getPeriodWork7($object): string
    {
        return array_key_exists('7', $this->periodWork) ? implode(', ', $this->periodWork[7]) : '';
    }

    private function getPeriodCollect1($object): string
    {
        return array_key_exists('1', $this->periodCollect) ? implode(', ', $this->periodCollect[1]) : '';
    }

    private function getPeriodCollect2($object): string
    {
        return array_key_exists('2', $this->periodCollect) ? implode(', ', $this->periodCollect[2]) : '';
    }

    private function getPeriodCollect3($object): string
    {
        return array_key_exists('3', $this->periodCollect) ? implode(', ', $this->periodCollect[3]) : '';
    }

    private function getPeriodCollect4($object): string
    {
        return array_key_exists('4', $this->periodCollect) ? implode(', ', $this->periodCollect[4]) : '';
    }

    private function getPeriodCollect5($object): string
    {
        return array_key_exists('5', $this->periodCollect) ? implode(', ', $this->periodCollect[5]) : '';
    }

    private function getPeriodCollect6($object): string
    {
        return array_key_exists('6', $this->periodCollect) ? implode(', ', $this->periodCollect[6]) : '';
    }

    private function getPeriodCollect7($object): string
    {
        return array_key_exists('7', $this->periodCollect) ? implode(', ', $this->periodCollect[7]) : '';
    }
}
