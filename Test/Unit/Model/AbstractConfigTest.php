<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model;

use MaGuru\Core\Model\AbstractConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractConfigTest
 *
 * @package MaGuru\Core\Test\Unit\Model
 */
class AbstractConfigTest extends TestCase
{
    private ScopeConfigInterface&MockObject $scopeConfig;
    private AbstractConfig $config;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->config = new class ($this->scopeConfig) extends AbstractConfig {
            /**
             * @param string $path
             * @param int|string|null $scopeCode
             * @return string
             */
            public function publicGetValue(string $path, int|string|null $scopeCode = null): string
            {
                return $this->getValue($path, $scopeCode);
            }

            /**
             * @param string $path
             * @param int|string|null $scopeCode
             * @return int
             */
            public function publicGetIntValue(string $path, int|string|null $scopeCode = null): int
            {
                return $this->getIntValue($path, $scopeCode);
            }

            /**
             * @param string $path
             * @param int|string|null $scopeCode
             * @return bool
             */
            public function publicIsFlag(string $path, int|string|null $scopeCode = null): bool
            {
                return $this->isFlag($path, $scopeCode);
            }
        };
    }

    /**
     * @return void
     */
    public function testGetValueReturnsStringFromScopeConfig(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('some/config/path', ScopeInterface::SCOPE_STORE, null)
            ->willReturn('config_value');

        $this->assertSame('config_value', $this->config->publicGetValue('some/config/path'));
    }

    /**
     * @return void
     */
    public function testGetIntValueCastsToInt(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('some/config/number', ScopeInterface::SCOPE_STORE, null)
            ->willReturn('42');

        $this->assertSame(42, $this->config->publicGetIntValue('some/config/number'));
    }

    /**
     * @return void
     */
    public function testIsFlagReturnsBoolFromScopeConfig(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('some/config/flag', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(true);

        $this->assertTrue($this->config->publicIsFlag('some/config/flag'));
    }

    /**
     * @return void
     */
    public function testGetValuePassesScopeCodeToScopeConfig(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('some/config/path', ScopeInterface::SCOPE_STORE, 2)
            ->willReturn('store_value');

        $this->assertSame('store_value', $this->config->publicGetValue('some/config/path', 2));
    }
}
