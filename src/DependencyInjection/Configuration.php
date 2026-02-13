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
        $treeBuilder = new TreeBuilder(self::NODE_NAME);
        $rootNode = $treeBuilder->getRootNode();

        /** @phpstan-ignore-next-line */
        $rootNode
            ->children()
                ->scalarNode('entity_manager')
                    ->defaultValue('default')
                    ->info('Doctrine entity manager name')
                ->end()
                ->scalarNode('dependency_factory')
                    ->defaultValue('doctrine.migrations.dependency_factory')
                    ->info('Doctrine Migrations dependency factory service ID')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
