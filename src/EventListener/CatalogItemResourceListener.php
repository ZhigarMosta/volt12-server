<?php

namespace App\EventListener;

use App\Entity\CatalogItem;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogItemResourceListener
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

        if (!$item instanceof CatalogItem) {
            return;
        }

        $position = $item->getPosition();
        $catalog = $item->getCatalog();

        if ($position === null || $catalog === null) {
            return;
        }

        $repository = $this->entityManager->getRepository(CatalogItem::class);

        $existing = $repository->findOneBy([
            'position' => $position,
            'catalog' => $catalog,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {

            $message = sprintf(
                'Ошибка! Позиция %d уже занята товаром "%s" в этом каталоге "%s"',
                $position,
                $existing->getName(),
                $existing->getCatalog()->getName()
            );

            $event->stop($message);
            $redirectUrl = $this->requestStack->getCurrentRequest()->headers->get('referer');
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
