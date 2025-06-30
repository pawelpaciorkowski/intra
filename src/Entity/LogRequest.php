<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LogRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: "log_requests")]
#[ORM\Entity(repositoryClass: LogRequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class LogRequest
{
    use TimestampableEntity;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(length: 128)]
    private ?string $method = null;

    #[ORM\Column(length: 1024)]
    private ?string $url = null;

    #[ORM\Column(type: 'blob', nullable: true)]
    private ?string $content = null;

    #[Column(type: 'blob', nullable: true)]
    private ?string $request = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?User $user = null;

    #[ManyToOne]
    #[JoinColumn(onDelete: 'CASCADE')]
    private ?Link $link = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(?string $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function setLink(?Link $link): self
    {
        $this->link = $link;

        return $this;
    }
}
