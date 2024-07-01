<?php


namespace App\Services;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;



class ArticleService
{

    private $doctrine;
    private $articleRepository;
    private $entityManager;
    private $targetDirectoryArticleCover;
    private $tokenStorage;

    public function __construct(ManagerRegistry $doctrine,TokenStorageInterface $tokenStorage, string $targetDirectoryArticleCover, EntityManagerInterface $entityManager, ArticleRepository $articleRepository)
    {
        $this->doctrine = $doctrine;
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
        $this->targetDirectoryArticleCover = $targetDirectoryArticleCover;
        $this->tokenStorage = $tokenStorage;
    }


    public function addArticle($data, $filename)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to add an article'], JsonResponse::HTTP_FORBIDDEN);
        }

        $entityManager = $this->entityManager;
        $article = new Article();

        // Définir les propriétés de l'article à partir des données reçues
        $article->setTextFr($data['textFr']);
        $article->setTextAr($data['textAr']);
        $article->setTextAm($data['textAm']);
        $publicationYear = \DateTimeImmutable::createFromFormat('Y-m-d', $data['createdAt']);
        $article->setCreatedAt($publicationYear);
        $article->setTitleFr($data['titleFr']);
        $article->setTitleAr($data['titleAr']);
        $article->setTitleAm($data['titleAm']);
        $articleCover = $_ENV['API_URL'] . '/api/uploads/articleCover/' . $filename;
        $article->setCover($articleCover);

        $entityManager->persist($article);
        try {
            $entityManager->flush();
            return null; // Succès: retourne null pour indiquer aucune erreur
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de l\'enregistrement de l\'article: ' . $e->getMessage()];
        }
    }

    public function getAllArticles()
    {
        try {
            $articles = $this->articleRepository->findAll();
            return $articles;
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la récupération des articles'];
        }
    }

    public function deleteArticle(int $id): bool
    {

        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if ($user->getRoles()[0] !== 'ROLE_ADMIN') {
            return new JsonResponse(['message' => 'You are not authorized to delete this article'], JsonResponse::HTTP_FORBIDDEN);
        }

        $article = $this->articleRepository->find($id);

        if (!$article) {
            return false; // Article non trouvé
        }

        $articleCover = $article->getCover();

        try {
            if ($articleCover) {
                $this->deleteArticleCover(basename($articleCover));
            }
            $this->entityManager->remove($article);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la suppression de l\'article: ' . $e->getMessage());
        }
    }


    public function deleteArticleCover(string $coverName): void
    {
        $filePath = $this->getTargetDirectoryArticleCover() . $coverName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getTargetDirectoryArticleCover(): string
    {
        return $this->targetDirectoryArticleCover;
    }
}
