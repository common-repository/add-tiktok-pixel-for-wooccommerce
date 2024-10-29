=== Add TikTok Pixel for WooCommerce ===
Contributors: scottybo2
Donate link: https://www.dcsdigital.co.uk
Tags: tiktok, analytics, tracking
Requires at least: 6.0.0
Tested up to: 6.2.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A free plugin that adds the TikTok pixel to your WooCommerce site and enables e-commerce related TikTok events.

== Description ==

## Why this TikTok Pixel plugin?

After trying out the other TikTok pixel plugins available on the WordPress plugin directory we discovered they were buggy, incomplete and were tracking duplicate events. Our clients needed a working solution, so we created this free plugin for them - and thought we'd share it with you too!

This is free, simple and lightweight plugin which adds the TikTok Pixel to every page on your site, and also adds support for the following TikTok events:

- Page View
- Single Product Page - View Content
- Add to Cart (supports AJAX carts)
- Initiate Checkout
- Complete Payment

The plugin passes across as much data as possible, including SKUs, cart values, currency and so on.

## How to use

Once activated, go to Woocommerce > TikTok Pixel and enter your TikTok Pixel ID.

## Working with TikTok SKUs

Although it's possible to define your WooCommerce product SKUs within the TikTok product catalogs, you actually need to use the TikTok SKU when firing the tracking pixel.

This plugin adds a new field "TikTok SKU" to simple and variable products, and if a value is set it will use this when firing the tracking pixel.

If no value is set, it revets to the WooCommerce SKU, and then to the product ID if no SKU has been set.

** Tip **

To export all your SKU IDs from TikTok, go to the TikTok Shop Seller Center and go to "Batch Edit" all your products. Select all your products, and download the export file. This will contain all the TikTok SKU Ids, allowing you to match them against your WooCommerce SKUs.

We recommend doing a product export from WooCommerce (go to "Products" and choose to "Export") and make sure to tick the box to "Export Product Data". You can then put the TikTok SKU into the `Meta: tiktok_sku` column (make sure you've saved at least one product with a TikTok Sku so this column is shown in the export).

== Installation ==

1. Add the `widget-speed-test-for-elementor` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Woocommerce > TikTok Pixel and enter your TikTok Pixel ID.

== Frequently Asked Questions ==

= How much does this plugin cost? =

It's completely free - we initially made it for our clients but decided to release it as a plugin for the world to enjoy!

= Where do I get my TikTok Pixel ID? =

1. Go to https://ads.tiktok.com and then head over to **Assets** > **Events**
2. Under **Web Events** choose **Manage**

**No existing pixels? Create a new one!**

3. Choose to **Set Up Web Event** and select TikTok Pixel
4. Choose to **Manually Install Pixel Code**
5. Choose **Custom**, choose your options and then hit **Next** and **Complete Setup**
6. Grab your ID from the top of the page


**Already have a pixel?**

Grab the text next to **ID:** (that's your pixel ID!)

= How do I test if everything is working? =

1. Go to https://ads.tiktok.com and then head over to **Assets** > **Events**
2. Under **Web Events** choose **Manage**
3. Select your pixel that has been added to the site
4. Click on the **Test Events** tab and scan the QR code
5. Browse the site, add products to the cart and then refresh the TikTok admin page - you should see your test events

You can also install the Chrome extension which will show you the events that are being fired: https://chrome.google.com/webstore/detail/tiktok-pixel-helper/aelgobmabdmlfmiblddjfnjodalhidnn

== Screenshots ==

1. Settings screen
2. Events being triggered

== Changelog ==

= 1.0.3 =
* Add new META field where can define a custom SKU to match what is contained within TikTok, and use it when firing events
* A few bug fixes where the pixel wasn't firing correctly for variable products

= 1.0.2 =
* Add WordPress Multisite support

= 1.0.1 =
* Bug fix

= 1.0.0 =
* Initial release