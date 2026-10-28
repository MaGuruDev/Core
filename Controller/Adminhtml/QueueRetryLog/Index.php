<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Controller\Adminhtml\QueueRetryLog;

use Magento\Backend\App\Action as BaseAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package MaGuru\Core\Controller\Adminhtml\QueueRetryLog
 */
class Index extends BaseAction implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'MaGuru_Core::queue_retry_log';

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                      $context,
        private readonly PageFactory $pageFactory,
    ) {
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->setActiveMenu('MaGuru_Core::queue_retry_log');
        $page->getConfig()->getTitle()->prepend((string) __('Queue Retry Log'));
        return $page;
    }
}
