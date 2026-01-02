<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Migration;

use Doctrine\ORM\EntityManagerInterface;
use Roslov\MigrationChecker\Contract\QueryInterface;

/**
 * Fetches data from MySQL.
 */
final class MySqlQuery implements QueryInterface
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
        return $this->em->getConnection()->prepare($query)->executeQuery($params)->fetchAllAssociative();
    }
}
