Database Migration Checker Bundle
=================================

[![Latest Stable Version](https://poser.pugx.org/roslov/migration-checker-bundle/v)](https://packagist.org/packages/roslov/migration-checker-bundle)
[![Total Downloads](https://poser.pugx.org/roslov/migration-checker-bundle/downloads)](https://packagist.org/packages/roslov/migration-checker-bundle)
[![License](https://poser.pugx.org/roslov/migration-checker-bundle/license)](https://packagist.org/packages/roslov/migration-checker-bundle)
[![PHP Version Require](https://poser.pugx.org/roslov/migration-checker-bundle/require/php)](https://packagist.org/packages/roslov/migration-checker-bundle)

The Database Migration Checker Bundle validates Doctrine Migrations in Symfony by executing each migration
up and down against a clean test database and ensuring the schema returns to the exact same state after the
rollback. It wraps the core [Migration Checker](https://github.com/roslov/migration-checker) library and
provides a ready-to-use Symfony console command.

This is intended for CI pipelines and local checks where you want confidence that every migration can be
applied and reverted without leaving stray tables, columns, or indexes behind.


## Requirements

- PHP 8.1 or higher,
- Symfony 6.0 or higher,
- Doctrine Migrations Bundle 3.2.1 or higher,
- Doctrine ORM 2.0 or higher.


## Supported database types and versions

This bundle supports MySQL, MariaDB, PostgreSQL, and others.

See all supported database types and versions in the
[Migration Checker documentation](https://github.com/roslov/migration-checker#supported-database-types-and-versions).


## Limitations

The console command `migration-checker:check` runs only in the test environment to avoid accidentally affecting the
working database. Therefore, it should always be used with the option `--env=test`.


## Installation

The package could be installed with composer:

```shell
composer require --dev roslov/migration-checker-bundle
```

If you are not using Symfony Flex, register the bundle manually:

```php
// config/bundles.php
return [
    // ...
    Roslov\MigrationCheckerBundle\RoslovMigrationCheckerBundle::class => ['dev' => true, 'test' => true],
];
```

## General usage

### What the checker does

For each new Doctrine migration, the checker will:

1. Apply the migration (up).
2. Roll it back (down).
3. Compare the database schema before and after to ensure they match.
4. Re-apply the migration to proceed to the next one.

If the schema differs after a rollback, the command fails and prints a unified diff of the schema changes.

### Prerequisites

To get useful results, make sure:

- The command is executed in the **test** environment (`--env=test`).
- Your test database is **empty** before the run (fresh schema).
- Doctrine Migrations is configured for the same connection your test environment uses.

### Run locally

You can run the command to check your migrations:

```shell
php bin/console migration-checker:check --env=test -vv
```

Be careful to run it in the test environment, otherwise you can damage your data.

Also, ensure that you run this command on an empty database each time.

### Successful output example
```
[info] Migration check started.
[info] Preparing migration environment...
[info] Checking if another migration can be applied...
[info] Saving the current state...
[info] Applying the up migration...
[info] Applying the up migration "DoctrineMigrations\Version20241105145435"...
[info] Applying the down migration...
[info] Applying the down migration "DoctrineMigrations\Version20241105145435"...
[info] Saving the state after up and down migrations...
[info] Comparing the states...
[info] The up and down migrations have been applied successfully without any state changes.
[info] Applying the up migration before the next step...
[info] Applying the up migration "DoctrineMigrations\Version20241105145435"...
[info] Checking if another migration can be applied...
[info] There are no migrations available.
[info] Cleaning up migration environment...
[info] Migration check completed successfully.
```

### Failed output example
```
[info] Migration check started.
[info] Preparing migration environment...
[info] Checking if another migration can be applied...
[info] Saving the current state...
[info] Applying the up migration...
[info] Applying the up migration "DoctrineMigrations\Version20241105145435"...
[info] Applying the down migration...
[info] Applying the down migration "DoctrineMigrations\Version20241105145435"...
[info] Saving the state after up and down migrations...
[info] Comparing the states...
[error] The down migration has resulted in a different schema state after rollback.
--- Original
+++ New
@@ @@
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci


+-- Table:
+event
+
+-- Create Table:
+CREATE TABLE `event` (
+  `id` bigint(20) NOT NULL AUTO_INCREMENT,
+  `microtime` double(16,6) NOT NULL COMMENT 'Unix timestamp with microseconds',
+  `producer_name` varchar(64) NOT NULL COMMENT 'Producer name',
+  `body` varchar(4096) NOT NULL COMMENT 'Full message body',
+  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Creation timestamp',
+  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Update timestamp',
+  PRIMARY KEY (`id`)
+) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Events (transactional outbox)'
+
+
 -- ### Triggers ###


In MigrationChecker.php line 67:

  [Roslov\MigrationChecker\Exception\SchemaDiffersException]
  The up and down migrations have resulted in a different schema state after rollback.
```

### Run in CI

The following example spins up a MySQL container, waits for it to be ready, runs the check, and then
cleans up. Adjust database credentials and the image name to match your project.

If you want to run it in your CI, you can do something like this:

```shell
# Stops the previously running test environment and database (if the previous run failed)
docker stop test-db || true
docker network rm test-network || true
# Prepares the new test environment
docker network create test-network
# Starts the test database
docker run --name test-db --network=test-network -d --rm \
    -e MYSQL_ROOT_PASSWORD=testrootpass \
    -e MYSQL_DATABASE=test \
    -e MYSQL_USER=testuser \
    -e MYSQL_PASSWORD=testpass \
    mysql:8.4.5 --character-set-server=utf8mb4 --collation-server=utf8mb4_0900_ai_ci
# Waits until the database is ready
while ! docker exec test-db \
    mysql --user=testuser --password=testpass \
    -e 'SELECT 1' \
    >/dev/null 2>&1; do
    echo 'Waiting for database connection...'
    sleep 1
done
# Runs the migration check.
# This command should fail if there are problems with migrations
docker run --network=test-network --rm \
    your-project-image:latest \
    php bin/console migration-checker:check --env=test -vv
# Stops the test database
docker stop test-db
# Stops the test environment
docker network rm test-network
```


## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

inside a container, or

```shell
make test
```

### Code style analysis

The code style is analyzed with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) and
[PSR-12 Ext coding standard](https://github.com/roslov/psr12ext). To run code style analysis:

```shell
./vendor/bin/phpcs --extensions=php --colors --standard=PSR12Ext --ignore=vendor/* -p -s .
```

inside a container, or

```shell
make phpcs
```

from the host machine.
