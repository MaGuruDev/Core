<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Pagination;

use MaGuru\Core\Model\Pagination\TotalOnlyCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class TotalOnlyCollectionTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Pagination
 */
class TotalOnlyCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testReportsTheTotalAndTheNumberOfPages(): void
    {
        $collection = new TotalOnlyCollection(95);
        $collection->setPageSize(20)->setCurPage(2);

        $this->assertSame(95, $collection->getSize());
        $this->assertSame(5, $collection->getLastPageNumber());
        $this->assertSame(2, $collection->getCurPage());
    }
}
