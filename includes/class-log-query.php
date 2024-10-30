<?php

namespace WCIH;

defined( 'ABSPATH' ) || exit;

class Log_Query
{
    private $args = [
        'product_id'    =>  null,
        'order_id'      =>  null
    ];

    public function __construct( $args )
    {
        $this->set_log_args( $args );
    }

    private function set_log_args( array $args )
    {
        foreach ($this->args as $key => $value) {
            if ( isset($args[$key]) ) {
                $this->args[$key] = intval( $args[$key] );
            } else {
                unset($this->args[$key]);
            }
        }
    }

    public function get_logs()
    {
        global $wpdb;
        $members_table = $wpdb->prefix . 'wc_inventory_history';
        $log_query = '';

        if ( empty($this->args) || count($this->args) !== 1 ) {
            return null;
        }

        foreach ($this->args as $key => $value) {
            $where = "WHERE $key = %d";
            $log_query = $wpdb->prepare("SELECT * FROM $members_table $where", $value);
            break;
        }

        return $wpdb->get_results( $log_query, OBJECT_K );
    }
}