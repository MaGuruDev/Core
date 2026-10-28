<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb as BaseAbstractDb;

/**
 * Class QueueRetryLog
 *
 * @package MaGuru\Core\Model\ResourceModel
 */
class QueueRetryLog extends BaseAbstractDb
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('maguru_core_queue_retry_log', 'entity_id');
    }
}
