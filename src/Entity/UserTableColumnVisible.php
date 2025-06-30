<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserTableColumnVisibleRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: "user_table_column_visibles")]
#[ORM\Entity(repositoryClass: UserTableColumnVisibleRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
class UserTableColumnVisible
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
    private bool $isVisible;

    public function __construct()
    {
        $this->isVisible = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible($isVisible): self
    {
        $this->isVisible = $isVisible;

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
