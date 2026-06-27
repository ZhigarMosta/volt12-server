<?php

namespace App\Twig;

use App\Entity\Cart;
use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Entity\Compare;
use App\Entity\Favorite;
use App\Entity\FeedbackFromMap;
use App\Entity\Service;
use App\Entity\User;
use App\Entity\UserOrder;
use App\Service\DatabaseBackupService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DashboardExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $em,
        private DatabaseBackupService $backupService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_dashboard_stats', [$this, 'getStats']),
            new TwigFunction('app_db_backups', [$this, 'getBackups']),
        ];
    }

    /**
     * Список файловых дампов БД (от свежего к старому).
     *
     * @return array<int, array{name: string, size: int, mtime: int}>
     */
    public function getBackups(): array
    {
        $backups = [];
        foreach ($this->backupService->listBackups() as $path) {
            $name = basename($path);
            // db-dump-20260627-171757.sql -> 20260627-171757
            $stamp = preg_match('/^db-dump-(\d{8}-\d{6})\.sql$/', $name, $m) ? $m[1] : null;
            if ($stamp === null) {
                continue;
            }
            $backups[] = [
                'name'  => $name,
                'stamp' => $stamp,
                'size'  => (int) (@filesize($path) ?: 0),
                'mtime' => (int) (@filemtime($path) ?: 0),
            ];
        }

        return $backups;
    }

    /**
     * Считаем напрямую через DBAL (raw SQL), чтобы не задеть глобальный
     * ORDER BY, который кто-то добавляет в DQL и ломает агрегаты в PostgreSQL.
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return [
            'orders'        => $this->count(UserOrder::class),
            'orders_last_7' => $this->ordersLast7(),
            'revenue'       => $this->revenue(),
            'users'         => $this->count(User::class),
            'products'      => $this->count(CatalogItem::class),
            'catalogs'      => $this->count(Catalog::class),
            'services'      => $this->count(Service::class),
            'feedback'      => $this->count(FeedbackFromMap::class),
            'carts'         => $this->count(Cart::class),
            'favorites'     => $this->count(Favorite::class),
            'compares'      => $this->count(Compare::class),
        ];
    }

    private function count(string $class): int
    {
        $table = $this->quotedTable($class);

        return (int) $this->em->getConnection()->fetchOne("SELECT COUNT(*) FROM {$table}");
    }

    private function revenue(): int
    {
        $meta = $this->em->getClassMetadata(UserOrder::class);
        $table = $this->quotedTable(UserOrder::class);
        $column = $this->quote($meta->getColumnName('totalPrice'));

        return (int) $this->em->getConnection()->fetchOne("SELECT COALESCE(SUM({$column}), 0) FROM {$table}");
    }

    private function ordersLast7(): int
    {
        $meta = $this->em->getClassMetadata(UserOrder::class);
        $table = $this->quotedTable(UserOrder::class);
        $column = $this->quote($meta->getColumnName('createdAt'));

        return (int) $this->em->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM {$table} WHERE {$column} >= :since",
            ['since' => (new \DateTime('-7 days'))->format('Y-m-d H:i:s')],
        );
    }

    private function quotedTable(string $class): string
    {
        return $this->quote($this->em->getClassMetadata($class)->getTableName());
    }

    private function quote(string $identifier): string
    {
        return $this->em->getConnection()->getDatabasePlatform()->quoteIdentifier($identifier);
    }
}
