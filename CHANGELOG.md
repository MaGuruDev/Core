# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-10-28

### Added

- `Block\PaginatedListTrait` and `Model\Pagination\TotalOnlyCollection` - shared pager support for storefront lists of the B2B modules (page and limit clamped, total remembered once)
- `Model\Log\PiiMasker` - masks personal data in debug request and response logs
- `Model\Db\InsertIgnore` - helper for `INSERT IGNORE` writes through the DB adapter
- `Model\Config\Backend\ApiUrl` - config backend model that validates API URLs
- `view/frontend/web/js/submit-guard.js` - prevents double form submission
- Config `maguru_extension/info/send_usage_info` (default off, Stores > Configuration > MaGuru > Extensions): the registry is contacted, and the store domain and the Magento, PHP and MaGuru versions are sent to it, only when the merchant opts in. While it is off, the extensions list shows the locally installed MaGuru modules with a notice instead of registry data
- Brand icons and favicon for the admin configuration pages
- Unit tests for `PaginatedListTrait`, `TotalOnlyCollection`, `PiiMasker`, `InsertIgnore`, `ApiUrl` and `ExtensionsInfo`

### Changed

- `PRIVACY_POLICY.md`: describes the optional registry request and the usage information sent when **Share Store Domain and Versions** is enabled
- `ExtensionsInfo`: registry values are escaped, the module link is rendered only for `https://` URLs and gets `rel="noopener noreferrer"`
- Admin config: money and limit fields validate digits and a value greater than zero
- Admin config styles (`config.less`) tidied
- Ukrainian translations filled in for the payment, delivery and Prom modules
- `declare(strict_types=1)` added to `RepositoryModuleInfo` and `Registry`; PHPStan level 8 is clean for the whole module including tests

## [1.2.2] - 2026-07-27

### Fixed

- Admin grid **Extensions > Queue Retry Log** failed with `Not registered handle maguru_core_queue_retry_log_listing_data_source` - the module's own `di.xml` never registered `QueueRetryLog\Grid\Collection` with `Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory`, unlike every other module that uses the plain core `DataProvider` class for its listing (e.g. `MonoFiscal`'s fiscal-check grid). Added the missing registration.

## [1.2.1] - 2026-07-26

### Added

- `Test/Integration/Queue/QueueRetryLogPipelineTest` - first real-database coverage for the `QueueRetryLogger` -> `QueueRetryLogRepository::getDueForRetry()` contract (7 tests): a fresh failure with `next_retry_at = NULL` is immediately due; a row scheduled in the future is excluded and reappears once past due; `exhausted`/`succeeded` rows are never returned regardless of `next_retry_at`; unencrypted payloads are stored as readable plaintext and encrypted payloads round-trip through the real `EncryptorInterface` without the plaintext ever touching disk.

## [1.2.0] - 2026-07-19

### Added

- Shared queue message retry mechanism (`RetryFailed`): `RetryManagerInterface`/`RetryManager` (exponential backoff calculator), `QueueRetryLoggerInterface`/`QueueRetryLogger`, `QueueRetryPolicyProviderInterface` (per-module retry budget override), `RetryableHandlerInterface`, `RetryHandlerPoolInterface`/`RetryHandlerPool` (topic > handler registry, merged additively across modules' `di.xml`), `MaGuru\Core\Cron\RetryFailedQueueMessages` (single shared retry cron, `*/5 * * * *`)
- `maguru_core_queue_retry_log` table - domain-neutral ledger for failed queue messages, with optional per-row payload encryption and a captured retry-budget snapshot
- Admin grid `Extensions > Queue Retry Log` with its own ACL resource `MaGuru_Core::queue_retry_log`
- `maguru_extension/queue_retry/*` config (Advanced): enabled flag, batch size, default max retries / base delay / max delay
- Dispatches `maguru_core_queue_retry_exhausted` event when a row's retry budget is exhausted

## [1.1.7] - 2026-07-16

### Changed

- `LICENSE.txt`: added Limitation of Liability, No Obligation to Support, No Fitness for Production, Governing Law, and Contributions clauses

## [1.1.6] - 2026-07-01

### Added

- `ExtensionsInfo`: `stability` field support - new column in the admin extensions table with color-coded badges (`stable` / `beta` / `alpha` / `dev`); reads `stability` key from registry `modules.json`
- `ExtensionsInfo`: constants `STABILITY_STABLE`, `STABILITY_BETA`, `STABILITY_ALPHA`, `STABILITY_DEV` and `getStabilityBadge()` method

## [1.1.5] - 2026-06-26

### Added

- Unit tests: `CacheTest`, `RegistryTest`, `ModuleVersionTest`, `RepositoryModuleInfoTest`, `AbstractConfigTest` - 19 tests, 26 assertions, PHPStan Level 8
- `README.md`: Tests section with PHPUnit command and test count

## [1.1.4] - 2026-06-26

### Added

- `.gitignore`: excludes `var/`, `generated/`, `vendor/`, `*.log`, and IDE files

### Changed

- `Logger/AbstractHandler`: default log level changed from `DEBUG` to `WARNING` to avoid verbose output in production
- `composer.json`: `license` changed from array to string (`"proprietary"`); description updated to reflect free-to-use nature; `version` field removed - versions are now managed via git tags
- `composer.json`: PHP requirement extended to include `~8.4.0||~8.5.0` (PHP 8.1-8.5 supported)

## [1.1.3] - 2026-05-15

### Added PRIVACY_POLICY

- Added PRIVACY_POLICY.md

## [1.1.2] - 2025-12-15

### Added DraggableProductTabs extension info

- Added new extension https://github.com/MaGuruDev/DraggableProductTabs

## [1.1.1] - 2025-11-19

### Added fix

- Fixed relative PHP 8.1

## [1.1.0] - 2025-09-22

### Added language pack

- [MaGuru Language Pack uk_UA for Magento 2](https://github.com/MaGuruDev/Language_uk_UA)

## [1.0.1] - 2025-09-20

### Added

- All initial module functionalities
