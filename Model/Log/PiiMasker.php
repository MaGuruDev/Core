<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Log;

/**
 * Replaces credentials and personal data in a request/response tree before it reaches
 * a debug log. Keys are matched case-insensitively at any depth.
 *
 * @package MaGuru\Core\Model\Log
 */
final class PiiMasker
{
    private const MASK = '****';

    private const SENSITIVE_KEYS = [
        'authorization', 'token', 'access_token', 'refresh_token', 'password', 'pin', 'pin_code',
        'license_key', 'secret', 'email', 'phone', 'address', 'delivery_address',
        'first_name', 'last_name', 'surname', 'patronymic', 'firstname', 'lastname',
        'customer_name', 'buyer_name', 'client_name', 'card_mask', 'rrn',
    ];

    /**
     * @param array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    public static function mask(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                $data[$key] = self::MASK;
            } elseif (is_array($value)) {
                $data[$key] = self::mask($value);
            }
        }

        return $data;
    }
}
