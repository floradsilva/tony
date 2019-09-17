<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Variables
 * @since 2.4.6
 */
class Variables {

	/** @var array */
	private static $loaded_variables = [];

	/** @var array */
	private static $variables_list;

	/** @var array */
	private static $included_variables = [
		'customer' => [
			'email',
			'first_name',
			'last_name',
			'full_name',
			'order_count',
			'total_spent',
			'address_line_1',
			'address_line_2',
			'country',
			'state',
			'city',
			'postcode',
			'phone',
			'company',
			'generate_coupon',
			'meta',
			'user_id',
			'username',
			'tags',
			'unsubscribe_url',
		],
		'user' => [],
		'order' => [
			'id',
			'number',
			'status',
			'date',
			'subtotal',
			'total',
			'itemscount',
			'items',
			'cross_sells',
			'related_products',
			'billing_phone',
			'billing_address',
			'shipping_address',
			'view_url',
			'payment_url',
			'reorder_url',
			'shipping_method',
			'payment_method',
			'customer_note',
			'customer_details',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address_line_1',
			'shipping_address_line_2',
			'shipping_city',
			'shipping_country',
			'shipping_state',
			'shipping_postcode',
			'shipping_company_name',
			'meta',
			'meta_date',
			'admin_url',
		],
		'order_item' => [
			'attribute',
			'meta',
			'quantity'
		],
		'order_note' => [
			'content'
		],
		'guest' => [
			'email',
			'generate_coupon',
			'first_name',
			'last_name'
		],
		'review' => [
			'content',
			'rating'
		],
		'comment' => [
			'id',
			'author_name',
			'author_ip',
			'content'
		],
		'product' => [
			'id',
			'title',
			'current_price',
			'regular_price',
			'featured_image',
			'permalink',
			'add_to_cart_url',
			'sku',
			'parent_sku',
			'short_description',
			'description',
			'meta',
			'meta_date',
		],
		'category' => [
			'id',
			'title',
			'permalink'
		],
		'wishlist' => [
			'items',
			'view_link',
			'itemscount'
		],
		'cart' => [
			'link',
			'items',
			'item_count',
			'total',
		],
		'subscription' => [
			'id',
			'status',
			'payment_method',
			'total',
			'view_order_url',
			'start_date',
			'next_payment_date',
			'trial_end_date',
			'end_date',
			'last_payment_date',
			'items',
			'billing_address',
			'shipping_address',
			'meta',
			'admin_url',
		],
		'membership' => [
			'id',
			'plan_id',
			'plan_name',
			'status',
			'date_started',
			'date_expires',
			'renewal_url',
			'meta',
		],
		'card' => [
			'type',
			'expiry_month',
			'expiry_year',
			'last_four'
		],
		'shop' => [
			'title',
			'tagline',
			'url',
			'admin_email',
			'current_datetime',
			'products'
		],
	];


	/**
	 * @return array
	 */
	static function get_list() {
		// cache the list after first generation
		if ( isset( self::$variables_list ) ) {
			return self::$variables_list;
		}

		$variables = [];
		$included_variables = self::$included_variables;

		if ( Integrations::is_subscriptions_active( '2.5' ) ) {
			$included_variables[ 'subscription' ][] = 'change_payment_method_url';
		}

		if ( class_exists( 'WC_Shipment_Tracking' ) ) {
			$included_variables['order'][] = 'tracking_number';
			$included_variables['order'][] = 'tracking_url';
			$included_variables['order'][] = 'date_shipped';
			$included_variables['order'][] = 'shipping_provider';
		}

		/**
		 * @since 4.5.0
		 */
		if ( Integrations::is_subscriptions_active( '2.3' ) &&
		     class_exists( 'WCS_Early_Renewal_Manager' ) &&
		     \WCS_Early_Renewal_Manager::is_early_renewal_enabled() ) {
			$included_variables['subscription'][] = 'early_renewal_url';
		}

		// generate paths to included variables
		foreach ( $included_variables as $data_type => $fields ) {
			foreach ( $fields as $field ) {
				$filename = str_replace( '_', '-', $data_type ) . '-' . str_replace( '_', '-', $field ) . '.php';
				$variables[$data_type][$field] = AW()->path("/includes/variables/$filename");
			}
		}

		self::$variables_list = apply_filters( 'automatewoo/variables', $variables );
		self::$variables_list = aw_array_move_to_end( self::$variables_list, 'shop' );
		return self::$variables_list;
	}


	/**
	 * @param $data_type
	 * @param $data_field
	 * @return false|string
	 */
	static function get_path_to_variable( $data_type, $data_field ) {

		$list = self::get_list();

		if ( isset( $list[$data_type][$data_field] ) ) {
			return $list[$data_type][$data_field];
		}

		return false;
	}


	/**
	 * @param $variable_name string
	 * @return Variable|false
	 */
	static function get_variable( $variable_name ) {

		if ( isset( self::$loaded_variables[$variable_name] ) ) {
			return self::$loaded_variables[$variable_name];
		}

		list( $data_type, $data_field ) = explode( '.', $variable_name );

		$path = self::get_path_to_variable( $data_type, $data_field );

		if ( ! file_exists( $path ) ) {

			// backwards compat user with customer
			if ( $data_type === 'customer' ) {
				$path = self::get_path_to_variable( 'user', $data_field );
			}

			if ( ! file_exists( $path ) ) {
				return false;
			}
		}

		/** @var Variable $class */
		if ( ! $class = require_once $path ) {
			return false;
		}

		$class->setup( $variable_name );

		self::$loaded_variables[$variable_name] = $class;

		return $class;
	}

}
