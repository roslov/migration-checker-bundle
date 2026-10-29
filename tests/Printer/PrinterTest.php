<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Tests\Printer;

use PHPUnit\Framework\TestCase;
use Roslov\MigrationChecker\Contract\StateInterface;
use Roslov\MigrationCheckerBundle\Printer\Printer;
use Symfony\Component\Console\Output\OutputInterface;

use function ob_get_clean;
use function ob_start;
use function str_contains;

/**
 * Tests for the schema diff printer.
 */
final class PrinterTest extends TestCase
{
    /**
     * Tests displaying diff with Symfony console output.
     */
    public function testDisplayDiffWithConsoleOutput(): void
    {
        $printer = new Printer();
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    static fn (string $diff): bool => str_contains($diff, '<fg=cyan;options=bold>--- Original</>')
                        && str_contains($diff, '<fg=cyan;options=bold>+++ New</>')
                        && str_contains($diff, '<fg=red>-line1</>')
                        && str_contains($diff, '<fg=green>+line2</>'),
                ),
            );
        $printer->setOutput($output);

        $previousState = $this->createMock(StateInterface::class);
        $previousState->method('toString')->willReturn("line1\n");

        $currentState = $this->createMock(StateInterface::class);
        $currentState->method('toString')->willReturn("line2\n");

        $printer->displayDiff($previousState, $currentState);
    }

    /**
     * Tests displaying diff with ANSI color codes when no output is set.
     */
    public function testDisplayDiffWithAnsiOutput(): void
    {
        $printer = new Printer();

        $previousState = $this->createMock(StateInterface::class);
        $previousState->method('toString')->willReturn("line1\n");

        $currentState = $this->createMock(StateInterface::class);
        $currentState->method('toString')->willReturn("line2\n");

        ob_start();
        $printer->displayDiff($previousState, $currentState);
        $result = (string) ob_get_clean();

        $this->assertStringContainsString("\033[1m\033[36m--- Original\033[0m", $result);
        $this->assertStringContainsString("\033[1m\033[36m+++ New\033[0m", $result);
        $this->assertStringContainsString("\033[31m-line1\033[0m", $result);
        $this->assertStringContainsString("\033[32m+line2\033[0m", $result);
    }
}
