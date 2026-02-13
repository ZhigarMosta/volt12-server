<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CatalogItemsRequestDto
{
    #[Assert\NotBlank(message: 'Поле category_id обязательно для заполнения.')]
    #[Assert\Type(type: 'integer', message: 'Поле category_id должно быть числом.')]
    #[Assert\Positive(message: 'Поле category_id должен быть положительным числом.')]
    public ?int $categoryId = null;

    public function __construct(array $data = [])
    {
        $this->categoryId = $data['category_id'] ?? null;
    }
}
