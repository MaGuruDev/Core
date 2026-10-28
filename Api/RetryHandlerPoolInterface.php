<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

use Magento\Framework\Exception\NotFoundException;

/**
 * Topic to RetryableHandlerInterface registry. Modules add items to the "handlers" argument of
 * MaGuru\Core\Model\Queue\RetryHandlerPool in their di.xml.
 *
 * @api
 *
 * @package MaGuru\Core\Api
 */
interface RetryHandlerPoolInterface
{
    /**
     * @param string $topic
     * @return bool
     */
    public function has(string $topic): bool;

    /**
     * @param string $topic
     * @return RetryableHandlerInterface
     * @throws NotFoundException
     */
    public function get(string $topic): RetryableHandlerInterface;
}
