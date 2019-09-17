<?php
/**
 * Front-end pricing.
 *
 * @version 1.8.6
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Frontend_Pricing class.
 */
class WCPBC_Frontend_Pricing {

	/**
	 * Init the frontend pricing
	 */
	public static function init() {
		if ( ! wcpbc_the_zone() ) {
			return;
		}
		self::init_hooks();
		do_action( 'wc_price_based_country_frontend_princing_init' );
	}

	/**
	 * Hook actions and filters
	 *
	 * @since 1.7.0
	 */
	private static function init_hooks() {
		self::add_product_properties_filters();
		add_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata' ), 10, 4 );
		add_filter( 'woocommerce_currency', array( __CLASS__, 'get_currency' ), 100 );
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'get_variation_prices_hash' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'set_cart_item_price' ), -10 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'set_cart_item_price' ), -10 );
		add_filter( 'woocommerce_get_catalog_ordering_args', array( __CLASS__, 'get_catalog_ordering_args' ) );
		add_filter( 'posts_clauses', array( __CLASS__, 'filter_price_post_clauses' ), 25, 2 );
		add_filter( 'the_posts', array( __CLASS__, 'remove_product_query_filters' ) );
		add_filter( 'woocommerce_product_query_meta_query', array( __CLASS__, 'product_query_meta_query' ), 10, 2 );
		add_filter( 'woocommerce_price_filter_meta_keys', array( __CLASS__, 'price_filter_meta_keys' ) );
		add_filter( 'woocommerce_price_filter_sql', array( __CLASS__, 'price_filter_sql' ) );
		add_filter( 'pre_transient_wc_products_onsale', array( __CLASS__, 'product_ids_on_sale' ), 10, 2 );
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'package_rates' ), 10, 2 );
		add_filter( 'woocommerce_shipping_zone_shipping_methods', array( __CLASS__, 'shipping_zone_shipping_methods' ), 10, 4 );
		add_filter( 'woocommerce_adjust_non_base_location_prices', array( __CLASS__, 'adjust_non_base_location_prices' ) );
		add_action( 'woocommerce_coupon_loaded', array( __CLASS__, 'coupon_loaded' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'update_order_meta' ), 10, 2 );
	}

	/**
	 * Add product properties filters. WC 3.6 compatibility.
	 */
	public static function add_product_properties_filters() {
		if ( version_compare( WC_VERSION, '3.6', '<' ) ) {
			return;
		}

		$props = array( 'regular_price', 'sale_price', 'price', 'sale_price_dates_from', 'sale_price_dates_to' );

		foreach ( $props as $prop ) {
			add_filter( 'woocommerce_product_get_' . $prop, array( __CLASS__, 'get_product_property' ), 5, 2 );
			add_filter( 'woocommerce_product_variation_get_' . $prop, array( __CLASS__, 'get_product_property' ), 5, 2 );
		}
	}

	/**
	 * Retrun the product property. WC 3.6 compatibility.
	 *
	 * @since 1.8.0
	 * @param mixed      $value Property value.
	 * @param WC_Product $product Product instance.
	 * @return mixed
	 */
	public static function get_product_property( $value, $product ) {
		if ( in_array( $product->get_type(), array_unique( apply_filters( 'wc_price_based_country_product_types_overriden', array( 'simple', 'variable', 'external' ) ) ), true ) ) {

			$prop = str_replace( 'woocommerce_product_get_', '', str_replace( 'woocommerce_product_variation_get_', '', current_filter() ) );

			if ( ! array_key_exists( $prop, $product->get_changes() ) ) {
				$meta_key = '_' === substr( $prop, 0, 1 ) ? $prop : '_' . $prop;
				$value    = self::get_price_metadata( $value, $product->get_id(), $meta_key, true );
			}
		}
		return $value;
	}

	/**
	 * Return price meta data value
	 *
	 * @param null|array|string $meta_value The value get_metadata() should return - a single metadata value or an array of values.
	 * @param int               $object_id Object ID.
	 * @param string            $meta_key Meta key.
	 * @param bool              $single Whether to return only the first value of the specified $meta_key.
	 */
	public static function get_price_metadata( $meta_value, $object_id, $meta_key, $single ) {

		if ( $single && in_array( $meta_key, wcpbc_get_overwrite_meta_keys(), true ) ) {

			// Remove filter to not going into an endless loop.
			remove_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata' ), 10, 4 );

			if ( in_array( $meta_key, wcpbc_get_date_on_sale_meta_keys(), true ) && 'manual' === wcpbc_the_zone()->get_postmeta( $object_id, '_sale_price_dates' ) ) {

				$meta_value = wcpbc_the_zone()->get_postmeta( $object_id, $meta_key );

			} elseif ( in_array( $meta_key, wcpbc_get_price_meta_keys(), true ) ) {

				$meta_value = wcpbc_the_zone()->get_post_price( $object_id, $meta_key );

			} elseif ( ! in_array( $meta_key, wcpbc_get_date_on_sale_meta_keys(), true ) ) {

				$meta_value = wcpbc_the_zone()->get_postmeta( $object_id, $meta_key );
			}

			// Add filter.
			add_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata' ), 10, 4 );
		}

		return $meta_value;
	}

	/**
	 * Get currency code.
	 *
	 * @param string $currency_code Currency code.
	 * @return string
	 */
	public static function get_currency( $currency_code ) {
		return wcpbc_the_zone()->get_currency();
	}

	/**
	 * Returns unique cache key to store variation child prices
	 *
	 * @param array      $price_hash Unique cache key.
	 * @param WC_Product $product Product instance.
	 * @param bool       $display If taxes should be calculated or not.
	 * @return array
	 */
	public static function get_variation_prices_hash( $price_hash, $product, $display ) {
		$price_hash[] = wcpbc_the_zone()->get_postmetakey() . wcpbc_the_zone()->get_currency() . wcpbc_the_zone()->get_exchange_rate();
		return $price_hash;
	}

	/**
	 * WC 3.6 compatibility. Set pricing zone price for items in the cart. Fix compatibility issue for plugins that uses 'edit' context to get the price.
	 *
	 * @since 1.8.4
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public static function set_cart_item_price( $cart_item ) {
		if ( version_compare( WC_VERSION, '3.6', '>=' ) && in_array( $cart_item['data']->get_type(), array_unique( apply_filters( 'wc_price_based_country_product_types_overriden', array( 'simple', 'variable', 'external' ) ) ), true ) ) {
			$cart_item['data']->set_price( self::get_price_metadata( null, $cart_item['data']->get_id(), '_price', true ) );
			$cart_item['data']->set_regular_price( self::get_price_metadata( null, $cart_item['data']->get_id(), '_regular_price', true ) );
			$cart_item['data']->set_sale_price( -9999 ); // Force change on the sale price property updating it with a ridiculous value.
			$cart_item['data']->set_sale_price( self::get_price_metadata( null, $cart_item['data']->get_id(), '_sale_price', true ) );
		}
		return $cart_item;
	}

	/**
	 * Override _price metakey in array of arguments for ordering products based on the selected values.
	 *
	 * @param array $args Ordering args.
	 * @return array
	 */
	public static function get_catalog_ordering_args( $args ) {
		if ( isset( $args['meta_key'] ) && '_price' === $args['meta_key'] ) {
			$args['meta_key'] = wcpbc_the_zone()->get_postmetakey( '_price' ); // WPCS: slow query ok.
		} elseif ( isset( $args['orderby'] ) && 'price' === $args['orderby'] ) {
			// Since WC 3.1.
			add_filter( 'posts_clauses', array( __CLASS__, 'order_by_price_post_clauses' ), 20 );
		}

		return $args;
	}

	/**
	 * Replace the _price metakey in order post clauses.
	 *
	 * @version 1.8.6
	 * @param array $args Query args.
	 * @return array
	 */
	public static function order_by_price_post_clauses( $args ) {
		global $wpdb;
		if ( version_compare( WC_VERSION, '3.6', '<' ) ) {
			$args['join'] = str_replace( "meta_key='_price'", "meta_key='" . wcpbc_the_zone()->get_postmetakey( '_price' ) . "'", $args['join'] ); // WPCS: slow query ok.
		} else {

			$args['join']    = self::append_wcpbc_price_table_join( $args['join'] );
			$args['orderby'] = str_replace( array( 'wc_product_meta_lookup.max_price ', 'wc_product_meta_lookup.min_price ' ), array( 'wcpbc_price.max_price ', 'wcpbc_price.min_price ' ), $args['orderby'] );
		}

		return $args;
	}

	/**
	 * Replace the _price metakey in filter post clauses. WC 3.6 compatibility.
	 *
	 * @param array    $args Query args.
	 * @param WC_Query $wp_query WC_Query object.
	 * @return array
	 */
	public static function filter_price_post_clauses( $args, $wp_query ) {
		global $wpdb;

		if ( version_compare( WC_VERSION, '3.6', '<' ) || ! $wp_query->is_main_query() || ( ! isset( $_GET['max_price'] ) && ! isset( $_GET['min_price'] ) ) ) { // WPCS: CSRF ok.
			return $args;
		}

		$args['join']  = self::append_wcpbc_price_table_join( $args['join'] );
		$args['where'] = str_replace( array( ' wc_product_meta_lookup.min_price >= ', ' wc_product_meta_lookup.max_price <= ' ), array( ' wcpbc_price.min_price >= ', ' wcpbc_price.max_price <= ' ), $args['where'] );

		return $args;
	}

	/**
	 * Join wcpbc_price to posts if not already joined.
	 *
	 * @since 1.8.5
	 * @version 1.8.6
	 * @param string $sql SQL join.
	 * @return string
	 */
	private static function append_wcpbc_price_table_join( $sql ) {
		global $wpdb;

		if ( ! strstr( $sql, 'wcpbc_price' ) ) {
			$sql .= $wpdb->prepare( " LEFT JOIN (
				SELECT post_meta.post_id, min( post_meta.meta_value + 0) as min_price, max( post_meta.meta_value + 0) as max_price
				FROM {$wpdb->postmeta} post_meta
				INNER JOIN {$wpdb->wc_product_meta_lookup} product_meta_lookup ON post_meta.post_id = product_meta_lookup.product_id WHERE post_meta.meta_key = %s GROUP BY post_meta.post_id
			) wcpbc_price ON {$wpdb->posts}.ID = wcpbc_price.post_id", wcpbc_the_zone()->get_postmetakey( '_price' ) );
		}

		return $sql;
	}

	/**
	 * Remove custom pre_get_post filters after the main WooCommerce query is done. WC 3.6 compatibility.
	 *
	 * @param array $posts Posts from WP Query.
	 * @return array
	 */
	public static function remove_product_query_filters( $posts ) {
		remove_filter( 'posts_clauses', array( __CLASS__, 'order_by_price_post_clauses' ), 20 );
		remove_filter( 'posts_clauses', array( __CLASS__, 'filter_price_post_clauses' ), 25, 2 );
		return $posts;
	}

	/**
	 * Override _price metakey in meta query for filtering by price.
	 *
	 * @param array    $meta_query Meta query args.
	 * @param WC_Query $q WC Query instance.
	 * @return array
	 */
	public static function product_query_meta_query( $meta_query, $q ) {
		if ( isset( $meta_query['price_filter']['key'] ) && '_price' === $meta_query['price_filter']['key'] ) {
			$meta_query['price_filter']['key'] = wcpbc_the_zone()->get_postmetakey( '_price' );
		}
		return $meta_query;
	}

	/**
	 * Override _price metakey for get filtered min and max price for current products.
	 *
	 * @param array $meta_keys Metadata keys array.
	 * @return array
	 */
	public static function price_filter_meta_keys( $meta_keys ) {
		return array( wcpbc_the_zone()->get_postmetakey( '_price' ) );
	}

	/**
	 * Override price filter SQL. WC 3.6 compatibility.
	 *
	 * @param string $sql Price filter sql.
	 * @return string
	 */
	public static function price_filter_sql( $sql ) {
		global $wpdb;

		if ( version_compare( WC_VERSION, '3.6', '<' ) ) {
			return $sql;
		}

		$where_pos = strpos( strtoupper( $sql ), 'WHERE ' );
		if ( $where_pos ) {
			$_sql = "
				SELECT min( wcpbc_price.meta_value + 0 ) as min_price, max( wcpbc_price.meta_value + 0 ) as max_price
				FROM {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup
				LEFT JOIN {$wpdb->postmeta} wcpbc_price ON wc_product_meta_lookup.product_id = wcpbc_price.post_id and wcpbc_price.meta_key = '" . wcpbc_the_zone()->get_postmetakey( '_price' ) . "'
			";
			$sql  = $_sql . substr( $sql, $where_pos );
		}
		return $sql;
	}

	/**
	 * Returns an array containing the IDs of the products that are on sale. Filter through get_transient
	 *
	 * @param mixed  $value The default value to return if the transient does not exist.
	 * @param string $transient Transient name.
	 * @return array
	 */
	public static function product_ids_on_sale( $value, $transient = false ) {
		global $wpdb;

		$cache_key = 'wcpbc_products_onsale_' . wcpbc_the_zone()->get_zone_id();

		// Load from cache.
		$product_ids_on_sale = get_transient( $cache_key );

		// Valid cache found.
		if ( false !== $product_ids_on_sale ) {
			return $product_ids_on_sale;
		}

		$decimals = absint( wc_get_price_decimals() );

		$on_sale_posts = $wpdb->get_results( $wpdb->prepare( "
			SELECT post.ID, post.post_parent FROM `{$wpdb->posts}` AS post
			LEFT JOIN `{$wpdb->postmeta}` AS meta ON post.ID = meta.post_id
			LEFT JOIN `{$wpdb->postmeta}` AS meta2 ON post.ID = meta2.post_id
			WHERE post.post_type IN ( 'product', 'product_variation' )
				AND post.post_status = 'publish'
				AND meta.meta_key = %s
				AND meta2.meta_key = %s
				AND CAST( meta.meta_value AS DECIMAL ) >= 0
				AND CAST( meta.meta_value AS CHAR ) != ''
				AND CAST( meta.meta_value AS DECIMAL( 10, %d ) ) = CAST( meta2.meta_value AS DECIMAL( 10, %d ) )
			GROUP BY post.ID
		", wcpbc_the_zone()->get_postmetakey( '_sale_price' ), wcpbc_the_zone()->get_postmetakey( '_price' ), $decimals, $decimals ) );

		$product_ids_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

		set_transient( $cache_key, $product_ids_on_sale, DAY_IN_SECONDS * 30 );

		return $product_ids_on_sale;
	}

	/**
	 * Apply exchange rate to shipping cost
	 *
	 * @param array $rates Rates.
	 * @param array $package Cart items.
	 * @return float
	 */
	public static function package_rates( $rates, $package ) {

		if ( 'yes' === get_option( 'wc_price_based_country_shipping_exchange_rate', 'no' ) ) {

			foreach ( $rates as $rate ) {
				$change = false;

				if ( ! isset( $rate->wcpbc_data ) ) {

					$rate->wcpbc_data = array(
						'exchange_rate' => wcpbc_the_zone()->get_exchange_rate(),
						'orig_cost'     => $rate->cost,
						'orig_taxes'    => $rate->taxes,
					);

					$change = true;

				} elseif ( wcpbc_the_zone()->get_exchange_rate() !== $rate->wcpbc_data['exchange_rate'] ) {

					$rate->wcpbc_data['exchange_rate'] = wcpbc_the_zone()->get_exchange_rate();
					$change = true;

				}

				if ( $change ) {
					// Apply exchange rate.
					if ( ! wc_prices_include_tax() ) {
						$rate->cost = wcpbc_the_zone()->get_exchange_rate_price( $rate->cost );
					} else {
						$rate->cost = wcpbc_the_zone()->get_exchange_rate_price( $rate->cost, false );
					}

					// Recalculate taxes.
					$rate_taxes = $rate->taxes;
					foreach ( $rate->wcpbc_data['orig_taxes'] as $i => $tax ) {
						$rate_taxes[ $i ] = ( $tax / $rate->wcpbc_data['orig_cost'] ) * $rate->cost;
					}
					$rate->taxes = $rate_taxes;
				}
			}
		}

		return $rates;
	}

	/**
	 * Apply exchange rate to free shipping min amount
	 *
	 * @param array            $methods Array of shipping methods.
	 * @param array            $raw_methods Raw methods.
	 * @param array            $allowed_classes Array of allowed classes.
	 * @param WC_Shipping_Zone $shipping Shipiing zone instance.
	 * @return array
	 */
	public static function shipping_zone_shipping_methods( $methods, $raw_methods, $allowed_classes, $shipping ) {
		if ( 'yes' === get_option( 'wc_price_based_country_shipping_exchange_rate', 'no' ) ) {
			foreach ( $methods as $instance_id => $method ) {
				if ( 'free_shipping' === $method->id ) {
					$method->min_amount = wcpbc_the_zone()->get_exchange_rate_price( $method->min_amount );
				}
			}
		}

		return $methods;
	}

	/**
	 * Filters the non-base location tax adjust.
	 *
	 * @param bool $adjust True or False.
	 * @return bool
	 */
	public static function adjust_non_base_location_prices( $adjust ) {
		if ( wcpbc_the_zone()->get_disable_tax_adjustment() ) {
			$adjust = false;
		}
		return $adjust;
	}

	/**
	 * Apply exchange rate to coupon
	 *
	 * @param WC_Coupon $coupon Coupon instance.
	 */
	public static function coupon_loaded( $coupon ) {
		$_back = version_compare( WC_VERSION, '3.0', '<' );

		$discount_type  = $_back ? $coupon->discount_type : $coupon->get_discount_type();
		$coupon_id      = $_back ? $coupon->id : $coupon->get_id();
		$coupon_amount  = $_back ? $coupon->coupon_amount : $coupon->get_amount();
		$minimum_amount = $_back ? $coupon->minimum_amount : $coupon->get_minimum_amount();
		$maximum_amount = $_back ? $coupon->maximum_amount : $coupon->get_maximum_amount();

		$zone_pricing_type = get_post_meta( $coupon_id, 'zone_pricing_type', true );

		if ( wcpbc_is_exchange_rate( $zone_pricing_type ) && 'percent' !== $discount_type ) {
			$amount = wcpbc_the_zone()->get_exchange_rate_price( $coupon_amount );
			self::set_coupon_prop( $coupon, 'coupon_amount', $amount, $_back );
		}

		if ( $minimum_amount ) {
			$amount = wcpbc_the_zone()->get_exchange_rate_price( $minimum_amount );
			self::set_coupon_prop( $coupon, 'minimum_amount', $amount, $_back );

		}
		if ( $maximum_amount ) {
			$amount = wcpbc_the_zone()->get_exchange_rate_price( $maximum_amount );
			self::set_coupon_prop( $coupon, 'maximum_amount', $amount, $_back );
		}
	}

	/**
	 * Set a coupon property value
	 *
	 * @since 1.7
	 * @param WC_Coupon $coupon Coupon instance.
	 * @param string    $prop The property to set.
	 * @param mixed     $value Value of property.
	 * @param boolean   $wc_old Is WC Version minor thant 3.0?.
	 */
	private static function set_coupon_prop( $coupon, $prop, $value, $wc_old ) {
		if ( $wc_old ) {
			$coupon->{$prop} = $value;
		} else {
			$setter = 'coupon_amount' === $prop ? 'set_amount' : 'set_' . $prop;
			$coupon->{$setter}( $value );
		}
	}

	/**
	 * Add zone data to order meta
	 *
	 * @since 1.7.4
	 * @param int   $order_id Order ID.
	 * @param array $data Order metadata.
	 */
	public static function update_order_meta( $order_id, $data ) {
		update_post_meta( $order_id, '_wcpbc_base_exchange_rate', wcpbc_the_zone()->get_base_currency_amount( 1 ) );
		update_post_meta( $order_id, '_wcpbc_pricing_zone', wcpbc_the_zone()->get_data() );
	}
}
