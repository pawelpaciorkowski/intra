<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Ticket;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

use function bin2hex;
use function ceil;
use function random_bytes;
use function substr;

final class TicketService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateTicketForUser(User $user, ?int $type = null): string
    {
        if (!$type) {
            $type = Ticket::TYPE_PASSWORD;
        }

        $ticketString = $this->generateTicketString();

        $ticket = new Ticket();
        $ticket
            ->setType($type)
            ->setValidUntil(new DateTime('+' . Ticket::VALID_TIMEOUT . ' seconds'))
            ->setUser($user)
            ->setTicket($ticketString);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $ticketString;
    }

    public function generateTicketString(): string
    {
        return substr(bin2hex(random_bytes((int)ceil(Ticket::LENGTH / 2))), 0, Ticket::LENGTH);
    }
}
