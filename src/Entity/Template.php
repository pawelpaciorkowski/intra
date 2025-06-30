<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "templates")]
#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogDbTracking::class)]
class Template
{
    use TimestampableEntity;

    #[ORM\ManyToOne(inversedBy: "templates")]
    #[ORM\JoinColumn]
    #[Gedmo\Versioned]
    private ?TemplateTag $templateTag = null;

    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    #[Assert\Email]
    #[Gedmo\Versioned]
    private ?string $senderAddress = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    #[Gedmo\Versioned]
    private ?string $senderName = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(max: 500)]
    #[Gedmo\Versioned]
    private ?string $recipient = null;

    #[ORM\Column(name: "attachment", type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $attachment;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private bool $isActive;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 500)]
    #[Gedmo\Versioned]
    private ?string $subject = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $body = null;

    public function __construct()
    {
        $this->isActive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderAddress(): ?string
    {
        return $this->senderAddress;
    }

    public function setSenderAddress(?string $senderAddress): self
    {
        $this->senderAddress = $senderAddress;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getTemplateTag(): ?TemplateTag
    {
        return $this->templateTag;
    }

    public function setTemplateTag(?TemplateTag $templateTag): self
    {
        $this->templateTag = $templateTag;

        return $this;
    }
}
