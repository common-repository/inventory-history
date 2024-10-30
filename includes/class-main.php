<?php

namespace WCIH;

defined( 'ABSPATH' ) || exit;

class Main
{
    /**
     * Instance of WCIH
     * @access protected
     * @var object $instance The instance of this class.
     */
    protected static $instance = null;

    public static $version = '0.1.2';

    /**
     * Cloning is forbidden.
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__,  'Cloning is forbidden.', '5.4.1' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Unserializing instances of this class is forbidden.', '5.4.1' );
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        try {
            spl_autoload_register( [self::class, 'autoload'], false );
        } catch ( \Exception $e ) {
            return;
        }

        $this->define_constants();
        register_activation_hook(WCIH_PLUGIN_FILE, [self::class, 'install'] );
        add_action( 'woocommerce_init', [self::class, 'init'] );
    }

    public static function init()
    {
        if ( !defined('WC_VERSION') || !version_compare( WC_VERSION, '4.0', ">=" ) ) {
            return;
        }

        new Logger();
        Viewer::init();
    }

    /**
     * Plugin activation logic
     */
    public static function install()
    {
        if ( is_multisite() ) {
            deactivate_plugins( WCIH_PLUGIN_FILE );
            die( 'WooCommerce Inventory History: Multi-site support is not available yet.' );
        }

        $compatibility_status = self::is_environment_compatible();
        if ( true !== $compatibility_status ) {
            deactivate_plugins( WCIH_PLUGIN_FILE );
            if ( $compatibility_status === -1 ) {
                die( 'WooCommerce Inventory History requires PHP 7.0 or higher.' );
            } elseif ( $compatibility_status === -2 ) {
                die( 'WooCommerce Inventory History requires WordPress 5.0 or higher.' );
            }
        } else {
            self::create_tables();
            self::update_versions();
        }
    }

    private static function update_versions()
    {
        delete_option( 'WCIH_version' );
        add_option( 'WCIH_version', self::$version );
    }

    private static function create_tables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $wcih_table = $wpdb->prefix . 'wc_inventory_history';
        $sql = "CREATE TABLE IF NOT EXISTS $wcih_table (
		  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  product_id bigint(20) UNSIGNED NOT NULL,
		  variation_id bigint(20) UNSIGNED DEFAULT 0,
		  old_stock MEDIUMINT(8) NOT NULL,
		  stock_change MEDIUMINT(8) NOT NULL,
		  new_stock MEDIUMINT(8) NOT NULL,
		  order_id bigint(20) UNSIGNED DEFAULT 0,
		  user_id bigint(20) UNSIGNED DEFAULT 0,
          `date` datetime DEFAULT '1970-00-00 00:00:00' NOT NULL,
          `type` varchar(255),
          PRIMARY KEY  (ID),
          KEY product_id (product_id),
          KEY variation_id (variation_id),
          KEY order_id (order_id),
          KEY user_id (user_id),
          KEY `type` (`type`)
          ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    private static function is_environment_compatible()
    {
        if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
            return -1;
        }
        if ( version_compare( $GLOBALS['wp_version'], '5.0', '<=' ) ) {
            return -2;
        }
        return true;
    }

    private function define_constants()
    {
        if ( ! defined( 'WCIH_INC_PATH' ) ) {
            define( 'WCIH_INC_PATH', dirname( WCIH_PLUGIN_FILE ) . '/includes' );
        }
    }

    public static function autoload( $class )
    {
        if ( false === strpos( $class, 'WCIH' ) ) {
            return;
        }

        // Split the class name into an array to read the namespace and class.

        $class_name = str_ireplace( 'WCIH\\', '',  $class );
	    $class_name = str_ireplace( '_', '-', strtolower( $class_name ) );

	    $filepath  = trailingslashit(WCIH_INC_PATH) . "class-$class_name.php";

        // If the file exists in the specified path, then include it.
        if ( file_exists( $filepath ) ) {
            include_once( $filepath );
        }
    }

    /**
     * Retrieves the instance of the plugin
     *
     * @since 3.0
     * @return object instance of the class
     */
    static public function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}