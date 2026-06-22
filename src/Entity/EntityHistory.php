<?php

namespace App\Entity;

use App\Repository\EntityHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity(repositoryClass: EntityHistoryRepository::class)]
#[ORM\Table(name: 'entity_history')]
#[ORM\Index(columns: ['entity', 'entity_id'], name: 'idx_entity_history_lookup')]
class EntityHistory implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'string', length: 255)]
    private string $entity = '';

    #[ORM\Column(name: 'entity_id', type: 'integer')]
    private int $entityId = 0;

    #[ORM\Column(name: 'entity_class', type: 'string', length: 512)]
    private string $entityClass = '';

    #[ORM\Column(type: 'json')]
    private array $fields = [];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function getEntity(): string { return $this->entity; }
    public function setEntity(string $entity): void { $this->entity = $entity; }

    public function getEntityId(): int { return $this->entityId; }
    public function setEntityId(int $entityId): void { $this->entityId = $entityId; }

    public function getEntityClass(): string { return $this->entityClass; }
    public function setEntityClass(string $entityClass): void { $this->entityClass = $entityClass; }

    public function getFields(): array { return $this->fields; }
    public function setFields(array $fields): void { $this->fields = $fields; }
}
