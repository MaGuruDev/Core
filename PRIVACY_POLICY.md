## Privacy Policy
### MaGuru - MaGuru Core Extension for Magento 2
Last updated: October 2026

This Privacy Policy describes how the MaGuru Core Extension for Magento 2 extension handles data.

### Extensions List (disabled by default)
The setting **Share Store Domain and Versions** (`maguru_extension/info/send_usage_info`,
Stores > Configuration > MaGuru > Extensions) is **No** by default. While it is **No**, the
extension makes no external request: the extensions list shows only the MaGuru modules installed
in your store and their local versions.

If you set it to **Yes**, then when an administrator opens the extensions list, your server requests
the public list of MaGuru extensions (`https://maguru-registry.maguru.workers.dev/modules.json`)
to show available extensions and updates. This request carries these HTTP headers:

- `X-Source-Domain` - the domain of your store
- `X-MaGuru-Version` - the installed version of MaGuru Core
- `X-Magento-Version` - the Magento version
- `X-PHP-Version` - the PHP version

No customer, order, or administrator data is sent. As with any HTTP request, the registry host can
see the IP address of your server. You can turn the setting off again at any time.

### No Other Data Transmission
Apart from the optional request described above, this extension operates entirely within your Magento
installation. It does not transmit, send, or share any personal data with external servers,
third-party services, or MaGuru.

### No Data Collection by MaGuru
MaGuru does not collect, store, or process any data about your customers through this extension.
Store domain and version information is received only if you enable the optional setting above.

### No Cookies
This extension does not set or use any browser cookies.

### No Account Required
No MaGuru account or registration is required to install or use this
extension.

### Contact
If you have questions about this Privacy Policy, contact:
maguru.sup@gmail.com
