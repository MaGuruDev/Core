<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Api;

/**
 * Interface RepositoryModuleInfoInterface
 *
 * @package MaGuru\Core\Api
 */
interface RepositoryModuleInfoInterface
{
    /**
     * @return array<int, \Magento\Framework\DataObject>
     */
    public function getList(): array;
}