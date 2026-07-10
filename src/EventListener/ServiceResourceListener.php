<?php

namespace App\EventListener;

use App\Entity\Service;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class ServiceResourceListener
{
    private const MAX_IN_FOOTER = 7;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private SlugGenerator $slugGenerator
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->ensureSlug($event);
        $this->validate($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->ensureSlug($event);
        $this->validate($event);
    }

    private function ensureSlug(ResourceControllerEvent $event): void
    {
        $service = $event->getSubject();

        if (!$service instanceof Service || $service->getSlug()) {
            return;
        }

        $repository = $this->entityManager->getRepository(Service::class);

        $slug = $this->slugGenerator->generateUnique(
            $service->getName(),
            function (string $candidate) use ($repository, $service): bool {
                $existing = $repository->findOneBy(['slug' => $candidate]);

                return $existing && $existing->getId() !== $service->getId();
            },
            'service'
        );

        $service->setSlug($slug);
    }

    private function validate(ResourceControllerEvent $event): void
    {
        $service = $event->getSubject();
        if (!$service instanceof Service) {
            return;
        }

        $errors = [];

        if ($error = $this->getSlugError($service)) {
            $errors[] = $error;
        }

        if ($error = $this->getPositionError($service)) {
            $errors[] = $error;
        }

        if ($error = $this->getInFooterError($service)) {
            $errors[] = $error;
        }

        if (empty($errors)) {
            return;
        }

        $message = implode(' ' . PHP_EOL, $errors);

        $event->stop($message);

        $request = $this->requestStack->getCurrentRequest();
        $referer = $request?->headers->get('referer');
        if ($referer !== null) {
            $event->setResponse(new RedirectResponse($referer));
        }
    }

    private function getSlugError(Service $service): ?string
    {
        $slug = $service->getSlug();
        if (!$slug) {
            return null;
        }

        $repository = $this->entityManager->getRepository(Service::class);
        $existing = $repository->findOneBy(['slug' => $slug]);

        if ($existing && $existing->getId() !== $service->getId()) {
            return sprintf(
                'Ошибка! Slug "%s" уже занят услугой "%s"',
                $slug,
                $existing->getName(),
            );
        }

        return null;
    }

    private function getPositionError(Service $service): ?string
    {
        $position = $service->getPosition();
        $group = $service->getServiceGroup();

        if ($position === null || $group === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(Service::class);

        $existing = $repository->findOneBy([
            'position' => $position,
            'serviceGroup' => $group,
        ]);

        if ($existing && $existing->getId() !== $service->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята услугой "%s" в группе "%s".',
                $position,
                $existing->getName(),
                $existing->getServiceGroup()->getName()
            );
        }

        return null;
    }

    private function getInFooterError(Service $service): ?string
    {
        if (!$service->getInFooter()) {
            return null;
        }

        $repository = $this->entityManager->getRepository(Service::class);

        $count = (int) $repository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.in_footer = true')
            ->andWhere('s.id != :id')
            ->setParameter('id', $service->getId() ?? 0)
            ->getQuery()
            ->getSingleScalarResult();

        if ($count >= self::MAX_IN_FOOTER) {
            return sprintf(
                'Ошибка! Лимит в %d услуг для футера уже исчерпан.',
                self::MAX_IN_FOOTER
            );
        }

        return null;
    }
}
