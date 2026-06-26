<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

namespace MaGuru\Core\Model;

use Magento\Framework\DataObject;
use MaGuru\Core\Api\RepositoryModuleInfoInterface;
use MaGuru\Core\Model\ModuleInfo\Registry;

/**
 * Class RepositoryModuleInfo
 * @package MaGuru\Core\Model
 */
class RepositoryModuleInfo implements RepositoryModuleInfoInterface
{
    /**
     * RepositoryModuleInfo constructor.
     * @param Registry $registry
     */
    public function __construct(
        private readonly Registry $registry,
    ) {}

    /**
     * @inheritDoc
     */
    public function getList(): array
    {
        return array_map(function ($data) {
            return new DataObject($data);
        }, $this->load());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function load(): array
    {
        return $this->registry->getModules();
    }
}
