<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Exception;

class ImageUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile|string $file): string
    {
        $isPath = is_string($file);
        $mimeType = $isPath ? mime_content_type($file) : $file->getMimeType();

        if ($mimeType !== 'image/webp') {
             throw new Exception('Разрешены только WebP изображения!');
        }

        $originalFilename = $isPath ? pathinfo($file, PATHINFO_FILENAME) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.' . ($isPath ? 'webp' : $file->guessExtension());

        try {
            if ($isPath) {
                copy($file, $this->targetDirectory . '/' . $fileName);
            } else {
                $file->move($this->targetDirectory, $fileName);
            }
        } catch (FileException $e) {
            throw new Exception('Ошибка при загрузке файла');
        }

        return $fileName;
    }
}
