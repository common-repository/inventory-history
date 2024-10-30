<?php

namespace WCIH;

use WC_Product_Variable;

defined( 'ABSPATH' ) || exit;

class Viewer
{
    private static $change_types = [];

    public static function init()
    {
        add_action( 'add_meta_boxes', [ self::class, 'add_metabox' ] );
    }

    public static function add_metabox()
    {
        add_meta_box(
                'wcih_metabox',
                __( 'Inventory History', 'wc-inventory-history'),
                [self::class, 'render_metabox'],
                'product',
                'normal'
        );
    }

    public static function render_metabox( $post )
    {
        self::set_change_types();
        if ( 'product' !== get_post_type($post) ) {
            return;
        }

        $log_query = new Log_Query( [ 'product_id' => $post->ID ] );
        $logs = $log_query->get_logs();

        if ( ! count($logs) > 0 ) {
            echo '<span>' . esc_html__('No log found for this product.', 'wc-inventory-history' ) . '</span>';
            return;
        }
        $product = wc_get_product($post->ID);
        ?>
        <div>
            <div style="padding:10px;">
                <?php
                if ( $product->is_type('variable') ) {
                    /**
                     * @var WC_Product_Variable $product
                     */
                    ?>
                    <select id="wcih-variation-id-filter" aria-label="Variation Filter">
                        <option selected="selected" value="all">
                            <?php esc_html_e('All Variations', 'wc-inventory-history'); ?>
                        </option>

                        <?php
                        $variations = $product->get_available_variations();

                        foreach ($variations as $variation) {
                            ?>
                            <option value="<?= esc_attr($variation['variation_id']) ?>">
                                <?php
                                $i = count( $variation['attributes'] );
                                foreach ( $variation['attributes'] as $attribute_name => $attribute_value ) {
                                    --$i;

                                    if ( strpos($attribute_name, 'attribute_pa_' ) === 0 ) {
                                        $term = get_term_by(
                                                'slug',
                                                $attribute_value,
                                                str_replace('attribute_', '', $attribute_name )
                                        );
                                        if ( $term ) {
                                            echo esc_html($term->name);
                                        } else {
                                            echo esc_html($attribute_value);
                                        }
                                    } else {
                                        echo esc_html($attribute_value);
                                    }

                                    if ( $i ) {
                                        echo ' - ';
                                    }
                                }
                                ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                }

                $order_ids = array_unique( array_filter( wp_list_pluck( $logs,'order_id' ) ) );
                if ( count( $order_ids ) ) {
                    ?>
                    <select id="wcih-order-id-filter" aria-label="Order Filter">
                        <option selected="selected" value="all">
                            <?php esc_html_e('All Orders', 'wc-inventory-history'); ?>
                        </option>
                        <?php
                        foreach ($order_ids as $order_id) {
                            echo "<option value='$order_id'>$order_id</option>";
                        }
                        ?>
                    </select>
                    <?php
                }

                $change_types = array_unique( wp_list_pluck( $logs,'type' ) );
                ?>
                <select id="wcih-type-filter" aria-label="Change Type Filter">
                    <option selected="selected" value="all">
                        <?php esc_html_e('All Events', 'wc-inventory-history'); ?>
                    </option>
                    <?php
                    foreach ($change_types as $change_type) {
                        $label = !$change_type ? 'N/A' : (
                                isset( self::$change_types[$change_type] ) ?
                                    self::$change_types[$change_type] : $change_type
                        );

                        echo "<option value='$change_type'>" . $label . "</option>";
                    }
                    ?>
                </select>
                <?php
                ?>
            </div>
            <div>
                <table style="width: 100%; text-align: center;">
                    <tr>
                        <th><?php esc_html_e('User', 'wc-inventory-history' ); ?></th>
                        <th><?php esc_html_e('Date', 'wc-inventory-history' ); ?></th>
                        <?php
                        if ( $product->is_type('variable') ) {
                            ?>
                            <th><?php esc_html_e('Variation ID', 'wc-inventory-history' ); ?></th>
                            <?php
                        }
                        ?>
                        <th><?php esc_html_e('Order ID', 'wc-inventory-history' ); ?></th>
                        <th><?php esc_html_e('Old Stock', 'wc-inventory-history' ); ?></th>
                        <th><?php esc_html_e('Stock Change', 'wc-inventory-history' ); ?></th>
                        <th><?php esc_html_e('New Stock', 'wc-inventory-history' ); ?></th>
                        <th><?php esc_html_e('Event Type', 'wc-inventory-history' ); ?></th>
                    </tr>
                    <?php
                    foreach ($logs as $log) {
                        ?>
                        <tr>
                            <td class="wcih-user-id"><?= esc_html($log->user_id); ?></td>
                            <td class="wcih-date"><?= esc_html($log->date); ?></td>
                            <?php
                            if ( $product->is_type('variable') ) {
                                ?>
                                <td class="wcih-variation-id"><?= esc_html($log->variation_id); ?></td>
                                <?php
                            }
                            ?>
                            <td class="wcih-order-id"><?= $log->order_id ? esc_html($log->order_id) : '-'; ?></td>
                            <td class="wcih-old-stock"><?= esc_html($log->old_stock); ?></td>
                            <td class="wcih-stock-change"><?= esc_html($log->stock_change); ?></td>
                            <td class="wcih-new-stock"><?= esc_html($log->new_stock); ?></td>
                            <td class="wcih-type" data-value="<?= esc_attr($log->type); ?>">
                                <?= isset(self::$change_types[$log->type]) ?
                                    self::$change_types[$log->type] : esc_html($log->type); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
        </div>
        <script>
            jQuery(document).ready( function($) {
                let filters = ['variation-id', 'order-id', 'type'];
                $.each(filters, function() {
                    let filterName = this + '';
                    let select = $('#wcih-' + filterName + '-filter');
                    if ( select.length ) {
                        select.change( function() {

                            let otherFilters = filters.filter( function(element) {
                                return element !== filterName;
                            });

                            let selectedValue = $(this).val();
                            let tds = $('td.wcih-' + filterName );
                            tds.each( function() {
                                let singleTD = $(this);

                                if ( singleTD.is(":visible") ) {
                                    if ( selectedValue !== 'all' && getTDValue(singleTD) !== selectedValue ) {
                                        singleTD.closest('tr').hide();
                                    }
                                } else {
                                    if ( getTDValue(singleTD) === selectedValue || selectedValue === 'all') {
                                        let singleRow = singleTD.closest('tr');
                                        let hidden = false;

                                        // Check if this row should bi hidden by other filters
                                        $.each(otherFilters, function () {
                                            let otherFilterName = this + '';
                                            let otherSelect = $('#wcih-' + otherFilterName + '-filter');
                                            let otherFilterValue = otherSelect.val();
                                            if ( otherFilterValue !== 'all' && getTDValue( singleRow.find('td.wcih-' + otherFilterName) ) !== otherFilterValue ) {
                                                hidden = true;
                                            }
                                        })

                                        if ( !hidden ) {
                                            singleRow.show();
                                        }
                                    }
                                }
                            });
                        });
                    }
                });

                function getTDValue( td ) {
                    //td = $(td);
                    if ( !td.length ) {
                        return '';
                    }
                    if ( td.hasClass('wcih-type') ) {
                        return td.data('value');
                    }

                    return td.text();
                }
            } );
        </script>
        <?php
    }

    private static function set_change_types()
    {
        self::$change_types = [
            'product_created'           =>  __( 'Product: Created', 'wc-inventory-history' ),
            'product_variation_created' =>  __( 'Product: Variation Created', 'wc-inventory-history' ),
            'product_stock_update'      =>  __( 'Product: Stock Updated', 'wc-inventory-history' ),
            'product_manage_stock'      =>  __( 'Product: Stock Managed', 'wc-inventory-history' ),
            'product_no_manage_stock'   =>  __( 'Product: Stock Unmanaged', 'wc-inventory-history' ),
            'variation_stock_update'    =>  __( 'Variation: Stock Updated', 'wc-inventory-history' ),
            'variation_manage_stock'    =>  __( 'Variation: Stock Managed', 'wc-inventory-history' ),
            'variation_no_manage_stock' =>  __( 'Variation: Stock Unmanaged', 'wc-inventory-history' ),
            'customer_order_placed'     =>  __( 'Order: Customer Order Placed', 'wc-inventory-history' ),
            'add_order_item'            =>  __( 'Order Edit: Item Added', 'wc-inventory-history' ),
            'remove_order_item'         =>  __( 'Order Edit: Item Removed', 'wc-inventory-history' ),
            'refund_line_items'         =>  __( 'Order Edit: Item Refunded', 'wc-inventory-history' ),
            'save_order_items'          =>  __( 'Order Edit: Items Saved', 'wc-inventory-history' ),
            'save_order'                =>  __( 'Order Edit: Order Updated', 'wc-inventory-history' ),
        ];
    }

}