# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.4] - 2026-06-26

### Changed

- `Logger/AbstractHandler`: default log level changed from `DEBUG` to `WARNING` to avoid verbose output in production
- `composer.json`: `license` changed from array to string (`"proprietary"`); description updated to reflect free-to-use nature; `version` field removed — versions are now managed via git tags
- `composer.json`: PHP requirement extended to include `~8.4.0||~8.5.0` (PHP 8.1–8.5 supported)

### Added

- `.gitignore`: excludes `var/`, `generated/`, `vendor/`, `*.log`, and IDE files

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
