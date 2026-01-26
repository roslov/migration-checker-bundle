<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Migration;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Version\Direction;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use Roslov\MigrationChecker\Contract\MigrationInterface;

use function count;

/**
 * Handles database migrations.
 */
final class Migration implements MigrationInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Constructor.
     *
     * @param DependencyFactory $dependencyFactory Dependency factory
     * @param LoggerInterface|null $logger Logger
     */
    public function __construct(
        private readonly DependencyFactory $dependencyFactory,
        ?LoggerInterface $logger,
    ) {
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @inheritDoc
     */
    public function canUp(): bool
    {
        $statusCalculator = $this->dependencyFactory->getMigrationStatusCalculator();
        $newMigrations = $statusCalculator->getNewMigrations();

        return count($newMigrations) > 0;
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $metadataStorage = $this->dependencyFactory->getMetadataStorage();
        $metadataStorage->ensureInitialized();
        $statusCalculator = $this->dependencyFactory->getMigrationStatusCalculator();
        $newMigrations = $statusCalculator->getNewMigrations();
        $firstMigrationPlan = $newMigrations->getItems()[0];
        $version = $firstMigrationPlan->getVersion();
        $planCalculator = $this->dependencyFactory->getMigrationPlanCalculator();
        $plan = $planCalculator->getPlanForVersions([$version], Direction::UP);
        $this->resetMigration($plan->getFirst()->getMigration());
        $migrator = $this->dependencyFactory->getMigrator();
        $this->getLogger()->info(sprintf('Applying the up migration "%s"...', $version));
        $migrator->migrate($plan, new MigratorConfiguration());
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $metadataStorage = $this->dependencyFactory->getMetadataStorage();
        $executedMigrations = $metadataStorage->getExecutedMigrations();
        $version = $executedMigrations->getLast()->getVersion();
        $planCalculator = $this->dependencyFactory->getMigrationPlanCalculator();
        $plan = $planCalculator->getPlanForVersions([$version], Direction::DOWN);
        $this->resetMigration($plan->getFirst()->getMigration());
        $migrator = $this->dependencyFactory->getMigrator();
        $this->getLogger()->info(sprintf('Applying the down migration "%s"...', $version));
        $migrator->migrate($plan, new MigratorConfiguration());
    }

    /**
     * Resets the migration for reuse.
     */
    private function resetMigration(AbstractMigration $migration): void
    {
        $reflection = new ReflectionClass(AbstractMigration::class);
        $property = $reflection->getProperty('plannedSql');
        $property->setValue($migration, []);
        try {
            $property = $reflection->getProperty('frozen');
            $property->setValue($migration, false);
            // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        } catch (ReflectionException) {
            // If missing, the `frozen` property is ignored for back-compatibility
        }
    }

    /**
     * Return the logger.
     *
     * @return LoggerInterface Logger
     */
    private function getLogger(): LoggerInterface
    {
        if (!$this->logger instanceof LoggerInterface) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }
}
