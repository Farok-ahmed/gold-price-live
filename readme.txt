=== Gold Price Live ===
Contributors: farokahmed
Tags: gold price, silver price, platinum price, precious metals, live prices, calculator
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display live gold, silver, and platinum prices with interactive calculators. Supports multiple API providers.

== Description ==

Gold Price Live is a comprehensive WordPress plugin that displays real-time precious metal prices from multiple API providers. Perfect for jewelers, precious metal dealers, investors, and financial websites.

= Key Features =

* **Multiple API Support** - Works with GoldPrice.org, MetalPriceAPI.com, Metals-API.com
* **Auto Currency Detection** - Automatically detects currency from API URL (USD, CAD, EUR, GBP, AUD, JPY, CHF, INR, CNY)
* **5 Display Options** - Topbar, spot price table, premium jewellery pricing, comprehensive tables, interactive calculator
* **Real-time Calculator** - AJAX-powered scrap metal price calculator with Nice Select dropdowns
* **Smart Caching** - Reduces API calls with 12-hour cache system
* **Fully Responsive** - Works perfectly on all devices and screen sizes
* **Translation Ready** - Fully internationalized and ready for translation
* **Security Focused** - Follows WordPress security best practices

= Shortcodes =

1. `[gold_price_topbar]` - Horizontal price display bar
2. `[gold_price_table]` - Spot price per gram table
3. `[gold_price_premium_jewellery]` - Premium jewellery pricing by karat
4. `[gold_price_jewellery]` - Comprehensive jewellery pricing (Gold, Silver, Platinum)
5. `[gold_price_calculator]` - Interactive scrap metal calculator

= Supported API Providers =

**GoldPrice.org**
* URL Format: `https://data-asg.goldprice.org/dbXRates/USD`

**MetalPriceAPI.com**
* URL Format: `https://api.metalpriceapi.com/v1/latest?api_key=YOUR_KEY&base=USD&currencies=XAG,XAU,XPT`

**Metals-API.com**
* URL Format: `https://metals-api.com/api/latest?access_key=YOUR_KEY&base=USD&symbols=XAU,XAG,XPT`

= Supported Currencies =

USD, CAD, EUR, GBP, AUD, JPY, CHF, INR, CNY and more. Currency is automatically detected from your API URL.

= Usage =

1. Install and activate the plugin
2. Go to Settings → Gold Price Live
3. Enter your API URL from any supported provider
4. Use shortcodes in posts, pages, or widgets
5. Prices update automatically twice per day

= Developer Friendly =

* Object-oriented code structure
* WordPress coding standards
* Well-documented functions
* Extensible with hooks and filters
* Clean, semantic HTML markup

== Installation ==

= Automatic Installation =

1. Log in to your WordPress dashboard
2. Navigate to Plugins → Add New
3. Search for "Gold Price Live"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Upload to /wp-content/plugins/ directory
3. Unzip the file
4. Activate through the 'Plugins' menu in WordPress

= Configuration =

1. Go to Settings → Gold Price Live
2. Enter your API URL from a supported provider
3. The plugin will automatically detect the provider and currency
4. Save settings and start using shortcodes

== Frequently Asked Questions ==

= Do I need an API key? =

Yes, you need an API URL from one of the supported providers (GoldPrice.org, MetalPriceAPI.com, or Metals-API.com). Most providers offer free tiers.

= How often do prices update? =

Prices are cached for 12 hours and update automatically twice per day. You can also manually fetch fresh data from the settings page.

= Can I display prices in different currencies? =

Yes! The plugin supports multiple currencies including USD, CAD, EUR, GBP, AUD, JPY, CHF, INR, and CNY. Currency is automatically detected from your API URL.

= Can I use multiple API providers? =

While you can only configure one API URL at a time, you can easily switch between providers by changing the API URL in settings.

= Is the calculator mobile-friendly? =

Yes! All components including the calculator are fully responsive and work perfectly on mobile devices.

= Can I customize the styling? =

Yes! Each component has its own CSS file that you can override in your theme's stylesheet.

= Does it work with page builders? =

Yes! The shortcodes work with all major page builders including Elementor, Divi, WPBakery, Beaver Builder, and Gutenberg.

= Is it translation ready? =

Yes! The plugin is fully internationalized and ready for translation into any language.

== Screenshots ==

1. Horizontal price topbar showing gold, silver, and platinum prices
2. Spot price table with per-gram pricing
3. Premium jewellery pricing table by karat
4. Comprehensive jewellery pricing with all metals
5. Interactive scrap metal calculator with dropdown selections
6. Admin settings page with API configuration
7. Multiple currency support display

== Changelog ==

= 1.0.0 - 2025-11-13 =
* Initial release
* Multiple API provider support (GoldPrice.org, MetalPriceAPI.com, Metals-API.com)
* 5 shortcodes for different display formats
* Interactive AJAX calculator
* Auto currency detection (9+ currencies)
* 12-hour smart caching system
* Fully responsive design
* Translation ready
* Security hardened with nonces and sanitization

== Upgrade Notice ==

= 1.0.0 =
Initial release of Gold Price Live plugin.

== Privacy Policy ==

Gold Price Live does not collect, store, or share any personal data from your website visitors. The plugin only:
* Fetches precious metal price data from your configured API provider
* Stores API responses in WordPress transient cache for 12 hours
* Stores your API URL in WordPress options (admin only)

All data remains on your server. No external tracking or analytics are used.

== Credits ==

* Nice Select library for enhanced dropdown styling
* API providers: GoldPrice.org, MetalPriceAPI.com, Metals-API.com

== Support ==

For support, feature requests, or bug reports, please visit:
* Plugin Repository: https://wordpress.org/plugins/gold-price-live/
* GitHub: https://github.com/Farok-ahmed/gold-price-live
* Author Website: https://farok.me

== Requirements ==

* WordPress 5.0 or higher
* PHP 7.0 or higher
* Active API key from a supported provider
* cURL or allow_url_fopen enabled for API requests
