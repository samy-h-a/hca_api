<?php


namespace App\Services;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;



class BookService
{

    private $doctrine;
    private $bookRepository;

    public function __construct(ManagerRegistry $doctrine, BookRepository $bookRepository)
    {
        $this->doctrine = $doctrine;
        $this->bookRepository = $bookRepository;
    }


    public function addBook($data, $fileName, $coverFileName)
    {
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
        $bookCover = $_ENV['API_URL'] . '/api/uploads/cover/' . $coverFileName;
        $book-> setCover($bookCover);
        $book->setDownloads(0);
        $book->setViews(0);
        $book->setCategory($data['category']);
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


    public function getAllFiles()
    {
        try {
            $books = $this->bookRepository->findAll();
            return $books;
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la récupération des fichiers'];
        }
    }

}
