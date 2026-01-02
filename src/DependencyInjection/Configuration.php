<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Migration checker configuration.
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * Tree main node name
     */
    private const NODE_NAME = 'roslov_migration_checker';

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder(self::NODE_NAME);
    }
}
