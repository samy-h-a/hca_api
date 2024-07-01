<?php


namespace App\Services;

use App\Entity\User;
use App\Entity\Downloads;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class RegistrationService
{

    private $doctrine;
    private $userRepository;
    private TransportInterface $mailer;
    private UrlGeneratorInterface $router;
    private EntityManagerInterface $entityManager;
    
   

    public function __construct(ManagerRegistry $doctrine,EntityManagerInterface $entityManager,TransportInterface $mailer, UrlGeneratorInterface $router, UserRepository $userRepository)
    {
        $this->doctrine = $doctrine;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    public function registration($data, $isAdmin)
    {
        $entityManager = $this->doctrine->getManager();
        if (!isset($data['email']) || !isset($data['password'])) {
            return ['message' => 'Tous les champs sont requis'];
        }
        $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return ['message' => 'Cette adresse mail a déjà été utilisée'];
        }
        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->setPassword($hashedPassword);

        $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $user->setVerified(false);
        $entityManager->persist($user);
        try {
            $entityManager->flush();
            
            if (!$isAdmin) {
                $this->sendVerificationEmail($user);
                $this->createDownloadTable($user);
            }
            
            return ['message' => 'Utilisateur enregistré avec succès'];
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()];
        }
    }


    private function createDownloadTable($user)
    {
        $entityManager = $this->doctrine->getManager();
        $download = new Downloads();
        $download->setUser($user);
        $download->setCount(0);
        $entityManager->persist($download);
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            return ['message' => 'Erreur lors de l\'enregistrement'];
        }
    }

    public function sendVerificationEmail(User $user)
    {
        $token = bin2hex(random_bytes(32));
        $user->setVerificationToken($token);
        $this->entityManager->flush();

        $verificationUrl = $this->router->generate('app_verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('noreply@minervadz.com')
            ->to($user->getEmail())
            ->subject('Email Verification')
            ->html(sprintf('Please verify your email by clicking <a href="%s">here</a>.', $verificationUrl));

        $this->mailer->send($email);
    }

    public function reSendVerificationEmail(string $email): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->sendVerificationEmail($user);
            return new JsonResponse(['message' => 'Un email de confirmation a été renvoyé'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de l\'envoi de l\'email de confirmation'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
