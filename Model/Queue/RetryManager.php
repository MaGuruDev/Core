<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Queue;

use MaGuru\Core\Api\RetryManagerInterface;

/**
 * Class RetryManager
 *
 * @package MaGuru\Core\Model\Queue
 */
class RetryManager implements RetryManagerInterface
{
    /**
     * @param int $attemptNumber
     * @param int $baseDelaySeconds
     * @param int $maxDelaySeconds
     * @return int
     */
    public function calculateDelaySeconds(int $attemptNumber, int $baseDelaySeconds, int $maxDelaySeconds): int
    {
        $delay = $baseDelaySeconds * (2 ** max(0, $attemptNumber - 1));
        return min($maxDelaySeconds, $delay);
    }

    /**
     * @param int $attemptNumber
     * @param int $maxRetries
     * @return bool
     */
    public function hasExceededMaxRetries(int $attemptNumber, int $maxRetries): bool
    {
        return $attemptNumber >= $maxRetries;
    }
}
