<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Queue;

use MaGuru\Core\Api\RetryableHandlerInterface;
use MaGuru\Core\Model\Queue\RetryHandlerPool;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RetryHandlerPoolTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Queue
 */
class RetryHandlerPoolTest extends TestCase
{
    /**
     * @return void
     */
    public function testHasAndGetResolveRegisteredHandler(): void
    {
        $handler = $this->createMock(RetryableHandlerInterface::class);
        $handlerClass = get_class($handler);

        $tmapFactory = $this->createMock(TMapFactory::class);
        $tmapFactory->method('create')
            ->with(['array' => ['mono_payment.invoice.cancel' => $handlerClass], 'type' => RetryableHandlerInterface::class])
            ->willReturn(new \ArrayObject(['mono_payment.invoice.cancel' => $handler]));

        $pool = new RetryHandlerPool($tmapFactory, ['mono_payment.invoice.cancel' => $handlerClass]);

        $this->assertTrue($pool->has('mono_payment.invoice.cancel'));
        $this->assertSame($handler, $pool->get('mono_payment.invoice.cancel'));
    }

    /**
     * @return void
     */
    public function testHasReturnsFalseForUnregisteredTopic(): void
    {
        $tmapFactory = $this->createMock(TMapFactory::class);
        $tmapFactory->method('create')->willReturn(new \ArrayObject([]));

        $pool = new RetryHandlerPool($tmapFactory, []);

        $this->assertFalse($pool->has('unknown.topic'));
    }

    /**
     * @return void
     */
    public function testGetThrowsForUnregisteredTopic(): void
    {
        $tmapFactory = $this->createMock(TMapFactory::class);
        $tmapFactory->method('create')->willReturn(new \ArrayObject([]));

        $pool = new RetryHandlerPool($tmapFactory, []);

        $this->expectException(NotFoundException::class);
        $pool->get('unknown.topic');
    }
}
