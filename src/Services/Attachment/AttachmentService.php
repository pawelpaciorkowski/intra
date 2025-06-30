<?php

declare(strict_types=1);

namespace App\Services\Attachment;

use JsonException;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function basename;
use function call_user_func_array;
use function json_decode;
use function property_exists;

final class AttachmentService
{
    private Attachments $attachments;
    private array $contextData;

    public function __construct(private readonly ContainerInterface $container)
    {
        $this->attachments = new Attachments();
    }

    /**
     * @throws JsonException
     */
    public function parse(string $string): self
    {
        $this->attachments->setAttachments([]);

        $json = json_decode($string, null, 512, JSON_THROW_ON_ERROR);

        if (property_exists($json, 'files')) {
            $this->parseFiles($json->files);
        }

        if (property_exists($json, 'services')) {
            $this->parseServices($json->services);
        }

        if (property_exists($json, 'attachments')) {
            $this->parseAttachments($json->attachments);
        }

        return $this;
    }

    private function parseFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->attachments->pushAttachment(
                Attachment::create(
                    file_get_contents(__DIR__ . '/../../../attachments/' . basename($file)),
                    $file
                )
            );
        }
    }

    private function parseServices(array $services): void
    {
        foreach ($services as $service) {
            $this->attachments->pushAttachment(
                Attachment::create(
                    call_user_func_array(
                        [$this->container->get($service->service), $service->method],
                        ParameterParser::parse($service->parameters, $this->contextData)
                    ),
                    $service->filename,
                    $service->contentType
                )
            );
        }
    }

    private function parseAttachments(StdClass $attachments): void
    {
        $attachmentFiles = call_user_func_array(
            [$this->container->get($attachments->service), $attachments->method],
            ParameterParser::parse($attachments->parameters, $this->contextData)
        );

        foreach ($attachmentFiles as $attachmentFile) {
            $this->attachments->pushAttachment(
                Attachment::create(
                    file_get_contents($attachmentFile->getAbsolutePath()),
                    $attachmentFile->getOriginalFilename()
                )
            );
        }
    }

    public function getAttachments(): Attachments
    {
        return $this->attachments;
    }

    public function setContextData($contextData): self
    {
        $this->contextData = $contextData;

        return $this;
    }
}
