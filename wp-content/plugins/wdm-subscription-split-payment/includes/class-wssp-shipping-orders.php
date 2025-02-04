<?php
/**
 * WSSP Shipping Information In Orders
 *
 * @category    Class
 * @since       1.0
 */
class WSSP_Shipping_Orders {

    /**
     * Add shipping information in Orders
     *
     * @since 1.0
     */

    public static function init() {
        add_action( 'woocommerce_checkout_subscription_created', __CLASS__ . '::on_subscription_creation', 10, 3 );
        add_action( 'woocommerce_order_status_processing', __CLASS__ . '::on_processing_status_subscription_order' );
        add_filter( 'wcs_renewal_order_created', __CLASS__ . '::add_shipping_to_renewal_orders', 100, 2 );
        add_action( 'add_meta_boxes', __CLASS__ . '::change_meta_box', 35 );
        add_action( 'woocommerce_admin_order_totals_after_refunded', __CLASS__ . '::add_subscription_details_to_orders' );

        add_action( 'plugins_loaded', __CLASS__ . '::my_plugin_override' );

        
        // Add column that indicates whether an order is installment or payment order
		add_filter( 'manage_edit-shop_order_columns', __CLASS__ . '::add_order_type_column' );
        add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::add_order_type_column_content', 10, 1 );
    
        add_filter( 'wc_get_template', __CLASS__ . '::replace_templates', 10, 5 );

        add_action( 'woocommerce_order_details_after_order_table', __CLASS__ . '::add_order_details_to_display', 10, 1 );
    }



    public static function add_order_details_to_display($order ) {
        $subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) );
        $subscription = false;

        foreach ( $subscriptions as $key => $value ) {
            $subscription = $value;
            break;
        }

        if ( $subscription ) {

            $wssp_subscription_total = wcs_get_objects_property( $subscription, 'wssp_subscription_total' );
            $shipping_interval       = $subscription->get_meta( '_wssp_shipping_interval' );
            $wssp_total_paid         = wcs_get_objects_property( $order, 'wssp_total_paid' );
            $wssp_balance            = wcs_get_objects_property( $order, 'wssp_balance' );
            $shipping_status         = (int) $order->get_meta( '_wssp_shipping_status' );
            ?>
        
            <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                <tfoot>
                    <tr>
                        <th scope="row"><?php echo esc_html_e( 'Total Number of Installments', 'wdm-subscription-split-payment' ); ?></th>
                        <td><?php echo $shipping_interval; ?></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html_e( 'Installment Number', 'wdm-subscription-split-payment' ); ?></th>
                        <td><?php echo '#' . ($shipping_status + 1); ?></td>
                    </tr>


                    <?php if ( isset( $wssp_subscription_total ) ) :?>
                        <tr>
                            <th scope="row"><?php echo esc_html_e( 'Total Product Price', 'wdm-subscription-split-payment' ); ?></th>
                            <td><?php echo wc_price( $wssp_subscription_total, array( 'currency' => $order->get_currency() ) );  ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if ( isset( $wssp_total_paid ) ) :?>
                        <tr>
                            <th scope="row"><?php echo esc_html_e( 'Total Amount Paid', 'wdm-subscription-split-payment' ); ?></th>
                            <td><?php echo wc_price( $wssp_total_paid, array( 'currency' => $order->get_currency() ) );  ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if ( isset( $wssp_balance ) ) :?>
                        <tr>
                            <th scope="row"><?php echo esc_html_e( 'Balance Amount', 'wdm-subscription-split-payment' ); ?></th>
                            <td><?php echo wc_price( $wssp_balance, array( 'currency' => $order->get_currency() ) );  ?></td>
                        </tr>
                    <?php endif; ?>
                    
                </tfoot>
            </table> <?php
        }
    }



    public static function replace_templates($template, $template_name, $args, $template_path, $default_path ) {
        if ( 'myaccount/subscription-totals-table.php' === $template_name ) {
            $template = WSSP_PLUGIN_PATH . '/templates/public/subscription-totals-table.php';
        } elseif ( 'myaccount/related-orders.php' === $template_name ) {
            $template = WSSP_PLUGIN_PATH . '/templates/public/related-orders.php';
        }

        return $template;
    }


    public static function my_plugin_override() {
        remove_filter( 'manage_edit-shop_order_columns', array( 'WC_Subscriptions_Order', 'add_contains_subscription_column' ), 10 );
        remove_action( 'manage_shop_order_posts_custom_column', array( 'WC_Subscriptions_Order', 'add_contains_subscription_column_content' ), 10);
    }


    /**
     * Tasks to do when subscription is created.
     */
    public static function on_subscription_creation( $subscription, $order, $recurring_cart ) {
        $items = $subscription->get_items( 'line_item' );

        foreach ( $items as $key => $item ) {
            $product = $item->get_product();

            $shipping_interval       = $product->get_meta( '_subscription_shipping_interval' );
            

            $subscription->add_meta_data( '_wssp_shipping_interval', $shipping_interval );
            $subscription->add_meta_data( '_wssp_shipping_status', 0 );

            $order->add_meta_data( '_wssp_shipping_status', 0 );
            wcs_set_objects_property( $order, 'wssp_order_type', 'installment' );
            break;
        }

        $wssp_subscription_total = $order->get_subtotal() * $shipping_interval;
        $wssp_total_paid         = $order->get_subtotal();

        wcs_set_objects_property( $subscription, 'wssp_subscription_total', $wssp_subscription_total );
        wcs_set_objects_property( $subscription, 'wssp_total_paid', '0' );
        wcs_set_objects_property( $subscription, 'wssp_balance', $wssp_subscription_total );

        wcs_set_objects_property( $order, 'wssp_subscription_total', $wssp_subscription_total );
        wcs_set_objects_property( $order, 'wssp_total_paid', '0' );
        wcs_set_objects_property( $order, 'wssp_balance', $wssp_subscription_total );

        $subscription->save();
        $order->save();
    }


    /**
     * Tasks to do when the status of a subscription_order is in processing.
     */
    public static function on_processing_status_subscription_order( $order_id ) {
        $order = wc_get_order( $order_id );

        $subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) );
        $subscription = false;

        foreach ( $subscriptions as $key => $value ) {
            $subscription = $value;
            break;
        }

        if ( $subscription ) {
            $subscription_period          = $subscription->get_billing_period();
            $subscription_period_interval = $subscription->get_billing_interval();

            $shipping_interval       = $subscription->get_meta( '_wssp_shipping_interval' );
            $shipping_status         = (int) $subscription->get_meta( '_wssp_shipping_status' );
            $wssp_subscription_total = wcs_get_objects_property( $subscription, 'wssp_subscription_total' );
            $wssp_total_paid         = wcs_get_objects_property( $subscription, 'wssp_total_paid' );
            $wssp_balance            = wcs_get_objects_property( $subscription, 'wssp_balance' );

            if ( 0 !== $shipping_status ) {
                $order->update_status( 'completed', __( 'No Shipping Required.', 'wdm-subscription-split-payment' ) );
            }

            $shipping_status = ( $shipping_status + $subscription_period_interval ) % $shipping_interval;


            wcs_set_objects_property( $subscription, 'wssp_total_paid', $wssp_total_paid + $order->get_subtotal() );
            wcs_set_objects_property( $subscription, 'wssp_balance', $wssp_balance - $order->get_subtotal() );
            wcs_set_objects_property( $order, 'wssp_total_paid', $wssp_total_paid + $order->get_subtotal() );
            wcs_set_objects_property( $order, 'wssp_balance', $wssp_balance - $order->get_subtotal() );

            $subscription->update_meta_data( '_wssp_shipping_status', $shipping_status );
            $subscription->save();
            $order->save();
        }
    }


    public static function get_next_shipping_date( $shipping_interval ) {
        $next_shipping_date         = new WC_DateTime( 'now', wc_timezone_string() );
        $shipping_interval_duration = new DateInterval( 'P' . $shipping_interval . 'M' );
        $next_shipping_date         = $next_shipping_date->add( $shipping_interval_duration );

        return $next_shipping_date;
    }


    public static function add_shipping_to_renewal_orders( $order, $subscription ) {
        $shipping_status = (int) $subscription->get_meta( '_wssp_shipping_status' );

        if ( 0 !== $shipping_status ) {
            
            $wssp_total_paid         = wcs_get_objects_property( $subscription, 'wssp_total_paid' );
            $wssp_balance            = wcs_get_objects_property( $subscription, 'wssp_balance' );

            wcs_set_objects_property( $order, 'wssp_order_type', 'payment' );
            wcs_set_objects_property( $order, 'wssp_total_paid', $wssp_total_paid );
            wcs_set_objects_property( $order, 'wssp_balance', $wssp_balance );

            $shipping_items = $order->get_shipping_methods();
            foreach ( $shipping_items as $key => $shipping_item ) {
                $shipping_id = $shipping_item->get_id();
                $order->remove_item( $shipping_id );
            }

            $shipping_total = $order->calculate_shipping();

            $total = $order->calculate_totals();
            $order->save();
        } else {
            $wssp_subscription_total = wcs_get_objects_property( $subscription, 'wssp_subscription_total' );

            wcs_set_objects_property( $subscription, 'wssp_total_paid', '0' );
            wcs_set_objects_property( $subscription, 'wssp_balance', $wssp_subscription_total );

            wcs_set_objects_property( $order, 'wssp_order_type', 'installment' );
            wcs_set_objects_property( $order, 'wssp_total_paid', '0' );
            wcs_set_objects_property( $order, 'wssp_balance', $wssp_subscription_total );
        }

        $order->update_meta_data( '_wssp_shipping_status', $shipping_status );

        return $order;
    }


    public static function change_meta_box() {
        global $post_ID;

        remove_meta_box( 'subscription_renewal_orders', 'shop_subscription', 'normal' );

        add_meta_box( 'subscription_renewal_orders', __( 'Related Orders', 'woocommerce-subscriptions' ), 'WSSP_Shipping_Orders::output', 'shop_subscription', 'normal', 'low' );

        // Only display the meta box if an order relates to a subscription
        if ( 'shop_order' === get_post_type( $post_ID ) && wcs_order_contains_subscription( $post_ID, 'any' ) ) {
            add_meta_box( 'subscription_renewal_orders', __( 'Related Orders', 'woocommerce-subscriptions' ), 'WSSP_Shipping_Orders::output', 'shop_order', 'normal', 'low' );
        }
    }


    /**
     * Output the metabox
     */
    public static function output( $post ) {

        if ( wcs_is_subscription( $post->ID ) ) {
            $subscription = wcs_get_subscription( $post->ID );
            $order        = ( false == $subscription->get_parent_id() ) ? $subscription : $subscription->get_parent();
        } else {
            $order = wc_get_order( $post->ID );
        }

        add_action( 'woocommerce_subscriptions_related_orders_meta_box_rows', 'WSSP_Shipping_Orders' . '::output_rows', 10 );

        include_once WSSP_PLUGIN_PATH . '/templates/admin/html-related-orders-table.php';

        do_action( 'woocommerce_subscriptions_related_orders_meta_box', $order, $post );
    }


    /**
     * Displays the renewal orders in the Related Orders meta box.
     *
     * @param object $post A WordPress post
     * @since 2.0
     */
    public static function output_rows( $post ) {
        $orders_to_display     = array();
        $subscriptions         = array();
        $initial_subscriptions = array();
        $orders_by_type        = array();
        $unknown_orders        = array(); // Orders which couldn't be loaded.

        // If this is a subscriptions screen,
        if ( wcs_is_subscription( $post->ID ) ) {
            $this_subscription = wcs_get_subscription( $post->ID );
            $subscriptions[]   = $this_subscription;

            // Resubscribed subscriptions and orders.
            $initial_subscriptions         = wcs_get_subscriptions_for_resubscribe_order( $this_subscription );
            $orders_by_type['resubscribe'] = WCS_Related_Order_Store::instance()->get_related_order_ids( $this_subscription, 'resubscribe' );
        } else {
            $subscriptions         = wcs_get_subscriptions_for_order( $post->ID, array( 'order_type' => array( 'parent', 'renewal' ) ) );
            $initial_subscriptions = wcs_get_subscriptions_for_order( $post->ID, array( 'order_type' => array( 'resubscribe' ) ) );
        }

        foreach ( $subscriptions as $subscription ) {
            // If we're on a single subscription or renewal order's page, display the parent orders
            if ( 1 == count( $subscriptions ) && $subscription->get_parent_id() ) {
                $orders_by_type['parent'][] = $subscription->get_parent_id();
            }

            // Finally, display the renewal orders
            $orders_by_type['renewal'] = $subscription->get_related_orders( 'ids', 'renewal' );

            // Build the array of subscriptions and orders to display.
            $subscription->update_meta_data( '_relationship', _x( 'Subscription', 'relation to order', 'woocommerce-subscriptions' ) );
            $orders_to_display[] = $subscription;
        }

        foreach ( $initial_subscriptions as $subscription ) {
            $subscription->update_meta_data( '_relationship', _x( 'Initial Subscription', 'relation to order', 'woocommerce-subscriptions' ) );
            $orders_to_display[] = $subscription;
        }

        // Assign all order and subscription relationships and filter out non-objects.
        foreach ( $orders_by_type as $order_type => $orders ) {
            foreach ( $orders as $order_id ) {
                $order = wc_get_order( $order_id );

                switch ( $order_type ) {
                    case 'renewal':
                        $relation = _x( 'Renewal Order', 'relation to order', 'woocommerce-subscriptions' );
                        break;
                    case 'parent':
                        $relation = _x( 'Parent Order', 'relation to order', 'woocommerce-subscriptions' );
                        break;
                    case 'resubscribe':
                        $relation = wcs_is_subscription( $order ) ? _x( 'Resubscribed Subscription', 'relation to order', 'woocommerce-subscriptions' ) : _x( 'Resubscribe Order', 'relation to order', 'woocommerce-subscriptions' );
                        break;
                    default:
                        $relation = _x( 'Unknown Order Type', 'relation to order', 'woocommerce-subscriptions' );
                        break;
                }

                if ( $order ) {
                    $order->update_meta_data( '_relationship', $relation );
                    $orders_to_display[] = $order;
                } else {
                    $unknown_orders[] = array(
                        'order_id' => $order_id,
                        'relation' => $relation,
                    );
                }
            }
        }

        $orders_to_display = apply_filters( 'woocommerce_subscriptions_admin_related_orders_to_display', $orders_to_display, $subscriptions, $post );

        foreach ( $orders_to_display as $order ) {
            // Skip the order being viewed.
            if ( $order->get_id() === (int) $post->ID ) {
                continue;
            }

            include WSSP_PLUGIN_PATH . '/templates/admin/html-related-orders-row.php';
        }

        foreach ( $unknown_orders as $order_and_relationship ) {
            $order_id     = $order_and_relationship['order_id'];
            $relationship = $order_and_relationship['relation'];

            include WSSP_PLUGIN_PATH . '/templates/admin/html-unknown-related-orders-row.php';
        }
    }


    /**
     * Display Product Info.
     */
    public static function add_subscription_details_to_orders( $order_id ) {
        $order = wc_get_order( $order_id );
        $subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) );
        $subscription = false;

        foreach ( $subscriptions as $key => $value ) {
            $subscription = $value;
            break;
        }

        if ( ( $subscription ) || ( wcs_is_subscription( $order_id ) ) ) {

            if ( wcs_is_subscription( $order_id ) ) {
                $wssp_subscription_total = wcs_get_objects_property( $order, 'wssp_subscription_total' );
                $shipping_interval = $order->get_meta( '_wssp_shipping_interval' );
            } else {
                $wssp_subscription_total = wcs_get_objects_property( $subscription, 'wssp_subscription_total' );
                $shipping_interval = $subscription->get_meta( '_wssp_shipping_interval' );
            }
            
            $wssp_total_paid         = wcs_get_objects_property( $order, 'wssp_total_paid' );
            $wssp_balance            = wcs_get_objects_property( $order, 'wssp_balance' );
            $shipping_status   = (int) $order->get_meta( '_wssp_shipping_status' );

            ?>
                <tr>
                    <td class="label"><?php esc_html_e( 'Total Number of Installments', 'wdm-subscription-split-payment' ); ?>:</td>
                    <td width="1%"></td>
                    <td class="total">
                        <?php echo '<b>' . $shipping_interval . '</b>'; ?>
                    </td>
                </tr>

                <?php if ( ! wcs_is_subscription( $order_id ) ) :?>
                    <tr>
                        <td class="label"><?php esc_html_e( 'Installment Number', 'wdm-subscription-split-payment' ); ?>:</td>
                        <td width="1%"></td>
                        <td class="total">
                            <?php echo '<b>#' . ($shipping_status + 1) . '</b>'; ?>
                        </td>
                    </tr>
                <?php endif; ?>


                <?php if ( isset( $wssp_subscription_total ) ) :?>
                    <tr>
                        <td class="label"><?php esc_html_e( 'Total Product Price', 'wdm-subscription-split-payment' ); ?>:</td>
                        <td width="1%"></td>
                        <td class="total">
                            <?php echo wc_price( $wssp_subscription_total, array( 'currency' => $order->get_currency() ) ); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ( ( ! wcs_is_subscription( $order_id ) ) && ( isset( $wssp_total_paid ) ) ) :?>
                    <tr>
                        <td class="label"><?php esc_html_e( 'Total Amount Paid', 'wdm-subscription-split-payment' ); ?>:</td>
                        <td width="1%"></td>
                        <td class="total">
                            <?php echo wc_price( $wssp_total_paid, array( 'currency' => $order->get_currency() ) ); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ( ( ! wcs_is_subscription( $order_id ) ) && ( isset( $wssp_balance ) ) ) :?>
                    <tr>
                        <td class="label"><?php esc_html_e( 'Balance Amount', 'wdm-subscription-split-payment' ); ?>:</td>
                        <td width="1%"></td>
                        <td class="total">
                            <?php echo wc_price( $wssp_balance, array( 'currency' => $order->get_currency() ) ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php
        }
    }


    /**
    * Add a column to the WooCommerce -> Orders admin screen to indicate whether an order is a Regular Order, Shipping Order, Payment Order.
    *
    * @param array $columns The current list of columns
    * @since 2.1
    */
    public static function add_order_type_column( $columns ) {
        print_r( $columns );

        $column_header = esc_attr__( 'Type', 'wdm-subscription-split-payment' );

        $new_columns = wcs_array_insert_after( 'shipping_address', $columns, 'type', $column_header );

        return $new_columns;
    }

    /**
    * Add column content to the WooCommerce -> Orders admin screen to indicate whether an
    * order is a Regular Order, Installment Order, Payment Order, Renewal Order.
    *
    * @param string $column The string of the current column
    * @since 2.1
    */
    public static function add_order_type_column_content( $column ) {
        global $post;
        $order = wc_get_order( $post );

        if ( $order ) {
            $order_type = wcs_get_objects_property( $order, 'wssp_order_type' );

            if ( 'type' === $column ) {
                if ( 'payment' === $order_type ) {
                    echo esc_attr__( 'Payment', 'wdm-subscription-split-payment' );
                } elseif ( 'installment' === $order_type ) {
                    echo esc_attr__( 'Installment', 'wdm-subscription-split-payment' );
                } else {
                    echo esc_attr__( '', 'wdm-subscription-split-payment' );
                }
            }
        }
    }
}