<?php

namespace App\EventListener;

use App\Entity\CatalogItem;
use App\Entity\CatalogItemImage;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogItemImageResourceListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->checkPosition($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->checkPosition($event);
    }

    private function checkPosition(ResourceControllerEvent $event): void
    {
        $item = $event->getSubject();

        if (!$item instanceof CatalogItemImage) {
            return;
        }

        $position = $item->getPosition();
        $catalogItem = $item->getCatalogItem();

        if ($position === null || $catalogItem === null) {
            return;
        }

        $repository = $this->entityManager->getRepository(CatalogItemImage::class);

        $existing = $repository->findOneBy([
            'position' => $position,
            'catalogItem' => $catalogItem,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {

            $message = sprintf(
                'Ошибка! Позиция %d уже занята изоображением c title-ом "%s" у продукта "%s"',
                $position,
                $existing->getTitle(),
                $existing->getCatalogItem()->getName()
            );

            $event->stop($message);
            $redirectUrl = $this->requestStack->getCurrentRequest()->headers->get('referer');
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
