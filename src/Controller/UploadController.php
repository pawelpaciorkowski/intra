<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\UploadService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/upload")] final class UploadController extends AbstractController
{
    #[Route("/image", name: "upload-image", methods: ["POST"])]
    public function image(Request $request, UploadService $uploadService): Response
    {
        if (count($request->files) !== 1) {
            return $this->json(['error' => 'wrong number of files'], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return $this->json(['error' => 'not a file'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $filename = $uploadService->uploadImage($file);
        } catch (RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['location' => $filename], Response::HTTP_CREATED);
    }
}
