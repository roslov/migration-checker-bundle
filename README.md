Database Migration Checker Bundle
=================================

This package validates Doctrine database migrations. It checks whether all up and down migrations run without errors.

It also validates that down migrations revert all changes, so there is no diff in the database after running them.

It is a Symfony bundle. It wraps the [Migration Checker](https://github.com/roslov/migration-checker).


## Requirements

- PHP 8.1 or higher,
- Symfony 6.0 or higher,
- Doctrine Migrations 3.x


## Installation

The package could be installed with composer:

```shell
composer require --dev roslov/migration-checker-bundle
```

After package installation, add the bundle to your `config/bundles.php`.

```php
# config/bundles.php

return [
    // ...
    \Roslov\MigrationCheckerBundle\RoslovMigrationCheckerBundle::class => ['test' => true],
];
```


## General usage

You can run the command to check your migrations:

```shell
bin/console migration-checker:check --env=test -vv
```

Be careful to run it in the test environment, otherwise you can damage your data.

Also, ensure that you run this command on an empty database each time.

The output example of the successful run:
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

The output example of the failed run:
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


+Table: event
+
+Create Table: CREATE TABLE `event` (
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


## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Code style analysis

The code style is analyzed with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) and
[PSR-12 Ext coding standard](https://github.com/roslov/psr12ext). To run code style analysis:

```shell
./vendor/bin/phpcs --extensions=php --colors --standard=PSR12Ext --ignore=vendor/* -p -s .
```

