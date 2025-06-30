<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Laboratory;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class LaboratoryRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Laboratory::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['l', 'la', 're', 'bc', 'da', 'cs', 'ci', 'ph'])
            ->from(Laboratory::class, 'l')
            ->leftJoin('l.user', 'us')
            ->leftJoin('l.databases', 'da')
            ->leftJoin('l.region', 're')
            ->leftJoin('l.billingCenter', 'bc')
            ->leftJoin('l.customerService', 'cs')
            ->leftJoin('l.labs', 'la')
            ->leftJoin('la.labGroup', 'lg')
            ->leftJoin('la.user', 'us2')
            ->leftJoin('l.city', 'ci')
            ->leftJoin('l.collectionPoints', 'cp')
            ->leftJoin('cp.user2', 'us3')
            ->leftJoin('l.phones', 'ph');

        if ($parameterBag) {
            if ($parameterBag->has('returnCount') && $parameterBag->get('returnCount')) {
                $this->query->select(['count(distinct l)']);
            }
        }

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['l.name'],
                    'symbol' => ['l.symbol'],
                    'mpk' => ['l.mpk'],
                    'city' => ['ci.name'],
                    'postal-code' => ['l.postalCode'],
                    'phone' => ['ph.number'],
                    'street' => ['l.street'],
                    'database' => ['da.ip', 'da.name'],
                    'region' => ['re.name'],
                    'billing-center' => ['bc.name'],
                    'customer-service' => ['cs.name'],
                    'is-all-day' => ['l.isAllDay'],
                    'is-hospital' => ['l.isHospital'],
                    'is-collection-point' => ['l.isCollectionPoint'],
                    'query' => [
                        'l.symbol',
                        'l.name',
                        'l.mpk',
                        'ci.name',
                        'l.postalCode',
                        'l.street',
                        'ph.number',
                        'da.ip',
                        'da.name',
                        're.name',
                        'bc.name',
                        'cs.name',
                    ],
                ]
            )
            ->where(
                [
                    'is-included-in-laboratory-time' => 'wp.isIncludedInLaboratoryTime',
                    'is-active' => 'l.isActive',
                    'collection-point-is-active' => 'cp.isActive',
                    'laboratory-id' => 'l.id',
                    'laboratory-user-id' => 'us.id',
                    'lab-user-id' => 'us2.id',
                    'region-user-id' => 'us3.id',
                    'region-id' => 're.id',
                    'customer-service-id' => 'cs.id',
                    'billing-center-id' => 'bc.id',
                    'id' => 'l.id',
                ]
            )
            ->order('l.name')
            ->return();
    }
}
