<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Block;

use MaGuru\Core\Model\Pagination\TotalOnlyCollection;

/**
 * Page number, page size and the pager for storefront list blocks. The block needs a child
 * block named "pager" (Magento\Theme\Block\Html\Pager) in its layout and calls
 * rememberTotal() after loading the page.
 *
 * @package MaGuru\Core\Block
 */
trait PaginatedListTrait
{
    /**
     * @var int
     */
    private int $paginatedTotal = 0;

    /**
     * @return int
     */
    protected function getCurrentPage(): int
    {
        return max(1, (int) $this->getRequest()->getParam('p', 1));
    }

    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        $limit = (int) $this->getRequest()->getParam('limit', 20);

        return in_array($limit, [10, 20, 50], true) ? $limit : 20;
    }

    /**
     * @param int $total
     * @return void
     */
    protected function rememberTotal(int $total): void
    {
        $this->paginatedTotal = $total;
    }

    /**
     * @return string
     */
    public function getPagerHtml(): string
    {
        $pager = $this->getChildBlock('pager');
        if (!$pager instanceof \Magento\Theme\Block\Html\Pager || $this->paginatedTotal <= 0) {
            return '';
        }

        $pager->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
            ->setShowPerPage(true)
            ->setLimit($this->getPageSize())
            ->setCollection(new TotalOnlyCollection($this->paginatedTotal));

        return $pager->toHtml();
    }
}
