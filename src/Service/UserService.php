<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) { }

    public function createUserFromEmail(string $email): User
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $form->get('userEmail')->getData()]);

        if (null !== $existingUser) {
            return $existingUser;
        }
        
        if (null === $userToBeAdded) {
            $user = new User();
        }
        // @todo
    }
}