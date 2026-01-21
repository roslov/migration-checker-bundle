<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Command;

use InvalidArgumentException;
use Override;
use Psr\Log\LoggerAwareInterface;
use Roslov\MigrationChecker\Contract\MigrationCheckerInterface;
use Roslov\MigrationChecker\Contract\MigrationInterface;
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
     * @param MigrationCheckerInterface $migrationChecker Migration checker
     * @param MigrationInterface $migration Migration runner
     */
    public function __construct(
        private readonly MigrationCheckerInterface $migrationChecker,
        private readonly MigrationInterface $migration,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
        if ($this->migrationChecker instanceof LoggerAwareInterface) {
            $this->migrationChecker->setLogger($logger);
        }

        $this->migrationChecker->check();

        return Command::SUCCESS;
    }
}
