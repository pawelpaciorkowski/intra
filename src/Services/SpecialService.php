<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Special;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

final class SpecialService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twigEnvironment
    ) {
    }

    public function getSpecial(int $id): string
    {
        $special = $this->entityManager->getRepository(Special::class)->find($id);

        if (null === $special) {
            return '';
        }

        if ($special->getSpecialTemplate()) {
            return $this->twigEnvironment
                ->createTemplate($special->getSpecialTemplate()->getTemplate())
                ->render([
                    'special' => $special,
                ]);
        }

        return (string)$special->getLongText();
    }
}
