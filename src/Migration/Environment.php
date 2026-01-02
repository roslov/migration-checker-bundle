<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Migration;

use Doctrine\Migrations\DependencyFactory;
use Roslov\MigrationChecker\Contract\EnvironmentInterface;

/**
 * Prepares the database for migration checks.
 */
final class Environment implements EnvironmentInterface
{
    /**
     * Constructor.
     *
     * @param DependencyFactory $dependencyFactory Dependency factory
     */
    public function __construct(private readonly DependencyFactory $dependencyFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
        $metadataStorage = $this->dependencyFactory->getMetadataStorage();
        $metadataStorage->ensureInitialized();
    }

    /**
     * @inheritDoc
     */
    public function cleanUp(): void
    {
        // No-op
    }
}
