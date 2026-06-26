<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Model\ModuleInfo;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Cache
 * @package MaGuru\Core\Model\ModuleInfo
 */
class Cache
{
    private const CACHE_PREFIX   = 'maguru_core_';
    private const CACHE_LIFETIME = 3600;

    /**
     * Cache constructor.
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly CacheInterface      $cache,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * @param string $key
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        $cached = $this->cache->load(self::CACHE_PREFIX.$key);

        if (!is_string($cached)) {
            return null;
        }

        try {
            $result = $this->serializer->unserialize($cached);
            return is_array($result) ? $result : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string               $key
     * @param array<string, mixed> $data
     * @return void
     */
    public function set(string $key, array $data): void
    {
        $this->cache->save(
            (string) $this->serializer->serialize($data),
            self::CACHE_PREFIX.$key,
            ['maguru_core'],
            self::CACHE_LIFETIME
        );
    }
}
