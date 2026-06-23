<?php

namespace App\EventListener;

use App\Entity\EntityHistory;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntityHistorySubscriber
{
    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $historyMeta = $em->getClassMetadata(EntityHistory::class);

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$this->isHistoryEnabled($entity)) {
                continue;
            }

            $entityClass = get_class($entity);
            $classMetadata = $em->getClassMetadata($entityClass);
            $changeSet = $uow->getEntityChangeSet($entity);
            $fields = $this->captureState($entity, $classMetadata, $changeSet);

            $history = new EntityHistory();
            $history->setEntity($this->resolveEntityType($entityClass));
            $history->setEntityId((int) $entity->getId());
            $history->setEntityClass($entityClass);
            $history->setFields($fields);

            $em->persist($history);
            $uow->computeChangeSet($historyMeta, $history);
        }
    }

    private function isHistoryEnabled(object $entity): bool
    {
        return method_exists($entity, 'hasHistory') && $entity->hasHistory();
    }

    private function captureState(object $entity, ClassMetadata $meta, array $changeSet): array
    {
        $fields = [];

        foreach ($meta->getFieldNames() as $fieldName) {
            if (isset($changeSet[$fieldName])) {
                $value = $changeSet[$fieldName][0];
            } else {
                $value = $meta->getFieldValue($entity, $fieldName);
            }
            $fields[$fieldName] = $this->normalizeValue($value);
        }

        foreach ($meta->getAssociationNames() as $assocName) {
            if (!$meta->isSingleValuedAssociation($assocName)) {
                continue;
            }
            if (isset($changeSet[$assocName])) {
                $assoc = $changeSet[$assocName][0];
            } else {
                $assoc = $meta->getFieldValue($entity, $assocName);
            }
            $fields[$assocName . '_id'] = ($assoc !== null && method_exists($assoc, 'getId'))
                ? $assoc->getId()
                : null;
        }

        return $fields;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_object($value)) {
            return null;
        }
        return $value;
    }

    private function resolveEntityType(string $entityClass): string
    {
        $shortName = (new \ReflectionClass($entityClass))->getShortName();
        return mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
    }
}
