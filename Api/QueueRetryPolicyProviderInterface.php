<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

/**
 * Retry budget written onto a new retry-log row. Core's Queue\Config is the default
 * implementation; a module may provide its own via a QueueRetryLogger virtualType.
 *
 * @api
 *
 * @package MaGuru\Core\Api
 */
interface QueueRetryPolicyProviderInterface
{
    /**
     * @return int
     */
    public function getMaxRetries(): int;

    /**
     * @return int
     */
    public function getBaseDelaySeconds(): int;

    /**
     * @return int
     */
    public function getMaxDelaySeconds(): int;
}
