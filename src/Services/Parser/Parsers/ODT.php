<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

use DOMDocument;
use RuntimeException;
use ZipArchive;

class ODT implements ParserInterface
{
    public function parse(string $file): string
    {
        $return = [];

        $zip = new ZipArchive();
        if ($zip->open($file) === true) {
            $content = $zip->getFromName('content.xml');
            $zip->close();

            if ($content === false) {
                throw new RuntimeException("Cant read content.xml");
            }

            $xml = new DOMDocument();
            $xml->loadXML($content);

            foreach (['p', 'h'] as $key) {
                foreach ($xml->getElementsByTagName($key) as $element) {
                    $return[] = $element->textContent;
                }
            }
        }

        return implode(' ', $return);
    }
}
