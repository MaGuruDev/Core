<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Cron;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Api\QueueRetryLogRepositoryInterface;
use MaGuru\Core\Api\RetryableHandlerInterface;
use MaGuru\Core\Api\RetryHandlerPoolInterface;
use MaGuru\Core\Api\RetryManagerInterface;
use MaGuru\Core\Cron\RetryFailedQueueMessages;
use MaGuru\Core\Model\Queue\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class RetryFailedQueueMessagesTest
 *
 * @package MaGuru\Core\Test\Unit\Cron
 */
class RetryFailedQueueMessagesTest extends TestCase
{
    private QueueRetryLogRepositoryInterface&MockObject $repository;
    private RetryHandlerPoolInterface&MockObject $handlerPool;
    private RetryManagerInterface&MockObject $retryManager;
    private SerializerInterface&MockObject $serializer;
    private EncryptorInterface&MockObject $encryptor;
    private LoggerInterface&MockObject $logger;
    private DateTime&MockObject $dateTime;
    private ManagerInterface&MockObject $eventManager;
    private Config&MockObject $config;
    private RetryFailedQueueMessages $cron;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->repository   = $this->createMock(QueueRetryLogRepositoryInterface::class);
        $this->handlerPool  = $this->createMock(RetryHandlerPoolInterface::class);
        $this->retryManager = $this->createMock(RetryManagerInterface::class);
        $this->serializer   = $this->createMock(SerializerInterface::class);
        $this->encryptor    = $this->createMock(EncryptorInterface::class);
        $this->logger       = $this->createMock(LoggerInterface::class);
        $this->dateTime     = $this->createMock(DateTime::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->config       = $this->createMock(Config::class);

        $this->config->method('isEnabled')->willReturn(true);
        $this->config->method('getBatchSize')->willReturn(50);

        $this->cron = new RetryFailedQueueMessages(
            $this->repository,
            $this->handlerPool,
            $this->retryManager,
            $this->serializer,
            $this->encryptor,
            $this->logger,
            $this->dateTime,
            $this->eventManager,
            $this->config,
        );
    }

    /**
     * @return void
     */
    public function testExecuteDoesNothingWhenDisabled(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->config->method('isEnabled')->willReturn(false);
        $cron = new RetryFailedQueueMessages(
            $this->repository,
            $this->handlerPool,
            $this->retryManager,
            $this->serializer,
            $this->encryptor,
            $this->logger,
            $this->dateTime,
            $this->eventManager,
            $this->config,
        );

        $this->repository->expects($this->never())->method('getDueForRetry');
        $cron->execute();
    }

    /**
     * @return void
     */
    public function testExecuteSkipsRowWithoutRegisteredHandler(): void
    {
        $row = $this->createMock(QueueRetryLogInterface::class);
        $row->method('getTopic')->willReturn('unregistered.topic');

        $this->repository->method('getDueForRetry')->willReturn([$row]);
        $this->handlerPool->method('has')->with('unregistered.topic')->willReturn(false);

        $this->repository->expects($this->never())->method('save');
        $this->cron->execute();
    }

    /**
     * @return void
     */
    public function testExecuteMarksRowSucceededWhenHandlerSucceeds(): void
    {
        $row = $this->createMock(QueueRetryLogInterface::class);
        $row->method('getTopic')->willReturn('mono_payment.invoice.cancel');
        $row->method('getPayload')->willReturn('{"invoice_id":"abc"}');
        $row->method('getIsEncrypted')->willReturn(false);

        $handler = $this->createMock(RetryableHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with(['invoice_id' => 'abc']);

        $this->repository->method('getDueForRetry')->willReturn([$row]);
        $this->handlerPool->method('has')->willReturn(true);
        $this->handlerPool->method('get')->willReturn($handler);
        $this->serializer->method('unserialize')->with('{"invoice_id":"abc"}')->willReturn(['invoice_id' => 'abc']);

        $row->expects($this->once())->method('setStatus')->with(QueueRetryLogInterface::STATUS_SUCCEEDED);
        $this->repository->expects($this->once())->method('save')->with($row);

        $this->cron->execute();
    }

    /**
     * @return void
     */
    public function testExecuteReschedulesRowWhenHandlerFailsAndBudgetRemains(): void
    {
        $row = $this->createMock(QueueRetryLogInterface::class);
        $row->method('getTopic')->willReturn('mono_payment.invoice.cancel');
        $row->method('getPayload')->willReturn('{}');
        $row->method('getIsEncrypted')->willReturn(false);
        $row->method('getAttemptCount')->willReturn(1);
        $row->method('getMaxRetries')->willReturn(5);
        $row->method('getBaseDelaySeconds')->willReturn(300);
        $row->method('getMaxDelaySeconds')->willReturn(1800);

        $handler = $this->createMock(RetryableHandlerInterface::class);
        $handler->method('handle')->willThrowException(new \RuntimeException('API down'));

        $this->repository->method('getDueForRetry')->willReturn([$row]);
        $this->handlerPool->method('has')->willReturn(true);
        $this->handlerPool->method('get')->willReturn($handler);
        $this->serializer->method('unserialize')->willReturn([]);

        $this->retryManager->method('hasExceededMaxRetries')->with(2, 5)->willReturn(false);
        $this->retryManager->method('calculateDelaySeconds')->with(2, 300, 1800)->willReturn(600);
        $this->dateTime->method('gmtDate')->willReturn('2026-07-19 12:10:00');

        $row->expects($this->once())->method('setAttemptCount')->with(2);
        $row->expects($this->once())->method('setStatus')->with(QueueRetryLogInterface::STATUS_RETRYING);
        $row->expects($this->once())->method('setNextRetryAt')->with('2026-07-19 12:10:00');

        $this->eventManager->expects($this->never())->method('dispatch');
        $this->repository->expects($this->once())->method('save')->with($row);

        $this->cron->execute();
    }

    /**
     * @return void
     */
    public function testExecuteExhaustsRowAndDispatchesEventWhenBudgetExceeded(): void
    {
        $row = $this->createMock(QueueRetryLogInterface::class);
        $row->method('getTopic')->willReturn('mono_payment.invoice.cancel');
        $row->method('getPayload')->willReturn('{}');
        $row->method('getIsEncrypted')->willReturn(false);
        $row->method('getAttemptCount')->willReturn(4);
        $row->method('getMaxRetries')->willReturn(5);

        $handler = $this->createMock(RetryableHandlerInterface::class);
        $handler->method('handle')->willThrowException(new \RuntimeException('API down'));

        $this->repository->method('getDueForRetry')->willReturn([$row]);
        $this->handlerPool->method('has')->willReturn(true);
        $this->handlerPool->method('get')->willReturn($handler);
        $this->serializer->method('unserialize')->willReturn([]);

        $this->retryManager->method('hasExceededMaxRetries')->with(5, 5)->willReturn(true);

        $row->expects($this->once())->method('setStatus')->with(QueueRetryLogInterface::STATUS_EXHAUSTED);
        $this->repository->expects($this->once())->method('save')->with($row);
        $this->eventManager->expects($this->once())->method('dispatch')
            ->with('maguru_core_queue_retry_exhausted', ['retry_log' => $row]);

        $this->cron->execute();
    }

    /**
     * @return void
     */
    public function testExecuteExhaustsRowWithUndecodablePayload(): void
    {
        $row = $this->createMock(QueueRetryLogInterface::class);
        $row->method('getTopic')->willReturn('mono_payment.invoice.cancel');
        $row->method('getPayload')->willReturn('not-json');
        $row->method('getIsEncrypted')->willReturn(false);

        $this->repository->method('getDueForRetry')->willReturn([$row]);
        $this->handlerPool->method('has')->willReturn(true);
        $this->serializer->method('unserialize')->willThrowException(new \InvalidArgumentException('bad json'));

        $row->expects($this->once())->method('setStatus')->with(QueueRetryLogInterface::STATUS_EXHAUSTED);
        $this->handlerPool->expects($this->never())->method('get');

        $this->cron->execute();
    }

    /**
     * @return void
     */
    public function testExecuteCatchesUnexpectedErrorsAndLogsThem(): void
    {
        $this->repository->method('getDueForRetry')->willThrowException(new \RuntimeException('db down'));

        $this->logger->expects($this->once())->method('error');
        $this->cron->execute();
    }
}
