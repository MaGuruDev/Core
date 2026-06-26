<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

namespace MaGuru\Core\Model\ModuleInfo;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\Store;
use MaGuru\Core\Api\ModuleVersionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Registry
 * @package MaGuru\Core\Model\ModuleInfo
 */
class Registry
{
    private const REGISTRY_URL = 'https://maguru-registry.maguru.workers.dev/modules.json';
    private const CACHE_KEY = 'maguru_core_registry_modules';

    /**
     * Registry constructor.
     * @param Curl $curl
     * @param Cache $cache
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleVersionInterface $moduleVersion
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Curl                     $curl,
        private readonly Cache                    $cache,
        private readonly StoreManagerInterface    $storeManager,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly ModuleVersionInterface   $moduleVersion,
        private readonly LoggerInterface          $logger
    ) {}

    /**
     * @return array<mixed>
     */
    public function getModules(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $this->curl->setTimeout(5);
            $this->curl->addHeader('User-Agent', 'MaGuru-Magento/1.0');
            $this->curl->addHeader('X-Source-Domain', $this->getStoreDomain());
            $this->curl->addHeader('X-MaGuru-Version', $this->moduleVersion->getModuleVersion('MaGuru_Core'));
            $this->curl->addHeader('X-Magento-Version', $this->productMetadata->getVersion());
            $this->curl->addHeader('X-PHP-Version', PHP_VERSION);

            $this->curl->get(self::REGISTRY_URL);

            $data = json_decode($this->curl->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data['modules'])) {
                throw new \RuntimeException('Invalid registry response');
            }

            $this->cache->set(self::CACHE_KEY, $data['modules']);
            return $data['modules'];

        } catch (\Exception $e) {
            $this->logger->error('MaGuru Registry fetch failed: '.$e->getMessage());
            return [];
        }
    }

    private function getStoreDomain(): string
    {
        try {
            $store = $this->storeManager->getStore();
            /** @var Store $store */
            $baseUrl = (string)$store->getBaseUrl();
            $host = parse_url($baseUrl, PHP_URL_HOST);
            return is_string($host) && $host !== '' ? $host : 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }
}
