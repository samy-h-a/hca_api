<?php


namespace App\Services;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class VideoService
{

    private $doctrine;
    private $videoRepository;
    private $entityManager;
    private $targetDirectoryVideoCover;
    private $tokenStorage;

    public function __construct(ManagerRegistry $doctrine,TokenStorageInterface $tokenStorage, string $targetDirectoryVideoCover, EntityManagerInterface $entityManager, VideoRepository $videoRepository)
    {
        $this->doctrine = $doctrine;
        $this->videoRepository = $videoRepository;
        $this->entityManager = $entityManager;
        $this->targetDirectoryVideoCover = $targetDirectoryVideoCover;
        $this->tokenStorage = $tokenStorage;
    }

    public function addVideo($data, $videoCoverName)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to add an video'], JsonResponse::HTTP_FORBIDDEN);
        }
        $entityManager = $this->entityManager;
        $video = new Video();
        $video->setTitle($data['title']);
        $publicationYear = \DateTimeImmutable::createFromFormat('Y-m-d', $data['createdAt']);
        $video->setCreatedAt($publicationYear);
        $videoCover = $_ENV['API_URL'] . '/api/uploads/videoCover/' . $videoCoverName;
        $video->setCover($videoCover);
        $video->setLink($data['link']);
        $entityManager->persist($video);
        try {
            $entityManager->flush();
            return null; // Succès: retourne null pour indiquer aucune erreur
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de l\'enregistrement de la video: ' . $e->getMessage()];
        }
    }

    
    public function getAllVideos()
    {
        try {
            $videos = $this->videoRepository->findAll();
            return $videos;
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la récupération des videos'];
        }

    }

    public function deleteVideo(int $id): bool
    {

        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to delete this video'], JsonResponse::HTTP_FORBIDDEN);
        }

        $video = $this->videoRepository->find($id);

        if (!$video) {
            return false; // Video non trouvé
        }

        $videoCover = $video->getCover();

        try {
            if ($videoCover) {
                $this->deleteVideoCover(basename($videoCover));
            }
            $this->entityManager->remove($video);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la suppression de la video: ' . $e->getMessage());
        }
    }


    public function deleteVideoCover(string $coverName): void
    {
        $filePath = $this->getTargetDirectoryVideoCover() . $coverName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getTargetDirectoryVideoCover(): string
    {
        return $this->targetDirectoryVideoCover;
    }


}