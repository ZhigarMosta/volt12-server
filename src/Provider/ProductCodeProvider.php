<?php

namespace App\Provider;

class ProductCodeProvider
{
    public const CODE_VOLT12 = 'volt12';
    public const CODE_ANY = 'any';
    public const CODE_PANDORA = 'pandora';

    /**
     * Возвращает массив для выбора кода продукта: ['Метка' => 'Значение']
     */
    public static function getAllProducts(): array
    {
        return [
            'Мастер вольт 12' => self::CODE_VOLT12,
            'Пандора' => self::CODE_PANDORA,
            'Любой' => self::CODE_ANY,
        ];
    }

}
