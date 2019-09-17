=== Price Based on Country for WooCommerce ===
Contributors: oscargare
Tags: price based country, dynamic price based country, price by country, dynamic price, woocommerce, geoip, country-targeted pricing
Requires at least: 3.8
Tested up to: 5.2
Stable tag: 1.8.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add multicurrency support to WooCommerce, allowing you set product's prices in multiple currencies based on country of your site's visitor.

== Description ==

**Price Based on Country for WooCommerce** allows you to sell the same product in multiple currencies based on the country of the customer.

= How it works =

The plugin detects automatically the country of the website visitor throught the geolocation feature included in WooCommerce (2.3.0 or later) and display the currency and price you have defined previously for this country.

You have two ways to set product's price for each country:

* Calculate price by applying the exchange rate.
* Set price manually.

When country changes on checkout page, the cart, the order preview and all shop are updated to display the correct currency and pricing.

= Multicurrency =
Sell and receive payments in different currencies, reducing the costs of currency conversions.

= Country Switcher =
The extension include a country switcher widget to allow your customer change the country from the frontend of your website.

= Shipping currency conversion =
Apply currency conversion to Flat and International Flat Rate Shipping.

= Compatible with WPML =
WooCommerce Product Price Based on Countries is officially compatible with [WPML](https://wpml.org/extensions/woocommerce-product-price-based-countries/).

= Upgrade to Pro =

>This plugin offers a Pro addon which adds the following features:

>* Guaranteed support by private ticket system.
>* Automatic updates of exchange rates.
>* Add an exchange rate fee.
>* Round to nearest.
>* Display the currency code next to price.
>* Compatible with the WooCommerce built-in CSV importer and exporter.
>* Thousand separator, decimal separator and number of decimals by pricing zone.
>* Currency switcher widget.
>* Support to WooCommerce Subscriptions by Prospress .
>* Support to WooCommerce Product Bundles by SomewhereWarm .
>* Support to WooCommerce Product Add-ons by WooCommerce .
>* Support to WooCommerce Bookings by WooCommerce .
>* Support to WooCommerce Composite Product by SomewhereWarm.
>* Support to WooCommerce Name Your Price by Kathy Darling.
>* Bulk editing of variations princing.
>* Support for manual orders.
>* More features and integrations is coming.

>[Get Price Based on Country Pro now](https://www.pricebasedcountry.com?utm_source=wordpress.org&utm_medium=readme&utm_campaign=Extend)

= Requirements =
WooCommerce 2.6.0 or later.

== Installation ==

1. Download, install and activate the plugin.
1. Go to WooCommerce -> Settings -> Product Price Based on Country and configure as required.
1. Go to the product page and sets the price for the countries you have configured avobe.

= Adding a country selector to the front-end =

Once you’ve added support for multiple country and their currencies, you could display a country selector in the theme. You can display the country selector with a shortcode or as a hook.

**Shortcode**

[wcpbc_country_selector other_countries_text="Other countries"]

**PHP Code**

do_action('wcpbc_manual_country_selector', 'Other countries');

= Customize country selector (only for developers) =

1. Add action "wcpbc_manual_country_selector" to your theme.
1. To customize the country selector:
	1. Create a directory named "woocommerce-product-price-based-on-countries" in your theme directory.
	1. Copy to the directory created avobe the file "country-selector.php" included in the plugin.
	1. Work with this file.

== Frequently Asked Questions ==

= How might I test if the prices are displayed correctly for a given country? =

If you are in a test environment, you can configure the test mode in the setting page.

In a production environment you can use a privacy VPN tools like [TunnelBear](https://www.tunnelbear.com/) or [ZenMate](https://zenmate.com/)

You should do the test in a private browsing window to prevent data stored in the session. Open a private window on [Firefox](https://support.mozilla.org/en-US/kb/private-browsing-use-firefox-without-history#w_how-do-i-open-a-new-private-window) or on [Chrome](https://support.google.com/chromebook/answer/95464?hl=en)

== Screenshots ==

1. /assets/screenshot-1.png
2. /assets/screenshot-2.png
3. /assets/screenshot-3.png
4. /assets/screenshot-4.png
5. /assets/screenshot-5.png
5. /assets/screenshot-6.png

== Changelog ==

= 1.8.11 (2019-08-13) =
* Added: WooCoomerce 3.7 compatibility.

= 1.8.10 (2019-08-06) =
* Fixed: other_countries_text index is undefined on the country selector widget.
* Fixed: 'WCPBC_Admin_Notices' does not have a method warning.
* Dev: Prevent that the filter wc_price_based_country_shortcode_atts overrides the default shortcode attributes.

= 1.8.9 (2019-07-04) =
* Fixed: Double discount in cart related to discount plugins.
* Fixed: Switchers don't work on the checkout page.
* Dev: New filter to allow to third-party adding product type supported.

= 1.8.8 (2019-06-06) =
* Fixed: Products with sale price zero use the regular price instead of the zero sale price.
* Tweak: A tool (WooCommerce > Status > Tools) to updates the database to the latest version.

= 1.8.7 (2019-05-21) =
* Fixed: Store notice of the "Test mode" option displays a plain HTML.
* Fixed: empty_no_zero function detects "false" as a empty value different to zero.

= 1.8.6 (2019-05-14) =
* Fixed: Duplicate variable products when order by price.
* Fixed: Minor bugs.
* Tweak: Use a javascript file instead of an inline script for the country selector.
* Dev:   New JavaScript events.

= 1.8.5 (2019-05-03) =
* Fixed: "No products were found matching your selection." error on WC 3.6.

= 1.8.4 (2019-05-02) =
* Fixed: WC 3.6 and external products issue.
* Fixed: Compatible with discount plugins for WC 3.6.
* Fixed: Order products by price does not work.
* Fixed: Error on "order by price" main query.
* Fixed: Refresh the Ajax geolocation transient after the pricing zone update.

= 1.8.3 (2019-04-19) =
* Fixed: Shipping calculator form does not change the pricing zone.
* Fixed: Removed the stock check from the synchronization variable product prices with children.
* Fixed: Variable Subscription products page does not load.
* Added: Cache the geolocation AJAX response to improve performance.
* Tweak: Improve compatibilty with plugins and themes which uses AJAX to load content.
* Tweak: Force display of price in the geolocation AJAX function to fix plugins conflict.

= 1.8.1 (2019-04-10) =
* Added: Warning to users who uses a deprecated version of the Pro add-on.
* Added: get_regions as deprecated function.

= 1.8.0 (2019-04-09) =
* Added: Compatible with WooCommerce 3.6.
* Added: New option to allow users to disable the tax adjustment based on location when the prices are entering with tax included.
* Added: Test to detect geolocation problems in system report.
* Added: New constant to allow users uses the remote_addr as customer IP.
* Added: New interface for pricing zone table.
* Tweak: Country switcher template compatible with defer script attribute.
* Tweak: Button to unselect Eurozone form the countries list of the pricing zone.
* Fixed: Minor security issues.
* Dev: Adapt code to the WordPress code standards.
* Dev: New admin frawework.

[See changelog for all versions](https://plugins.svn.wordpress.org/woocommerce-product-price-based-on-countries/trunk/changelog.txt).

== Upgrade Notice ==

= 1.8 =
1.8 is a major update, make a backup before updating. If you are using the Pro version, you must update it. Version 1.8 is required to work with WooCommerce 3.6.