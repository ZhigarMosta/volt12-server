<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * ER-диаграмма текущей БД: строится на лету из information_schema
 * и рендерится в браузере библиотекой mermaid.
 */
#[Route('/admin/tools')]
class ErDiagramController extends AbstractController
{
    private const TYPE_MAP = [
        'character varying' => 'varchar',
        'timestamp without time zone' => 'timestamp',
        'timestamp with time zone' => 'timestamptz',
        'double precision' => 'double',
    ];

    public function __construct(
        private Connection $connection,
    ) {}

    #[Route('/er-diagram', name: 'app_admin_er_diagram', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $withSylius = $request->query->getBoolean('sylius');

        $tables = $this->loadTables($withSylius);

        return $this->render('admin/tools/er_diagram.html.twig', [
            'mermaid' => $this->buildMermaid($tables),
            'withSylius' => $withSylius,
            'tableCount' => count($tables),
        ]);
    }

    /**
     * @return array<string, array{columns: array<int, array{name: string, type: string, pk: bool, fk: bool}>, fks: array<int, array{column: string, target: string}>}>
     */
    private function loadTables(bool $withSylius): array
    {
        $names = $this->connection->fetchFirstColumn(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename",
        );
        if (!$withSylius) {
            $names = array_values(array_filter(
                $names,
                static fn (string $t): bool => !str_starts_with($t, 'sylius_') || in_array($t, ['sylius_admin_user', 'sylius_migrations'], true),
            ));
        }
        $allowed = array_flip($names);

        $columns = $this->connection->fetchAllAssociative(
            "SELECT table_name, column_name, data_type
             FROM information_schema.columns
             WHERE table_schema = 'public'
             ORDER BY table_name, ordinal_position",
        );

        $pks = $this->connection->fetchAllAssociative(
            "SELECT tc.table_name, kcu.column_name
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON kcu.constraint_name = tc.constraint_name AND kcu.table_schema = tc.table_schema
             WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.table_schema = 'public'",
        );
        $pkMap = [];
        foreach ($pks as $pk) {
            $pkMap[$pk['table_name']][$pk['column_name']] = true;
        }

        $fks = $this->connection->fetchAllAssociative(
            "SELECT tc.table_name AS src, kcu.column_name AS src_column, ccu.table_name AS dst
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON kcu.constraint_name = tc.constraint_name AND kcu.table_schema = tc.table_schema
             JOIN information_schema.constraint_column_usage ccu
               ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema
             WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public'",
        );
        $fkMap = [];
        $fkColumns = [];
        foreach ($fks as $fk) {
            if (!isset($allowed[$fk['src']]) || !isset($allowed[$fk['dst']])) {
                continue;
            }
            $fkMap[$fk['src']][] = ['column' => $fk['src_column'], 'target' => $fk['dst']];
            $fkColumns[$fk['src']][$fk['src_column']] = true;
        }

        $tables = [];
        foreach ($columns as $col) {
            $table = $col['table_name'];
            if (!isset($allowed[$table])) {
                continue;
            }
            $tables[$table]['columns'][] = [
                'name' => $col['column_name'],
                'type' => self::TYPE_MAP[$col['data_type']] ?? str_replace(' ', '_', (string) $col['data_type']),
                'pk' => isset($pkMap[$table][$col['column_name']]),
                'fk' => isset($fkColumns[$table][$col['column_name']]),
            ];
            $tables[$table]['fks'] ??= [];
        }
        foreach ($fkMap as $table => $list) {
            if (isset($tables[$table])) {
                $tables[$table]['fks'] = $list;
            }
        }

        return $tables;
    }

    /**
     * @param array<string, array{columns: array<int, array{name: string, type: string, pk: bool, fk: bool}>, fks: array<int, array{column: string, target: string}>}> $tables
     */
    private function buildMermaid(array $tables): string
    {
        $lines = ['erDiagram'];

        foreach ($tables as $name => $table) {
            $lines[] = sprintf('    %s {', $name);
            foreach ($table['columns'] as $col) {
                $marks = trim(($col['pk'] ? 'PK' : '') . ($col['fk'] ? ($col['pk'] ? ',FK' : 'FK') : ''));
                $lines[] = rtrim(sprintf('        %s %s %s', $col['type'], $col['name'], $marks));
            }
            $lines[] = '    }';
        }

        foreach ($tables as $name => $table) {
            foreach ($table['fks'] as $fk) {
                $lines[] = sprintf('    %s }o--|| %s : "%s"', $name, $fk['target'], $fk['column']);
            }
        }

        return implode("\n", $lines);
    }
}
