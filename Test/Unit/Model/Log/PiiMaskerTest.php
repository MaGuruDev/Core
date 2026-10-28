<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Model\Log;

use MaGuru\Core\Model\Log\PiiMasker;
use PHPUnit\Framework\TestCase;

/**
 * Class PiiMaskerTest
 *
 * @package MaGuru\Core\Test\Unit\Model\Log
 */
class PiiMaskerTest extends TestCase
{
    /**
     * @return void
     */
    public function testMasksSensitiveKeysAtAnyDepthAndKeepsTheRest(): void
    {
        $masked = PiiMasker::mask([
            'id' => 'receipt-1',
            'Email' => 'buyer@example.com',
            'delivery' => ['email' => 'x@example.com', 'phone' => '+380501112233'],
            'goods' => [['good' => ['name' => 'Cable', 'code' => 'SKU1'], 'quantity' => 1000]],
            'Authorization' => 'Bearer abc',
        ]);

        $this->assertSame('receipt-1', $masked['id']);
        $this->assertSame('****', $masked['Email']);
        $this->assertSame('****', $masked['delivery']['email']);
        $this->assertSame('****', $masked['delivery']['phone']);
        $this->assertSame('****', $masked['Authorization']);
        $this->assertSame('Cable', $masked['goods'][0]['good']['name']);
        $this->assertSame(1000, $masked['goods'][0]['quantity']);
    }
}
