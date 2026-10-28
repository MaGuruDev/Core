<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Config\Backend;

use MaGuru\Core\Model\Config\Backend\ApiUrl;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiUrlTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Config\Backend
 */
class ApiUrlTest extends TestCase
{
    /**
     * @return ApiUrl
     */
    private function model(): ApiUrl
    {
        return (new \ReflectionClass(ApiUrl::class))->newInstanceWithoutConstructor();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function acceptedProvider(): array
    {
        return [
            'empty'            => [''],
            'https'            => ['https://www.ukrposhta.ua/ecom/0.0.1'],
            'docker service'   => ['http://mock-monobank:8080'],
            'localhost'        => ['http://localhost:8081/api'],
            'private ip'       => ['http://172.18.0.5/api'],
            '.local host'      => ['http://magento249.local/mock'],
        ];
    }

    /**
     * @dataProvider acceptedProvider
     * @param string $value
     * @return void
     */
    public function testAcceptsSafeUrls(string $value): void
    {
        if ($value !== '') {
            $this->model()->validateUrl($value);
        }
        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function rejectedProvider(): array
    {
        return [
            'public http'   => ['http://api.carrier.example.com'],
            'no scheme'     => ['api.carrier.example.com'],
            'ftp'           => ['ftp://api.carrier.example.com'],
            'javascript'    => ['javascript:alert(1)'],
            'userinfo'      => ['https://user:pass@api.carrier.example.com'],
        ];
    }

    /**
     * @dataProvider rejectedProvider
     * @param string $value
     * @return void
     */
    public function testRejectsUnsafeUrls(string $value): void
    {
        $this->expectException(LocalizedException::class);
        $this->model()->validateUrl($value);
    }
}
