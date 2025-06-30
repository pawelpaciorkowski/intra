<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Helper;

class PeselValidator
{
    public static function validatePesel(string $pesel): bool
    {
        if (!preg_match('/^\d{11}$/', $pesel)) {
            return false;
        }

        $arrSteps = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
        $intSum = 0;
        for ($i = 0; $i < 10; ++$i) {
            $intSum += $arrSteps[$i] * $pesel[$i];
        }
        $int = 10 - $intSum % 10;
        $intControlNr = 10 === $int ? 0 : $int;

        return $intControlNr === (int)$pesel[10];
    }
}
