<?php

namespace App\Controller\Volt12;

use App\Entity\User;
use App\Exception\EmailLimitExceededException;
use App\Service\Volt12\CartService;
use App\Service\Volt12\CompareService;
use App\Service\Volt12\FeedbackService;
use App\Service\Volt12\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/volt12/auth')]
class AuthController extends AbstractController
{
    private const COOKIE_NAME = 'auth_token';
    private const COOKIE_LIFETIME = 2592000;

    public function __construct(
        private UserService $userService,
        private CartService $cartService,
        private CompareService $compareService,
        private FeedbackService $feedbackService,
        private RateLimiterFactory $emailVerificationLimiter,
        private RateLimiterFactory $passwordResetLimiter,
        private RateLimiterFactory $emailVerifyAttemptLimiter,
        private RateLimiterFactory $passwordChangeRequestLimiter,
        private RateLimiterFactory $passwordChangeAttemptLimiter,
        private RateLimiterFactory $passwordResetTargetLimiter,
        private RateLimiterFactory $registerLimiter,
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $loginTargetLimiter,
        private RateLimiterFactory $passwordResetAttemptLimiter,
    ) {}

    private function getTokenFromRequest(Request $request): ?string
    {
        $token = $request->cookies->get(self::COOKIE_NAME);
        if ($token) return $token;

        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return null;
    }

    #[Route('/register', name: 'volt12_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $limiter = $this->registerLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много регистраций. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['success' => false, 'error' => 'Заполните обязательные поля'], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Некорректный email'], 400);
        }

        try {
            [$user, $token] = $this->userService->register(
                $data['name'],
                $data['email'],
                $data['password'],
                $data['phone'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }

        $cart = $data['cart'] ?? [];
        $compare = $data['compare'] ?? [];

        if (!empty($cart) && is_array($cart)) {
            $this->cartService->importFromLocal($user, $cart);
        }

        if (!empty($compare) && is_array($compare)) {
            $this->compareService->importFromLocal($user, $compare);
        }

        $response = $this->json(['success' => true, 'user' => $this->serializeUser($user), 'token' => $token]);
        $response->headers->setCookie($this->createAuthCookie($token));

        return $response;
    }

    #[Route('/login', name: 'volt12_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $ipLimiter = $this->loginLimiter->create($request->getClientIp());
        if (!$ipLimiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много попыток. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['success' => false, 'error' => 'Заполните email и пароль'], 400);
        }

        $targetLimiter = $this->loginTargetLimiter->create(strtolower($data['email']));
        if (!$targetLimiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много попыток. Попробуйте позже'], 429);
        }

        $result = $this->userService->login($data['email'], $data['password']);
        if (!$result) {
            return $this->json(['success' => false, 'error' => 'Неверный email или пароль'], 401);
        }

        [$user, $token] = $result;

        $response = $this->json(['success' => true, 'user' => $this->serializeUser($user), 'token' => $token]);
        $response->headers->setCookie($this->createAuthCookie($token));

        return $response;
    }

    #[Route('/logout', name: 'volt12_auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $token = $this->getTokenFromRequest($request);
        if ($token) {
            $this->userService->logout($token);
        }

        $response = $this->json(['success' => true]);
        $response->headers->clearCookie(self::COOKIE_NAME, '/');

        return $response;
    }

    #[Route('/update-profile', name: 'volt12_auth_update_profile', methods: ['POST'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->userService->updateProfile($user, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }

        return $this->json(['success' => true, 'user' => $this->serializeUser($user)]);
    }

    #[Route('/me', name: 'volt12_auth_me', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        return $this->json(['success' => true, 'user' => $this->serializeUser($user)]);
    }

    #[Route('/send-verification', name: 'volt12_auth_send_verification', methods: ['POST'])]
    public function sendVerification(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        if ($user->isEmailVerified()) {
            return $this->json(['success' => false, 'error' => 'Email уже подтверждён'], 400);
        }

        $limiter = $this->emailVerificationLimiter->create('user_' . $user->getId());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много запросов. Попробуйте позже'], 429);
        }

        try {
            $this->userService->sendVerificationEmail($user, $this->feedbackService);
        } catch (EmailLimitExceededException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 429);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/verify-email', name: 'volt12_auth_verify_email', methods: ['POST'])]
    public function verifyEmail(Request $request): JsonResponse
    {
        $limiter = $this->emailVerifyAttemptLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много попыток. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $code = trim($data['code'] ?? '');

        if ($code === '') {
            return $this->json(['success' => false, 'error' => 'Код обязателен'], 400);
        }

        $verified = $this->userService->verifyEmail($code);
        if (!$verified) {
            return $this->json(['success' => false, 'error' => 'Неверный или устаревший код'], 400);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/forgot-password', name: 'volt12_auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $limiter = $this->passwordResetLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много запросов. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $email = trim($data['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Некорректный email'], 400);
        }

        if ($this->userService->checkPasswordResetEligibility($email) === 'unverified') {
            return $this->json(['success' => false, 'error' => 'Мы не можем отправить код, так как не уверены, что почта корректна. Обратитесь в поддержку для восстановления пароля.'], 400);
        }

        $targetLimiter = $this->passwordResetTargetLimiter->create(strtolower($email));
        if ($targetLimiter->consume(1)->isAccepted()) {
            try {
                $this->userService->sendPasswordResetCode($email, $this->feedbackService);
            } catch (EmailLimitExceededException $e) {
                // Глобальный лимит писем исчерпан — не раскрываем это отправителю,
                // чтобы не ломать защиту от перебора email на этом эндпоинте.
            }
        }

        return $this->json(['success' => true]);
    }

    #[Route('/reset-password', name: 'volt12_auth_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $limiter = $this->passwordResetAttemptLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много попыток. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $email = trim($data['email'] ?? '');
        $code = trim($data['code'] ?? '');
        $newPassword = $data['new_password'] ?? '';

        if ($email === '' || $code === '' || $newPassword === '') {
            return $this->json(['success' => false, 'error' => 'Заполните все поля'], 400);
        }

        try {
            $result = $this->userService->resetPassword($email, $code, $newPassword);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }

        if (!$result) {
            return $this->json(['success' => false, 'error' => 'Неверный код или email'], 400);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/request-password-change', name: 'volt12_auth_request_password_change', methods: ['POST'])]
    public function requestPasswordChange(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $limiter = $this->passwordChangeRequestLimiter->create('user_' . $user->getId());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много запросов. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $newPassword = $data['new_password'] ?? '';

        if ($newPassword === '') {
            return $this->json(['success' => false, 'error' => 'Введите новый пароль'], 400);
        }

        try {
            $this->userService->requestPasswordChange($user, $newPassword, $this->feedbackService);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (EmailLimitExceededException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 429);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/confirm-password-change', name: 'volt12_auth_confirm_password_change', methods: ['POST'])]
    public function confirmPasswordChange(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $limiter = $this->passwordChangeAttemptLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'error' => 'Слишком много попыток. Попробуйте позже'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $code = trim($data['code'] ?? '');

        if ($code === '') {
            return $this->json(['success' => false, 'error' => 'Код обязателен'], 400);
        }

        $confirmed = $this->userService->confirmPasswordChange($user, $code);
        if (!$confirmed) {
            return $this->json(['success' => false, 'error' => 'Неверный или устаревший код'], 400);
        }

        return $this->json(['success' => true]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'email_verified' => $user->isEmailVerified(),
        ];
    }

    private function createAuthCookie(string $token): Cookie
    {
        return Cookie::create(
            self::COOKIE_NAME,
            $token,
            time() + self::COOKIE_LIFETIME,
            '/',
            null,
            false,
            true,
            false,
            Cookie::SAMESITE_LAX
        );
    }
}
