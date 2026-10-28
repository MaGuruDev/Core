<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

/**
 * Backoff calculator for queue retries. Callers pass the delays and the retry limit.
 *
 * @api
 *
 * @package MaGuru\Core\Api
 */
interface RetryManagerInterface
{
    /**
     * Exponential backoff: min($maxDelaySeconds, $baseDelaySeconds * 2^($attemptNumber-1)).
     *
     * @param int $attemptNumber
     * @param int $baseDelaySeconds
     * @param int $maxDelaySeconds
     * @return int
     */
    public function calculateDelaySeconds(int $attemptNumber, int $baseDelaySeconds, int $maxDelaySeconds): int;

    /**
     * @param int $attemptNumber
     * @param int $maxRetries
     * @return bool
     */
    public function hasExceededMaxRetries(int $attemptNumber, int $maxRetries): bool;
}
