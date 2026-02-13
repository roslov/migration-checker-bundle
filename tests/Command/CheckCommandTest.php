<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Tests\Command;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Roslov\MigrationChecker\Contract\MigrationCheckerInterface;
use Roslov\MigrationChecker\Contract\MigrationInterface;
use Roslov\MigrationChecker\Contract\PrinterInterface;
use Roslov\MigrationCheckerBundle\Command\CheckCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests for the migration check command.
 */
final class CheckCommandTest extends TestCase
{
    /**
     * Tests that the command fails in a non-test environment.
     */
    public function testExecuteFailsInNonTestEnvironment(): void
    {
        $migrationChecker = $this->createMock(MigrationCheckerInterface::class);
        $migration = $this->createMock(MigrationInterface::class);
        $printer = $this->createMock(PrinterInterface::class);
        $command = new CheckCommand($migrationChecker, $migration, $printer);
        $command->addOption('env', null, InputOption::VALUE_REQUIRED);

        $commandTester = new CommandTester($command);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This command can run only in the test environment. Use option --env=test');

        $commandTester->execute(['--env' => 'dev']);
    }

    /**
     * Tests that the command execution is successful.
     */
    public function testExecuteSuccess(): void
    {
        $migrationChecker = $this->createMock(MigrationCheckerInterface::class);
        $migration = $this->createMock(MigrationInterface::class);
        $printer = $this->createMock(PrinterInterface::class);

        $migrationChecker->expects($this->once())
            ->method('check');

        $command = new CheckCommand($migrationChecker, $migration, $printer);
        $command->addOption('env', null, InputOption::VALUE_REQUIRED);

        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['--env' => 'test']);

        $this->assertSame(0, $exitCode);
    }
}
