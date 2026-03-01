<?php

namespace App\EventListener;

use App\Entity\CatalogItem;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CatalogItemDeletionListener
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router
    ) {}

    public function onPreDelete(ResourceControllerEvent $event): void
    {
        $catalogItem = $event->getSubject();

        if (!$catalogItem instanceof CatalogItem) {
            return;
        }

        $relations = [];

        if (!$catalogItem->getCharacteristics()->isEmpty()) {
            $relations[] = 'Характеристики (' . $catalogItem->getCharacteristics()->count() . ' шт.)';
        }
        if (!$catalogItem->getCatalogItemImages()->isEmpty()) {
            $relations[] = 'Изоображения (' . $catalogItem->getCatalogItemImages()->count() . ' шт.)';
        }

        if (!empty($relations)) {
            $message = sprintf(
                'Ошибка удаления! Продукт "%s" привязан к следующим таблицам: %s. Сначала удалите привязки (или сами сущности), затем попробуйте снова.',
                $catalogItem->getName(),
                implode(', ', $relations)
            );

            $event->stop(
                $message,
                ResourceControllerEvent::TYPE_ERROR
            );

            $redirectUrl = $this->router->generate('app_admin_catalog_item_index');

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
