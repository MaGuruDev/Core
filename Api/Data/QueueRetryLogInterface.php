<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api\Data;

/**
 * Failed queue message row shared by all MaGuru modules.
 *
 * @api
 *
 * @package MaGuru\Core\Api\Data
 */
interface QueueRetryLogInterface
{
    public const STATUS_FAILED    = 'failed';
    public const STATUS_RETRYING  = 'retrying';
    public const STATUS_EXHAUSTED = 'exhausted';
    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * @return string|null
     */
    public function getModule(): ?string;

    /**
     * @param string $module
     * @return self
     */
    public function setModule(string $module): self;

    /**
     * @return string|null
     */
    public function getTopic(): ?string;

    /**
     * @param string $topic
     * @return self
     */
    public function setTopic(string $topic): self;

    /**
     * @return string|null
     */
    public function getPayload(): ?string;

    /**
     * @param string|null $payload
     * @return self
     */
    public function setPayload(?string $payload): self;

    /**
     * @return bool
     */
    public function getIsEncrypted(): bool;

    /**
     * @param bool $isEncrypted
     * @return self
     */
    public function setIsEncrypted(bool $isEncrypted): self;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self;

    /**
     * @return int
     */
    public function getAttemptCount(): int;

    /**
     * @param int $attemptCount
     * @return self
     */
    public function setAttemptCount(int $attemptCount): self;

    /**
     * @return int
     */
    public function getMaxRetries(): int;

    /**
     * @param int $maxRetries
     * @return self
     */
    public function setMaxRetries(int $maxRetries): self;

    /**
     * @return int
     */
    public function getBaseDelaySeconds(): int;

    /**
     * @param int $baseDelaySeconds
     * @return self
     */
    public function setBaseDelaySeconds(int $baseDelaySeconds): self;

    /**
     * @return int
     */
    public function getMaxDelaySeconds(): int;

    /**
     * @param int $maxDelaySeconds
     * @return self
     */
    public function setMaxDelaySeconds(int $maxDelaySeconds): self;

    /**
     * @return string|null
     */
    public function getLastError(): ?string;

    /**
     * @param string|null $lastError
     * @return self
     */
    public function setLastError(?string $lastError): self;

    /**
     * @return string|null
     */
    public function getNextRetryAt(): ?string;

    /**
     * @param string|null $nextRetryAt
     * @return self
     */
    public function setNextRetryAt(?string $nextRetryAt): self;

    /**
     * @return string|null
     */
    public function getReferenceType(): ?string;

    /**
     * @param string|null $referenceType
     * @return self
     */
    public function setReferenceType(?string $referenceType): self;

    /**
     * @return int|null
     */
    public function getReferenceId(): ?int;

    /**
     * @param int|null $referenceId
     * @return self
     */
    public function setReferenceId(?int $referenceId): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;
}
