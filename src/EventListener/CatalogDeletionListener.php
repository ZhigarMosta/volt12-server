<?php

namespace App\EventListener;

use App\Entity\Catalog;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CatalogDeletionListener
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router
    ) {}

    public function onPreDelete(ResourceControllerEvent $event): void
    {
        $catalog = $event->getSubject();

        if (!$catalog instanceof Catalog) {
            return;
        }

        $relations = [];

        if (!$catalog->getCatalogItems()->isEmpty()) {
            $relations[] = 'Продукты (' . $catalog->getCatalogItems()->count() . ' шт.)';
        }

        if (!$catalog->getCharacteristics()->isEmpty()) {
            $relations[] = 'Характеристики (' . $catalog->getCharacteristics()->count() . ' шт.)';
        }

        if (!$catalog->getGroups()->isEmpty()) {
            $relations[] = 'Группы (' . $catalog->getGroups()->count() . ' шт.)';
        }

        if (!empty($relations)) {
            $message = sprintf(
                'Ошибка удаления! Каталог "%s" привязан к следующим таблицам: %s. Сначала удалите привязки (или сами сущности), затем попробуйте снова.',
                $catalog->getName(),
                implode(', ', $relations)
            );

            $event->stop(
                $message,
                ResourceControllerEvent::TYPE_ERROR
            );

            $redirectUrl = $this->router->generate('app_admin_catalog_index');

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
