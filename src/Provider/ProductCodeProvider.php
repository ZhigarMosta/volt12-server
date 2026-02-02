<?php

namespace App\Provider;

class ProductCodeProvider
{
    public const CODE_VOLT12 = 'volt12';
    public const CODE_ANY = 'any';

    /**
     * Возвращает массив для выбора кода продукта: ['Метка' => 'Значение']
     */
    public static function getAllProducts(): array
    {
        return [
            'Мастер вольт 12' => self::CODE_VOLT12,
            'Любой' => self::CODE_ANY,
        ];
    }

}
