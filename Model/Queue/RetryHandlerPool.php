<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Model\Queue;

use MaGuru\Core\Api\RetryableHandlerInterface;
use MaGuru\Core\Api\RetryHandlerPoolInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Like Magento\Payment\Gateway\Command\CommandPool: modules add "topic => handler class" items
 * to the "handlers" argument in their di.xml.
 *
 * @package MaGuru\Core\Model\Queue
 */
class RetryHandlerPool implements RetryHandlerPoolInterface
{
    /**
     * @var \ArrayAccess<string, RetryableHandlerInterface>
     */
    private \ArrayAccess $handlers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array<string, class-string<RetryableHandlerInterface>> $handlers Topic => handler
     *        class name (not an instance - TMap instantiates lazily via the object manager, same
     *        convention as Magento\Payment\Gateway\Command\CommandPool's "commands" argument).
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers = [],
    ) {
        $this->handlers = $tmapFactory->create([
            'array' => $handlers,
            'type' => RetryableHandlerInterface::class,
        ]);
    }

    /**
     * @param string $topic
     * @return bool
     */
    public function has(string $topic): bool
    {
        return isset($this->handlers[$topic]);
    }

    /**
     * @param string $topic
     * @return RetryableHandlerInterface
     * @throws NotFoundException
     */
    public function get(string $topic): RetryableHandlerInterface
    {
        $handler = $this->handlers[$topic] ?? null;
        if (!$handler instanceof RetryableHandlerInterface) {
            throw new NotFoundException(__('No retry handler is registered for topic "%1".', $topic));
        }
        return $handler;
    }
}
