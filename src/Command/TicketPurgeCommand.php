<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TicketPurgeCommand extends Command
{
    public function __construct(public EntityManagerInterface $entityManager, public LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:ticket:purge')->setDescription(
            'Remove old (' . Ticket::VALID_TIMEOUT . ' seconds) tickets'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->entityManager->getRepository(Ticket::class)->deleteOutdatedTickets();

        $this->logger->info('Invalid tickets was successfully purged.', ['counter' => $result]);

        $io = new SymfonyStyle($input, $output);
        $io->success('Invalid tickets was successfully purged.');

        return 0;
    }
}
