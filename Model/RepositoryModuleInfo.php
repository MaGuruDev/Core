<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Model;

use Magento\Framework\DataObject;
use MaGuru\Core\Api\RepositoryModuleInfoInterface;

/**
 * Class RepositoryModuleInfo
 *
 * @package MaGuru\Core\Model
 */
class RepositoryModuleInfo implements RepositoryModuleInfoInterface
{
    /**
     * @inheritDoc
     */
    public function getList(): array
    {
        return array_map(function ($data) {
            return new DataObject($data);
        }, $this->load());
    }

    /**
     * @return array
     */
    private function load(): array
    {
        return [
            'MaGuru_MonoCheckout' => [
                'name'        => 'MaGuru_MonoCheckout',
                'description' => 'MaGuru MonoCheckout Extension',
                'is_active'   => true,
                'version'     => '1.0.1',
                'change_log'  => 'https://github.com/MaGuruDev/MonoCheckout/blob/main/CHANGELOG.md',
                'user_guide'  => 'https://github.com/MaGuruDev/MonoCheckout/blob/main/README.md',
            ]
        ];
    }
}