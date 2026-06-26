<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AbstractConfig
 *
 * @package MaGuru\Core\Model
 */
abstract class AbstractConfig
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {}

    /**
     * @param string $path
     * @param int|string|null $scopeCode
     * @return string
     */
    protected function getValue(string $path, int|string|null $scopeCode = null): string
    {
        return (string)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @param string $path
     * @param int|string|null $scopeCode
     * @return int
     */
    protected function getIntValue(string $path, int|string|null $scopeCode = null): int
    {
        return (int)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @param string $path
     * @param int|string|null $scopeCode
     * @return bool
     */
    protected function isFlag(string $path, int|string|null $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $scopeCode);
    }
}
