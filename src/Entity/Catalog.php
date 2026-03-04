<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'catalogs')]
class Catalog implements ResourceInterface, TimestampableInterface
{
    const POPULAR = true;
    const LIMIT_POPULAR = 3;
    const FIRST_POPULAR = 1;

    use TimestampableTrait;
    public function __construct()
    {
        $this->characteristics = new ArrayCollection();
        $this->catalogItems = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $slug = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $product_code = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $is_popular = false;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }

    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(?string $product_code): void { $this->product_code = (string) $product_code; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }

    public function getIsPopular(): bool { return $this->is_popular; }
    public function setIsPopular( bool $is_popular): void { $this->is_popular = $is_popular; }

    #[ORM\OneToMany(mappedBy: 'catalog', targetEntity: CatalogCharacteristic::class)]
    #[Ignore]
    private Collection $characteristics;

    #[ORM\OneToMany(mappedBy: 'catalog', targetEntity: CatalogItem::class)]
    #[Ignore]
    private Collection $catalogItems;
    public function getCharacteristics(): Collection
    {
        return $this->characteristics;
    }

    #[ORM\OneToMany(mappedBy: 'catalog', targetEntity: CatalogGroup::class)]
    #[Ignore]
    private Collection $groups;
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function getCatalogItems(): Collection
    {
        return $this->catalogItems;
    }

    public function __toString(): string {
        return $this->name;
    }
}
