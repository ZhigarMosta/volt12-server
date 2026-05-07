<?php

namespace App\Service\Volt12;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function register(string $name, string $email, string $password, ?string $phone = null): User
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new \InvalidArgumentException('Email уже занят');
        }

        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 6 символов');
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setPassword(password_hash($password, PASSWORD_ARGON2ID));
        $user->setAuthToken($this->generateToken());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function login(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !password_verify($password, $user->getPassword())) {
            return null;
        }

        $user->setAuthToken($this->generateToken());
        $this->entityManager->flush();

        return $user;
    }

    public function logout(User $user): void
    {
        $user->setAuthToken(null);
        $this->entityManager->flush();
    }

    public function getUserByAuthToken(string $token): ?User
    {
        return $this->userRepository->findByAuthToken($token);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
