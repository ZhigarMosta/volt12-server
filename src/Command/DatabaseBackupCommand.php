<?php

namespace App\Command;

use App\Service\DatabaseBackupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:db:backup',
    description: 'Создаёт дамп БД на диск и хранит не более N последних (по умолчанию 14).',
)]
class DatabaseBackupCommand extends Command
{
    private const DEFAULT_KEEP = 14;

    public function __construct(
        private DatabaseBackupService $backupService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('keep', null, InputOption::VALUE_REQUIRED, 'Сколько дампов хранить', (string) self::DEFAULT_KEEP)
            ->addOption('min-age-days', null, InputOption::VALUE_REQUIRED, 'Пропустить, если последний дамп моложе N дней (0 — всегда делать)', '0')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Игнорировать проверку возраста и сделать дамп принудительно');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keep   = max(1, (int) $input->getOption('keep'));
        $minAge = (float) $input->getOption('min-age-days');

        if (!$input->getOption('force') && $minAge > 0) {
            $age = $this->backupService->latestBackupAgeDays();
            if ($age !== null && $age < $minAge) {
                $io->note(sprintf('Последний дамп моложе %s дн. (%.1f дн.) — пропускаю.', $minAge, $age));

                return Command::SUCCESS;
            }
        }

        try {
            $path = $this->backupService->createBackup();
        } catch (\Throwable $e) {
            $io->error('Не удалось создать дамп: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Дамп создан: ' . $path);

        $deleted = $this->backupService->rotate($keep);
        if ($deleted !== []) {
            $io->writeln(sprintf('Удалено старых дампов (лимит %d): %s', $keep, implode(', ', $deleted)));
        }

        $io->writeln(sprintf('Всего дампов сейчас: %d', count($this->backupService->listBackups())));

        return Command::SUCCESS;
    }
}
