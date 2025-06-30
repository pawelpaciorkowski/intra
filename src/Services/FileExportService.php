<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\FileExport;
use App\Entity\FileExportRow;
use App\Entity\UserFileExportRow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class FileExportService
{
    private $entityManager;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getRowsForUser(UserInterface $user, int $fileExportId, bool $onlyVisible = false): ?array
    {
        $fileExport = $this->entityManager->getRepository(FileExport::class)->find($fileExportId);

        return $this
            ->entityManager
            ->getRepository(UserFileExportRow::class)
            ->getFileRowsForFileByUser($fileExport, $user, $onlyVisible);
    }

    public function setRows(array $rows, int $fileExportId): bool
    {
        $fileExport = $this->entityManager->getRepository(FileExport::class)->find($fileExportId);

        if (!$fileExport) {
            return false;
        }

        $order = 0;
        foreach ($rows as $row) {
            $fileExportRow = $this->entityManager->getRepository(FileExportRow::class)->findOneBy([
                'fileExport' => $fileExport,
                'id' => $row['id'],
            ]);

            if (!$fileExportRow) {
                return false;
            }

            $userFileExportRow = $this->entityManager->getRepository(UserFileExportRow::class)->findOneBy([
                'fileExportRow' => $fileExportRow,
                'user' => $this->tokenStorage->getToken()->getUser(),
            ]);

            if (!$userFileExportRow) {
                $userFileExportRow = new UserFileExportRow();
                $userFileExportRow
                    ->setFileExportRow($fileExportRow)
                    ->setUser($this->tokenStorage->getToken()->getUser());
                $this->entityManager->persist($userFileExportRow);
            }

            $userFileExportRow
                ->setOrder($order++)
                ->setIsInclude('true' === $row['isInclude']);
        }

        $this->entityManager->getRepository(UserFileExportRow::class)->deleteOverOrder(
            $fileExport,
            $this->tokenStorage->getToken()->getUser(),
            $order
        );

        $this->entityManager->flush();

        return true;
    }
}
