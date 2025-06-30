<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserTableColumnOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: "user_table_column_orders")]
#[ORM\Entity(repositoryClass: UserTableColumnOrderRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
class UserTableColumnOrder
{
    use TimestampableEntity;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?TableColumn $tableColumn = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    private bool $isDirection;

    public function __construct()
    {
        $this->isDirection = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsDirection(): bool
    {
        return $this->isDirection;
    }

    public function setIsDirection($isDirection): self
    {
        $this->isDirection = $isDirection;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTableColumn(): ?TableColumn
    {
        return $this->tableColumn;
    }

    public function setTableColumn(?TableColumn $tableColumn = null): self
    {
        $this->tableColumn = $tableColumn;

        return $this;
    }
}
