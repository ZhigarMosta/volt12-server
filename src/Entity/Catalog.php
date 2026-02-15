<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
#[ORM\Entity]
#[ORM\Table(name: 'catalogs')]
class Catalog implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;
    public function __construct()
    {
        $this->characteristics = new ArrayCollection();
        $this->catalogItems = new ArrayCollection();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $product_code = '';

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(string $product_code): void { $this->product_code = $product_code; }

    #[ORM\OneToMany(mappedBy: 'catalog', targetEntity: CatalogCharacteristic::class)]
    private Collection $characteristics;

    #[ORM\OneToMany(mappedBy: 'catalog', targetEntity: CatalogItem::class)]
    private Collection $catalogItems;
    public function getCharacteristics(): Collection
    {
        return $this->characteristics;
    }

    public function getCatalogItems(): Collection
    {
        return $this->catalogItems;
    }

    public function __toString(): string {
        return $this->name;
    }
}
