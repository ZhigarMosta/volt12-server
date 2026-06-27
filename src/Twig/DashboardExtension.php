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
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DashboardExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_dashboard_stats', [$this, 'getStats']),
        ];
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
