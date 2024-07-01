<?php

namespace App\Controllers;

use App\Services\BookService;
use App\Services\FileDownloadService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\FileUploader;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class BookController extends AbstractController
{

    #[Route('/api/book/upload', name: 'app_book_upload', methods: ['POST'])]
    public function upload(Request $request, FileUploader $fileUploader, BookService $bookService): JsonResponse
    {
        $data = json_decode($request->get('book_info'), true);


        //TODO : VOIR QUELS CHAMPS SERONT TJRS REQUIS
        $requiredFields = ['title', 'author', 'isbn', 'publication_year', 'edition', 'translator', 'description', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['status' => 'error', 'message' => 'All fields are required'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
        $uploadedFileCover = $request->files->get('pdf_file_cover');
        if (!$uploadedFileCover) {
            return new JsonResponse(['message' => 'Aucun fichier image de couverture trouvé dans la requête'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $uploadedFile = $request->files->get('pdf_file');
        if (!$uploadedFile) {
            return new JsonResponse(['message' => 'Aucun fichier pdf trouvé dans la requête'], JsonResponse::HTTP_BAD_REQUEST);
        }
        try {
            $fileName = $fileUploader->saveFile($uploadedFile, 'pdf');
            $coverFileName = $fileUploader->saveFile($uploadedFileCover, 'image');
            $result = $bookService->addBook($data, $fileName, $coverFileName);
            if (!$result) {
                return new JsonResponse(['message' => 'Book added successfully'], JsonResponse::HTTP_CREATED);
            }

            return new JsonResponse($result, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('api/uploads/pdf/{fileName}', name: 'app_book_download', methods: ['GET'])]
    public function download(string $fileName, Request $request, FileDownloadService $fileDownloadService): BinaryFileResponse | JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['operation'])) {
            return new JsonResponse(['message' => 'Operation is required'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        if ($data['operation'] === 'view' || $data['operation'] === 'download') {
            return $fileDownloadService->downloadBook($fileName, $data['operation']);
        }
    
        return new JsonResponse(['message' => 'Operation is not valid'], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('api/uploads/cover/{coverName}', name: 'app_book_cover_download', methods: ['GET'])]
    public function downloadCover(string $coverName, FileDownloadService $fileDownloadService): BinaryFileResponse | JsonResponse
    {
        return $fileDownloadService->downloadBookCover($coverName);
    }
  

    #[Route('/api/books/getAllFiles', name: 'app_book_retriview', methods: ['GET'])]
    public function getAllFiles(BookService $bookService): JsonResponse
    {
        try {
            $books = $bookService->getAllFiles();
            if(empty($books)){
                return $this->json([], JsonResponse::HTTP_OK);
            }
            $booksData = [];
            foreach($books as $book){
                $booksData[] = [
                    'id' => $book->getId(),
                    'title' => $book->getTitle(),
                    'author' => $book->getAuthor(),
                    'isbn' => $book->getIsbn(),
                    'publication_year' => (string)$book->getYear(),
                    'edition' => $book->getEdition(),
                    'translator' => $book->getTranslator(),
                    'description' => $book->getDescription(),
                    'cover' => $book->getCover(),
                    'downloads' => $book->getDownloads(),
                    'views' => $book->getViews(),
                    'category' => $book->getCategory(),
                    'link' => $book->getLink()
                ];
            }
            return $this->json($booksData, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de la récupération des fichiers'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/uploads/deleteFile', name: 'app_book_delete', methods: ['DELETE'])]
    public function delete(Request $request, FileDownloadService $fileDownloadService): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['fileId'])) {
            return new JsonResponse(['message' => 'File ID is required'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $fileId = $data['fileId'];
        try {
            $fileDownloadService->deleteBook($fileId);
            return new JsonResponse(['message' => 'File deleted successfully'], JsonResponse::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting file'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
}