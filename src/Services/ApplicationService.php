<?php


namespace App\Services;

use App\Entity\Applications;
use App\Repository\ApplicationsRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;



class ApplicationService
{

    private $doctrine;
    private $applicationsRepository;

    public function __construct(ManagerRegistry $doctrine, ApplicationsRepository $applicationsRepository)
    {
        $this->doctrine = $doctrine;
        $this->applicationsRepository = $applicationsRepository;
    }

    public function addApplication($data, $fileName, $coverFileName)
    {
        $entityManager = $this->doctrine->getManager();
        $application = new Applications();
        $application->setTitle($data['title']);
        $publicationYear = \DateTimeImmutable::createFromFormat('Y-m-d', $data['createdAt']);
        $application->setCreatedAt($publicationYear);
        $application->setDescription($data['description']);
        $applicationCover = $_ENV['API_URL'] . '/api/uploads/applications/covers/' . $coverFileName;
        $application->setCover($applicationCover);
        $link = $_ENV['API_URL'] . '/api/uploads/applications/files/' . $fileName;
        $application->setLink($link);
        $entityManager->persist($application);

        try {
            $entityManager->flush();
            return null;
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de l\'enregistrement'];
        }
    }

    public function getAllApplications()
    {
        try {
            $videos = $this->applicationsRepository->findAll();
            return $videos;
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de la récupération des applications'];
        }
    }

}
