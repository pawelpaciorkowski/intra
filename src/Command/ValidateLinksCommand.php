<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ValidateLinksCommand extends Command
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public UrlGeneratorInterface $router,
        public LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:validate:links')->setDescription('Show wrong links');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $links = $this
            ->entityManager
            ->getRepository(Link::class)
            ->findAll();

        foreach ($links as $link) {
            if (null === $this->router->getRouteCollection()->get($link->getName())) {
                $this->logger->info('Removing link.', ['name' => $link->getName()]);
                $this->entityManager->remove($link);
            }
        }
        $this->entityManager->flush();

        $this->logger->info('All invalid links deleted.');

        $io = new SymfonyStyle($input, $output);
        $io->success('All invalid links deleted.');

        return 0;
    }
}
