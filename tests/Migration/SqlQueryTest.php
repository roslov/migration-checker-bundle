<?php

declare(strict_types=1);

namespace Roslov\MigrationCheckerBundle\Tests\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Roslov\MigrationCheckerBundle\Migration\SqlQuery;

/**
 * Tests for the SQL query.
 */
final class SqlQueryTest extends TestCase
{
    /**
     * Tests that the query is executed.
     */
    public function testExecute(): void
    {
        $query = 'SELECT * FROM `table` WHERE id = :id';
        $params = ['id' => 1];
        $expectedResult = [['id' => 1, 'name' => 'test']];

        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn($expectedResult);

        $statement = $this->createMock(Statement::class);
        $statement->expects($this->once())
            ->method('bindValue')
            ->with('id', 1);
        $statement->expects($this->once())
            ->method('executeQuery')
            ->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $sqlQuery = new SqlQuery($entityManager);
        $actualResult = $sqlQuery->execute($query, $params);

        $this->assertSame($expectedResult, $actualResult);
    }
}
