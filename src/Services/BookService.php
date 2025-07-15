<?php


namespace App\Services;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;



class BookService
{

    private $doctrine;
    private $bookRepository;
    private $categoryRepository;

    public function __construct(ManagerRegistry $doctrine, BookRepository $bookRepository, CategoryRepository $categoryRepository)
    {
        $this->doctrine = $doctrine;
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function addBook($data, $fileName, $coverFileName)
    {
        $categoryId = $data['category_id'];
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw new \Exception('Catégorie introuvable');
        }

        $entityManager = $this->doctrine->getManager();
        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setIsbn($data['isbn']);
        $publicationDate = \DateTimeImmutable::createFromFormat('Y-m-d', $data['publication_year'] . '-01-01');
        $book->setYear($publicationDate);
        $book->setEdition($data['edition']);
        $book->setTranslator($data['translator']);
        $book->setDescription($data['description']);
        $book->setCategory($category);
        $bookCover = $_ENV['API_URL'] . '/api/uploads/cover/' . $coverFileName;
        $book-> setCover($bookCover);
        $book->setDownloads(0);
        $book->setViews(0);
        $link = $_ENV['API_URL'] . '/api/uploads/pdf/' . $fileName;
        $book->setLink($link);

        $entityManager->persist($book);

        try {
            $entityManager->flush();
            return null;
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de l\'enregistrement'];
        }
    }


public function getAllFiles(?int $categoryId, int $limit, string $order)
{
    $qb = $this->bookRepository->createQueryBuilder('b')
        ->orderBy('b.title', $order)
        ->setMaxResults($limit);

    if ($categoryId !== null) {
        $qb->andWhere('b.category = :categoryId')
           ->setParameter('categoryId', $categoryId);
    }

    return $qb->getQuery()->getResult();
}


}
