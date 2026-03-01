<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use App\EventListener\CatalogItemImageListener;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_item_images')]
#[ORM\EntityListeners([CatalogItemImageListener::class])]
class CatalogItemImage implements ResourceInterface, TimestampableInterface
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
    private string $alt = '';
    #[ORM\Column(type: 'string', length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'integer')]
    private ?int $position = null;

    #[ORM\ManyToOne(targetEntity: CatalogItem::class)]
    #[ORM\JoinColumn(name: 'catalog_item_id', referencedColumnName: 'id', nullable: true)]
    #[Ignore]
    private ?CatalogItem $catalogItem = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: false)]
    private string $img_link = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $product_code = '';

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

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }

    public function getAlt(): string { return $this->alt; }
    public function setAlt(string $alt): void { $this->alt = $alt; }
    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }

    public function getImgLink(): string { return $this->img_link; }
    public function setImgLink(string $img_link): void { $this->img_link = $img_link; }

    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(?string $product_code): void { $this->product_code = (string) $product_code; }

    public function getCatalogItem(): ?CatalogItem {
        return $this->catalogItem;
    }
    public function setCatalogItem(?CatalogItem $catalogItem): void { $this->catalogItem = $catalogItem; }
}
