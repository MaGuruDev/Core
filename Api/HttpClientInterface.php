<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Api;

use MaGuru\Core\Exception\ApiException;

/**
 * Interface HttpClientInterface
 *
 * @package MaGuru\Core\Api
 */
interface HttpClientInterface
{
    /**
     * @param  array<string, string> $headers
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function get(string $path, array $headers = []): array;

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, string> $headers
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function post(string $path, array $body, array $headers = []): array;

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, string> $headers
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function put(string $path, array $body, array $headers = []): array;

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, string> $headers
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function patch(string $path, array $body, array $headers = []): array;

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, string> $headers
     * @return array<string, mixed>
     * @throws ApiException
     */
    public function delete(string $path, array $body = [], array $headers = []): array;
}
