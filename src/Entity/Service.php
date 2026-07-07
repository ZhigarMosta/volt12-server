<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use App\EventListener\ServiceImageListener;

#[ORM\Entity]
#[ORM\Table(name: 'services')]
#[ORM\Index(columns: ['position'], name: 'idx_services_position')]
#[ORM\Index(columns: ['name'], name: 'idx_services_name')]
#[ORM\EntityListeners([ServiceImageListener::class])]
class Service implements ResourceInterface, TimestampableInterface
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
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $short_description = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(name: 'img_link', type: 'string', length: 2048, nullable: true)]
    private ?string $img_link = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imgAlt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imgTitle = null;

    private ?File $file = null;

    #[ORM\ManyToOne(targetEntity: ServiceGroup::class, inversedBy: 'services')]
    #[ORM\JoinColumn(name: 'service_group_id', referencedColumnName: 'id', nullable: false)]
    private ?ServiceGroup $serviceGroup = null;

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
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): void { $this->slug = $slug; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function getShortDescription(): ?string { return $this->short_description; }
    public function setShortDescription(?string $short_description): void { $this->short_description = $short_description; }
    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }
    public function getImgLink(): ?string { return $this->img_link; }
    public function setImgLink(string $img_link): void
    {
        $this->img_link = $img_link;
        $this->updatedAt = new \DateTime();
    }
    public function getImgAlt(): ?string { return $this->imgAlt; }
    public function setImgAlt(string $imgAlt): void { $this->imgAlt = $imgAlt; }
    public function getImgTitle(): ?string { return $this->imgTitle; }
    public function setImgTitle(string $imgTitle): void { $this->imgTitle = $imgTitle; }
    public function getServiceGroup(): ?ServiceGroup { return $this->serviceGroup; }
    public function setServiceGroup(?ServiceGroup $serviceGroup): void { $this->serviceGroup = $serviceGroup; }

    public function __toString(): string {
        return $this->name;
    }
}
