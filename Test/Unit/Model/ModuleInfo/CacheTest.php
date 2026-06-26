<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\ModuleInfo;

use MaGuru\Core\Model\ModuleInfo\Cache;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CacheTest
 *
 * @package MaGuru\Core\Test\Unit\Model\ModuleInfo
 */
class CacheTest extends TestCase
{
    private CacheInterface&MockObject $cacheBackend;
    private SerializerInterface&MockObject $serializer;
    private Cache $cache;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cacheBackend = $this->createMock(CacheInterface::class);
        $this->serializer   = $this->createMock(SerializerInterface::class);
        $this->cache        = new Cache($this->cacheBackend, $this->serializer);
    }

    /**
     * @return void
     */
    public function testGetReturnsCachedArray(): void
    {
        $data = ['name' => 'MaGuru_Core', 'version' => '1.1.4'];
        $this->cacheBackend->method('load')->willReturn('serialized');
        $this->serializer->method('unserialize')->willReturn($data);

        $this->assertSame($data, $this->cache->get('test_key'));
    }

    /**
     * @return void
     */
    public function testGetReturnsNullOnCacheMiss(): void
    {
        $this->cacheBackend->method('load')->willReturn(false);

        $this->assertNull($this->cache->get('missing_key'));
    }

    /**
     * @return void
     */
    public function testGetReturnsNullWhenUnserializeFails(): void
    {
        $this->cacheBackend->method('load')->willReturn('corrupted');
        $this->serializer->method('unserialize')->willThrowException(new \InvalidArgumentException());

        $this->assertNull($this->cache->get('bad_key'));
    }

    /**
     * @return void
     */
    public function testGetReturnsNullWhenUnserializedValueIsNotArray(): void
    {
        $this->cacheBackend->method('load')->willReturn('serialized_scalar');
        $this->serializer->method('unserialize')->willReturn('just_a_string');

        $this->assertNull($this->cache->get('scalar_key'));
    }

    /**
     * @return void
     */
    public function testSetSavesSerializedDataToCache(): void
    {
        $data = ['foo' => 'bar'];
        $this->serializer->method('serialize')->willReturn('serialized_data');

        $this->cacheBackend->expects($this->once())
            ->method('save')
            ->with('serialized_data', 'maguru_core_my_key', ['maguru_core'], 3600);

        $this->cache->set('my_key', $data);
    }
}
