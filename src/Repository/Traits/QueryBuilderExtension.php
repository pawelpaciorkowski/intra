<?php

declare(strict_types=1);

namespace App\Repository\Traits;

use App\Services\Component\ParameterBagInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

use function implode;
use function is_array;
use function preg_match;
use function preg_replace;
use function trim;

trait QueryBuilderExtension
{
    private ?QueryBuilder $query = null;
    private ?ParameterBagInterface $parameterBag = null;

    private function includeFilterQueryData(array $columns): self
    {
        if (!$this->parameterBag) {
            return $this;
        }

        foreach ($columns as $paramName => $columnList) {
            if ($this->parameterBag->has($paramName) && '' !== (string)$this->parameterBag->get($paramName)) {
                $this->addWhereToQuery($this->query, $columnList, (string)$this->parameterBag->get($paramName));
            }
        }

        return $this;
    }

    private function addWhereToQuery(QueryBuilder $query, array $columns, string $data): self
    {
        $data = trim($data);

        $paramName = $this->createParamName($columns);

        if (preg_match('/^(>|>=|=|<=|<)([^<>=]*)$/', $data, $matches)) {
            $query->andWhere($this->createWhereMathString($columns, $paramName, $matches[1]))->setParameter(
                $paramName,
                $matches[2]
            );
        } elseif (preg_match('/^(\d+)(\s?\.\.\.\s?)(\d+)$/', $data, $matches)) {
            $query->andWhere($this->createWhereRangeString($columns, $paramName . '1', $paramName . '2'))
                ->setParameter($paramName . '1', $matches[1])
                ->setParameter($paramName . '2', $matches[3]);
        } elseif (preg_match('/^!(.*)$/', $data, $matches)) {
            $query->andWhere($this->createWhereNotLikeString($columns, $paramName))->setParameter(
                $paramName,
                '%' . $matches[1] . '%'
            );
        } else {
            $query->andWhere($this->createWhereLikeString($columns, $paramName))->setParameter(
                $paramName,
                '%' . $data . '%'
            );
        }

        return $this;
    }

    private function createParamName(array $name): string
    {
        return preg_replace('/\W/', '_', implode($name));
    }

    private function createWhereMathString(array $columns, string $paramName, string $rule): string
    {
        return implode(' ' . $rule . ' :' . $paramName . ' or ', $columns) . ' ' . $rule . ' :' . $paramName;
    }

    private function createWhereRangeString(array $columns, string $paramName1, string $paramName2): string
    {
        $whereElements = [];

        foreach ($columns as $column) {
            $whereElements[] = '(' . $column . ' >= :' . $paramName1 . ' and ' . $column . ' <= :' . $paramName2 . ')';
        }

        return implode(' or ', $whereElements);
    }

    private function createWhereNotLikeString(array $columns, string $paramName): string
    {
        return implode(' not like :' . $paramName . ' or ', $columns) . ' not like :' . $paramName;
    }

    private function createWhereLikeString(array $columns, string $paramName): string
    {
        return implode(' like :' . $paramName . ' or ', $columns) . ' like :' . $paramName;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function return(bool $cached = false): mixed
    {
        if ($this->parameterBag) {
            if ($this->parameterBag->has('id') || $this->parameterBag->has('uuid')) {
                $query = $this->query->getQuery();
                if ($cached) {
                    $query->enableResultCache();
                }
                return $query->getOneOrNullResult();
            }

            if ($this->parameterBag->has('rows_per_page')) {
                $this
                    ->query
                    ->setFirstResult($this->parameterBag->get('rows_per_page') * ($this->parameterBag->get('page') - 1))
                    ->setMaxResults($this->parameterBag->get('rows_per_page'));

                return new Paginator($this->query);
            }

            if ($this->parameterBag->has('limit')) {
                $this
                    ->query
                    ->setMaxResults($this->parameterBag->get('limit'));
            }

            if ($this->parameterBag->has('return_query')) {
                return $this->query;
            }

            if ($this->parameterBag->has('return_array')) {
                $query = $this->query->getQuery();
                if ($cached) {
                    $query->enableResultCache();
                }
                return $query->getArrayResult();
            }
        }

        $query = $this->query->getQuery();
        if ($cached) {
            $query->enableResultCache();
        }
        return $query->getResult();
    }

    private function order(string $default = '', string $order = 'asc'): self
    {
        if ($this->parameterBag && $this->parameterBag->has('orderBy')) {
            $this->query->add('orderBy', $this->parameterBag->get('orderBy'));
        } elseif ('' !== $default) {
            $this->query->addOrderBy($default, $order);
        }

        return $this;
    }

    private function group(?string $groupBy = null): self
    {
        if ($groupBy) {
            $this->query->add('groupBy', [$groupBy]);
        } elseif ($this->parameterBag && $this->parameterBag->has('groupBy')) {
            $this->query->add('groupBy', $this->parameterBag->get('groupBy'));
        }

        return $this;
    }

    private function where(array $params): self
    {
        $index = 0;
        foreach ($params as $param => $column) {
            if ($this->parameterBag && $this->parameterBag->has($param)) {
                if (is_array($column)) {
                    foreach ($column as $oneColumn) {
                        $this->query->andWhere($oneColumn . ' = :param' . $index)->setParameter(
                            'param' . $index,
                            $this->parameterBag->get($param)
                        );
                        ++$index;
                    }
                } elseif (null === $this->parameterBag->get($param)) {
                    $this->query->andWhere($column . ' is null');
                    ++$index;
                } else {
                    $this->query->andWhere($column . ' = :param' . $index)->setParameter(
                        'param' . $index,
                        $this->parameterBag->get($param)
                    );
                    ++$index;
                }
            }
        }

        return $this;
    }
}
