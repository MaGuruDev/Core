<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Api\QueueRetryLogRepositoryInterface;
use MaGuru\Core\Model\Data\QueueRetryLogFactory;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog as QueueRetryLogResource;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class QueueRetryLogRepository
 *
 * @package MaGuru\Core\Model
 */
class QueueRetryLogRepository implements QueueRetryLogRepositoryInterface
{
    /**
     * @param QueueRetryLogFactory $logFactory
     * @param QueueRetryLogResource $logResource
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly QueueRetryLogFactory  $logFactory,
        private readonly QueueRetryLogResource $logResource,
        private readonly CollectionFactory     $collectionFactory,
        private readonly DateTime              $dateTime,
    ) {
    }

    /**
     * @param QueueRetryLogInterface $log
     * @return QueueRetryLogInterface
     * @throws CouldNotSaveException
     */
    public function save(QueueRetryLogInterface $log): QueueRetryLogInterface
    {
        if (!$log instanceof AbstractModel) {
            throw new CouldNotSaveException(__('QueueRetryLog model must extend AbstractModel'));
        }
        try {
            $this->logResource->save($log);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save queue retry log: %1', $e->getMessage()), $e);
        }
        return $log;
    }

    /**
     * @param int $entityId
     * @return QueueRetryLogInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): QueueRetryLogInterface
    {
        $log = $this->logFactory->create();
        $this->logResource->load($log, $entityId);
        if (!$log->getEntityId()) {
            throw new NoSuchEntityException(__('Queue retry log with ID "%1" does not exist.', $entityId));
        }
        return $log;
    }

    /**
     * @param int $limit
     * @return array<int, QueueRetryLogInterface>
     */
    public function getDueForRetry(int $limit = 50): array
    {
        $now = $this->dateTime->gmtDate();
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', ['in' => [
            QueueRetryLogInterface::STATUS_FAILED,
            QueueRetryLogInterface::STATUS_RETRYING,
        ]])
            ->addFieldToFilter('next_retry_at', [
                ['null' => true],
                ['lteq' => $now],
            ])
            ->setOrder('created_at', 'ASC')
            ->setPageSize($limit);

        return array_values(array_filter(
            $collection->getItems(),
            static fn(mixed $item): bool => $item instanceof QueueRetryLogInterface
        ));
    }
}
