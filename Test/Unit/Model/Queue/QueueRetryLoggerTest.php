<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Queue;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Api\QueueRetryLogRepositoryInterface;
use MaGuru\Core\Api\QueueRetryPolicyProviderInterface;
use MaGuru\Core\Model\Data\QueueRetryLogFactory;
use MaGuru\Core\Model\Queue\QueueRetryLogger;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class QueueRetryLoggerTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Queue
 */
class QueueRetryLoggerTest extends TestCase
{
    private QueueRetryLogFactory&MockObject $logFactory;
    private QueueRetryLogRepositoryInterface&MockObject $logRepository;
    private SerializerInterface&MockObject $serializer;
    private EncryptorInterface&MockObject $encryptor;
    private LoggerInterface&MockObject $logger;
    private QueueRetryPolicyProviderInterface&MockObject $retryPolicy;
    private QueueRetryLogInterface&MockObject $log;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->logFactory    = $this->createMock(QueueRetryLogFactory::class);
        $this->logRepository = $this->createMock(QueueRetryLogRepositoryInterface::class);
        $this->serializer    = $this->createMock(SerializerInterface::class);
        $this->encryptor     = $this->createMock(EncryptorInterface::class);
        $this->logger        = $this->createMock(LoggerInterface::class);
        $this->retryPolicy   = $this->createMock(QueueRetryPolicyProviderInterface::class);
        $this->log            = $this->createMock(QueueRetryLogInterface::class);

        $this->logFactory->method('create')->willReturn($this->log);
        $this->retryPolicy->method('getMaxRetries')->willReturn(5);
        $this->retryPolicy->method('getBaseDelaySeconds')->willReturn(300);
        $this->retryPolicy->method('getMaxDelaySeconds')->willReturn(1800);
    }

    /**
     * @return void
     */
    public function testRecordFailureStoresPlainPayloadByDefault(): void
    {
        $logger = new QueueRetryLogger(
            $this->logFactory,
            $this->logRepository,
            $this->serializer,
            $this->encryptor,
            $this->logger,
            $this->retryPolicy,
        );

        $this->serializer->method('serialize')->with(['invoice_id' => 'abc'])->willReturn('{"invoice_id":"abc"}');
        $this->encryptor->expects($this->never())->method('encrypt');

        $this->log->expects($this->once())->method('setPayload')->with('{"invoice_id":"abc"}');
        $this->log->expects($this->once())->method('setIsEncrypted')->with(false);
        $this->log->expects($this->once())->method('setStatus')->with(QueueRetryLogInterface::STATUS_FAILED);
        $this->log->expects($this->once())->method('setMaxRetries')->with(5);
        $this->log->expects($this->once())->method('setBaseDelaySeconds')->with(300);
        $this->log->expects($this->once())->method('setMaxDelaySeconds')->with(1800);

        $this->logRepository->expects($this->once())->method('save')->with($this->log);
        $this->logger->expects($this->once())->method('error');

        $logger->recordFailure('MaGuru_MonoPayment', 'mono_payment.invoice.cancel', ['invoice_id' => 'abc'], new \RuntimeException('boom'));
    }

    /**
     * @return void
     */
    public function testRecordFailureEncryptsPayloadWhenConfigured(): void
    {
        $logger = new QueueRetryLogger(
            $this->logFactory,
            $this->logRepository,
            $this->serializer,
            $this->encryptor,
            $this->logger,
            $this->retryPolicy,
            true,
        );

        $this->serializer->method('serialize')->willReturn('{"email":"a@b.c"}');
        $this->encryptor->method('encrypt')->with('{"email":"a@b.c"}')->willReturn('encrypted-blob');

        $this->log->expects($this->once())->method('setPayload')->with('encrypted-blob');
        $this->log->expects($this->once())->method('setIsEncrypted')->with(true);

        $this->logRepository->expects($this->once())->method('save');

        $logger->recordFailure('MaGuru_Yespo', 'yespo.contact.sync', ['email' => 'a@b.c'], new \RuntimeException('boom'));
    }

    /**
     * @return void
     */
    public function testRecordFailureSwallowsPersistenceErrorsAndLogsThem(): void
    {
        $logger = new QueueRetryLogger(
            $this->logFactory,
            $this->logRepository,
            $this->serializer,
            $this->encryptor,
            $this->logger,
            $this->retryPolicy,
        );

        $this->serializer->method('serialize')->willReturn('{}');
        $this->logRepository->method('save')->willThrowException(new \RuntimeException('db down'));

        $this->logger->expects($this->exactly(2))->method('error');

        $logger->recordFailure('MaGuru_MonoPayment', 'mono_payment.invoice.cancel', [], new \RuntimeException('boom'));
    }
}
