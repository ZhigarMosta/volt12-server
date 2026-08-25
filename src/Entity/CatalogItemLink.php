<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

/**
 * Общая база для табличек «товар — товар» (рекомендуемые, с этим покупают,
 * также может понадобиться). Конкретные сущности задают только таблицу
 * и уникальный индекс пары.
 */
#[ORM\MappedSuperclass]
abstract class CatalogItemLink implements ResourceInterface, TimestampableInterface
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    use TimestampableTrait;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    /** Товар, на странице которого показывается блок. */
    #[ORM\ManyToOne(targetEntity: CatalogItem::class)]
    #[ORM\JoinColumn(name: 'catalog_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CatalogItem $catalogItem = null;

    /** Товар, который показывается в блоке. */
    #[ORM\ManyToOne(targetEntity: CatalogItem::class)]
    #[ORM\JoinColumn(name: 'linked_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CatalogItem $linkedItem = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $position = null;

    public function getId(): ?int { return $this->id; }

    public function getCatalogItem(): ?CatalogItem { return $this->catalogItem; }
    public function setCatalogItem(?CatalogItem $catalogItem): void { $this->catalogItem = $catalogItem; }

    public function getLinkedItem(): ?CatalogItem { return $this->linkedItem; }
    public function setLinkedItem(?CatalogItem $linkedItem): void { $this->linkedItem = $linkedItem; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }

    /** Название блока для сообщений об ошибках в админке. */
    abstract public static function getBlockLabel(): string;
}
