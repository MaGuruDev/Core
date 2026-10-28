<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Pagination;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

/**
 * Magento's pager block needs a collection that knows the total row count. Lists loaded
 * through a repository already hold their page, so this carries only the total.
 *
 * @package MaGuru\Core\Model\Pagination
 */
class TotalOnlyCollection extends Collection
{
    /**
     * @param int $total
     */
    public function __construct(int $total)
    {
        parent::__construct(new class implements EntityFactoryInterface {
            /**
             * @param string $type
             * @param array<string, mixed> $arguments
             * @return object
             */
            public function create($type, array $arguments = []): object
            {
                throw new \LogicException('A total-only collection holds no entities.');
            }
        });
        $this->_totalRecords = $total;
        $this->_isCollectionLoaded = true;
    }
}
