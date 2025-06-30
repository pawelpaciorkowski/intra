<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TableColumnRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "table_columns")]
#[ORM\Entity(repositoryClass: TableColumnRepository::class, readOnly: true)]
#[ORM\Cache(usage: "READ_ONLY")]
class TableColumn
{
    #[ORM\ManyToOne(inversedBy: "tableColumns")]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Table $table = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: "column_name", length: 50)]
    #[Assert\Length(max: 50)]
    private ?string $column = null;

    #[ORM\Column(name: "column_real_name_sort", length: 50, options: ["comment" => "real sort columns"])]
    #[Assert\Length(max: 50)]
    private ?string $columnRealSort = null;

    #[ORM\Column(options: ["comment" => "this column is a default order"])]
    private bool $isDefaultSort;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColumn(): ?string
    {
        return $this->column;
    }

    public function setColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function getColumnRealSort(): ?string
    {
        return $this->columnRealSort;
    }

    public function setColumnRealSort(string $columnRealSort): self
    {
        $this->columnRealSort = $columnRealSort;

        return $this;
    }

    public function getIsDefaultSort(): bool
    {
        return $this->isDefaultSort;
    }

    public function setIsDefaultSort(bool $isDefaultSort): self
    {
        $this->isDefaultSort = $isDefaultSort;

        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table = null): self
    {
        $this->table = $table;

        return $this;
    }
}
