<?php

namespace App\Controllers;

use App\Services\ApplicationService;
use App\Services\FileDownloadService;
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\FileUploader;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ApplicationController extends AbstractController
{
    #[Route('/api/applications/upload', name: 'app_application_upload', methods: ['POST'])]
    public function upload(Request $request, FileUploader $fileUploader, ApplicationService $applicationService): JsonResponse
    {
        $data = json_decode($request->get('app_data'), true);
        if ($data === null) {
            return new JsonResponse(['message' => 'Invalid or missing application data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $uploadedFileCover = $request->files->get('app_cover');
        if (!$uploadedFileCover) {
            return new JsonResponse(['message' => 'Aucun fichier image de couverture trouvé dans la requête'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $uploadedFile = $request->files->get('app_file');
        if (!$uploadedFile) {
            return new JsonResponse(['message' => 'Aucun fichier trouvé dans la requête'], JsonResponse::HTTP_BAD_REQUEST);
        }
        try {
            $fileName = $fileUploader->saveFile($uploadedFile, 'app_file');
            $coverFileName = $fileUploader->saveFile($uploadedFileCover, 'app_cover');
            $result = $applicationService->addApplication($data, $fileName, $coverFileName);
            if (!$result) {
                return new JsonResponse(['message' => 'Application added successfully'], JsonResponse::HTTP_CREATED);
            }

            return new JsonResponse($result, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/applications/getAllApplications', name: 'app_application_get', methods: ['GET'])]
    public function getAllApplications(ApplicationService $applicationService): JsonResponse
    {
        try {
            $applications = $applicationService->getAllApplications();
            $applicationsData = [];
            foreach ($applications as $application) {

                $applicationsData[] = [
                    'id' => $application->getId(),
                    'title' => $application->getTitle(),
                    'createdAt' => $application->getCreatedAt()->format('Y-m-d'),
                    'cover' => $application->getCover(),
                    'link' => $application->getLink()
                ];
            }
       
            return $this->json($applicationsData, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de la récupération des applications: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('api/uploads/applications/covers/{coverName}', name: 'app_application_cover_download', methods: ['GET'])]
    public function downloadApplicationCover(string $coverName, FileDownloadService $fileDownloadService): BinaryFileResponse | JsonResponse
    {
        return $fileDownloadService->downloadApplicationCover($coverName);
    }

    #[Route('api/uploads/applications/files/{fileName}', name: 'app_application_file_download', methods: ['GET'])]
    public function downloadApplicationFile(string $fileName, FileDownloadService $fileDownloadService): BinaryFileResponse | JsonResponse
    {
        return $fileDownloadService->downloadApplicationFile($fileName);
    }

    #[Route('/api/applications/deleteApplication', name: 'app_application_delete', methods: ['DELETE'])]
    public function deleteApp(Request $request, FileDownloadService $fileDownloadService): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['appId'])) {
            return new JsonResponse(['message' => 'File ID is required'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $appId = $data['appId'];
        try {
            $fileDownloadService->deleteApplication($appId);
            return new JsonResponse(['message' => 'Application deleted successfully'], JsonResponse::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting Application'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

} 
