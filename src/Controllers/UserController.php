<?php

namespace App\Controllers;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Services\RegistrationService;
use App\Services\AuthenticationService;

use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{

    #[Route('/api/register', name: 'app_registration', methods: ['POST'])]
    public function registration(Request $request, RegistrationService $registrationService): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        if (!isset($body['email']) || !isset($body['password'])) {
            return new JsonResponse(['message' => 'Tous les champs sont requis'], 400);
        }
        try {
            $isAdmin = isset($body['is_admin']) && $body['is_admin'] === 1;
            $result = $registrationService->registration($body, $isAdmin);

            if ($result === null) {
                return new JsonResponse(['message' => 'Utilisateur enregistré'], 200);
            } else {
                return new JsonResponse($result, 200);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Une erreur est survenue'], 500);
        }
    }



    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, AuthenticationService $authenticationService): JsonResponse
    {
        // Decode JSON body from the request
        $body = json_decode($request->getContent(), true);

        // Check if email and password are set
        if (!isset($body['email']) || !isset($body['password'])) {
            return new JsonResponse(['message' => 'Tous les champs sont requis'], 400);
        }

        try {
            // Attempt to authenticate and get a token
            $result = $authenticationService->login($body);

            // Check the result of the authentication attempt
            if ($result['status'] === 'user_not_found') {
                return new JsonResponse(['message' => "L'utilisateur n'existe pas"], 400);
            } elseif ($result['status'] === 'invalid_password') {
                return new JsonResponse(['message' => 'Le mot de passe est incorrect'], 400);
            } elseif ($result['status'] === 'success') {
                return new JsonResponse(['token' => [$result['token']], 'is_admin' => $result['is_admin'], 'is_verified' => $result['is_verified']], 200);
            }
        } catch (\Exception $e) {
            // Handle unexpected errors
            return new JsonResponse(['message' => 'Une erreur est survenue'], 500);
        }
    }

    #[Route('/api/sendVerification', name: 'app_send_verification', methods: ['POST'])]
    public function sendVerification(Request $request, RegistrationService $registrationService): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        
        if (!isset($body['email']) || empty($body['email'])) {
            return new JsonResponse(['message' => 'L\'email est requis'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $email = $body['email'];
        return $registrationService->reSendVerificationEmail($email);
    }
}
