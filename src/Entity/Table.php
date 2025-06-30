<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "table_definitions")]
#[ORM\Entity(readOnly: true)]
#[ORM\Index(name: "table_name_idx", columns: ["table_name"])]
#[UniqueEntity(fields: ["table"])]
#[ORM\Cache(usage: "READ_ONLY")]
class Table
{
    #[ORM\OneToMany(targetEntity: "TableColumn", mappedBy: "table")]
    private Collection $tableColumns;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: "table_name", length: 50, unique: true)]
    #[Assert\Length(max: 50)]
    private ?string $table = null;

    #[ORM\Column(options: [
        "default" => 0,
        "comment" => "default order column",
    ])]
    private bool $isDirection;

    public function __construct()
    {
        $this->tableColumns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function getIsDirection(): bool
    {
        return $this->isDirection;
    }

    public function setIsDirection(bool $isDirection): self
    {
        $this->isDirection = $isDirection;

        return $this;
    }

    public function addTableColumn(TableColumn $tableColumn): self
    {
        $this->tableColumns[] = $tableColumn;

        return $this;
    }

    public function removeTableColumn(TableColumn $tableColumn): void
    {
        $this->tableColumns->removeElement($tableColumn);
    }

    public function getTableColumns(): Collection
    {
        return $this->tableColumns;
    }
}
