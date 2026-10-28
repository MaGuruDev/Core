<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 *
 * @package MaGuru\Core\Api
 */
interface QueueRetryLogRepositoryInterface
{
    /**
     * @param QueueRetryLogInterface $log
     * @return QueueRetryLogInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueRetryLogInterface $log): QueueRetryLogInterface;

    /**
     * @param int $entityId
     * @return QueueRetryLogInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): QueueRetryLogInterface;

    /**
     * Failed or retrying rows whose next_retry_at has passed or is unset, oldest first.
     *
     * @param int $limit
     * @return array<int, QueueRetryLogInterface>
     */
    public function getDueForRetry(int $limit = 50): array;
}
