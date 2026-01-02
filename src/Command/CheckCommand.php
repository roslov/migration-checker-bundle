<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Command;

use InvalidArgumentException;
use Override;
use Roslov\MigrationChecker\Contract\EnvironmentInterface;
use Roslov\MigrationChecker\Contract\MigrationInterface;
use Roslov\MigrationChecker\Contract\PrinterInterface;
use Roslov\MigrationChecker\Contract\QueryInterface;
use Roslov\MigrationChecker\Db\MySqlDump;
use Roslov\MigrationChecker\Db\SchemaStateComparer;
use Roslov\MigrationChecker\MigrationChecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command: Checks migrations
 */
#[AsCommand('migration-checker:check', 'Checks migrations.')]
final class CheckCommand extends Command
{
    /**
     * Constructor.
     *
     * @param EnvironmentInterface $environment Environment preparer
     * @param MigrationInterface $migration Migration handler
     * @param PrinterInterface $printer State printer
     * @param QueryInterface $query Query fetcher
     */
    public function __construct(
        private readonly EnvironmentInterface $environment,
        private readonly MigrationInterface $migration,
        private readonly PrinterInterface $printer,
        private readonly QueryInterface $query,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = $input->getOption('env');
        if ($environment !== 'test') {
            throw new InvalidArgumentException(
                'This command can run only in the test environment. Use option --env=test',
            );
        }

        $logger = new ConsoleLogger($output);
        $this->migration->setLogger($logger);

        $dump = new MySqlDump($this->query);
        $comparer = new SchemaStateComparer($dump);

        $checker = new MigrationChecker(
            $logger,
            $this->environment,
            $this->migration,
            $comparer,
            $this->printer,
        );

        $checker->check();

        return Command::SUCCESS;
    }
}
