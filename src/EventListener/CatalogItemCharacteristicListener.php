<?php

namespace App\EventListener;

use App\Entity\CatalogItemCharacteristic;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogItemCharacteristicListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->checkDouble($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->checkDouble($event);
    }

    private function checkDouble(ResourceControllerEvent $event): void
    {
        $item = $event->getSubject();

        if (!$item instanceof CatalogItemCharacteristic) {
            return;
        }

        $catalogCharacteristic = $item->getCatalogCharacteristic();
        $catalogItem = $item->getCatalogItem();

        if ($catalogCharacteristic === null || $catalogItem === null) {
            return;
        }

        $repository = $this->entityManager->getRepository(CatalogItemCharacteristic::class);

        $existing = $repository->findOneBy([
            'catalogItem' => $catalogItem,
            'catalogCharacteristic' => $catalogCharacteristic,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {

            $message = sprintf(
                'Ошибка! У продукта "%s" уже есть характеристика "%s"',
                $existing->getCatalogItem()->getName(),
                $existing->getCatalogCharacteristic()->getName()
            );

            $event->stop($message);

            $event->stop($message);
            $redirectUrl = $this->requestStack->getCurrentRequest()->headers->get('referer');
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
