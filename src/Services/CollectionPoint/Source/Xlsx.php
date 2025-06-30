<?php

declare(strict_types=1);

namespace App\Services\CollectionPoint\Source;

use App\Services\CollectionPoint\Data\Data;
use App\Services\CollectionPoint\Data\DataInterface;
use App\Services\CollectionPoint\Data\Row;
use App\Services\CollectionPoint\Source\Helper\Formatter;

class Xlsx implements SourceInterface
{
    private $spreadsheet;

    public function __construct(string $filename)
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $this->spreadsheet = $reader->load($filename);
    }

    public function getData(): DataInterface
    {
        $data = new Data();

        $sheet = $this->spreadsheet->getSheet(0);

        $highestRow = $sheet->getHighestRow();
        for ($rowNumber = 2; $rowNumber <= $highestRow; ++$rowNumber) {
            $row = new Row();
            $formatter = new Formatter($sheet);

            $row
                ->setSymbol($formatter->getString(1, $rowNumber))
                ->setCollectionPointClassification($formatter->getString(3, $rowNumber))
                ->setCoordinator($formatter->getString(6, $rowNumber))
                ->setStreet($formatter->getString(7, $rowNumber))
                ->setCity($formatter->getString(8, $rowNumber))
                ->setPostalCode($formatter->getString(9, $rowNumber))
                ->setPhone($formatter->getString(10, $rowNumber))
                ->setLaboratory($formatter->getString(11, $rowNumber))
                ->setIsInternet($formatter->getBool(12, $rowNumber))
                ->setModel($formatter->getString(13, $rowNumber))
                ->setCollectionPointLocation($formatter->getString(16, $rowNumber))
                ->setCollectionPointPartner($formatter->getString(19, $rowNumber))
                ->setIsChildren($formatter->getBool(22, $rowNumber))
                ->setIsGynecology($formatter->getBool(23, $rowNumber))
                ->setIsDermatofit($formatter->getBool(24, $rowNumber))
                ->setIsSwab($formatter->getBool(25, $rowNumber))
                ->setOpenSunday($formatter->getString(26, $rowNumber))
                ->setOpenMonday($formatter->getString(27, $rowNumber))
                ->setOpenTuesday($formatter->getString(28, $rowNumber))
                ->setOpenWednesday($formatter->getString(29, $rowNumber))
                ->setOpenThursday($formatter->getString(30, $rowNumber))
                ->setOpenFriday($formatter->getString(31, $rowNumber))
                ->setOpenSaturday($formatter->getString(32, $rowNumber))
                ->setAdditionalInfo($formatter->getString(35, $rowNumber))
                ->setCollectionPointType($formatter->getString(36, $rowNumber))
                ->setEmail($formatter->getString(37, $rowNumber))
                ->setPriceList($formatter->getString(42, $rowNumber))
                ->setMpk(sprintf('%06d', (int)$formatter->getString(2, $rowNumber)));

            $data->addRow($row);
        }

        return $data;
    }
}
