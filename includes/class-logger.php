<?php

namespace WCIH;

use WC_Product_Variation;

defined( 'ABSPATH' ) || exit;

class Logger
{
    private static $instance = null;

    private $customer_order = null;

    private $order_id = 0;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        add_action( 'woocommerce_new_product', [$this, 'new_product'], 10, 2 );
        add_action( 'woocommerce_new_product_variation', [$this, 'new_product'], 10, 2 );

        add_action( 'woocommerce_admin_process_product_object', [ $this, 'product_save_stock_changes'] );
        add_action( 'woocommerce_admin_process_variation_object', [ $this, 'variation_save_stock_changes'] );

        add_action( 'woocommerce_checkout_order_processed', [$this, 'is_customer_order'] );
        add_action( 'woocommerce_pre_payment_complete', [$this, 'is_customer_order'] );
        add_filter( 'woocommerce_update_product_stock_query', [ $this, 'order_stock_changes' ], 10, 4 );
    }

    public function is_customer_order( $order_id )
    {
        $this->customer_order = true;
        $this->order_id = $order_id;
    }

    /**
     * @param int $id
     * @param \WC_Product $product
     */
    public function new_product( $id, $product )
    {
        $log = new Log();
        $log->set_user_id( wp_get_current_user()->ID );

        $bypass = apply_filters('wcih_bypass_log', false, $product);

        if ( $bypass ) {
        	return;
        }

        if ( $product->is_type('variation') ) {
        	/** @var WC_Product_Variation $product */
            $log->set_product_id( $product->get_parent_id() );
            $log->set_variation_id( $id );
        } else {
            $log->set_product_id( $id );
            $log->set_variation_id( 0 );
        }
        $log->set_old_stock(0);
        $log->set_stock_change(0);
        $log->set_new_stock(0);
        $log->set_type($product->is_type('variation') ? 'product_variation_created' : 'product_created' );
        $log->save();
    }

    /**
     * @param WC_Product_Variation $variation
     */
    public function variation_save_stock_changes( $variation )
    {
        $old_data = $variation->get_data();
        $old_manage_stock = $old_data['manage_stock'];
        $new_manage_stock = $variation->get_manage_stock();

        if ( $new_manage_stock === false && $old_manage_stock === false ) {
            return;
        }

        $old_stock = (int) $old_data['stock_quantity'];
        $new_stock = $variation->get_stock_quantity();

        if ( $new_manage_stock === $old_manage_stock && $old_stock === $new_stock ) {
            return;
        }

        $log = new Log();
        $log->set_user_id( wp_get_current_user()->ID );
        $log->set_product_id( $variation->get_parent_id() );
        $log->set_variation_id( $variation->get_id() );

        // Set event type
        $type = '';
        if ( $new_manage_stock === $old_manage_stock ) {
            $type = "variation_stock_update";
        } elseif ( $new_manage_stock === true ) {
            $type = 'variation_manage_stock';
        } elseif ( $new_manage_stock === false || $new_manage_stock === 'parent' ) {
            $type = 'variation_no_manage_stock';
        }

        $log->set_stock_change( $new_stock - $old_stock );
        $log->set_old_stock( $old_stock );
        $log->set_new_stock( $new_stock );
        $log->set_type($type);
        $log->save();
    }
    /**
     * @param \WC_Product $product
     */
    public function product_save_stock_changes( $product )
    {
        $old_data = $product->get_data();
        $old_manage_stock = $old_data['manage_stock'];
        $new_manage_stock = $product->get_manage_stock();

        if ( !$new_manage_stock && !$old_manage_stock ) {
            return;
        }

        $old_stock = (int) $old_data['stock_quantity'];
        $new_stock = $product->get_stock_quantity();


        if ( $new_manage_stock === $old_manage_stock && $old_stock === $new_stock ) {
            return;
        }

        $log = new Log();
        $log->set_user_id( wp_get_current_user()->ID );
        $log->set_product_id( $product->get_id() );

        // Set event type
        $type = '';
        if ( $new_manage_stock === $old_manage_stock ) {
            $type = "product_stock_update";
        } elseif ( $new_manage_stock === true ) {
            $type = 'product_manage_stock';
        } elseif ( $new_manage_stock === false ) {
            $type = 'product_no_manage_stock';
        }

        $log->set_stock_change( $new_stock - $old_stock );
        $log->set_old_stock( $old_stock );
        $log->set_new_stock( $new_stock );
        $log->set_type($type);
        $log->save();
    }

    public function order_stock_changes( $sql, $product_id, $new_stock, $operation )
    {
        $product = wc_get_product($product_id);

        $log = new Log();
        $log->set_old_stock( $product->get_stock_quantity() );
        $log->set_stock_change( $new_stock - $product->get_stock_quantity() );
        $log->set_new_stock($new_stock);
        $log->set_user_id( wp_get_current_user()->ID );
        $type = '';
        $order_id = 0;

	    $bypass = apply_filters('wcih_bypass_log', false, $product);

	    if ( $bypass ) {
	    	return $sql;
	    }

	    if ( $product->is_type('variation') ) {
		    /** @var WC_Product_Variation $product */
		    $log->set_product_id( $product->get_parent_id() );
		    $log->set_variation_id( $product->get_id() );
	    } else {
		    $log->set_product_id( $product->get_id() );
		    $log->set_variation_id( 0 );
	    }

        // Order items edit by admin
        if ( isset($_REQUEST['order_id']) && isset($_REQUEST['action']) ) {
	        $order_id = intval($_REQUEST['order_id']);

            switch ( $_REQUEST['action']) {
                case 'woocommerce_add_order_item':
                   $type = 'add_order_item';
                   break;

                case 'woocommerce_remove_order_item':
                    $type = 'remove_order_item';
                    break;

                case 'woocommerce_refund_line_items':
                    $type = 'refund_line_items';
                    break;

                case 'woocommerce_save_order_items':
                    $type = 'save_order_items';
                    break;

                default:
                    $type = '';
            }
        }

        // Order edit by admin
        elseif (
        	isset( $_REQUEST['post_ID'])
	        && isset($_REQUEST['post_type'])
	        && $_REQUEST['post_type'] === 'shop_order'
        ) {
	        $order_id = intval($_REQUEST['post_ID']);
	        $type = 'save_order';
        }

        // Order placement by customers
        elseif ( $this->customer_order === true ) {
	        $order_id = $this->order_id;
	        $type = 'customer_order_placed';
        }

	    $log->set_type($type);
	    $log->set_order_id($order_id);
        $log->save();

        return $sql;
    }

    public static function get_instance()
    {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}