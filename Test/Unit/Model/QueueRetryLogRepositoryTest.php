<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Model\Data\QueueRetryLog;
use MaGuru\Core\Model\Data\QueueRetryLogFactory;
use MaGuru\Core\Model\QueueRetryLogRepository;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog as QueueRetryLogResource;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog\Collection;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueRetryLogRepositoryTest
 *
 * @package MaGuru\Core\Test\Unit\Model
 */
class QueueRetryLogRepositoryTest extends TestCase
{
    private QueueRetryLogFactory&MockObject $logFactory;
    private QueueRetryLogResource&MockObject $logResource;
    private CollectionFactory&MockObject $collectionFactory;
    private DateTime&MockObject $dateTime;
    private QueueRetryLogRepository $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->logFactory       = $this->createMock(QueueRetryLogFactory::class);
        $this->logResource      = $this->createMock(QueueRetryLogResource::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->dateTime          = $this->createMock(DateTime::class);

        $this->repository = new QueueRetryLogRepository(
            $this->logFactory,
            $this->logResource,
            $this->collectionFactory,
            $this->dateTime,
        );
    }

    /**
     * @return void
     */
    public function testSaveCallsResourceSave(): void
    {
        $log = $this->createMock(QueueRetryLog::class);
        $this->logResource->expects($this->once())->method('save')->with($log);

        $result = $this->repository->save($log);
        $this->assertSame($log, $result);
    }

    /**
     * @return void
     */
    public function testSaveThrowsWhenNotAbstractModel(): void
    {
        $log = $this->createMock(QueueRetryLogInterface::class);
        $this->expectException(CouldNotSaveException::class);
        $this->repository->save($log);
    }

    /**
     * @return void
     */
    public function testSaveWrapsResourceExceptions(): void
    {
        $log = $this->createMock(QueueRetryLog::class);
        $this->logResource->method('save')->willThrowException(new \RuntimeException('db down'));

        $this->expectException(CouldNotSaveException::class);
        $this->repository->save($log);
    }

    /**
     * @return void
     */
    public function testGetByIdReturnsLogWhenFound(): void
    {
        $log = $this->createMock(QueueRetryLog::class);
        $log->method('getEntityId')->willReturn(7);
        $this->logFactory->method('create')->willReturn($log);

        $result = $this->repository->getById(7);
        $this->assertSame($log, $result);
    }

    /**
     * @return void
     */
    public function testGetByIdThrowsWhenNotFound(): void
    {
        $log = $this->createMock(QueueRetryLog::class);
        $log->method('getEntityId')->willReturn(null);
        $this->logFactory->method('create')->willReturn($log);

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById(99);
    }

    /**
     * @return void
     */
    public function testGetDueForRetryFiltersByStatusAndNextRetryAt(): void
    {
        $log = $this->createMock(QueueRetryLog::class);
        $collection = $this->createMock(Collection::class);

        $this->dateTime->method('gmtDate')->willReturn('2026-07-19 12:00:00');

        $collection->expects($this->exactly(2))->method('addFieldToFilter')->willReturnSelf();
        $collection->method('setOrder')->willReturnSelf();
        $collection->method('setPageSize')->with(50)->willReturnSelf();
        $collection->method('getItems')->willReturn([$log]);

        $this->collectionFactory->method('create')->willReturn($collection);

        $results = $this->repository->getDueForRetry(50);
        $this->assertSame([$log], $results);
    }
}
