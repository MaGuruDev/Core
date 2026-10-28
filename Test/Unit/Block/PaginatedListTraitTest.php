<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Block;

use MaGuru\Core\Block\PaginatedListTrait;
use Magento\Framework\App\RequestInterface;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\TestCase;

/**
 * Class PaginatedListTraitTest
 *
 * @package MaGuru\Core\Test\Unit\Block
 */
class PaginatedListTraitTest extends TestCase
{
    /**
     * @param array<string, mixed> $params
     * @param Pager|null $pager
     * @return PaginatedListTraitHarness
     */
    private function block(array $params, ?Pager $pager = null): PaginatedListTraitHarness
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->willReturnCallback(fn (string $name, $default = null) => $params[$name] ?? $default);

        return new PaginatedListTraitHarness($request, $pager);
    }

    /**
     * @return void
     */
    public function testPageAndSizeAreClamped(): void
    {
        $this->assertSame(1, $this->block(['p' => '-4'])->page());
        $this->assertSame(3, $this->block(['p' => '3'])->page());
        $this->assertSame(20, $this->block([])->size());
        $this->assertSame(50, $this->block(['limit' => '50'])->size());
        $this->assertSame(20, $this->block(['limit' => '100000'])->size());
    }

    /**
     * @return void
     */
    public function testNoPagerHtmlWhenThereAreNoRows(): void
    {
        $pager = $this->createMock(Pager::class);
        $pager->expects($this->never())->method('toHtml');

        $this->assertSame('', $this->block([], $pager)->getPagerHtml());
    }

    /**
     * @return void
     */
    public function testPagerReceivesTheRememberedTotalAndPageSize(): void
    {
        $pager = $this->createMock(Pager::class);
        $pager->method('setAvailableLimit')->willReturnSelf();
        $pager->method('setShowPerPage')->willReturnSelf();
        $pager->expects($this->once())->method('setLimit')->with(10)->willReturnSelf();
        $pager->expects($this->once())->method('setCollection')
            ->with($this->callback(fn ($collection): bool => $collection->getSize() === 42))
            ->willReturnSelf();
        $pager->method('toHtml')->willReturn('<nav/>');

        $block = $this->block(['limit' => '10'], $pager);
        $block->total(42);

        $this->assertSame('<nav/>', $block->getPagerHtml());
    }
}

/**
 * Class PaginatedListTraitHarness
 *
 * @package MaGuru\Core\Test\Unit\Block
 */
class PaginatedListTraitHarness
{
    use PaginatedListTrait;

    /**
     * @param RequestInterface $request
     * @param Pager|null $pager
     * @return void
     */
    public function __construct(private readonly RequestInterface $request, private readonly ?Pager $pager)
    {
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param string $alias
     * @return Pager|null
     */
    public function getChildBlock(string $alias): ?Pager
    {
        return $this->pager;
    }

    /**
     * @return int
     */
    public function page(): int
    {
        return $this->getCurrentPage();
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return $this->getPageSize();
    }

    /**
     * @param int $n
     * @return void
     */
    public function total(int $n): void
    {
        $this->rememberTotal($n);
    }
}
