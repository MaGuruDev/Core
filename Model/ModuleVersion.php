<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Model;

use Magento\Framework\Module\Dir\Reader;
use MaGuru\Core\Api\ModuleVersionInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ModuleVersion
 *
 * @package MaGuru\Core\Model
 */
class ModuleVersion implements ModuleVersionInterface
{
    private const FILENAME = 'composer.json';

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * ModuleVersion constructor.
     *
     * @param DriverInterface     $driver
     * @param Reader              $moduleReader
     * @param ModuleListInterface $moduleList
     * @param SerializerInterface $serializer
     */
    public function __construct(
        DriverInterface     $driver,
        Reader              $moduleReader,
        ModuleListInterface $moduleList,
        SerializerInterface $serializer,
    ) {
        $this->driver = $driver;
        $this->moduleReader = $moduleReader;
        $this->moduleList = $moduleList;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function getModuleVersion(string $moduleName): string
    {
        $module = $this->moduleList->getOne($moduleName);
        if ($module) {
            $content = $this->get($moduleName);
            if (!empty($content)) {
                try {
                    $data = $this->serializer->unserialize($content);
                } catch (\Exception $e) {
                    $data = [];
                }
                $version = $data['version'] ?? null;
                $version = empty($version) ? ($module['setup_version'] ?? null) : $version;
                return !empty($version) ? $version : '';
            }
        }

        return '';
    }

    /**
     * @param string $moduleName
     *
     * @return string
     */
    private function get(string $moduleName): string
    {
        try {
            $etcDirectoryPath = $this->moduleReader->getModuleDir(
                '',
                $moduleName
            );
            return $this->driver->fileGetContents(
                $etcDirectoryPath . '/' . self::FILENAME
            );
        } catch (\Throwable $exception) {
            return '';
        }
    }
}