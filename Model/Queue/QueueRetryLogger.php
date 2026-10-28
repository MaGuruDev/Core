<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Queue;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Api\QueueRetryLoggerInterface;
use MaGuru\Core\Api\QueueRetryLogRepositoryInterface;
use MaGuru\Core\Api\QueueRetryPolicyProviderInterface;
use MaGuru\Core\Model\Data\QueueRetryLogFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Default QueueRetryLoggerInterface. The payload is not encrypted by default; a module with
 * personal data in it registers a virtualType with encryptPayload=true.
 *
 * @package MaGuru\Core\Model\Queue
 */
class QueueRetryLogger implements QueueRetryLoggerInterface
{
    /**
     * @param QueueRetryLogFactory $logFactory
     * @param QueueRetryLogRepositoryInterface $logRepository
     * @param SerializerInterface $serializer
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param QueueRetryPolicyProviderInterface $retryPolicy
     * @param bool $encryptPayload
     */
    public function __construct(
        private readonly QueueRetryLogFactory            $logFactory,
        private readonly QueueRetryLogRepositoryInterface $logRepository,
        private readonly SerializerInterface             $serializer,
        private readonly EncryptorInterface               $encryptor,
        private readonly LoggerInterface                  $logger,
        private readonly QueueRetryPolicyProviderInterface $retryPolicy,
        private readonly bool                             $encryptPayload = false,
    ) {
    }

    /**
     * @param string $module
     * @param string $topic
     * @param array<string, mixed> $payload
     * @param \Throwable $e
     * @return void
     */
    public function recordFailure(string $module, string $topic, array $payload, \Throwable $e): void
    {
        $this->logger->error(sprintf('[%s] %s failed: %s', $module, $topic, $e->getMessage()));

        try {
            $serialized = (string) $this->serializer->serialize($payload);

            /** @var QueueRetryLogInterface $log */
            $log = $this->logFactory->create();
            $log->setModule($module);
            $log->setTopic($topic);
            $log->setPayload($this->encryptPayload ? $this->encryptor->encrypt($serialized) : $serialized);
            $log->setIsEncrypted($this->encryptPayload);
            $log->setStatus(QueueRetryLogInterface::STATUS_FAILED);
            $log->setAttemptCount(0);
            $log->setMaxRetries($this->retryPolicy->getMaxRetries());
            $log->setBaseDelaySeconds($this->retryPolicy->getBaseDelaySeconds());
            $log->setMaxDelaySeconds($this->retryPolicy->getMaxDelaySeconds());
            $log->setLastError($e->getMessage());
            $this->logRepository->save($log);
        } catch (\Throwable $loggingError) {
            $this->logger->error(sprintf(
                '[%s] %s: failed to persist retry-log row: %s',
                $module,
                $topic,
                $loggingError->getMessage()
            ));
        }
    }
}
