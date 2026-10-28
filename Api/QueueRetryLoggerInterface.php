<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

/**
 * @api
 *
 * @package MaGuru\Core\Api
 */
interface QueueRetryLoggerInterface
{
    /**
     * Saves a failed queue message for retry and logs the error.
     *
     * @param string $module
     * @param string $topic
     * @param array<string, mixed> $payload
     * @param \Throwable $e
     * @return void
     */
    public function recordFailure(string $module, string $topic, array $payload, \Throwable $e): void;
}
