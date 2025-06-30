<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserSetting;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class UserSettingRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSetting::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['us', 's', 'u'])
            ->from(UserSetting::class, 'us')
            ->join('us.setting', 's')
            ->join('us.user', 'u');

        return $this
            ->where([
                'key' => 's.key',
                'value' => 'us.value',
                'user' => 'us.user',
                'is-active' => 's.isActive',
                'id' => 'us.id',
            ])
            ->return();
    }
}
