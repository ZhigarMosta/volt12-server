<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_items')]
class CatalogItem implements ResourceInterface, TimestampableInterface
{

    const POPULAR = true;
    const LIMIT_POPULAR = 12;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->characteristics = new ArrayCollection();
        $this->catalogItemImages = new ArrayCollection();
    }

    use TimestampableTrait;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'integer')]
    private ?int $price;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(targetEntity: Catalog::class)]
    #[ORM\JoinColumn(name: 'catalog_id', referencedColumnName: 'id', nullable: true)]
    #[Ignore]
    private ?Catalog $catalog = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $product_code = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $slug = '';

    #[ORM\Column(type: 'boolean')]
    private bool $is_new = false;

    #[ORM\Column(type: 'boolean')]
    private bool $is_popular = false;

    #[ORM\Column(type: 'boolean')]
    private bool $is_published = false;

    #[ORM\OneToMany(mappedBy: 'catalogItem', targetEntity: CatalogItemCharacteristic::class)]
    #[Ignore]
    private Collection $characteristics;
    public function getCharacteristics(): Collection
    {
        return $this->characteristics;
    }

    #[ORM\OneToMany(mappedBy: 'catalogItem', targetEntity: CatalogItemImage::class)]
    private Collection $catalogItemImages;
    public function getCatalogItemImages(): Collection
    {
        return $this->catalogItemImages;
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }

    public function getPrice(): int { return $this->price; }
    public function setPrice(int $price): void { $this->price = $price; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }

    public function getIsNew(): bool { return $this->is_new; }
    public function setIsNew( bool $is_new): void { $this->is_new = $is_new; }

    public function getIsPopular(): bool { return $this->is_popular; }
    public function setIsPopular( bool $is_popular): void { $this->is_popular = $is_popular; }

    public function getIsPublished(): bool { return $this->is_published; }
    public function setIsPublished( bool $is_published): void { $this->is_published = $is_published; }

    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(?string $product_code): void { $this->product_code = (string) $product_code; }

    public function getCatalog(): ?Catalog {
        return $this->catalog;
    }
    public function setCatalog(?Catalog $catalog): void { $this->catalog = $catalog; }
}
