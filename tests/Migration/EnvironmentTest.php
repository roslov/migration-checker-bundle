<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Tests\Migration;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use PHPUnit\Framework\TestCase;
use Roslov\MigrationCheckerBundle\Migration\Environment;

/**
 * Tests for the migration environment.
 */
final class EnvironmentTest extends TestCase
{
    /**
     * Tests that the environment is prepared.
     */
    public function testPrepare(): void
    {
        $metadataStorage = $this->createMock(MetadataStorage::class);
        $metadataStorage->expects($this->once())
            ->method('ensureInitialized');

        $dependencyFactory = $this->createMock(DependencyFactory::class);
        $dependencyFactory->expects($this->once())
            ->method('getMetadataStorage')
            ->willReturn($metadataStorage);

        $environment = new Environment($dependencyFactory);
        $environment->prepare();
    }

    /**
     * Tests that the environment is cleaned up.
     */
    public function testCleanUp(): void
    {
        $dependencyFactory = $this->createMock(DependencyFactory::class);
        $environment = new Environment($dependencyFactory);
        $environment->cleanUp();
        $this->expectNotToPerformAssertions();
    }
}
