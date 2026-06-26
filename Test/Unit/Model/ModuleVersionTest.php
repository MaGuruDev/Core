<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model;

use MaGuru\Core\Model\ModuleVersion;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleVersionTest
 *
 * @package MaGuru\Core\Test\Unit\Model
 */
class ModuleVersionTest extends TestCase
{
    private DriverInterface&MockObject $driver;
    private Reader&MockObject $moduleReader;
    private ModuleListInterface&MockObject $moduleList;
    private SerializerInterface&MockObject $serializer;
    private ModuleVersion $moduleVersion;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->driver        = $this->createMock(DriverInterface::class);
        $this->moduleReader  = $this->createMock(Reader::class);
        $this->moduleList    = $this->createMock(ModuleListInterface::class);
        $this->serializer    = $this->createMock(SerializerInterface::class);

        $this->moduleVersion = new ModuleVersion(
            $this->driver,
            $this->moduleReader,
            $this->moduleList,
            $this->serializer
        );
    }

    /**
     * @return void
     */
    public function testGetModuleVersionReadsFromComposerJson(): void
    {
        $this->moduleList->method('getOne')->with('MaGuru_Core')->willReturn(['setup_version' => '1.0.0']);
        $this->moduleReader->method('getModuleDir')->willReturn('/path/to/module');
        $this->driver->method('fileGetContents')->willReturn('{"version":"1.1.4"}');
        $this->serializer->method('unserialize')->willReturn(['version' => '1.1.4']);

        $this->assertSame('1.1.4', $this->moduleVersion->getModuleVersion('MaGuru_Core'));
    }

    /**
     * @return void
     */
    public function testGetModuleVersionFallsBackToSetupVersion(): void
    {
        $this->moduleList->method('getOne')->willReturn(['setup_version' => '1.0.0']);
        $this->moduleReader->method('getModuleDir')->willReturn('/path/to/module');
        $this->driver->method('fileGetContents')->willReturn('{}');
        $this->serializer->method('unserialize')->willReturn([]);

        $this->assertSame('1.0.0', $this->moduleVersion->getModuleVersion('MaGuru_Core'));
    }

    /**
     * @return void
     */
    public function testGetModuleVersionReturnsEmptyStringForUnknownModule(): void
    {
        $this->moduleList->method('getOne')->willReturn(null);

        $this->assertSame('', $this->moduleVersion->getModuleVersion('Unknown_Module'));
    }

    /**
     * @return void
     */
    public function testGetModuleVersionReturnsEmptyStringWhenFileReadFails(): void
    {
        $this->moduleList->method('getOne')->willReturn(['setup_version' => null]);
        $this->moduleReader->method('getModuleDir')->willThrowException(new \RuntimeException('Module dir not found'));

        $this->assertSame('', $this->moduleVersion->getModuleVersion('MaGuru_Core'));
    }
}
