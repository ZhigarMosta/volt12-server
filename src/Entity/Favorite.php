<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
#[ORM\Table(name: 'favorites')]
#[ORM\UniqueConstraint(name: 'uniq_favorites_user_catalog_item', columns: ['user_id', 'catalog_item_id'])]
class Favorite implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: CatalogItem::class)]
    #[ORM\JoinColumn(name: 'catalog_item_id', referencedColumnName: 'id', nullable: false)]
    private ?CatalogItem $catalogItem = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): void { $this->user = $user; }

    public function getCatalogItem(): ?CatalogItem { return $this->catalogItem; }
    public function setCatalogItem(?CatalogItem $catalogItem): void { $this->catalogItem = $catalogItem; }
}
