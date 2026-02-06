<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'group_characteristics')]
class GroupCharacteristic implements ResourceInterface, TimestampableInterface
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
    private ?int $id = null;


    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = '';

    #[ORM\ManyToOne(targetEntity: Catalog::class)]
    #[ORM\JoinColumn(name: 'catalog_id', referencedColumnName: 'id', nullable: false, onDelete: 'SET NULL')]
    private ?Catalog $catalog = null;

    #[ORM\ManyToOne(targetEntity: CatalogCharacteristic::class)]
    #[ORM\JoinColumn(name: 'catalog_characteristic_id', referencedColumnName: 'id', nullable: false, onDelete: 'SET NULL')]
    private ?CatalogCharacteristic $catalogCharacteristic = null;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getCatalog(): ?Catalog {
        return $this->catalog;
    }
    public function setCatalog(?Catalog $catalog): void { $this->catalog = $catalog; }

    public function getCatalogCharacteristic(): ?CatalogCharacteristic {
        return $this->catalogCharacteristic;
    }
    public function setCatalogCharacteristic(?CatalogCharacteristic $catalogCharacteristic): void { $this->catalogCharacteristic = $catalogCharacteristic; }

    public function __toString(): string {
        return $this->name;
    }
}
