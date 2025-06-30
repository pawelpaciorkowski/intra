<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Index;
use App\Entity\IndexInterface;
use App\Repository\IndexRepository;
use App\Services\Helper\Utf;
use App\Services\Url\UrlService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist, priority: 0, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 0, connection: 'default')]
#[AsDoctrineListener(event: Events::preRemove, priority: 0, connection: 'default')]
final readonly class IndexListener
{
    public function __construct(
        private IndexRepository $indexRepository,
        private EntityManagerInterface $entityManager,
        private UrlService $urlService,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof IndexInterface) {
            if (!$entity->getIsIndex()) {
                return;
            }

            $index = new Index();
            $index
                ->setName($entity->getNameForIndex())
                ->setDescription($entity->getDescriptionForIndex())
                ->setObjectId($entity->getId())
                ->setObjectClass(get_class($entity))
                ->setObjectData(Utf::cleanUp(implode(', ', $entity->getDataForIndex())))
                ->setUrl($this->urlService->generate($entity))
                ->setPriority($entity->getPriority());

            $this->entityManager->persist($index);
            $this->entityManager->flush();
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof IndexInterface) {
            $index = $this->indexRepository->findOneBy(['objectId' => $entity->getId(), 'objectClass' => get_class($entity)]);

            if (!$entity->getIsIndex()) {
                if ($index) {
                    $this->entityManager->remove($index);
                    $this->entityManager->flush();
                }

                return;
            }

            if (!$index) {
                $index = new Index();
                $index
                    ->setObjectId($entity->getId())
                    ->setObjectClass(get_class($entity))
                    ->setPriority($entity->getPriority());
                $this->entityManager->persist($index);
            }

            $objectData = implode(', ', $entity->getDataForIndex());
            $url = $this->urlService->generate($entity);

            if (
                $index->getName() !== $entity->getNameForIndex()
                || $index->getDescription() !== $entity->getDescriptionForIndex()
                || $index->getObjectData() !== $objectData
                || $index->getUrl() !== $url
            ) {
                $index
                    ->setName($entity->getNameForIndex())
                    ->setDescription($entity->getDescriptionForIndex())
                    ->setObjectData($objectData)
                    ->setUrl($url);

                $this->entityManager->flush();
            }
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof IndexInterface) {
            $index = $this->indexRepository->findOneBy(['objectId' => $entity->getId(), 'objectClass' => get_class($entity)]);

            if ($index) {
                $this->entityManager->remove($index);
                $this->entityManager->flush();
            }
        }
    }
}
