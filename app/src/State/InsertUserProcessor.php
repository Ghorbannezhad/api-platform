<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InsertUserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager
    )
    {
        
    }
    public function process(mixed $user, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $hashPassword = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
