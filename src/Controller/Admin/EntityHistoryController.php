<?php

namespace App\Controller\Admin;

use App\Entity\EntityHistory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin/entity-history')]
class EntityHistoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {}

    #[Route('/{id}/show', name: 'app_admin_entity_history_show', methods: ['GET'])]
    public function show(EntityHistory $history): Response
    {
        return $this->render('admin/entity_history/show.html.twig', [
            'history' => $history,
        ]);
    }

    #[Route('/{id}/restore', name: 'app_admin_entity_history_restore', methods: ['POST'])]
    public function restore(EntityHistory $history, Request $request): RedirectResponse
    {
        $tokenId = 'restore_' . $history->getId();
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($tokenId, $request->request->get('_token')))) {
            $this->addFlash('error', 'Неверный CSRF-токен.');
            return $this->redirectToReferer($request);
        }

        $entityClass = $history->getEntityClass();

        if (!class_exists($entityClass)) {
            $this->addFlash('error', 'Класс сущности не найден: ' . $entityClass);
            return $this->redirectToReferer($request);
        }

        $entity = $this->em->find($entityClass, $history->getEntityId());

        if ($entity === null) {
            $this->addFlash('error', 'Сущность не найдена (ID: ' . $history->getEntityId() . ').');
            return $this->redirectToReferer($request);
        }

        $this->applyFields($entity, $entityClass, $history->getFields());

        $this->em->flush();

        $this->addFlash('success', 'Версия успешно восстановлена.');
        return $this->redirectToReferer($request);
    }

    private function applyFields(object $entity, string $entityClass, array $fields): void
    {
        $meta = $this->em->getClassMetadata($entityClass);

        foreach ($meta->getFieldNames() as $fieldName) {
            if ($fieldName === 'id' || !array_key_exists($fieldName, $fields)) {
                continue;
            }

            $value = $fields[$fieldName];

            if ($value !== null) {
                $type = $meta->getTypeOfField($fieldName);
                $value = $this->castValue($value, $type);
            }

            $meta->setFieldValue($entity, $fieldName, $value);
        }

        foreach ($meta->getAssociationNames() as $assocName) {
            if (!$meta->isSingleValuedAssociation($assocName)) {
                continue;
            }

            $assocIdKey = $assocName . '_id';
            if (!array_key_exists($assocIdKey, $fields)) {
                continue;
            }

            $assocId = $fields[$assocIdKey];
            if ($assocId !== null) {
                $assocClass = $meta->getAssociationTargetClass($assocName);
                $assocEntity = $this->em->find($assocClass, $assocId);
                $meta->setFieldValue($entity, $assocName, $assocEntity);
            } else {
                $meta->setFieldValue($entity, $assocName, null);
            }
        }

        $uow = $this->em->getUnitOfWork();
        $uow->recomputeSingleEntityChangeSet($meta, $entity);
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer', 'smallint', 'bigint' => (int) $value,
            'float', 'decimal' => (float) $value,
            'boolean' => (bool) $value,
            'datetime', 'datetime_immutable' => new \DateTime($value),
            default => $value,
        };
    }

    private function redirectToReferer(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        if ($referer) {
            return new RedirectResponse($referer);
        }
        return $this->redirectToRoute('app_admin_entity_history_index');
    }
}
