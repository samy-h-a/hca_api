<?php

namespace App\Controllers;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookCategoriesController extends AbstractController
{
    #[Route('/api/repositories/categories', name: 'app_repositories_categories', methods: ['GET'])]
    public function getBookCategories(CategoryRepository $repository): JsonResponse
    {
        $categories = $repository->findAll();

        $result = array_map(function ($category) {
            return [
                'id' => $category->getId(),
                'label' => $category->getLabel(),
            ];
        }, $categories);

        return $this->json($result);
    }
}
