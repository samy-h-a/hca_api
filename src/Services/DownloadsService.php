<?php


namespace App\Services;

use App\Entity\Downloads;
use App\Repository\DownloadsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class DownloadsService
{

    private $doctrine;
    private $downloadsRepository;
    private $tokenStorage;

    public function __construct(ManagerRegistry $doctrine, DownloadsRepository $downloadsRepository, TokenStorageInterface $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->downloadsRepository = $downloadsRepository;
        $this->tokenStorage = $tokenStorage;
    }


    public function resetAllUsersDownloadLimit()
    {
        $entityManager = $this->doctrine->getManager();
        $downloads = $this->downloadsRepository->findAll();
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to perform this action'], JsonResponse::HTTP_FORBIDDEN);
        }
        foreach ($downloads as $download) {
            $download->setCount(0);
            $entityManager->persist($download);
        }
        try {
            $entityManager->flush();
            return new JsonResponse(['message' => 'All users download limit has been reset']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while resetting download limit: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}