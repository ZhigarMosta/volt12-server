<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use App\EventListener\ServiceGroupImageListener;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'service_groups')]
#[ORM\EntityListeners([ServiceGroupImageListener::class])]
class ServiceGroup implements ResourceInterface, TimestampableInterface
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->services = new ArrayCollection();
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

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $product_code = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(name: 'img_link', type: 'string', length: 2048, nullable: true)]
    private ?string $img_link = null;

    private ?File $file = null;

    #[ORM\OneToMany(mappedBy: 'serviceGroup', targetEntity: Service::class)]
    #[Ignore]
    private Collection $services;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(?string $product_code): void { $this->product_code = (string) $product_code; }
    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }
    public function getImgLink(): ?string { return $this->img_link; }
    public function setImgLink(string $img_link): void
    {
        $this->img_link = $img_link;
        $this->updatedAt = new \DateTime();
    }
    public function getServices(): Collection { return $this->services; }

    public function __toString(): string {
        return $this->name;
    }
}
