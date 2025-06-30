<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\RichText;

class PPTX implements ParserInterface
{
    public function parse(string $file): string
    {
        $reader = IOFactory::createReader('PowerPoint2007'); // Obsługuje PPTX
        $presentation = $reader->load($file);

        $return = [];

        foreach ($presentation->getAllSlides() as $slide) {
            foreach ($slide->getShapeCollection() as $shape) {
                if ($shape instanceof RichText) {
                    foreach ($shape->getParagraphs() as $paragraf) {
                        foreach ($paragraf->getRichTextElements() as $element) {
                            $return[] = $element->getText();
                        }
                    }
                }
            }
        }

        return implode(' ', $return);
    }
}
