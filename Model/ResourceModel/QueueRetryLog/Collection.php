<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\ResourceModel\QueueRetryLog;

use MaGuru\Core\Model\Data\QueueRetryLog;
use MaGuru\Core\Model\ResourceModel\QueueRetryLog as QueueRetryLogResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as BaseAbstractCollection;

/**
 * Class Collection
 *
 * @package MaGuru\Core\Model\ResourceModel\QueueRetryLog
 */
class Collection extends BaseAbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(QueueRetryLog::class, QueueRetryLogResource::class);
    }
}
