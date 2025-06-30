<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Psajdak
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 256)]
    private ?string $type = null;

    #[Assert\Expression("this.getType() != 'email' or this.getEmail()", message: "Musi być wypełnione dla wskazanego typu wiadomości")]
    #[Assert\Length(max: 256)]
    private ?string $fromEmail = null;

    #[Assert\Length(max: 256)]
    private ?string $fromName = null;

    #[Assert\Expression("this.getType() != 'email' or this.getEmail()", message: "Musi być wypełnione dla wybranego typu wiadomości")]
    #[Assert\Length(max: 256)]
    private ?string $email = null;

    #[Assert\Expression("this.getType() != 'sms' or this.getPhone()", message: "Musi być wypełnione dla wybranego typu wiadomości")]
    #[Assert\Length(max: 256)]
    private ?string $phone = null;

    #[Assert\Expression("this.getType() != 'email' or this.getSubject()", message: "Musi być wypełnione dla wybranego typu wiadomości")]
    #[Assert\Length(max: 256)]
    private ?string $subject = null;

    private ?string $htmlbody = null;

    #[Assert\Expression("(this.getType() == 'sms' and this.getTextBody()) or (this.getType() == 'email' and (this.getHtmlBody() or this.getTextBody()))", message: "Musi być wypełnione dla wybranego typu wiadomości")]
    private ?string $textBody = null;

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getHtmlbody(): ?string
    {
        return $this->htmlbody;
    }

    public function setHtmlbody(?string $htmlbody): void
    {
        $this->htmlbody = $htmlbody;
    }

    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    public function setTextBody(?string $textBody): void
    {
        $this->textBody = $textBody;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): void
    {
        $this->fromEmail = $fromEmail;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): void
    {
        $this->fromName = $fromName;
    }
}
