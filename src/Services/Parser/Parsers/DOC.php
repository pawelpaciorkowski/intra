<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\IOFactory;

class DOC implements ParserInterface
{
    public function parse(string $file): string
    {
        $phpWord = IOFactory::load($file, 'MsDoc');
        $sections = $phpWord->getSections();
        $return = [];

        foreach ($sections as $value) {
            $sectionElements = $value->getElements();

            foreach ($sectionElements as $elementValue) {
                if ($elementValue instanceof Text) {
                    $text = $this->getEncodingText($elementValue->getText());
                    $return[] = $text;
                }
            }
        }

        return implode(' ', $return);
    }

    protected function isASCII(string $text): bool
    {
        return mb_detect_encoding($text, mb_detect_order(), true) === 'ASCII';
    }

    protected function getEncodingText(string $text): false | string
    {
        if (!$text) {
            return false;
        }

        if ($this->isASCII($text)) {
            return $text;
        }

        $detectedEncoding = mb_detect_encoding($text, mb_detect_order(), true);
        //        return iconv('Windows-1251', 'UTF-8', iconv('UTF-16', 'CP1251', $text));

        //        return iconv(iconv_get_encoding($text), 'UTF-8', $text);
        return iconv($detectedEncoding, 'UTF-8', $text);
    }
}
