<?php

declare(strict_types=1);

namespace App\Services\Parser\Parsers;

use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;

class DOCX implements ParserInterface
{
    public function parse(string $file): string
    {
        $phpWord = IOFactory::load($file);
        $sections = $phpWord->getSections();
        $content = [];

        foreach ($sections as $value) {
            $sectionElements = $value->getElements();
            foreach ($sectionElements as $elementValue) {
                if ($elementValue instanceof TextRun) {
                    $content = array_merge($content, $this->getRegularText($elementValue));
                }
                if ($elementValue instanceof Table) {
                    $content = array_merge($content, $this->getTableText($elementValue));
                }
            }
        }

        return implode(' ', $content);
    }

    protected function getRegularText(TextRun $element): array
    {
        $content = [];

        foreach ($element->getElements() as $elementValue) {
            if ($elementValue instanceof Text) {
                $content[] = $elementValue->getText();
            }
        }

        return $content;
    }

    protected function getTableText(Table $element): array
    {
        $content = [];

        foreach ($element->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                $cellElements = $cell->getElements();
                foreach ($cellElements as $cellElement) {
                    if ($cellElement instanceof TextRun) {
                        $content = array_merge($content, $this->getRegularText($cellElement));
                    }
                }
            }
        }

        return $content;
    }
}
