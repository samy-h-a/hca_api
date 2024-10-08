<?php

namespace App\Services;

use App\Entity\Downloads;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\BookRepository;
use App\Repository\ApplicationsRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FileDownloadService
{
    private $doctrine;
    private $bookRepository;
    private $applicationsRepository;
    private $targetDirectoryPdfFile;
    private $targetDirectoryPdfCover;
    private $targetDirectoryArticleCover;
    private $targetDirectoryVideoCover;
    private $targetDirectoryApplicationCover;
    private $targetDirectoryApplicationFile;
    private $targetDirectoryAlbumPicture;

    private $tokenStorage;
    public const MAX_DOWNLOADS = 3;

    public function __construct(string $targetDirectoryAlbumPicture, string $targetDirectoryVideoCover,string $targetDirectoryApplicationCover, string $targetDirectoryApplicationFile, string $targetDirectoryPdfFile, string $targetDirectoryArticleCover, string $targetDirectoryPdfCover, TokenStorageInterface $tokenStorage, ManagerRegistry $doctrine, BookRepository $bookRepository, ApplicationsRepository $applicationsRepository)
    {
        $this->doctrine = $doctrine;
        $this->bookRepository = $bookRepository;
        $this->applicationsRepository = $applicationsRepository;
        $this->targetDirectoryPdfFile = $targetDirectoryPdfFile;
        $this->targetDirectoryPdfCover = $targetDirectoryPdfCover;
        $this->targetDirectoryArticleCover = $targetDirectoryArticleCover;
        $this->tokenStorage = $tokenStorage;
        $this->targetDirectoryVideoCover = $targetDirectoryVideoCover;
        $this->targetDirectoryApplicationCover = $targetDirectoryApplicationCover;
        $this->targetDirectoryApplicationFile = $targetDirectoryApplicationFile;
        $this->targetDirectoryAlbumPicture = $targetDirectoryAlbumPicture;
    }

    public function downloadBook(string $fileName, string $operation): BinaryFileResponse | JsonResponse
    {
        $entityManager = $this->doctrine->getManager();
        // $token = $this->tokenStorage->getToken();
        // $user = $token->getUser();
    
        // // Check if user is not an admin
        // if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
        //     $downloads = $entityManager->getRepository(Downloads::class)->findOneBy(['user' => $user]);
        //     $downloadNumber = $downloads->getCount();
    
        //     if ($downloadNumber >= self::MAX_DOWNLOADS) {
        //         return new JsonResponse(['message' => 'You have reached the maximum number of downloads'], JsonResponse::HTTP_FORBIDDEN);
        //     }
        // }
    
        $filePath = $this->getTargetDirectoryPdfFile() . $fileName;
    
        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        $file = new File($filePath);
        if (!$file->isFile()) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());
    
        // if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
        //     $this->incrementDownloadCount($user);
        // }
    
        if ($operation === 'download') {
            $this->incrementBookDownloadCount($fileName);
        } else {
            $this->incrementBookViewCount($fileName);
        }
    
        $entityManager->flush();
    
        return $response;
    }
    
    public function downloadVideoCover(string $coverName): BinaryFileResponse | JsonResponse
    {
        $filePath = $this->getTargetDirectoryVideoCover() . $coverName;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

        return $response;
    }

    
    public function downloadApplicationCover(string $coverName): BinaryFileResponse | JsonResponse
    {
        $filePath = $this->getTargetDirectoryApplicationCover() . $coverName;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());
        return $response;

    }

    public function downloadApplicationFile(string $fileName):  BinaryFileResponse | JsonResponse
    {
        $filePath = $this->getTargetDirectoryApplicationFile() . $fileName;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

        return $response;
    }
    

    public function downloadBookCover(string $coverName): BinaryFileResponse | JsonResponse
    {
        $filePath = $this->getTargetDirectoryPdfCover() . $coverName;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

        return $response;
    }

    public function downloadArticleCover(string $coverName): BinaryFileResponse | JsonResponse
    {
        $filePath = $this->getTargetDirectoryArticleCover() . $coverName;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

        return $response;
    }

    private function incrementDownloadCount($user)
    {
        $entityManager = $this->doctrine->getManager();
        $downloads = $entityManager->getRepository(Downloads::class)->findOneBy(['user' => $user]);
        $downloadNumber = $downloads->getCount();
        $downloads->setCount($downloadNumber + 1);
        $entityManager->flush();
    }

    private function incrementBookDownloadCount($fileName)
    {
        $entityManager = $this->doctrine->getManager();
        $book = $this->bookRepository->findOneBy(['link' => $_ENV['API_URL'] . '/api/uploads/pdf/' . $fileName]);
        $book->setDownloads($book->getDownloads() + 1);
        $entityManager->flush();
    }

    private function incrementBookViewCount($fileName)
    {
        $entityManager = $this->doctrine->getManager();
        $book = $this->bookRepository->findOneBy(['link' => $_ENV['API_URL'] . '/api/uploads/pdf/' . $fileName]);
        $book->setViews($book->getViews() + 1);
        $entityManager->flush();
    }


    public function deleteApplication(string $fileId)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to delete applications'], JsonResponse::HTTP_FORBIDDEN);
        }
        $application = $this->applicationsRepository->find($fileId);
        if (!$application) { 
            throw new NotFoundHttpException('Application not found');
        }

        $fileLink = $application->getLink();

        $fileName = basename($fileLink);
        $filePath = $this->targetDirectoryApplicationFile . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
            $coverName = basename($application->getCover());
            $this->removeApplicationCover($coverName);
        } else {
            throw new NotFoundHttpException('File not found');
        }

        $this->removeApplicationFromDatabase($fileId);
    }

    private function removeApplicationCover(string $coverName): void
    {
        $coverPath = $this->targetDirectoryApplicationCover . '/' . $coverName;
        if (file_exists($coverPath)) {
            unlink($coverPath);
        }
    }

    private function removeApplicationFromDatabase(string $fileId): void
    {
        $entityManager = $this->doctrine->getManager();
        $application = $this->applicationsRepository->find($fileId);
        if (!$application) {
            throw new NotFoundHttpException('Application not found in database');
        }

        $entityManager->remove($application);
        $entityManager->flush();
    }

    public function deleteBook(string $fileId)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to delete books'], JsonResponse::HTTP_FORBIDDEN);
        }
        $book = $this->bookRepository->find($fileId);
        if (!$book) {
            throw new NotFoundHttpException('Book not found');
        }

        $fileLink = $book->getLink();

        $fileCoverLink = $book->getCover();
        $coverName = basename($fileCoverLink);
        $coverPath = $this->targetDirectoryPdfCover . '/' . $coverName;
        if (file_exists($coverPath)) {
            unlink($coverPath);
        }
        $fileName = basename($fileLink);
        $filePath = $this->targetDirectoryPdfFile . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        } else {
            throw new NotFoundHttpException('File not found');
        }

        $this->removeBookFromDatabase($fileId);
    }

    private function removeBookFromDatabase(string $fileId): void
    {
        $entityManager = $this->doctrine->getManager();
        $book = $this->bookRepository->find($fileId);
        if (!$book) {
            throw new NotFoundHttpException('Book not found in database');
        }

        $entityManager->remove($book);
        $entityManager->flush();
    }

    public function downloadPicture(string $fileName): BinaryFileResponse | JsonResponse
    {
        $filePath = $this->targetDirectoryAlbumPicture . $fileName;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $file = new File($filePath);
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

        return $response;
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
    private function getTargetDirectoryApplicationCover(): string
    {
        return $this->targetDirectoryApplicationCover;
    }
    private function getTargetDirectoryApplicationFile(): string
    {
        return $this->targetDirectoryApplicationFile;
    }
    private function getTargetDirectoryAlbumPicture():string 
    {
        return $this->targetDirectoryAlbumPicture;
    }
}
