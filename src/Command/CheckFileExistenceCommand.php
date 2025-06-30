<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ISOFile;
use App\Entity\PageFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckFileExistenceCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:check-file-existence');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $iso = $this->entityManager->getRepository(ISOFile::class)->findAll();

        foreach ($iso as $file) {
            if ($file->getOriginalFileAbsolutePath() && !file_exists($file->getOriginalFileAbsolutePath())) {
                print("ISO ORIGINAL Nie ma pliku " . $file->getId() . ", " . $file->getOriginalFileAbsolutePath(
                    ) . "\n");
            }

            if ($file->getCurrentFileAbsolutePath() && !file_exists($file->getCurrentFileAbsolutePath())) {
                print("ISO CURRENT Nie ma pliku " . $file->getId() . ", " . $file->getCurrentFileAbsolutePath() . "\n");
            }
        }

        $page = $this->entityManager->getRepository(PageFile::class)->findAll();

        foreach ($page as $file) {
            if ($file->getOriginalFileAbsolutePath() && !file_exists($file->getOriginalFileAbsolutePath())) {
                print("PAGE ORIGINAL Nie ma pliku " . $file->getId() . ", " . $file->getOriginalFileAbsolutePath(
                    ) . "\n");
            }

            if ($file->getCurrentFileAbsolutePath() && !file_exists($file->getCurrentFileAbsolutePath())) {
                print("PAGE CURRENT Nie ma pliku " . $file->getId() . ", " . $file->getCurrentFileAbsolutePath(
                    ) . "\n");
            }
        }

        return 0;
    }
}
