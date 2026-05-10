<?php

namespace App\EventListener;

use App\Entity\ServiceGroup;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class ServiceGroupResourceListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->validate($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->validate($event);
    }

    private function validate(ResourceControllerEvent $event): void
    {
        $group = $event->getSubject();
        if (!$group instanceof ServiceGroup) {
            return;
        }

        $errors = [];

        if ($error = $this->getPositionError($group)) {
            $errors[] = $error;
        }

        if (empty($errors)) {
            return;
        }

        $message = implode(' ' . PHP_EOL, $errors);

        $event->stop($message);

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $referer = $request->headers->get('referer');
            $event->setResponse(new RedirectResponse($referer));
        }
    }

    private function getPositionError(ServiceGroup $group): ?string
    {
        $position = $group->getPosition();

        if ($position === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(ServiceGroup::class);

        $existing = $repository->findOneBy([
            'position' => $position
        ]);

        if ($existing && $existing->getId() !== $group->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята группой услуг "%s".',
                $position,
                $existing->getName()
            );
        }

        return null;
    }
}
