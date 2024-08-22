<?php

namespace App\Controllers;

use App\Services\AlbumService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\FileDownloadService; 
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AlbumController extends AbstractController
{
    private AlbumService $albumService;
    private FileDownloadService $fileDownloadService;
    private $tokenStorage;

    public function __construct(AlbumService $albumService, FileDownloadService $fileDownloadService, TokenStorageInterface $tokenStorage)
    {
        $this->albumService = $albumService;
        $this->fileDownloadService = $fileDownloadService;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('api/album/create', name: 'create_album', methods: ['POST'])]
    public function createAlbum(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to add an album'], JsonResponse::HTTP_FORBIDDEN);
        }

        $datas = json_decode($request->get('album_data'), true);
        $title = $datas['title'];
        // get all the pictures sent in "pictures" key with form data postman
        $pictures = $request->files->get('pictures');
        if (!$title) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }
        if (!$pictures) {
            try {
                $album = $this->albumService->createAlbumWithoutPictures($title);
                if ($album == null) {
                    return new JsonResponse(['message' => 'Album created Successfully'], 200);
                } else {
                    return new JsonResponse(['error' => 'An error occured'], 500);
                }
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage()], 500);
            }
        }
        try {
            $album = $this->albumService->createAlbumWithPictures($title, $pictures);
            if ($album == null) {
                return new JsonResponse(['message' => 'Album created Successfully'], 200);
            } else {
                return new JsonResponse(['error' => 'An error occured'], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
        return new JsonResponse(['message' => 'Album created Successfully'], 200);
    }

    #[Route('api/album/getAllAlbums', name: 'get_albums', methods: ['GET'])]
    public function getAllAlbums(): JsonResponse
    {
        try {
            $albums = $this->albumService->getAllAlbums();
            if ($albums == null) {
                return new JsonResponse(['error' => 'An error occured'], 500);
            }
            $albumsData = [];
            foreach ($albums as $album) {
                $albumsData[] = [
                    'id' => $album->getId(),
                    'title' => $album->getTitle(),
                    'pictures' => $album->getAllPicturesData($album->getPictures()),
                    'createdAt' => $album->getCreatedAt(),
                ];
            }
            return new JsonResponse($albumsData, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('api/album/addPictureToAlbum', name: 'add_picture_to_album', methods: ['POST'])]
    public function addPictureToAlbum(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to add an article'], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->get('album'), true);
        $albumId = $data['album_id'];
        $pictures = $request->files->get('picture');
        if (!$albumId || !$pictures) {
            return new JsonResponse(['error' => 'Invalid data in body'], 500);
        }
        try {
            $album = $this->albumService->addPictureToAlbum($albumId, $pictures);
            if ($album == null) {
                return new JsonResponse(['message' => 'Picture added to album Successfully'], 200);
            }
            return new JsonResponse(['error' => 'An error occured'], 500);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('api/album/removePictureFromAlbum', name: 'remove_picture_from_album', methods: ['DELETE'])]
    public function deletePictureFromAlbum(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to remove a picture from an album'], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $albumId = $data['album_id'];
        $pictureId = $data['picture_id'];
        if (!$albumId || !$pictureId) {
            return new JsonResponse(['error' => 'Invalid data in body'], 500);
        }
        try {
            $album = $this->albumService->deletePictureFromAlbum($albumId, $pictureId);
            if ($album == null) {
                return new JsonResponse(['message' => 'Picture deleted from album Successfully'], 200);
            }
            return new JsonResponse(['error' => 'An error occured'], 500);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('api/album/removeAlbum', name: 'remove_album', methods: ['DELETE'])]
    public function deleteAlbum(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to remove an album'], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $albumId = $data['album_id'];
        if (!$albumId) {
            return new JsonResponse(['error' => 'Invalid data in body'], 500);
        }
        try {
            $album = $this->albumService->deleteAlbum($albumId);
            if ($album == null) {
                return new JsonResponse(['message' => 'Album deleted Successfully'], 200);
            }
            return new JsonResponse(['error' => 'An error occured'], 500);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('api/uploads/pictures/{fileName}', name: 'download_picture', methods: ['GET'])]
    public function downloadPicture(string $fileName) : BinaryFileResponse | JsonResponse
    {
        return $this->fileDownloadService->downloadPicture($fileName); 
    }
   
}
