<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model;

use MaGuru\Core\Model\ModuleInfo\Registry;
use MaGuru\Core\Model\RepositoryModuleInfo;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RepositoryModuleInfoTest
 *
 * @package MaGuru\Core\Test\Unit\Model
 */
class RepositoryModuleInfoTest extends TestCase
{
    private Registry&MockObject $registry;
    private RepositoryModuleInfo $repositoryModuleInfo;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->registry             = $this->createMock(Registry::class);
        $this->repositoryModuleInfo = new RepositoryModuleInfo($this->registry);
    }

    /**
     * @return void
     */
    public function testGetListReturnsDataObjectsWithCorrectData(): void
    {
        $this->registry->method('getModules')->willReturn([
            ['name' => 'MaGuru_Core', 'version' => '1.1.4'],
            ['name' => 'MaGuru_MonoCore', 'version' => '1.0.0'],
        ]);

        $result = $this->repositoryModuleInfo->getList();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DataObject::class, $result);
        $this->assertSame('MaGuru_Core', $result[0]->getData('name'));
        $this->assertSame('1.0.0', $result[1]->getData('version'));
    }

    /**
     * @return void
     */
    public function testGetListReturnsEmptyArrayWhenRegistryIsEmpty(): void
    {
        $this->registry->method('getModules')->willReturn([]);

        $this->assertSame([], $this->repositoryModuleInfo->getList());
    }
}
