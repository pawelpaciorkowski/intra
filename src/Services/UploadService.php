<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadService
{
    private const string PATH_IMG = '/upload/img/';

    public function uploadImage(UploadedFile $file): string
    {
        $extension = $file->guessExtension();
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            throw new RuntimeException('Invalid file extension');
        }

        $newFilename = uniqid('alab_', true) . '.' . $extension;

        $file->move('../public' . self::PATH_IMG, $newFilename);

        return self::PATH_IMG . $newFilename;
    }
}
