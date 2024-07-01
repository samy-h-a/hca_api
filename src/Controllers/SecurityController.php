<?php

namespace App\Controllers;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return new Response('Invalid token', Response::HTTP_BAD_REQUEST);
        }

        $user->setVerificationToken(null);
        $user->setVerified(true);
        $entityManager->flush();

        return new Response('Email verified successfully');
    }
}