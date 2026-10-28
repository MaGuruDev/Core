<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Config\Backend;

use Magento\Framework\App\Config\Value as BaseValue;
use Magento\Framework\Exception\LocalizedException;

/**
 * Backend model for admin-editable carrier/bank API addresses: keeps API
 * credentials and signed requests off plain HTTP. http:// stays possible for
 * local or internal hosts only, so mock servers keep working in development.
 *
 * @package MaGuru\Core\Model\Config\Backend
 */
class ApiUrl extends BaseValue
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave(): static
    {
        $value = trim((string) $this->getValue());
        if ($value !== '') {
            $this->validateUrl($value);
        }
        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * @param string $url
     * @return void
     * @throws LocalizedException
     */
    public function validateUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '' || isset($parts['user']) || isset($parts['pass']) || !in_array($scheme, ['http', 'https'], true)) {
            throw new LocalizedException(__('Please enter a valid URL, for example https://api.example.com/.'));
        }

        if ($scheme === 'http' && !$this->isInternalHost($host)) {
            throw new LocalizedException(__('The URL must use https. Plain http is allowed for local hosts only.'));
        }
    }

    /**
     * @param string $host
     * @return bool
     */
    private function isInternalHost(string $host): bool
    {
        if ($host === 'localhost' || !str_contains($host, '.')) {
            return true;
        }
        foreach (['.local', '.localhost', '.test', '.internal'] as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }

        return false;
    }
}
