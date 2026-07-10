<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Проверка целостности картинок: у каких сущностей в БД указан файл изображения,
 * которого физически нет на диске. Используется после восстановления БД из дампа
 * (дамп содержит только данные, файлы картинок в него не входят).
 */
class ImageIntegrityService
{
    public function __construct(
        private Connection $connection,
        private string $publicDir,
    ) {}

    /**
     * @return array<int, array{entity: string, id: int, name: string, path: string, reason: string}>
     */
    public function findMissingImages(): array
    {
        $missing = [];

        $sources = [
            [
                'entity' => 'Изображение товара',
                'sql' => 'SELECT i.id, i.img_link AS path, COALESCE(ci.name, \'товар #\' || COALESCE(i.catalog_item_id::text, \'?\')) AS name
                          FROM catalog_item_images i
                          LEFT JOIN catalog_items ci ON ci.id = i.catalog_item_id',
            ],
            [
                'entity' => 'Каталог',
                'sql' => 'SELECT id, imglink AS path, name FROM catalogs',
            ],
            [
                'entity' => 'Услуга',
                'sql' => 'SELECT id, img_link AS path, name FROM services',
            ],
        ];

        foreach ($sources as $source) {
            foreach ($this->connection->fetchAllAssociative($source['sql']) as $row) {
                $path = trim((string) ($row['path'] ?? ''));
                if ($path === '') {
                    continue;
                }

                // Абсолютные URL не проверяем — файл не на нашем диске
                if (preg_match('~^https?://~i', $path)) {
                    continue;
                }

                $file = $this->publicDir . '/' . ltrim($path, '/');
                $reason = null;
                if (!is_file($file)) {
                    $reason = 'файл отсутствует';
                } elseif ((int) filesize($file) === 0) {
                    $reason = 'файл пустой (0 байт)';
                }

                if ($reason !== null) {
                    $missing[] = [
                        'entity' => $source['entity'],
                        'id' => (int) $row['id'],
                        'name' => (string) $row['name'],
                        'path' => $path,
                        'reason' => $reason,
                    ];
                }
            }
        }

        return $missing;
    }

    /**
     * Сколько всего записей с картинками (для процента целостности в отчёте).
     */
    public function countImageRecords(): int
    {
        return (int) $this->connection->fetchOne(
            "SELECT
                (SELECT COUNT(*) FROM catalog_item_images WHERE img_link <> '')
              + (SELECT COUNT(*) FROM catalogs WHERE imglink IS NOT NULL AND imglink <> '')
              + (SELECT COUNT(*) FROM services WHERE img_link IS NOT NULL AND img_link <> '')",
        );
    }
}
