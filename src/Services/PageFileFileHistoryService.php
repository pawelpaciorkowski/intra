<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Enum\FileType;
use App\Entity\PageFile;
use App\Entity\PageFileFileHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PageFileFileHistoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function saveCurrent(PageFile $pageFile): void
    {
        $pageFileFileHistory = new PageFileFileHistory();
        $pageFileFileHistory->setPageFile($pageFile)
            ->setUser($this->tokenStorage->getToken()?->getUser())
            ->setFileType(FileType::CURRENT)
            ->setFilename($pageFile->getCurrentFileOriginalFilename())
            ->setTemporaryFilename($pageFile->getCurrentFileFilename());

        $this->entityManager->persist($pageFileFileHistory);
        $this->entityManager->flush();
    }

    public function saveOriginal(PageFile $pageFile): void
    {
        $pageFileFileHistory = new PageFileFileHistory();
        $pageFileFileHistory->setPageFile($pageFile)
            ->setUser($this->tokenStorage->getToken()?->getUser())
            ->setFileType(FileType::ORIGINAL)
            ->setFilename($pageFile->getOriginalFileOriginalFilename())
            ->setTemporaryFilename($pageFile->getOriginalFileFilename());

        $this->entityManager->persist($pageFileFileHistory);
        $this->entityManager->flush();
    }
}
