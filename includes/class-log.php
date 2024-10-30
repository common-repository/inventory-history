<?php

namespace WCIH;

defined( 'ABSPATH' ) || exit;

class Log
{
    protected $id = 0;

    /**
     * Stores member data.
     *
     * @var array
     */
    protected $data = [
        'product_id'    =>  0,
        'variation_id'  =>  0,
        'old_stock'     =>  0,
        'stock_change'  =>  0,
        'new_stock'     =>  0,
        'order_id'      =>  0,
        'user_id'       =>  0,
        'date'          =>  null,
        'type'          =>  '',
    ];

    /**
     * @var Log_Data_Store
     */
    protected $data_store;

    public function __construct( $log_id = 0 )
    {
        $this->set_id( intval( $log_id ) );

        $this->data_store = new Log_Data_Store();
        if ( $this->get_id() > 0 ) {
            $read = $this->data_store->read( $this );
            if ( ! $read ) {
                $this->set_id(0);
            }
        }
    }

    /**
     * Returns all data for this object.
     *
     * @return array
     */
    public function get_data()
    {
        return array_merge( [ 'id' => $this->get_id() ], $this->data );
    }


    /**
     * Set log data array to the instance.
     *
     * @param array $log_array
     */
    public function set_data(array $log_array )
    {
        $log_array = array_replace_recursive( $this->get_data(), $log_array );

        $this->set_id( isset($log_array['ID']) ? $log_array['ID'] : 0 );
        $this->set_product_id( $log_array['product_id'] );
        $this->set_variation_id( $log_array['variation_id'] );
        $this->set_old_stock( $log_array['old_stock'] );
        $this->set_stock_change( $log_array['stock_change'] );
        $this->set_new_stock( $log_array['new_stock'] );
        $this->set_order_id( $log_array['order_id'] );
        $this->set_user_id( $log_array['user_id'] );
        $this->set_date( $log_array['date'] );
        $this->set_type( $log_array['type'] );
    }

    /**
     * Returns the ID for this object.
     *
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Set ID.
     *
     * @param int $id ID.
     */
    public function set_id( $id )
    {
        $this->id = absint( $id );
    }

    /**
     * @return int Product ID.
     */
    public function get_product_id()
    {
        return $this->data['product_id'];
    }

    /**
     * @param int $product_id Product ID.
     */
    public function set_product_id( $product_id )
    {
        $this->data['product_id'] = absint( $product_id );
    }

    /**
     * @return int Variation ID.
     */
    public function get_variation_id()
    {
        return $this->data['variation_id'];
    }

    /**
     * @param int $variation_id Variation ID
     */
    public function set_variation_id( $variation_id )
    {
        $this->data['variation_id'] = absint( $variation_id );
    }

    /**
     * @return int Old Stock
     */
    public function get_old_stock()
    {
        return $this->data['old_stock'];
    }

    /**
     * @param int $old_stock Old Stock
     */
    public function set_old_stock( $old_stock )
    {
        $this->data['old_stock'] = intval( $old_stock );
    }

    /**
     * @return int Stock change
     */
    public function get_stock_change()
    {
        return $this->data['stock_change'];
    }

    /**
     * @param int $stock_change Stock change
     */
    public function set_stock_change( $stock_change )
    {
        $this->data['stock_change'] = intval( $stock_change );
    }

    /**
     * @return int New Stock
     */
    public function get_new_stock()
    {
        return $this->data['new_stock'];
    }

    /**
     * @param int $new_stock New Stock
     */
    public function set_new_stock( $new_stock )
    {
        $this->data['new_stock'] = intval( $new_stock );
    }

    /**
     * @return int Order ID.
     */
    public function get_order_id()
    {
        return $this->data['order_id'];
    }

    /**
     * @param int $order_id Order ID
     */
    public function set_order_id( $order_id )
    {
        $this->data['order_id'] = absint( $order_id );
    }

    /**
     * @return int User ID.
     */
    public function get_user_id()
    {
        return $this->data['user_id'];
    }

    /**
     * @param int $user_id User ID
     */
    public function set_user_id( $user_id )
    {
        $this->data['user_id'] = absint( $user_id );
    }

    /**
     * @return string Log date
     */
    public function get_date()
    {
        return $this->data['date'];
    }

    /**
     * @param string $date Log date
     */
    public function set_date( $date )
    {
        $this->data['date'] = $date;
    }

    /**
     * @return string Type
     */
    public function get_type()
    {
        return $this->data['type'];
    }

    /**
     * @param string $type Type
     */
    public function set_type( $type )
    {
        $this->data['type'] = (string) $type;
    }

    /**
     * Delete an object, set the ID to 0, and return result.
     *
     * @return bool result
     */
    public function delete()
    {
        if ( $this->data_store ) {
            $deleted = $this->data_store->delete( $this );
            if ( $deleted ) {
                $this->set_id( 0 );
                return true;
            }
        }
        return false;
    }

    /**
     * Creates log on the db
     *
     * @return int | bool
     */
    public function save()
    {
        if ( ! $this->data_store || $this->get_id() ) {
            return $this->get_id();
        }

        $result = $this->data_store->create( $this );

        return $result ? $this->get_id() : false;
    }
}