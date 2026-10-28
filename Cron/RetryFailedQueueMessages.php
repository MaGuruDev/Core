<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Cron;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Api\QueueRetryLogRepositoryInterface;
use MaGuru\Core\Api\RetryHandlerPoolInterface;
use MaGuru\Core\Api\RetryManagerInterface;
use MaGuru\Core\Model\Queue\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Retries due rows of maguru_core_queue_retry_log by calling the handler registered for the
 * row's topic directly, not through the queue. Rows without a handler are left untouched.
 *
 * @package MaGuru\Core\Cron
 */
class RetryFailedQueueMessages
{
    /**
     * @param QueueRetryLogRepositoryInterface $repository
     * @param RetryHandlerPoolInterface $handlerPool
     * @param RetryManagerInterface $retryManager
     * @param SerializerInterface $serializer
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param ManagerInterface $eventManager
     * @param Config $config
     */
    public function __construct(
        private readonly QueueRetryLogRepositoryInterface $repository,
        private readonly RetryHandlerPoolInterface        $handlerPool,
        private readonly RetryManagerInterface            $retryManager,
        private readonly SerializerInterface              $serializer,
        private readonly EncryptorInterface               $encryptor,
        private readonly LoggerInterface                  $logger,
        private readonly DateTime                         $dateTime,
        private readonly ManagerInterface                 $eventManager,
        private readonly Config                           $config,
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        try {
            $rows = $this->repository->getDueForRetry($this->config->getBatchSize());
            foreach ($rows as $row) {
                $this->processRow($row);
            }
        } catch (\Throwable $e) {
            $this->logger->error('[MaGuru_Core] RetryFailedQueueMessages cron error: ' . $e->getMessage());
        }
    }

    /**
     * @param QueueRetryLogInterface $row
     * @return void
     */
    private function processRow(QueueRetryLogInterface $row): void
    {
        $topic = (string) $row->getTopic();
        if (!$this->handlerPool->has($topic)) {
            return;
        }

        $payload = $this->decodePayload($row);
        if ($payload === null) {
            $this->markExhausted($row, 'Could not decode retry payload');
            return;
        }

        try {
            $this->handlerPool->get($topic)->handle($payload);
            $row->setStatus(QueueRetryLogInterface::STATUS_SUCCEEDED);
            $this->saveQuietly($row);
        } catch (\Throwable $e) {
            $this->handleFailure($row, $e);
        }
    }

    /**
     * @param QueueRetryLogInterface $row
     * @param \Throwable $e
     * @return void
     */
    private function handleFailure(QueueRetryLogInterface $row, \Throwable $e): void
    {
        $newAttempt = $row->getAttemptCount() + 1;
        $row->setAttemptCount($newAttempt);
        $row->setLastError($e->getMessage());

        if ($this->retryManager->hasExceededMaxRetries($newAttempt, $row->getMaxRetries())) {
            $this->markExhausted($row, $e->getMessage());
            return;
        }

        $delaySeconds = $this->retryManager->calculateDelaySeconds(
            $newAttempt,
            $row->getBaseDelaySeconds(),
            $row->getMaxDelaySeconds()
        );
        $row->setStatus(QueueRetryLogInterface::STATUS_RETRYING);
        $row->setNextRetryAt($this->dateTime->gmtDate(null, time() + $delaySeconds));
        $this->saveQuietly($row);
    }

    /**
     * @param QueueRetryLogInterface $row
     * @param string $reason
     * @return void
     */
    private function markExhausted(QueueRetryLogInterface $row, string $reason): void
    {
        $row->setStatus(QueueRetryLogInterface::STATUS_EXHAUSTED);
        $row->setLastError($reason);
        $this->saveQuietly($row);
        $this->eventManager->dispatch('maguru_core_queue_retry_exhausted', ['retry_log' => $row]);
    }

    /**
     * @param QueueRetryLogInterface $row
     * @return array<string, mixed>|null
     */
    private function decodePayload(QueueRetryLogInterface $row): ?array
    {
        $raw = $row->getPayload();
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            $json = $row->getIsEncrypted() ? $this->encryptor->decrypt($raw) : $raw;
            $decoded = $this->serializer->unserialize($json);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param QueueRetryLogInterface $row
     * @return void
     */
    private function saveQuietly(QueueRetryLogInterface $row): void
    {
        try {
            $this->repository->save($row);
        } catch (CouldNotSaveException $e) {
            $this->logger->error('[MaGuru_Core] RetryFailedQueueMessages could not save retry log row: ' . $e->getMessage());
        }
    }
}
