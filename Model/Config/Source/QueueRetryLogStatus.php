<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Config\Source;

use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class QueueRetryLogStatus
 *
 * @package MaGuru\Core\Model\Config\Source
 */
class QueueRetryLogStatus implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => QueueRetryLogInterface::STATUS_FAILED,    'label' => (string) __('Failed')],
            ['value' => QueueRetryLogInterface::STATUS_RETRYING,  'label' => (string) __('Retrying')],
            ['value' => QueueRetryLogInterface::STATUS_EXHAUSTED, 'label' => (string) __('Exhausted')],
            ['value' => QueueRetryLogInterface::STATUS_SUCCEEDED, 'label' => (string) __('Succeeded')],
        ];
    }
}
