# MaGuru Core for Magento 2

![Magento 2](https://img.shields.io/badge/Magento-2.4%2B-brightgreen)
[![First Beta Version](https://poser.pugx.org/maguru/magento2-core/v/stable)](https://packagist.org/packages/maguru/magento2-core)
[![Total Downloads](https://poser.pugx.org/maguru/magento2-core/downloads)](https://packagist.org/packages/maguru/magento2-core)

<img width="150" height="100" src="documentation/images/made_in_ukraine.jpeg">

---

## Requirements

* Magento Community Edition 2.1.x-2.4.x or Magento Enterprise Edition 2.1.x-2.4.x
* This module is required for other MaGuru extensions for Magento 2

## How to install & upgrade MaGuru_Core

### 1. Install via composer (recommend)

* We recommend you to install MaGuru_Core module via composer. It is easy to install, update and maintaince.

* Run the following command in Magento 2 root folder.

#### 1.1 Install

```
composer require maguru/magento2-core
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

#### 1.2 Upgrade

```
composer update maguru/magento2-core
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

Run compile if your store in Product mode:

```
php bin/magento setup:di:compile
```

### 2. Copy and paste

If you don't want to install via composer, you can use this way.

- Download [the latest version here](https://github.com/MaGuruDev/Core/archive/refs/tags/v1.0.1.zip)
- Extract `master.zip` file to `app/code/MaGuru/Core` ; You should create a folder path `app/code/MaGuru/Core` if not exist.
- Go to Magento root folder and run upgrade command line to install `MaGuru_Core`:

```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## 🧾 License

- [The code is licensed](LICENSE.txt).

## 🆘 Support

- **Email:** maguru.sup@gmail.com
- **Issues:** [GitHub Issues](https://github.com/MaGuruDev/Core/issues)