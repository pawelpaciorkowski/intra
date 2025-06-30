<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FileExport;
use App\Entity\FileExportRow;
use App\Entity\UserFileExportRow;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserFileExportRowRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFileExportRow::class);
    }

    public function getFileRowsForFileByUser(
        FileExport $fileExport,
        UserInterface $user,
        bool $onlyVisible = false
    ): ?array {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select([
                'fe',
                'fer',
                'ufer',
                '(CASE WHEN ufer.id is not null THEN ufer.order ELSE fer.order END) AS HIDDEN s',
            ])
            ->from(FileExportRow::class, 'fer')
            ->join('fer.fileExport', 'fe')
            ->leftJoin('fer.userFileExportRows', 'ufer', Join::WITH, 'ufer.user = :user')
            ->setParameter('user', $user)
            ->andWhere('fe = :file')
            ->setParameter('file', $fileExport)
            ->addOrderBy('s', 'asc');

        if ($onlyVisible) {
            $query->andWhere('ufer.isInclude = 1 or (ufer.id is null and fer.isInclude = 1)');
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function deleteOverOrder(FileExport $fileExport, UserInterface $user, int $order): void
    {
        $rows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(['ufer'])
            ->from(UserFileExportRow::class, 'ufer')
            ->join('ufer.fileExportRow', 'fer')
            ->join('fer.fileExport', 'fe')
            ->andWhere('fe = :fileExport')
            ->setParameter('fileExport', $fileExport)
            ->andWhere('ufer.user = :user')
            ->setParameter('user', $user)
            ->andWhere('ufer.order > :order')
            ->setParameter('order', $order)
            ->getQuery()
            ->getResult();

        foreach ($rows as $row) {
            $this->getEntityManager()->remove($row);
        }

        $this->getEntityManager()->flush();
    }
}
