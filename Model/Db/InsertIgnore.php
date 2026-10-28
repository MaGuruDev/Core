<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Magento's DB adapter has no insertIgnore(); this is the bound-parameter
 * INSERT IGNORE for tables where a UNIQUE key decides who wins a race.
 *
 * @package MaGuru\Core\Model\Db
 */
final class InsertIgnore
{
    /**
     * @param AdapterInterface $connection
     * @param string $table
     * @param array<string, scalar|null> $data column => value
     * @return int rows inserted, 0 when the unique key already existed
     */
    public static function run(AdapterInterface $connection, string $table, array $data): int
    {
        $sql = sprintf(
            'INSERT IGNORE INTO %s (%s) VALUES (%s)',
            $connection->quoteIdentifier($table),
            implode(', ', array_map([$connection, 'quoteIdentifier'], array_keys($data))),
            implode(', ', array_fill(0, count($data), '?'))
        );

        return (int) $connection->query($sql, array_values($data))->rowCount();
    }
}
