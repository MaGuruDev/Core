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
 */
interface RepositoryModuleInfoInterface
{
    /**
     * @return array
     */
    public function getList(): array;
}