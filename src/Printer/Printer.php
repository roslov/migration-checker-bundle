<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Printer;

use Roslov\MigrationChecker\Contract\PrinterInterface;
use Roslov\MigrationChecker\Contract\StateInterface;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;
use function preg_split;
use function str_starts_with;

/**
 * Prints schema state changes.
 */
final class Printer implements PrinterInterface
{
    /**
     * Color reset (default color)
     */
    private const COLOR_RESET = "\033[0m";

    /**
     * Color: red
     */
    private const COLOR_RED = "\033[31m";

    /**
     * Color: green
     */
    private const COLOR_GREEN = "\033[32m";

    /**
     * Color: cyan
     */
    private const COLOR_CYAN = "\033[36m";

    /**
     * Bold font
     */
    private const COLOR_BOLD = "\033[1m";

    /**
     * Output
     */
    private ?OutputInterface $output = null;

    /**
     * @inheritDoc
     */
    public function displayDiff(StateInterface $previousState, StateInterface $currentState): void
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder());
        $diff = $differ->diff($previousState->toString(), $currentState->toString());
        if ($this->output instanceof OutputInterface) {
            $this->output->write($this->colorizeUnifiedDiff($diff));
        } else {
            echo $this->colorizeUnifiedDiffAnsi($diff);
        }
    }

    /**
     * Sets the output.
     *
     * @param OutputInterface $output Output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Applies Symfony console tags to a unified diff string for visual enhancement.
     *
     * @param string $diff The unified diff string to be colorized
     *
     * @return string The colorized unified diff string
     */
    private function colorizeUnifiedDiff(string $diff): string
    {
        $out = [];
        foreach ((array) preg_split("/\r\n|\n|\r/", $diff) as $line) {
            $out[] = $this->colorizeLineSymfony((string) $line);
        }

        return implode(PHP_EOL, $out) . PHP_EOL;
    }

    /**
     * Colorizes a single line using Symfony console tags.
     *
     * @param string $line The line to be colorized
     *
     * @return string The colorized line
     */
    private function colorizeLineSymfony(string $line): string
    {
        if (str_starts_with($line, '+++ ') || str_starts_with($line, '--- ')) {
            return "<fg=cyan;options=bold>$line</>";
        }
        if (str_starts_with($line, '@@')) {
            return "<fg=cyan;options=bold>$line</>";
        }
        if (str_starts_with($line, '+')) {
            return "<fg=green>$line</>";
        }
        if (str_starts_with($line, '-')) {
            return "<fg=red>$line</>";
        }

        return $line;
    }

    /**
     * Applies ANSI color codes to a unified diff string for visual enhancement.
     *
     * @param string $diff The unified diff string to be colorized
     *
     * @return string The colorized unified diff string
     */
    // phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    private function colorizeUnifiedDiffAnsi(string $diff): string
    {
        $out = [];
        foreach ((array) preg_split("/\r\n|\n|\r/", $diff) as $line) {
            $line = (string) $line;
            if (str_starts_with($line, '+++ ') || str_starts_with($line, '--- ')) {
                $out[] = self::COLOR_BOLD . self::COLOR_CYAN . $line . self::COLOR_RESET;
            } elseif (str_starts_with($line, '@@')) {
                $out[] = self::COLOR_BOLD . self::COLOR_CYAN . $line . self::COLOR_RESET;
            } elseif (str_starts_with($line, '+')) {
                $out[] = self::COLOR_GREEN . $line . self::COLOR_RESET;
            } elseif (str_starts_with($line, '-')) {
                $out[] = self::COLOR_RED . $line . self::COLOR_RESET;
            } else {
                $out[] = $line;
            }
        }

        return implode(PHP_EOL, $out) . PHP_EOL;
    }
}
