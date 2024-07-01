<?php

namespace App\Services;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class AuthenticationService {
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;


    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager) {
        $this->doctrine = $doctrine;    
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
  
    }

    public function login($data) {

        $entityManager = $this->doctrine->getManager();


        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        

        if (!$user) {
            return ['status' => 'user_not_found']; 
        }

        // Validate the password
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return ['status' => 'invalid_password']; 
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);
        $is_admin = $user->getRoles()[0] === 'ROLE_ADMIN';
        $is_verified = $user->isVerified();
        return ['status' => 'success', 'token' => $token, 'is_admin' => $is_admin, 'is_verified' => $is_verified];
    }


   
}