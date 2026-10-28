<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Queue;

use MaGuru\Core\Api\QueueRetryPolicyProviderInterface;
use MaGuru\Core\Model\AbstractConfig as BaseAbstractConfig;

/**
 * Default queue retry settings (Advanced > MaGuru > Extensions > Queue Retry).
 *
 * @package MaGuru\Core\Model\Queue
 */
class Config extends BaseAbstractConfig implements QueueRetryPolicyProviderInterface
{
    private const XML_PATH_ENABLED            = 'maguru_extension/queue_retry/enabled';
    private const XML_PATH_BATCH_SIZE          = 'maguru_extension/queue_retry/batch_size';
    private const XML_PATH_MAX_RETRIES         = 'maguru_extension/queue_retry/max_retries';
    private const XML_PATH_BASE_DELAY_SECONDS  = 'maguru_extension/queue_retry/base_delay_seconds';
    private const XML_PATH_MAX_DELAY_SECONDS   = 'maguru_extension/queue_retry/max_delay_seconds';

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isFlag(self::XML_PATH_ENABLED);
    }

    /**
     * @return int
     */
    public function getBatchSize(): int
    {
        return $this->getIntValue(self::XML_PATH_BATCH_SIZE);
    }

    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return $this->getIntValue(self::XML_PATH_MAX_RETRIES);
    }

    /**
     * @return int
     */
    public function getBaseDelaySeconds(): int
    {
        return $this->getIntValue(self::XML_PATH_BASE_DELAY_SECONDS);
    }

    /**
     * @return int
     */
    public function getMaxDelaySeconds(): int
    {
        return $this->getIntValue(self::XML_PATH_MAX_DELAY_SECONDS);
    }
}
