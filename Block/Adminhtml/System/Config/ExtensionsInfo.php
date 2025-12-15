<?php
/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */

declare(strict_types=1);

namespace MaGuru\Core\Block\Adminhtml\System\Config;

use Magento\Framework\DataObject;
use MaGuru\Core\Api\ModuleVersionInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Config\Block\System\Config\Form\Field;
use MaGuru\Core\Api\RepositoryModuleInfoInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ExtensionsInfo
 *
 * @package MaGuru\Core\Block\Adminhtml\System\Config
 */
class ExtensionsInfo extends Field
{
    const STATUS_INSTALLED = 'installed';
    const STATUS_NEW       = 'new';
    const TABLE_MAPPING    = [
            'name'       => 'Extension Name',
            'version'    => 'Version',
            'change_log' => 'Change Log',
            'user_guide' => 'User Guide',
            'link'       => 'Download Link',
        ];

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ModuleVersionInterface
     */
    private $moduleVersion;

    /**
     * @var RepositoryModuleInfoInterface
     */
    private $repositoryModuleInfo;

    /**
     * ExtensionsInfo constructor.
     *
     * @param Context                       $context
     * @param ModuleListInterface           $moduleList
     * @param ModuleVersionInterface        $moduleVersion
     * @param RepositoryModuleInfoInterface $repositoryModuleInfo
     * @param array                         $data
     * @param SecureHtmlRenderer|null       $secureRenderer
     */
    public function __construct(
        Context                       $context,
        ModuleListInterface           $moduleList,
        ModuleVersionInterface        $moduleVersion,
        RepositoryModuleInfoInterface $repositoryModuleInfo,
        array                         $data = [],
        ?SecureHtmlRenderer           $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);

        $this->moduleList = $moduleList;
        $this->moduleVersion = $moduleVersion;
        $this->repositoryModuleInfo = $repositoryModuleInfo;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $repositoryModules = $this->repositoryModuleInfo->getList();
        if (empty($repositoryModules)) {
            return '';
        }

        $sortedData = [];

        foreach ($repositoryModules as $moduleName => $repositoryModule) {
            $moduleData = $this->moduleList->getOne($moduleName);

            $sortedData[$moduleData ? self::STATUS_INSTALLED : self::STATUS_NEW][$moduleName] = $this->makeArrayData($repositoryModule, $moduleName);
        }

        return $this->convertArrayToTable($sortedData);
    }

    /**
     * @param DataObject $module
     * @param string     $moduleName
     *
     * @return array
     */
    private function makeArrayData(DataObject $module, string $moduleName): array
    {
        $data = [];

        foreach (self::TABLE_MAPPING as $key => $label) {
            $data[$key] = match ($key) {
                'version' => $this->getVersionResult($moduleName, $module),
                'change_log', 'user_guide', 'link' => $this->getLinkResult($module->getData($key)),
                default => $module->getData($key),
            };
        }

        return $data;
    }

    /**
     * @param $link
     *
     * @return string
     */
    private function getLinkResult($link): string
    {
        return '<a target="_blank" href="' . $link . '">' . __('Link') . '</a>';
    }

    /**
     * @param string     $moduleName
     * @param DataObject $module
     *
     * @return string
     */
    private function getVersionResult(string $moduleName, DataObject $module): string
    {
        $moduleVersionInstalled = $this->moduleVersion->getModuleVersion($moduleName);
        $repositoryVersion = $module->getData('version');
        if (!empty($moduleVersionInstalled) && version_compare($moduleVersionInstalled, $repositoryVersion) < 0) {
            return $moduleVersionInstalled . ' -> ' . $repositoryVersion;
        } else {
            if (!empty($moduleVersionInstalled) && version_compare($moduleVersionInstalled, $repositoryVersion) >= 0) {
                return $moduleVersionInstalled;
            }
        }

        return $repositoryVersion;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function convertArrayToTable(array $data): string
    {
        $html = '<h3>' . __('Instaled Extensions') . '</h3>';
        $html .= '<table class="admin__data-grid-wrap data-grid">';

        $htmlHead = '<thead><tr>';
        foreach (self::TABLE_MAPPING as $key => $label) {
            $htmlHead .= '<th class="data-grid-th">' . __($label) . '</th>';
        }
        $htmlHead .= '</tr></thead>';

        $html .= $htmlHead . '<tbody>';

        foreach ($data[self::STATUS_INSTALLED] ?? [] as $moduleData) {
            $html .= '<tr class="data-row">';
            foreach (self::TABLE_MAPPING as $key => $label) {
                $html .= '<td>' . ($moduleData[$key] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table><br/>';

        $html .= '<h3>' . __('New Extensions') . '</h3>';
        $html .= '<table class="admin__data-grid-wrap data-grid">';
        $html .= $htmlHead . '<tbody>';

        foreach ($data[self::STATUS_NEW] ?? [] as $moduleData) {
            $html .= '<tr class="data-row">';
            foreach (self::TABLE_MAPPING as $key => $label) {
                $html .= '<td>' . ($moduleData[$key] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}