<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
declare(strict_types=1);

namespace MaGuru\Core\Test\Unit\Block\Adminhtml\System\Config;

use MaGuru\Core\Api\ModuleVersionInterface;
use MaGuru\Core\Api\RepositoryModuleInfoInterface;
use MaGuru\Core\Block\Adminhtml\System\Config\ExtensionsInfo;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ExtensionsInfoTest
 *
 * @package MaGuru\Core\Test\Unit\Block\Adminhtml\System\Config
 */
class ExtensionsInfoTest extends TestCase
{
    /**
     * The backend Template base class resolves its helpers through the ObjectManager singleton.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturnCallback(function (string $class): object {
            /** @var class-string $class */
            return $this->createMock($class);
        });
        ObjectManager::setInstance($objectManager);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $property = new \ReflectionProperty(ObjectManager::class, '_instance');
        $property->setValue(null, null);
    }

    /**
     * @param array<string, mixed> $module
     * @return string
     */
    private function render(array $module): string
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn(true);
        $context = $this->createMock(Context::class);
        $context->method('getEscaper')->willReturn(new Escaper());
        $context->method('getScopeConfig')->willReturn($scopeConfig);

        $repository = $this->createMock(RepositoryModuleInfoInterface::class);
        $repository->method('getList')->willReturn([new DataObject($module)]);

        $versions = $this->createMock(ModuleVersionInterface::class);
        $versions->method('getModuleVersion')->willReturn('');

        $block = new ExtensionsInfo(
            $context,
            $this->createMock(ModuleListInterface::class),
            $versions,
            $repository,
            [],
            $this->createMock(\Magento\Framework\View\Helper\SecureHtmlRenderer::class)
        );

        return $block->render($this->createMock(AbstractElement::class));
    }

    /**
     * @return void
     */
    public function testRegistryTextIsEscaped(): void
    {
        $html = $this->render([
            'name' => 'Evil<script>alert(1)</script>',
            'version' => '1.0"><img src=x onerror=alert(1)>',
            'stability' => 'stable',
            'dev_status' => 'ready',
            'change_log' => 'https://example.com/log',
            'user_guide' => 'https://example.com/guide',
            'link' => 'https://example.com/dl',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    /**
     * @return void
     */
    public function testNonHttpsLinksAreDropped(): void
    {
        $html = $this->render([
            'name' => 'MaGuru_Test',
            'version' => '1.0',
            'stability' => 'stable',
            'dev_status' => 'ready',
            'change_log' => 'javascript:alert(1)',
            'user_guide' => 'http://example.com/guide',
            'link' => '" onmouseover="alert(1)',
        ]);

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onmouseover', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    /**
     * @return void
     */
    public function testLocalModulesAreListedWithoutTheRegistryWhenSharingIsOff(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn(false);
        $context = $this->createMock(Context::class);
        $context->method('getEscaper')->willReturn(new Escaper());
        $context->method('getScopeConfig')->willReturn($scopeConfig);

        $repository = $this->createMock(RepositoryModuleInfoInterface::class);
        $repository->expects($this->never())->method('getList');

        $moduleList = $this->createMock(ModuleListInterface::class);
        $moduleList->method('getNames')->willReturn(['Magento_Catalog', 'MaGuru_Core', 'MaGuru_Alpha']);

        $versions = $this->createMock(ModuleVersionInterface::class);
        $versions->method('getModuleVersion')->willReturn('1.2.3');

        $block = new ExtensionsInfo(
            $context,
            $moduleList,
            $versions,
            $repository,
            [],
            $this->createMock(\Magento\Framework\View\Helper\SecureHtmlRenderer::class)
        );
        $html = $block->render($this->createMock(AbstractElement::class));

        $this->assertStringContainsString('MaGuru_Core', $html);
        $this->assertStringContainsString('MaGuru_Alpha', $html);
        $this->assertStringNotContainsString('Magento_Catalog', $html);
        $this->assertStringContainsString('1.2.3', $html);
        $this->assertLessThan(strpos($html, 'MaGuru_Core'), strpos($html, 'MaGuru_Alpha'));
    }
}
