<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Data;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog as QueueRetryLogResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class QueueRetryLog
 *
 * @package MaGuru\Core\Model\Data
 */
class QueueRetryLog extends AbstractModel implements QueueRetryLogInterface
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(QueueRetryLogResource::class);
    }

    /**
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        $id = $this->getData('entity_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * @return string|null
     */
    public function getModule(): ?string
    {
        $v = $this->getData('module');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string $module
     * @return self
     */
    public function setModule(string $module): self
    {
        return $this->setData('module', $module);
    }

    /**
     * @return string|null
     */
    public function getTopic(): ?string
    {
        $v = $this->getData('topic');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string $topic
     * @return self
     */
    public function setTopic(string $topic): self
    {
        return $this->setData('topic', $topic);
    }

    /**
     * @return string|null
     */
    public function getPayload(): ?string
    {
        $v = $this->getData('payload');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string|null $payload
     * @return self
     */
    public function setPayload(?string $payload): self
    {
        return $this->setData('payload', $payload);
    }

    /**
     * @return bool
     */
    public function getIsEncrypted(): bool
    {
        return (bool) $this->getData('is_encrypted');
    }

    /**
     * @param bool $isEncrypted
     * @return self
     */
    public function setIsEncrypted(bool $isEncrypted): self
    {
        return $this->setData('is_encrypted', $isEncrypted);
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        $v = $this->getData('status');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self
    {
        return $this->setData('status', $status);
    }

    /**
     * @return int
     */
    public function getAttemptCount(): int
    {
        return (int) $this->getData('attempt_count');
    }

    /**
     * @param int $attemptCount
     * @return self
     */
    public function setAttemptCount(int $attemptCount): self
    {
        return $this->setData('attempt_count', $attemptCount);
    }

    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return (int) $this->getData('max_retries');
    }

    /**
     * @param int $maxRetries
     * @return self
     */
    public function setMaxRetries(int $maxRetries): self
    {
        return $this->setData('max_retries', $maxRetries);
    }

    /**
     * @return int
     */
    public function getBaseDelaySeconds(): int
    {
        return (int) $this->getData('base_delay_seconds');
    }

    /**
     * @param int $baseDelaySeconds
     * @return self
     */
    public function setBaseDelaySeconds(int $baseDelaySeconds): self
    {
        return $this->setData('base_delay_seconds', $baseDelaySeconds);
    }

    /**
     * @return int
     */
    public function getMaxDelaySeconds(): int
    {
        return (int) $this->getData('max_delay_seconds');
    }

    /**
     * @param int $maxDelaySeconds
     * @return self
     */
    public function setMaxDelaySeconds(int $maxDelaySeconds): self
    {
        return $this->setData('max_delay_seconds', $maxDelaySeconds);
    }

    /**
     * @return string|null
     */
    public function getLastError(): ?string
    {
        $v = $this->getData('last_error');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string|null $lastError
     * @return self
     */
    public function setLastError(?string $lastError): self
    {
        return $this->setData('last_error', $lastError);
    }

    /**
     * @return string|null
     */
    public function getNextRetryAt(): ?string
    {
        $v = $this->getData('next_retry_at');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string|null $nextRetryAt
     * @return self
     */
    public function setNextRetryAt(?string $nextRetryAt): self
    {
        return $this->setData('next_retry_at', $nextRetryAt);
    }

    /**
     * @return string|null
     */
    public function getReferenceType(): ?string
    {
        $v = $this->getData('reference_type');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @param string|null $referenceType
     * @return self
     */
    public function setReferenceType(?string $referenceType): self
    {
        return $this->setData('reference_type', $referenceType);
    }

    /**
     * @return int|null
     */
    public function getReferenceId(): ?int
    {
        $v = $this->getData('reference_id');
        return $v !== null ? (int) $v : null;
    }

    /**
     * @param int|null $referenceId
     * @return self
     */
    public function setReferenceId(?int $referenceId): self
    {
        return $this->setData('reference_id', $referenceId);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        $v = $this->getData('created_at');
        return $v !== null ? (string) $v : null;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        $v = $this->getData('updated_at');
        return $v !== null ? (string) $v : null;
    }
}
