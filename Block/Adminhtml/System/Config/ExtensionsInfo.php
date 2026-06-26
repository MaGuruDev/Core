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
    const STATUS_INSTALLED      = 'installed';
    const STATUS_NEW            = 'new';
    const DEV_STATUS_DEVELOPING  = 'in_development';
    const DEV_STATUS_READY       = 'ready';
    const DEV_STATUS_FOR_SALE    = 'for_sale';
    const DEV_STATUS_PUBLIC_REPO = 'public_repo';
    const TABLE_MAPPING         = [
            'name'       => 'Extension Name',
            'version'    => 'Version',
            'dev_status' => 'Dev Status',
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
     * @param array<string, mixed>          $data
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
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $repositoryModules = $this->repositoryModuleInfo->getList();
        if (empty($repositoryModules)) {
            return '';
        }

        $sortedData = [];

        foreach ($repositoryModules as $repositoryModule) {
            $moduleName = (string)$repositoryModule->getData('name');
            $moduleData = $this->moduleList->getOne($moduleName);

            $sortedData[$moduleData ? self::STATUS_INSTALLED : self::STATUS_NEW][$moduleName] = $this->makeArrayData($repositoryModule, $moduleName);
        }

        return $this->convertArrayToTable($sortedData);
    }

    /**
     * @param DataObject $module
     * @param string     $moduleName
     * @return array<string, mixed>
     */
    private function makeArrayData(DataObject $module, string $moduleName): array
    {
        $data = [];

        foreach (self::TABLE_MAPPING as $key => $label) {
            $data[$key] = match ($key) {
                'version'    => $this->getVersionResult($moduleName, $module),
                'dev_status' => $this->getDevStatusBadge((string)$module->getData($key)),
                'change_log', 'user_guide', 'link' => $this->getLinkResult($module->getData($key)),
                default => $module->getData($key),
            };
        }

        return $data;
    }

    /**
     * @param string $status
     * @return string
     */
    private function getDevStatusBadge(string $status): string
    {
        $map = [
            self::DEV_STATUS_DEVELOPING  => ['In Development', '#f59e0b'],
            self::DEV_STATUS_READY       => ['Ready',          '#3b82f6'],
            self::DEV_STATUS_FOR_SALE    => ['For Sale',       '#22c55e'],
            self::DEV_STATUS_PUBLIC_REPO => ['Public Repo',    '#8b5cf6'],
        ];

        [$label, $color] = $map[$status] ?? ['Unknown', '#9ca3af'];
        $style = sprintf(
            'background:%s;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;white-space:nowrap',
            $color
        );

        return '<span style="' . $style . '">' . __($label) . '</span>';
    }

    /**
     * @param mixed $link
     * @return string
     */
    private function getLinkResult(mixed $link): string
    {
        return '<a target="_blank" href="' . $link . '">' . __('Link') . '</a>';
    }

    /**
     * @param string     $moduleName
     * @param DataObject $module
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

        return (string)$repositoryVersion;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $data
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
