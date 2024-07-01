<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileUploader
{
    private $targetDirectoryPdfFile;
    private $targetDirectoryPdfCover;
    private $targetDirectoryArticleCover;
    private $targetDirectoryVideoCover;



    private $doctrine;
    private $tokenStorage;
    private $client;

    public function __construct(string $targetDirectoryPdfFile, string $targetDirectoryVideoCover,  string $targetDirectoryArticleCover, string $targetDirectoryPdfCover, PersistenceManagerRegistry $doctrine, TokenStorageInterface $tokenStorage, HttpClientInterface $client)
    {
        $this->targetDirectoryPdfFile = $targetDirectoryPdfFile;
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->client = $client;
        $this->targetDirectoryPdfCover = $targetDirectoryPdfCover;
        $this->targetDirectoryArticleCover = $targetDirectoryArticleCover;
        $this->targetDirectoryVideoCover = $targetDirectoryVideoCover;
    }

    public function saveFile(UploadedFile $file, string $fileType): string
    {

        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            throw new NotFoundHttpException('You are not authorized to upload this book');
        }

        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        try {
            if ($fileType === 'image') {
                $file->move($this->getTargetDirectoryPdfCover(), $fileName);
            } else if ($fileType === 'article_cover') {
                $file->move($this->getTargetDirectoryArticleCover(), $fileName);
            } else if ($fileType === 'pdf') {
                $file->move($this->getTargetDirectoryPdfFile(), $fileName);
            } else if ($fileType === 'video_cover') {
                $file->move($this->getTargetDirectoryVideoCover(), $fileName);
            }
        } catch (FileException $e) {
            throw new \Exception($e->getMessage());
        }
        return $fileName;
    }

    private function getTargetDirectoryPdfFile(): string
    {
        return $this->targetDirectoryPdfFile;
    }

    private function getTargetDirectoryPdfCover(): string
    {
        return $this->targetDirectoryPdfCover;
    }
    private function getTargetDirectoryArticleCover(): string
    {
        return $this->targetDirectoryArticleCover;
    }
    private function getTargetDirectoryVideoCover(): string
    {
        return $this->targetDirectoryVideoCover;
    }
}
