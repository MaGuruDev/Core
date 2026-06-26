<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class AbstractHandler
 *
 * @package MaGuru\Core\Logger
 */
abstract class AbstractHandler extends Base
{
    protected $loggerType = Logger::WARNING;
}
