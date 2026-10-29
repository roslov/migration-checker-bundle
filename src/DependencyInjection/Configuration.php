<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function assert;

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
        assert($rootNode instanceof ArrayNodeDefinition);

        $children = $rootNode->children();

        $children
            ->scalarNode('entity_manager')
                ->defaultValue('default')
                ->info('Doctrine entity manager name')
            ->end();

        $children
            ->scalarNode('dependency_factory')
                ->defaultValue('doctrine.migrations.dependency_factory')
                ->info('Doctrine Migrations dependency factory service ID')
            ->end();

        return $treeBuilder;
    }
}
