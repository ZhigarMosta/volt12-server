<?php

namespace App\EventListener;

use App\Entity\User;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class UserResourceListener
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof User) {
            return;
        }

        $this->processPassword($user);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof User) {
            return;
        }

        $this->processPassword($user);
    }

    private function processPassword(User $user): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $formData = $request->request->all('user');
        $password = $formData['password'] ?? '';

        if ($password !== '') {
            $user->setPassword(password_hash($password, PASSWORD_ARGON2ID));
        }
    }
}
