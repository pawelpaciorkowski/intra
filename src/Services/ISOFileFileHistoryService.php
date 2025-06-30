<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Enum\FileType;
use App\Entity\ISOFile;
use App\Entity\ISOFileFileHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ISOFileFileHistoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function saveCurrent(ISOFile $ISOFile): void
    {
        $ISOFileFileHistory = new ISOFileFileHistory();
        $ISOFileFileHistory->setISOFile($ISOFile)
            ->setUser($this->tokenStorage->getToken()?->getUser())
            ->setFileType(FileType::CURRENT)
            ->setFilename($ISOFile->getCurrentFileOriginalFilename())
            ->setTemporaryFilename($ISOFile->getCurrentFileFilename());

        $this->entityManager->persist($ISOFileFileHistory);
        $this->entityManager->flush();
    }

    public function saveOriginal(ISOFile $ISOFile): void
    {
        $ISOFileFileHistory = new ISOFileFileHistory();
        $ISOFileFileHistory->setISOFile($ISOFile)
            ->setUser($this->tokenStorage->getToken()?->getUser())
            ->setFileType(FileType::ORIGINAL)
            ->setFilename($ISOFile->getOriginalFileOriginalFilename())
            ->setTemporaryFilename($ISOFile->getOriginalFileFilename());

        $this->entityManager->persist($ISOFileFileHistory);
        $this->entityManager->flush();
    }
}
