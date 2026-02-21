<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_characteristics')]
class CatalogCharacteristic implements ResourceInterface, TimestampableInterface
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->itemCharacteristics = new ArrayCollection();
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

    #[ORM\ManyToOne(targetEntity: Catalog::class)]
    #[ORM\JoinColumn(name: 'catalog_id', referencedColumnName: 'id', nullable: false)]
    private ?Catalog $catalog = null;

    #[ORM\OneToMany(mappedBy: 'catalogCharacteristic', targetEntity: CatalogItemCharacteristic::class)]
    private Collection $itemCharacteristics;
    public function getItemCharacteristics(): Collection
    {
        return $this->itemCharacteristics;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $product_code = '';

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(string $product_code): void { $this->product_code = $product_code; }
    public function getCatalog(): ?Catalog {
        return $this->catalog;
    }
    public function setCatalog(?Catalog $catalog): void { $this->catalog = $catalog; }

    public function __toString(): string {
        return $this->name;
    }
}
