<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Services\Export\Generator\GeneratorInterface;
use App\Services\Export\Map\MapInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportService
{
    private $map;
    private $generator;
    private $data;
    private $locale;

    public function setGenerator(GeneratorInterface $generator): self
    {
        $this->generator = $generator;

        return $this;
    }

    public function export(string $filename): StreamedResponse
    {
        if ($this->locale) {
            $this->generator->setLocale($this->locale);
        }

        return $this
            ->generator
            ->setData($this->data)
            ->setMap($this->map)
            ->generate($filename);
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function setMap(MapInterface $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
