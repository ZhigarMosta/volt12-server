<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use App\EventListener\CatalogItemListener;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
#[ORM\Entity]
#[ORM\Table(name: 'catalog_items')]
#[ORM\EntityListeners([CatalogItemListener::class])]
class CatalogItem implements ResourceInterface, TimestampableInterface
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
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $price = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(targetEntity: Catalog::class)]
    #[ORM\JoinColumn(name: 'catalog_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Catalog $catalog = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: false)]
    private string $img_link = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $product_code = '';

    #[ORM\Column(type: 'boolean')]
    private bool $is_new = false;

    #[ORM\Column(type: 'boolean')]
    private bool $is_popular = false;

    private ?File $file = null;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
        if ($file) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getPrice(): int { return $this->price; }
    public function setPrice(int $price): void { $this->price = $price; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }

    public function getIsNew(): bool { return $this->is_new; }
    public function setIsNew( bool $is_new): void { $this->is_new = $is_new; }

    public function getIsPopular(): bool { return $this->is_popular; }
    public function setIsPopular( bool $is_popular): void { $this->is_popular = $is_popular; }

    public function getImgLink(): string { return $this->img_link; }
    public function setImgLink(string $img_link): void { $this->img_link = $img_link; }

    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(string $product_code): void { $this->product_code = $product_code; }

    public function getCatalog(): ?Catalog {
        return $this->catalog;
    }
    public function setCatalog(?Catalog $catalog): void { $this->catalog = $catalog; }
}
