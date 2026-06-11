<?php

namespace App\Service\Volt12;

use App\Entity\User;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserTokenRepository $userTokenRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function register(string $name, string $email, string $password, ?string $phone = null): array
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

        $this->entityManager->persist($user);

        $userToken = new UserToken($user, $this->generateToken());
        $this->entityManager->persist($userToken);

        $this->entityManager->flush();

        return [$user, $userToken->getToken()];
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !password_verify($password, $user->getPassword())) {
            return null;
        }

        $userToken = new UserToken($user, $this->generateToken());
        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        return [$user, $userToken->getToken()];
    }

    public function updateProfile(User $user, array $data): void
    {
        if (!empty($data['name'])) {
            $user->setName($data['name']);
        }

        if (array_key_exists('phone', $data)) {
            $user->setPhone($data['phone'] ?: null);
        }

        $this->entityManager->flush();
    }

    public function sendVerificationEmail(User $user, FeedbackService $feedbackService, string $baseUrl): void
    {
        $token = $this->generateToken();
        $userToken = new UserToken($user, $token, 'email_verify');
        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $verifyUrl = rtrim($baseUrl, '/') . '/verify-email?token=' . $token;
        $feedbackService->sendEmailVerification($user->getEmail(), $verifyUrl);
    }

    public function verifyEmail(string $token): bool
    {
        $userToken = $this->userTokenRepository->findByTokenAndType($token, 'email_verify');
        if (!$userToken) {
            return false;
        }

        $user = $userToken->getUser();
        $user->setEmailVerified(true);
        $this->userTokenRepository->deleteByToken($token);
        $this->entityManager->flush();

        return true;
    }

    public function logout(string $token): void
    {
        $this->userTokenRepository->deleteByToken($token);
    }

    public function getUserByAuthToken(string $token): ?User
    {
        return $this->userTokenRepository->findByToken($token)?->getUser();
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
