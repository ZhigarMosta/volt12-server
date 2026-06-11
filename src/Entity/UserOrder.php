<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'user_orders')]
class UserOrder implements ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'new';

    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    private string $firstName = '';

    #[ORM\Column(name: 'last_name', type: 'string', length: 255)]
    private string $lastName = '';

    #[ORM\Column(type: 'string', length: 50)]
    private string $phone = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $email = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $house = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $entrance = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $apartment = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $city = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $region = '';

    #[ORM\Column(name: 'postal_code', type: 'string', length: 20)]
    private string $postalCode = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(name: 'total_price', type: 'integer')]
    private int $totalPrice = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: UserOrderItem::class, cascade: ['persist', 'remove'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): void { $this->user = $user; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }

    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }

    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $phone): void { $this->phone = $phone; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getStreet(): ?string { return $this->street; }
    public function setStreet(?string $street): void { $this->street = $street; }

    public function getHouse(): ?string { return $this->house; }
    public function setHouse(?string $house): void { $this->house = $house; }

    public function getEntrance(): ?string { return $this->entrance; }
    public function setEntrance(?string $entrance): void { $this->entrance = $entrance; }

    public function getApartment(): ?string { return $this->apartment; }
    public function setApartment(?string $apartment): void { $this->apartment = $apartment; }

    public function getCity(): string { return $this->city; }
    public function setCity(string $city): void { $this->city = $city; }

    public function getRegion(): string { return $this->region; }
    public function setRegion(string $region): void { $this->region = $region; }

    public function getPostalCode(): string { return $this->postalCode; }
    public function setPostalCode(string $postalCode): void { $this->postalCode = $postalCode; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): void { $this->comment = $comment; }

    public function getTotalPrice(): int { return $this->totalPrice; }
    public function setTotalPrice(int $totalPrice): void { $this->totalPrice = $totalPrice; }

    public function getItems(): Collection { return $this->items; }

    public function addItem(UserOrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
    }
}
