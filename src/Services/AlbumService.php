<?php

namespace App\Services;

use App\Entity\Album;
use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AlbumService
{
    private EntityManagerInterface $entityManager;
    private string $targetDirectoryAlbumPicture;

    public function __construct(EntityManagerInterface $entityManager, string $targetDirectoryAlbumPicture)
    {
        $this->entityManager = $entityManager;
        $this->targetDirectoryAlbumPicture = $targetDirectoryAlbumPicture;
    }


    public function createAlbumWithPictures(string $title, $picturesFiles)
    {
       

        $album = new Album();
        $album->setTitle($title);
        $album->setCreatedAt(new \DateTimeImmutable());
        try {
            $this->entityManager->persist($album);
            $this->entityManager->flush();
                foreach ($picturesFiles as $pictureFile) {
                    if ($pictureFile instanceof UploadedFile) {
                        $this->savePictureInAlbum($album->getId(), $pictureFile);
                    } else {
                        throw new \Exception('Invalid file in the pictures array.');
                    }
                }
        
        } catch (\Exception $e) {

            throw $e;
        }

        return null;
    }

    public function savePictureInAlbum(int $albumId, $pictureFile)
    {
        $album = $this->entityManager->getRepository(Album::class)->find($albumId);

        if (!$album) {
            throw new \Exception('Album not found');
        }

        if ($pictureFile instanceof UploadedFile) {
            $filename = uniqid() . '.' . $pictureFile->guessExtension();
            $fileUrl =  $_ENV['API_URL'] . '/api/uploads/pictures/' . $filename;
            $pictureFile->move($this->targetDirectoryAlbumPicture, $filename);

            $picture = new Picture();
            $picture->setUrl($fileUrl);
            $picture->setAlbum($album);
            $album->addPicture($picture);

            try {
                $this->entityManager->persist($picture);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return null;
    }



    public function createAlbumWithoutPictures(string $title)
    {
       

        $album = new Album();
        $album->setTitle($title);
        $album->setCreatedAt(new \DateTimeImmutable());
        try {
            $this->entityManager->persist($album);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw $e;
        }
        return null;
    }


    public function getAllAlbums(): array
    {
        $albums = $this->entityManager->getRepository(Album::class)->findAll();
        return $albums;
    }


    public function addPictureToAlbum(int $albumId, $pictureFile)
    {
       
        $album = $this->entityManager->getRepository(Album::class)->find($albumId);

        if (!$album) {
            throw new \Exception('Album not found');
        }

        if ($pictureFile instanceof UploadedFile) {
            $filename = uniqid() . '.' . $pictureFile->guessExtension();
            $fileUrl =  $_ENV['API_URL'] . '/api/uploads/pictures/' . $filename;
            $pictureFile->move($this->targetDirectoryAlbumPicture, $filename);

            $picture = new Picture();
            $picture->setUrl($fileUrl);
            $picture->setAlbum($album);
            $album->addPicture($picture);

            try {
                $this->entityManager->persist($picture);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return null;
    }


    public function deletePictureFromAlbum(int $albumId, int $pictureId)
    {
       

        $album = $this->entityManager->getRepository(Album::class)->find($albumId);

        if (!$album) {
            throw new \Exception('Album not found');
        }

        $picture = $this->entityManager->getRepository(Picture::class)->find($pictureId);

        if (!$picture) {
            throw new \Exception('Picture not found');
        }

        if ($picture->getAlbum()->getId() !== $album->getId()) {
            throw new \Exception('Picture does not belong to the album');
        }

        $album->removePicture($picture);
        $filePath = $this->targetDirectoryAlbumPicture . '/' . basename($picture->getUrl());
        $this->removePictureFile($filePath);
        try {
            $this->entityManager->remove($picture);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw $e;
        }

        return null;
    }

    private function removePictureFile(string $filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function deleteAlbum(int $albumId)
    {
      
        $album = $this->entityManager->getRepository(Album::class)->find($albumId);

        if (!$album) {
            throw new \Exception('Album not found');
        }

        $pictures = $album->getPictures();

        foreach ($pictures as $picture) {
            $filePath = $this->targetDirectoryAlbumPicture . '/' . basename($picture->getUrl());
            $this->removePictureFile($filePath);
        }

        try {
            $this->entityManager->remove($album);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw $e;
        }

        return null;
    }



}
