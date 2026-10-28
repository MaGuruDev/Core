<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\ModuleInfo;

use MaGuru\Core\Api\ModuleVersionInterface;
use MaGuru\Core\Model\ModuleInfo\Cache;
use MaGuru\Core\Model\ModuleInfo\Registry;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class RegistryTest
 *
 * @package MaGuru\Core\Test\Unit\Model\ModuleInfo
 */
class RegistryTest extends TestCase
{
    private Curl&MockObject $curl;
    private Cache&MockObject $cache;
    private StoreManagerInterface&MockObject $storeManager;
    private ProductMetadataInterface&MockObject $productMetadata;
    private ModuleVersionInterface&MockObject $moduleVersion;
    private \Magento\Framework\App\Config\ScopeConfigInterface&MockObject $scopeConfig;
    private LoggerInterface&MockObject $logger;
    private Registry $registry;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->curl            = $this->createMock(Curl::class);
        $this->cache           = $this->createMock(Cache::class);
        $this->storeManager    = $this->createMock(StoreManagerInterface::class);
        $this->productMetadata = $this->createMock(ProductMetadataInterface::class);
        $this->moduleVersion   = $this->createMock(ModuleVersionInterface::class);
        $this->logger          = $this->createMock(LoggerInterface::class);
        $this->scopeConfig     = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfig->method('isSetFlag')->willReturn(true);

        $this->registry = new Registry(
            $this->curl,
            $this->cache,
            $this->storeManager,
            $this->productMetadata,
            $this->moduleVersion,
            $this->logger,
            $this->scopeConfig
        );
    }

    /**
     * @return void
     */
    public function testGetModulesReturnsCachedDataWithoutHttpCall(): void
    {
        $modules = [['name' => 'MaGuru_Core', 'version' => '1.1.4']];
        $this->cache->method('get')->willReturn($modules);
        $this->curl->expects($this->never())->method('get');

        $this->assertSame($modules, $this->registry->getModules());
    }

    /**
     * @return void
     */
    public function testGetModulesFetchesFromRemoteAndCaches(): void
    {
        $modules = [['name' => 'MaGuru_Core', 'version' => '1.1.4']];
        $this->cache->method('get')->willReturn(null);
        $this->cache->expects($this->once())->method('set');

        $this->setUpHttpMocks();
        $this->curl->method('getBody')->willReturn(json_encode(['modules' => $modules]));

        $this->assertSame($modules, $this->registry->getModules());
    }

    /**
     * @return void
     */
    public function testRegistryIsNotContactedUnlessTheMerchantOptedIn(): void
    {
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn(false);
        $registry = new Registry(
            $this->curl,
            $this->cache,
            $this->storeManager,
            $this->productMetadata,
            $this->moduleVersion,
            $this->logger,
            $scopeConfig
        );
        $this->cache->expects($this->never())->method('get');
        $this->curl->expects($this->never())->method('get');

        $this->assertFalse($registry->isUsageInfoEnabled());
        $this->assertSame([], $registry->getModules());
    }

    /**
     * @return void
     */
    public function testStoreDetailsAreSentWhenTheMerchantOptedIn(): void
    {
        $this->cache->method('get')->willReturn(null);
        $this->setUpHttpMocks();
        $this->curl->method('getBody')->willReturn(json_encode(['modules' => [['name' => 'MaGuru_Core']]]));

        $headers = [];
        $this->curl->method('addHeader')->willReturnCallback(function (string $name) use (&$headers): void {
            $headers[] = $name;
        });

        $this->registry->getModules();

        $this->assertContains('X-Source-Domain', $headers);
        $this->assertContains('X-PHP-Version', $headers);
    }

    /**
     * @return void
     */
    public function testGetModulesReturnsEmptyArrayOnInvalidJson(): void
    {
        $this->cache->method('get')->willReturn(null);
        $this->setUpHttpMocks();
        $this->curl->method('getBody')->willReturn('not-valid-json{');
        $this->logger->expects($this->once())->method('error');

        $this->assertSame([], $this->registry->getModules());
    }

    /**
     * @return void
     */
    public function testGetModulesReturnsEmptyArrayOnNetworkException(): void
    {
        $this->cache->method('get')->willReturn(null);
        $this->setUpHttpMocks();
        $this->curl->method('get')->willThrowException(new \Exception('Connection refused'));
        $this->logger->expects($this->once())->method('error');

        $this->assertSame([], $this->registry->getModules());
    }

    /**
     * @return void
     */
    private function setUpHttpMocks(): void
    {
        $store = $this->createMock(Store::class);
        $store->method('getBaseUrl')->willReturn('https://example.com/');
        $this->storeManager->method('getStore')->willReturn($store);
        $this->productMetadata->method('getVersion')->willReturn('2.4.8');
        $this->moduleVersion->method('getModuleVersion')->willReturn('1.1.4');
    }
}
