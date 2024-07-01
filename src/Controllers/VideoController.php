<?php

namespace App\Controllers;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\FileUploader;

use App\Services\VideoService;

use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Services\FileDownloadService;


use Symfony\Component\HttpFoundation\BinaryFileResponse;



class VideoController extends AbstractController
{

    #[Route('/api/video/addVideo', name: 'app_vide_add', methods: ['POST'])]
    public function createArticle(VideoService $videoService, FileUploader $fileUploader, Request $request): JsonResponse
    {
        $data = json_decode($request->get('video_data'), true);
        if ($data === null) {
            return new JsonResponse(['message' => 'Invalid or missing video data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if there is key title, createdAt, and link on $data
        if (!isset($data['title']) || !isset($data['createdAt']) || !isset($data['link'])) {
            return new JsonResponse(['message' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $uploadedFile = $request->files->get('video_cover');

        if (!$uploadedFile) {
            return new JsonResponse(['message' => 'No video cover file found in the request'], JsonResponse::HTTP_BAD_REQUEST);
        }
        try {
            $fileName = $fileUploader->saveFile($uploadedFile, 'video_cover');
            $result = $videoService->addVideo($data, $fileName);
            if (!$result) {
                return new JsonResponse(['message' => 'Video ajouté avec succès'], JsonResponse::HTTP_CREATED);
            } else {
                return new JsonResponse($result, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/video/getAllVideos', name: 'app_video_retrieve', methods: ['GET'])]
    public function getAllFiles(VideoService $videoService): JsonResponse
    {
        try {
            $videos = $videoService->getAllVideos();
            $videosData = [];
            
            foreach ($videos as $video) {
                // Conversion des champs texte en UTF-8
                $title = mb_convert_encoding($video->getTitle(), 'UTF-8', 'auto');
                $cover = mb_convert_encoding($video->getCover(), 'UTF-8', 'auto');
                $link = mb_convert_encoding($video->getLink(), 'UTF-8', 'auto');

                $videosData[] = [
                    'id' => $video->getId(),
                    'title' => $title,
                    'publication_year' => $video->getCreatedAt()->format('Y-m-d'),
                    'cover' => $cover,
                    'link' => $link
                ];
            }
            // Retourner les données sous forme de réponse JSON
            return $this->json($videosData, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            // Gérer les exceptions et retourner une réponse d'erreur en cas de problème
            return new JsonResponse(['message' => 'Erreur lors de la récupération des videos: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('api/uploads/videoCover/{coverName}', name: 'app_video_cover_download', methods: ['GET'])]
    public function downloadCover(string $coverName, FileDownloadService $fileDownloadService): BinaryFileResponse | JsonResponse
    {
        return $fileDownloadService->downloadVideoCover($coverName);
    }

    #[Route('/api/video/delete/{id}', name: 'app_video_delete', methods: ['DELETE'])]
    public function deleteArticleAction(VideoService $videoService, int $id): JsonResponse
    {
        try {
            $result = $videoService->deleteVideo($id);

            if ($result) {
                return new JsonResponse(['message' => 'Video supprimée avec succès'], JsonResponse::HTTP_OK);
            } else {
                return new JsonResponse(['message' => 'Video non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de la suppression de la video: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
