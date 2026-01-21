<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Migration;

use Doctrine\ORM\EntityManagerInterface;
use Roslov\MigrationChecker\Contract\QueryInterface;

/**
 * Fetches data from a database.
 */
final class SqlQuery implements QueryInterface
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em Entity manager
     */
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $query, array $params = []): array
    {
        $stmt = $this->em->getConnection()->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
