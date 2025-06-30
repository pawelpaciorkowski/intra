<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Template;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\Component\ParameterBagInterface;

final class TemplateRepository extends ServiceEntityRepository
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['t', 'ta'])
            ->from(Template::class, 't')
            ->join('t.templateTag', 'ta');

        return $this
            ->includeFilterQueryData(
                [
                    'subject' => ['t.subject'],
                    'sender' => ['t.senderName', 't.senderAddress'],
                    'recipient' => ['t.recipient'],
                    'template-tag' => ['ta.name'],
                    'query' => ['t.subject', 't.senderName', 't.senderAddress', 't.recipient', 't.body', 'ta.name'],
                ]
            )
            ->where(['id' => 't.id'])
            ->order('t.subject')
            ->return();
    }
}
