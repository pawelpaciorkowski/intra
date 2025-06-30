<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ticket;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function deleteOutdatedTickets(): int
    {
        return (int)$this
            ->getEntityManager()
            ->createQueryBuilder()
            ->delete('App:Ticket', 't')
            ->where('t.validUntil <= :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }
}
