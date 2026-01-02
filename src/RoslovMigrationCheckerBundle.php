<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle;

use Roslov\MigrationCheckerBundle\DependencyInjection\RoslovMigrationCheckerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use function dirname;

/**
 * Migration checker bundle.
 */
final class RoslovMigrationCheckerBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @inheritDoc
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new RoslovMigrationCheckerExtension();
    }
}
