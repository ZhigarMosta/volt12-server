<?php

namespace App\Entity;

use App\Repository\FeedbackFromMapRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: FeedbackFromMapRepository::class)]
#[ORM\Table(name: 'feedback_from_map')]
class FeedbackFromMap implements ResourceInterface, TimestampableInterface
{

    const YANDEX = 'yandex';
    const DOUBLE_GIS = '2gis';

    const ALL_MAP = [
        'Яндекс' => self::YANDEX,
        '2gis' => self::DOUBLE_GIS,
    ];
    const COUNT_STAR = [
        '0' => 0,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5
    ];

    const LIMIT_MAIN = 12;

    use TimestampableTrait;
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $user_name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $map = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $product_code = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $star_count = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: false)]
    private string $message = '';

    #[ORM\Column(type: 'string', length: 2048, nullable: false)]
    #[Assert\Url(message: 'Поле feedback_link должно быть валидной ссылкой (http://...)')]
    private string $feedback_link = '';
    public function getId(): ?int { return $this->id; }

    public function getUserName(): string { return $this->user_name; }
    public function setUserName(string $userName): void { $this->user_name = $userName; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): void { $this->message = $message; }

    public function getProductCode(): string { return $this->product_code; }
    public function setProductCode(?string $product_code): void { $this->product_code = (string) $product_code; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): void { $this->position = $position; }

    public function getMap(): string { return $this->map; }
    public function setMap( ?string $map): void { $this->map = (string) $map; }

    public function getStarCount(): ?int { return $this->star_count; }
    public function setStarCount(?int $starCount): void { $this->star_count = $starCount; }

    public function getFeedbackLink(): string { return $this->feedback_link; }
    public function setFeedbackLink( string $feedbackLink): void { $this->feedback_link = $feedbackLink; }
}
