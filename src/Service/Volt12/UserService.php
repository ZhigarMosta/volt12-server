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
        if ((!empty($data['name']) || array_key_exists('phone', $data)) && !$user->isEmailVerified()) {
            throw new \InvalidArgumentException('Подтвердите почту, чтобы изменить данные профиля');
        }

        if (!empty($data['name'])) {
            $user->setName($data['name']);
        }

        if (array_key_exists('phone', $data)) {
            $user->setPhone($data['phone'] ?: null);
        }

        $this->entityManager->flush();
    }

    public function sendVerificationEmail(User $user, FeedbackService $feedbackService): void
    {
        $this->userTokenRepository->deleteByUserAndType($user, 'email_verify');

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $userToken = new UserToken($user, $code, 'email_verify');
        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $feedbackService->sendEmailVerification($user->getEmail(), $code);
    }

    public function verifyEmail(string $code): bool
    {
        $userToken = $this->userTokenRepository->findByTokenAndType($code, 'email_verify');
        if (!$userToken) {
            return false;
        }

        $user = $userToken->getUser();
        $user->setEmailVerified(true);
        $this->userTokenRepository->deleteByToken($code);
        $this->entityManager->flush();

        return true;
    }

    public function checkPasswordResetEligibility(string $email): string
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return 'not_found';
        }

        return $user->isEmailVerified() ? 'eligible' : 'unverified';
    }

    public function sendPasswordResetCode(string $email, FeedbackService $feedbackService): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !$user->isEmailVerified()) {
            return false;
        }

        $this->userTokenRepository->deleteByUserAndType($user, 'password_reset');

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $userToken = new UserToken($user, $code, 'password_reset');
        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $feedbackService->sendPasswordResetCode($user->getEmail(), $code);

        return true;
    }

    public function resetPassword(string $email, string $code, string $newPassword): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return false;
        }

        $userToken = $this->userTokenRepository->findByTokenAndType($code, 'password_reset');
        if (!$userToken || $userToken->getUser()->getId() !== $user->getId()) {
            return false;
        }

        if ($userToken->getCreatedAt() < new \DateTime('-15 minutes')) {
            $this->userTokenRepository->deleteByUserAndType($user, 'password_reset');
            return false;
        }

        if (strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 6 символов');
        }

        $user->setPassword(password_hash($newPassword, PASSWORD_ARGON2ID));
        $this->userTokenRepository->deleteByUserAndType($user, 'password_reset');
        $this->entityManager->flush();

        return true;
    }

    public function changePassword(User $user, string $newPassword): void
    {
        if (strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 6 символов');
        }

        $user->setPassword(password_hash($newPassword, PASSWORD_ARGON2ID));
        $this->entityManager->flush();
    }

    public function requestPasswordChange(User $user, string $newPassword, FeedbackService $feedbackService): void
    {
        if (!$user->isEmailVerified()) {
            throw new \InvalidArgumentException('Подтвердите почту, чтобы изменить пароль');
        }

        if (strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Пароль должен содержать минимум 6 символов');
        }

        $this->userTokenRepository->deleteByUserAndType($user, 'password_change');

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $userToken = new UserToken($user, $code, 'password_change');
        $userToken->setPayload(password_hash($newPassword, PASSWORD_ARGON2ID));
        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $feedbackService->sendPasswordChangeCode($user->getEmail(), $code);
    }

    public function confirmPasswordChange(User $user, string $code): bool
    {
        $userToken = $this->userTokenRepository->findByTokenAndType($code, 'password_change');
        if (!$userToken || $userToken->getUser()->getId() !== $user->getId()) {
            return false;
        }

        if ($userToken->getCreatedAt() < new \DateTime('-15 minutes')) {
            $this->userTokenRepository->deleteByUserAndType($user, 'password_change');
            return false;
        }

        $user->setPassword($userToken->getPayload());
        $this->userTokenRepository->deleteByUserAndType($user, 'password_change');
        $this->entityManager->flush();

        return true;
    }

    public function logout(string $token): void
    {
        $this->userTokenRepository->deleteByToken($token);
    }

    public function getUserByAuthToken(string $token): ?User
    {
        return $this->userTokenRepository->findByTokenAndType($token, 'auth')?->getUser();
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
