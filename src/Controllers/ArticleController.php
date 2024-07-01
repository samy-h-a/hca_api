<?php

namespace App\Controllers;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\FileUploader;
use App\Services\ArticleService;
use App\Services\FileDownloadService;


use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArticleController extends AbstractController
{

    #[Route('/api/article/addArticle', name: 'app_article_add', methods: ['POST'])]
    public function createArticle(ArticleService $articleService, FileUploader $fileUploader, Request $request): JsonResponse
    {
        $data = json_decode($request->get('article_data'), true);
        if ($data === null) {
            return new JsonResponse(['message' => 'Invalid or missing article data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $uploadedFile = $request->files->get('article_cover');

        if (!$uploadedFile) {
            return new JsonResponse(['message' => 'No article cover file found in the request'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $fileName = $fileUploader->saveFile($uploadedFile, 'article_cover');
            $result = $articleService->addArticle($data, $fileName);
            if (!$result) {
                return new JsonResponse(['message' => 'Article ajouté avec succès'], JsonResponse::HTTP_CREATED);
            } else {
                return new JsonResponse($result, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/article/getAllArticles', name: 'app_article_retrieve', methods: ['GET'])]
    public function getAllFiles(ArticleService $articleService): JsonResponse
    {
        try {
            $articles = $articleService->getAllArticles();
            $articlesData = [];

            foreach ($articles as $article) {
                // Conversion des champs texte en UTF-8
                $titleAr = mb_convert_encoding($article->getTitleAr(), 'UTF-8', 'auto');
                $titleFr = mb_convert_encoding($article->getTitleFr(), 'UTF-8', 'auto');
                $titleAm = mb_convert_encoding($article->getTitleAm(), 'UTF-8', 'auto');
                $textAr = mb_convert_encoding($article->getTextAr(), 'UTF-8', 'auto');
                $textFr = mb_convert_encoding($article->getTextFr(), 'UTF-8', 'auto');
                $textAm = mb_convert_encoding($article->getTextAm(), 'UTF-8', 'auto');
                $cover = mb_convert_encoding($article->getCover(), 'UTF-8', 'auto');

                $articlesData[] = [
                    'id' => $article->getId(),
                    'titleAr' => $titleAr,
                    'titleFr' => $titleFr,
                    'titleAm' => $titleAm,
                    'textAr' => $textAr,
                    'textFr' => $textFr,
                    'textAm' => $textAm,
                    'publication_year' => $article->getCreatedAt()->format('Y-m-d'),
                    'cover' => $cover
                ];
            }

            // Retourner les données sous forme de réponse JSON
            return $this->json($articlesData, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            // Gérer les exceptions et retourner une réponse d'erreur en cas de problème
            return new JsonResponse(['message' => 'Erreur lors de la récupération des articles: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/article/delete/{id}', name: 'app_article_delete', methods: ['DELETE'])]
    public function deleteArticleAction(ArticleService $articleService, int $id): JsonResponse
    {
        try {
            $result = $articleService->deleteArticle($id);

            if ($result) {
                return new JsonResponse(['message' => 'Article supprimé avec succès'], JsonResponse::HTTP_OK);
            } else {
                return new JsonResponse(['message' => 'Article non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de la suppression de l\'article: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('api/uploads/articleCover/{coverName}', name: 'app_article_cover_download', methods: ['GET'])]
    public function downloadCover(string $coverName, FileDownloadService $fileDownloadService): BinaryFileResponse | JsonResponse
    {
        return $fileDownloadService->downloadArticleCover($coverName);
    }
}
