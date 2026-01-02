<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);
    }

    /**
     * @inheritDoc
     */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
