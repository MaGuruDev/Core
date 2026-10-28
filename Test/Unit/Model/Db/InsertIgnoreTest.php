<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Db;

use MaGuru\Core\Model\Db\InsertIgnore;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class InsertIgnoreTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Db
 */
class InsertIgnoreTest extends TestCase
{
    /**
     * @return void
     */
    public function testBuildsABoundInsertIgnoreAndReturnsTheRowCount(): void
    {
        $statement = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statement->method('rowCount')->willReturn(1);

        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('quoteIdentifier')->willReturnCallback(fn (string $name): string => '`' . $name . '`');
        $connection->expects($this->once())->method('query')
            ->with(
                'INSERT IGNORE INTO `t` (`a`, `b`) VALUES (?, ?)',
                [1, 'x']
            )
            ->willReturn($statement);

        $this->assertSame(1, InsertIgnore::run($connection, 't', ['a' => 1, 'b' => 'x']));
    }

    /**
     * @return void
     */
    public function testReturnsZeroWhenTheKeyAlreadyExists(): void
    {
        $statement = $this->createMock(\Zend_Db_Statement_Interface::class);
        $statement->method('rowCount')->willReturn(0);
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('quoteIdentifier')->willReturnArgument(0);
        $connection->method('query')->willReturn($statement);

        $this->assertSame(0, InsertIgnore::run($connection, 't', ['a' => 1]));
    }
}
