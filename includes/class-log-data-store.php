<?php

namespace WCIH;

defined( 'ABSPATH' ) || exit;

class Log_Data_Store
{
    /*
	|-----------------------
	| CR(U)D Methods
	|-----------------------
	*/

    /**
     * @param Log $log
     *
     * @return bool|int
     */
    public function create( &$log )
    {
        if ( $log->get_id() > 0 ) {
            return false;
        }

        if (
            ! $log->get_product_id()
            || ! $log->get_user_id()
        ) {
            return false;
        }

        if ( ! $log->get_date() ) {
            $log->set_date( current_time( 'mysql', true ) );
        }

        global $wpdb;
        $db_prefix = $wpdb->prefix;

        $save = $wpdb->insert(
            $db_prefix . 'wc_inventory_history',
            [
                'product_id'    =>  $log->get_product_id(),
                'variation_id'  =>  $log->get_variation_id(),
                'old_stock'     =>  $log->get_old_stock(),
                'stock_change'  =>  $log->get_stock_change(),
                'new_stock'     =>  $log->get_new_stock(),
                'order_id'      =>  $log->get_order_id(),
                'user_id'       =>  $log->get_user_id(),
                'date'          =>  $log->get_date(),
                'type'          =>  $log->get_type(),
            ],
            [ '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s' ]
        );

        $possible_new_log_id = $wpdb->insert_id;

        if ( 1 === $save && $possible_new_log_id > 0 ) {
            $log->set_id( $wpdb->insert_id );
            return $possible_new_log_id;
        } else {
            return false;
        }
    }

    /**
     * @param Log $log
     *
     * @return bool
     */
    public function read( &$log )
    {
        global $wpdb;
        $id = $log->get_id();

        if ( ! $id > 0 ) {
            return false;
        }

        $table_name = $wpdb->prefix . 'wc_inventory_history';
        $log_data = $wpdb->get_results(
        	$wpdb->prepare( "SELECT * FROM $table_name WHERE ID = %d", $id ),
	        ARRAY_A
        );

        if ( count($log_data) != 1 ) {
            return false;
        }

        $log_data = $log_data[0];
        $log->set_data( $log_data );

        return true;
    }

    /**
     * @param Log $log
     *
     * @return bool
     */
    public function delete( &$log )
    {
        global $wpdb;
        $table_name =  $wpdb->prefix . 'wc_inventory_history';
        $id = $log->get_id();

        if ( ! $id ) {
            return false;
        }

        $delete = $wpdb->delete(
            $table_name,
            [ 'ID'  =>  $id ],
            '%d'
        );

        if ( 1 === $delete) {
            $log->set_id( 0 );
        } else {
            return false;
        }

        return true;
    }
}