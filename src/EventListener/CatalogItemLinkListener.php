<?php

namespace App\EventListener;

use App\Entity\CatalogItemLink;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Проверки для табличек «товар — товар» (рекомендуемые, с этим покупают,
 * также может понадобиться): дубль пары, ссылка товара на самого себя,
 * занятая позиция внутри блока одного товара.
 */
class CatalogItemLinkListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->check($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->check($event);
    }

    private function check(ResourceControllerEvent $event): void
    {
        $link = $event->getSubject();

        if (!$link instanceof CatalogItemLink) {
            return;
        }

        $owner = $link->getCatalogItem();
        $linked = $link->getLinkedItem();

        if ($owner === null || $linked === null) {
            return;
        }

        if ($owner->getId() === $linked->getId()) {
            $this->stop($event, sprintf(
                'Ошибка! Нельзя привязать товар "%s" к самому себе',
                $owner->getName()
            ));
            return;
        }

        $repository = $this->entityManager->getRepository($link::class);

        $existing = $repository->findOneBy([
            'catalogItem' => $owner,
            'linkedItem' => $linked,
        ]);

        if ($existing && $existing->getId() !== $link->getId()) {
            $this->stop($event, sprintf(
                'Ошибка! В блоке «%s» у товара "%s" уже есть товар "%s"',
                $link::getBlockLabel(),
                $owner->getName(),
                $linked->getName()
            ));
            return;
        }

        $position = $link->getPosition();
        if ($position === null) {
            return;
        }

        $samePosition = $repository->findOneBy([
            'catalogItem' => $owner,
            'position' => $position,
        ]);

        if ($samePosition && $samePosition->getId() !== $link->getId()) {
            $this->stop($event, sprintf(
                'Ошибка! Позиция %d в блоке «%s» у товара "%s" уже занята товаром "%s"',
                $position,
                $link::getBlockLabel(),
                $owner->getName(),
                $samePosition->getLinkedItem()->getName()
            ));
        }
    }

    private function stop(ResourceControllerEvent $event, string $message): void
    {
        $event->stop($message);
        $redirectUrl = $this->requestStack->getCurrentRequest()->headers->get('referer');
        $event->setResponse(new RedirectResponse($redirectUrl));
    }
}
