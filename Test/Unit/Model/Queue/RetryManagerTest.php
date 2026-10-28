<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Queue;

use MaGuru\Core\Model\Queue\RetryManager;
use PHPUnit\Framework\TestCase;

/**
 * Class RetryManagerTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Queue
 */
class RetryManagerTest extends TestCase
{
    private RetryManager $retryManager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->retryManager = new RetryManager();
    }

    /**
     * @return void
     */
    public function testCalculateDelaySecondsAppliesExponentialBackoff(): void
    {
        $this->assertSame(300, $this->retryManager->calculateDelaySeconds(1, 300, 1800));
        $this->assertSame(600, $this->retryManager->calculateDelaySeconds(2, 300, 1800));
        $this->assertSame(1200, $this->retryManager->calculateDelaySeconds(3, 300, 1800));
    }

    /**
     * @return void
     */
    public function testCalculateDelaySecondsCapsAtMaxDelay(): void
    {
        $this->assertSame(1800, $this->retryManager->calculateDelaySeconds(4, 300, 1800));
        $this->assertSame(1800, $this->retryManager->calculateDelaySeconds(10, 300, 1800));
    }

    /**
     * @return void
     */
    public function testCalculateDelaySecondsClampsNonPositiveAttempt(): void
    {
        $this->assertSame(300, $this->retryManager->calculateDelaySeconds(0, 300, 1800));
        $this->assertSame(300, $this->retryManager->calculateDelaySeconds(-5, 300, 1800));
    }

    /**
     * @return void
     */
    public function testHasExceededMaxRetries(): void
    {
        $this->assertFalse($this->retryManager->hasExceededMaxRetries(4, 5));
        $this->assertTrue($this->retryManager->hasExceededMaxRetries(5, 5));
        $this->assertTrue($this->retryManager->hasExceededMaxRetries(6, 5));
    }
}
