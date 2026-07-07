<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user_order_items')]
#[ORM\Index(columns: ['order_id'], name: 'idx_user_order_items_order_id')]
#[ORM\Index(columns: ['catalog_item_id'], name: 'idx_user_order_items_catalog_item_id')]
class UserOrderItem implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: UserOrder::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private UserOrder $order;

    #[ORM\ManyToOne(targetEntity: CatalogItem::class)]
    #[ORM\JoinColumn(name: 'catalog_item_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CatalogItem $catalogItem = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'integer')]
    private int $price = 0;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    #[ORM\Column(name: 'total_price', type: 'integer')]
    private int $totalPrice = 0;

    public function getId(): ?int { return $this->id; }

    public function getOrder(): UserOrder { return $this->order; }
    public function setOrder(UserOrder $order): void { $this->order = $order; }

    public function getCatalogItem(): ?CatalogItem { return $this->catalogItem; }
    public function setCatalogItem(?CatalogItem $catalogItem): void { $this->catalogItem = $catalogItem; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getPrice(): int { return $this->price; }
    public function setPrice(int $price): void { $this->price = $price; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }

    public function getTotalPrice(): int { return $this->totalPrice; }
    public function setTotalPrice(int $totalPrice): void { $this->totalPrice = $totalPrice; }
}
