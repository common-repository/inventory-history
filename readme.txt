=== Inventory History ===
Contributors: yashar_hv
Tags: woocommerce,stock history,products,stock log
Requires at least: 5.0
Tested up to: 5.4.2
Requires PHP: 7.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Complete inventory story for WooCommerce

== Description ==

By default, WooCommerce does not save any kind of log or history for product stock changes. This means anyone with the edit access on WC products, may manipulate the stock quantities without getting logged somewhere.
WooCommerce Inventory History solves this problem and adds extra functionalities to monitor product stock quantity changes.
This plugin tries to log every stock change caused by customers or admins during order placement, product edit, order edit.
Site admins can view the inventory history of each product, from admin dashboard and filter the logs by event type, order ID or product variation (for variable products).
Each log contains information about the event, including customer or admin user ID, old stock quantity, stock change, new stock quantity, event type, date and order ID.

# Trigger Events #
*   Product: Created
*   Product: Variation Created
*   Product: Stock Updated
*   Product: Stock Managed
*   Product: Stock Unmanaged
*   Variation: Stock Updated
*   Variation: Stock Managed
*   Variation: Stock Unmanaged
*   Order: Customer Order Placed
*   Order Edit: Item Added
*   Order Edit: Item Removed
*   Order Edit: Item Refunded
*   Order Edit: Items Saved
*   Order Edit: Order Updated (Order Refunded, Refund Removed, etc)
*   Any other stock quantity change, caused by third-party plugins using WooCommerce standard functions


# REQUIREMENTS #
WooCommerce Inventory History requires PHP version 7.1 or above and WooCommerce version 4.0 or above, to work smoothly.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/woocommerce-inventory-history` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the \'Plugins\' screen in WordPress

== Screenshots ==

1. Inventory History for a WooCommerce Product

== Changelog ==

= 0.1.2 =
Fix: Now order_id is logged correctly for customer orders paid by any third-party gateway

= 0.1.1 =
New: Added new filter 'wcih_bypass_log' to bypass logging process

= 0.1.0 =
Initial release