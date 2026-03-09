<?php

namespace App\EventListener;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Entity\FeedbackFromMap;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedbackFromMapListener
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
        /** @var Catalog $item */
        $item = $event->getSubject();

        if (!$item instanceof FeedbackFromMap) {
            return;
        }

        $errors = [];

        if ($error = $this->getPositionError($item)) {
            $errors[] = $error;
        }

        if ($linkError = $this->getLinkError($item)) {
            $errors[] = $linkError;
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

    private function getPositionError(FeedbackFromMap $item): ?string
    {
        $position = $item->getPosition();

        if ($position === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(FeedbackFromMap::class);

        $existing = $repository->findOneBy([
            'position' => $position,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята',
                $position
            );
        }

        return null;
    }
    private function getLinkError(FeedbackFromMap $item): ?string
    {
        $link = $item->getFeedbackLink();

        if (filter_var($link, FILTER_VALIDATE_URL) === false) {
            return 'Ошибка! Указано некорректное значение для ссылки.';
        }

        return null;
    }
}
