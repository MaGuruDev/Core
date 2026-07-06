# MaGuru Core — User Guide

**Version:** 1.1.6  
**Compatible with:** Adobe Commerce / Magento Open Source 2.4.4 – 2.4.x  
**PHP:** 8.1 or higher  
**License:** Free

---

## Overview

MaGuru Core is the **foundation module** for all MaGuru extensions. It does not add storefront features of its own. Instead, it provides shared infrastructure that every other MaGuru module relies on — and it adds a dedicated **MaGuru** section to your Magento admin configuration where all MaGuru modules are managed from a single location.

After installation, the **MaGuru** tab appears in **Stores → Configuration**, and a new **Extensions** panel shows you all installed MaGuru modules at a glance.

---

## What It Does

- Creates the **MaGuru** tab in **Stores → Configuration** — a unified home for all MaGuru extension settings
- Adds the **Extensions Overview** panel under **Stores → Configuration → MaGuru → Extensions** showing every installed MaGuru module with its version, changelog, and documentation links
- Provides shared HTTP client, configuration, logging, and error-handling infrastructure used by all MaGuru modules
- Has no customer-facing features on its own

---

## Requirements

| Requirement | Version |
|---|---|
| Adobe Commerce / Magento Open Source | 2.4.4 or higher |
| PHP | 8.1 or higher |

---

## Installation

### Step 1 — Install via Composer

Run the following commands from your Magento root directory:

```bash
composer require maguru/magento2-core
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

> **Note:** If your store is in production mode, also run `bin/magento setup:static-content:deploy` after compiling.

### Step 2 — Verify Installation

```bash
bin/magento module:status MaGuru_Core
```

The output should show `Module is enabled`.

---

## Admin Panels

### Extensions Overview

**Stores → Configuration → MaGuru → Extensions**

This panel is the central dashboard for your MaGuru extension ecosystem. It displays a table of all MaGuru modules found on your installation.

| Column | Description |
|---|---|
| **Extension Name** | The name of the MaGuru extension |
| **Version** | The currently installed version. If a newer version is available, an update indicator appears (e.g., `1.0.1 → 1.0.2`) |
| **Stability** | Release stability badge: Stable, Beta, Alpha, or Dev |
| **Dev Status** | Development status badge: In Development, Ready, For Sale, or Public Repo |
| **Changelog** | Link to the version history for this extension |
| **User Guide** | Link to the documentation for this extension |
| **Download** | Link to download the latest version (if applicable) |

#### Installed Extensions section

Shows all MaGuru modules currently installed on your Magento instance, with their version numbers and links.

#### New Extensions section

Shows MaGuru extensions that are available but not yet installed on your store. Use the Download link to obtain the package and install it via Composer.

> **Note:** Version update indicators are informational only — they do not trigger automatic updates. Updates are always performed manually via Composer as described in each extension's documentation.

---

## Frequently Asked Questions

**Q: I installed this module but I don't see any new features on my storefront. Is that normal?**

Yes. MaGuru Core is an infrastructure module — it has no storefront-facing features. Its purpose is to enable all other MaGuru extensions to work. Install the functional MaGuru modules (such as eSputnik Backend, Monobank Payment, Checkbox Fiscal, or RZ-Delivery) to add features to your store.

---

**Q: The Extensions panel shows some extensions in "New Extensions". What does that mean?**

Those extensions are available in the MaGuru catalog but not yet installed on your store. To install one, use Composer:

```bash
composer require maguru/<module-package-name>
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

Refer to the User Guide link shown next to each extension for full installation instructions.

---

**Q: An extension shows "1.0.1 → 1.0.2" in the Version column. What does this mean?**

It means a newer version of that extension is available. Click the Changelog link to review what changed. When you are ready to update, run `composer update maguru/<package-name>` and then `bin/magento setup:upgrade && bin/magento cache:flush`.

---

**Q: I see a "MaGuru" tab in the configuration but none of the other MaGuru modules show their settings there. Why?**

Each MaGuru module adds its own configuration group to the MaGuru tab when installed. If you only have MaGuru Core installed, only the **Extensions** panel appears. Install the relevant module to see its settings.

---

**Q: Can I uninstall MaGuru Core while keeping other MaGuru modules?**

No. All MaGuru modules depend on MaGuru Core. Removing it will break all other MaGuru extensions. If you want to disable a specific extension, disable or uninstall that individual module rather than the Core.

---

## Support

If you encounter an issue:

1. Verify that `bin/magento module:status MaGuru_Core` returns `enabled`
2. Check that the **MaGuru** tab appears at **Stores → Configuration**
3. Contact support with your Magento version, PHP version, and a description of the issue

---

*MaGuru Core — foundation module for the MaGuru Extension Suite for Adobe Commerce*
