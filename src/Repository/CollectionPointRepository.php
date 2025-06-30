<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CollectionPoint;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class CollectionPointRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollectionPoint::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['c', 'la', 'ci', 'pr', 'us', 'cl', 'lo', 'pa', 'ty', 'alt', 'ph', 'ca', 'cc'])
            ->from(CollectionPoint::class, 'c')
            ->leftJoin('c.laboratory', 'la')
            ->leftJoin('c.user', 'us')
            ->leftJoin('la.user', 'us2')
            ->leftJoin('la.labs', 'lab')
            ->leftJoin('lab.user', 'us3')
            ->leftJoin('c.city', 'ci')
            ->leftJoin('ci.province', 'pr')
            ->leftJoin('la.region', 're')
            ->leftJoin('c.user2', 'us4')
            ->leftJoin('c.collectionPointClassification', 'cl')
            ->leftJoin('c.collectionPointLocation', 'lo')
            ->leftJoin('c.collectionPointPartner', 'pa')
            ->leftJoin('c.collectionPointType', 'ty')
            ->leftJoin('c.collectionPointAlternative', 'alt')
            ->leftJoin('c.phones', 'ph')
            ->leftJoin('c.calendars', 'ca')
            ->leftJoin('ca.chatbotizeCalendar', 'cc');

        if ($parameterBag && $parameterBag->has('returnCount') && $parameterBag->get('returnCount')) {
            $this->query->select(['count(distinct c.id)']);
        }

        if ($parameterBag && $parameterBag->has('withPeriods') && $parameterBag->get('withPeriods')) {
            $this->query
                ->addSelect(['pe', 'dw'])
                ->leftJoin('c.periods', 'pe')
                ->leftJoin('pe.dayOfWeek', 'dw');
        }

        return $this
            ->includeFilterQueryData(
                [
                    'user' => ['us.name', 'us.surname'],
                    'name' => ['c.name'],
                    'marcel' => ['c.marcel'],
                    'mpk' => ['c.mpk'],
                    'street' => ['c.street'],
                    'city' => ['ci.name'],
                    'province' => ['pr.name'],
                    'postal-code' => ['c.postalCode'],
                    'phone' => ['ph.number'],
                    'additional-info' => ['c.additionalInfo'],
                    'latitude' => ['c.latitude'],
                    'longitude' => ['c.longitude'],
                    'email' => ['c.email'],
                    'laboratory' => ['la.name'],
                    'collection-point-classification' => ['cl.name'],
                    'collection-point-location' => ['lo.name'],
                    'collection-point-partner' => ['pa.name'],
                    'taking-samples' => ['c.takingSamples'],
                    'registrants' => ['c.registrants'],
                    'priceList' => ['c.priceList'],
                    'collection-point-alternative' => ['alt.name'],
                    'open-at' => ['c.openAt'],
                    'signed-at' => ['c.signedAt'],
                    'close-at' => ['c.closeAt'],
                    'is-active' => ['c.isActive'],
                    'is-web' => ['c.isWeb'],
                    'is-shop' => ['c.isShop'],
                    'is-internet' => ['c.isInternet'],
                    'is-parking' => ['c.isParking'],
                    'is-for-disabled' => ['c.isForDisabled'],
                    'is-children' => ['c.isChildren'],
                    'is-dermatofit' => ['c.isDermatofit'],
                    'is-gynecology' => ['c.isGynecology'],
                    'is-gynecology-confirm' => ['c.isGynecologyConfirm'],
                    'is-children-confirm' => ['c.isChildrenConfirm'],
                    'is-children-age' => ['c.isChildrenAge'],
                    'is-dermatofit-confirm' => ['c.isDermatofitConfirm'],
                    'is-card' => ['c.isCard'],
                    'internal-info' => ['c.internalInfo'],
                    'address-info' => ['c.addressInfo'],
                    'user-region' => ['us4.name', 'us4.surname'],
                    'is-contest' => ['c.isContest'],
                    'is-covid-private' => ['c.isCovidPrivate'],
                    'is-40-plus' => ['c.is40Plus'],
                    'walk-3d' => ['c.walk3d'],
                    'chatbotize-calendar' => ['cc.name'],
                    'query' => [
                        'alt.name',
                        'c.additionalInfo',
                        'c.email',
                        'c.latitude',
                        'c.longitude',
                        'c.marcel',
                        'c.mpk',
                        'c.name',
                        'ph.number',
                        'c.postalCode',
                        'c.priceList',
                        'c.registrants',
                        'c.street',
                        'c.takingSamples',
                        'ci.name',
                        'pr.name',
                        'cl.name',
                        'la.name',
                        'lo.name',
                        'pa.name',
                        'ty.name',
                        'us.name',
                        'us.surname',
                        'c.openAt',
                        'c.signedAt',
                        'c.closeAt',
                        'c.isChildrenAge',
                        'c.internalInfo',
                        'c.addressInfo',
                        'us4.name',
                        'us4.surname',
                        'c.walk3d',
                        'cc.name',
                    ],
                ]
            )
            ->where([
                'id' => 'c.id',
                'collection-point-user-id' => 'us.id',
                'laboratory-user-id' => 'us2.id',
                'lab-user-id' => 'us3.id',
                'region-user-id' => 'us4.id',
                'region-id' => 're.id',
            ])
            ->order('c.marcel')
            ->return();
    }

    public function findToView(): array
    {
        $this->getEntityManager()->flush();

        $rows = $this
            ->createQueryBuilder('cp')
            ->select(['cp', 'ci', 'ph', 'pr', 'la', 'cl', 'lo', 'pa', 'ty', 'pe', 'do'])
            ->leftJoin('cp.city', 'ci')
            ->leftJoin('ci.province', 'pr')
            ->leftJoin('cp.phones', 'ph')
            ->leftJoin('cp.laboratory', 'la')
            ->leftJoin('cp.collectionPointClassification', 'cl')
            ->leftJoin('cp.collectionPointLocation', 'lo')
            ->leftJoin('cp.collectionPointPartner', 'pa')
            ->leftJoin('cp.collectionPointType', 'ty')
            ->leftJoin('cp.periods', 'pe')
            ->leftJoin('pe.dayOfWeek', 'do')
            ->where('cp.isActive = true')
            ->andWhere('cp.latitude != \'null\' and cp.longitude != \'null\'')
            ->getQuery()
            ->getResult();

        $return = [];

        foreach ($rows as $row) {
            $phones = [];
            foreach ($row->getPhones() as $phone) {
                $phones[] = $phone->getNumber();
            }

            $cp = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'marcel' => $row->getMarcel(),
                'mpk' => $row->getMpk(),
                'street' => trim(
                    ($row->getStreetType() ? $row->getStreetType()->getShort() : '') . ' ' . $row->getStreet()
                ),
                'city' => $row->getCity() ? $row->getCity()->getName() : null,
                'province' => $row->getCity() && $row->getCity()->getProvince() ? $row->getCity()->getProvince(
                )->getName() : null,
                'postalCode' => $row->getPostalCode(),
                'phones' => $phones,
                'additionalInfo' => $row->getAdditionalInfo(),
                'email' => $row->getEmail(),
                'laboratory' => $row->getLaboratory() ? $row->getLaboratory()->getName() : null,
                'latitude' => (float)$row->getLatitude(),
                'longitude' => (float)$row->getLongitude(),
                'isChildren' => $row->getIsChildren() ? 'Przyjazny dzieciom' : false,
                'isDermatofit' => $row->getIsDermatofit() ? 'Dermatofit' : false,
                'isSwab' => $row->getIsSwab() ? 'Wymazy' : false,
                'isGynecology' => $row->getIsGynecology() ? 'Ginekologiczny' : false,
//                'collectionPointClassification' => $row->getCollectionPointClassification() ? $row->getCollectionPointClassification()->getName() : '',
//                'collectionPointLocation' => $row->getCollectionPointLocation() ? $row->getCollectionPointLocation()->getName() : '',
                'collectionPointPartnerLogo' => $row->getCollectionPointPartner() && $row->getCollectionPointPartner(
                )->getFileLogoTemporaryFilename() ? $row->getCollectionPointPartner()->getFileLogoTemporaryFilename(
                ) : '',
                'user' => $row->getUser() ? $row->getUser()->getFullname() : null,
                'user2' => $row->getUser2() ? $row->getUser2()->getFullname() : null,
                'collectionPointType' => $row->getCollectionPointType() ? $row->getCollectionPointType()->getName(
                ) : '',
                'priceList' => $row->getPriceList(),
                'periods' => null,
            ];

            if ($row->getPeriods()) {
                foreach ($row->getPeriods() as $period) {
                    if ($period->getIsAllDay()) {
                        $p = 'całodobowo';
                    } else {
                        $p = ', ' . $period->getStartAt()->format('H:i') . ' - ' . $period->getEndAt()->format('H:i');
                    }

                    $cp['periods'][$period->getType()][$period->getDayOfWeek()->getId()] = trim($p, ', ');
                }
            }

            $return[] = $cp;
        }

        return $return;
    }
}
