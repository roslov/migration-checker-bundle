<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Migration checker extension.
 */
final class RoslovMigrationCheckerExtension extends Extension
{
    /**
     * Alias
     */
    private const ALIAS = 'roslov_migration_checker';

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $entityManagerServiceId = sprintf('doctrine.orm.%s_entity_manager', $config['entity_manager']);
        $dependencyFactoryServiceId = $config['dependency_factory'];

        $container->getDefinition('roslov_migration_checker.query')
            ->setArgument(0, new Reference($entityManagerServiceId));

        $container->getDefinition('roslov_migration_checker.environment')
            ->setArgument(0, new Reference($dependencyFactoryServiceId));

        $container->getDefinition('roslov_migration_checker.migration')
            ->setArgument(0, new Reference($dependencyFactoryServiceId));
    }

    /**
     * @inheritDoc
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
