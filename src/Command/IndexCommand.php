<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use App\Entity\CollectionPoint;
use App\Entity\Employee;
use App\Entity\Index;
use App\Entity\ISOCategory;
use App\Entity\ISOFile;
use App\Entity\Page;
use App\Entity\PageCategory;
use App\Entity\PageFile;
use App\Services\Url\UrlService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class IndexCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly UrlService $urlService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:index:seed')->setDescription(
            'Seed index with data'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Index::class, 'i')
            ->getQuery()
            ->execute();

        $classes = [
            Page::class => ['isActive' => true],
            Category::class => ['isActive' => true],
            Employee::class => [],
            CollectionPoint::class => [],
            ISOCategory::class => ['isActive' => true],
            ISOFile::class => ['isActive' => true],
            PageCategory::class => ['isActive' => true],
            PageFile::class => ['isActive' => true],
        ];

        $count = 0;
        foreach ($classes as $class => $attr) {
            $rows = $this->entityManager->getRepository($class)->findBy($attr);
            $count += count($rows);

            foreach ($rows as $row) {
                $index = new Index();
                $index
                    ->setUrl($this->urlService->generate($row))
                    ->setName($row->getNameForIndex())
                    ->setDescription($row->getDescriptionForIndex())
                    ->setObjectId($row->getId())
                    ->setObjectClass(get_class($row))
                    ->setObjectData(implode(', ', $row->getDataForIndex()))
                    ->setPriority($row->getPriority());
                $this->entityManager->persist($index);
            }
        }

        $this->entityManager->flush();

        $this->logger->info('Index ready', ['counter' => $count]);

        $io = new SymfonyStyle($input, $output);
        $io->success('Index ready');

        return 0;
    }
}
