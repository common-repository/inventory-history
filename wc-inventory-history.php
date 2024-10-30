<?php
/**
 * Plugin Name: Inventory History
 * Description: Inventory history for stock changes of WooCommerce products
 * Version: 0.1.2
 * Author: Yashar Hosseinpour
 * License: GPL2
 * Text Domain: wc-inventory-history
 * Requires PHP: 7.1
 * Requires at least: 5.0
 * Tested up to: 5.4.2
 * WC requires at least: 4.0
 * WC tested up to: 4.2.0
 */

namespace WCIH;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'WCIH_PLUGIN_FILE' ) ) {
    define( 'WCIH_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists('WCIH\Main') ) {
    require_once dirname( __FILE__ ) . '/includes/class-main.php';
}

function WCIH()
{
    Main::getInstance();
}
WCIH();