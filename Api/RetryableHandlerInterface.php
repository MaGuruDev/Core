<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

/**
 * Queue handler called by the shared retry cron. handle() has no try/catch of its own, so the
 * exception reaches the cron and the retry-log row can be updated.
 *
 * @api
 *
 * @package MaGuru\Core\Api
 */
interface RetryableHandlerInterface
{
    /**
     * @param array<string, mixed> $data
     * @return void
     * @throws \Throwable
     */
    public function handle(array $data): void;
}
