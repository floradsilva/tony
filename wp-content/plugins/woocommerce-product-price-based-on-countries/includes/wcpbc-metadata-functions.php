<?php
/**
 * WC Product Price Based Country Metadata Functions.
 *
 * @package WCPBC
 * @version 1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return product prices meta keys
 *
 * @since 1.6.0
 * @return array
 */
function wcpbc_get_price_meta_keys() {
	return array_unique( apply_filters( 'wc_price_based_country_price_meta_keys', array( '_price', '_regular_price', '_sale_price' ) ) );
}

/**
 * Return date on sale meta keys
 *
 * @since 1.6.22
 * @return array
 */
function wcpbc_get_date_on_sale_meta_keys() {
	return array_unique( apply_filters( 'wc_price_based_country_date_on_sale_meta_keys', array( '_sale_price_dates_from', '_sale_price_dates_to' ) ) );
}

/**
 * Returns all meta keys that must be overwriten
 *
 * @since 1.6.0
 * @return array
 */
function wcpbc_get_overwrite_meta_keys() {

	$price_meta_keys = wcpbc_get_price_meta_keys();
	$meta_keys       = array_merge( $price_meta_keys, wcpbc_get_date_on_sale_meta_keys() );

	foreach ( $price_meta_keys as $price_meta ) {
		array_push( $meta_keys,
			"_min_variation{$price_meta}",
			"_max_variation{$price_meta}",
			"_min{$price_meta}_variation_id",
			"_max{$price_meta}_variation_id"
		);
	}

	return array_unique( apply_filters( 'wc_price_based_country_overwrite_meta_keys', $meta_keys ) );
}

/**
 * Return all metakeys to delete from post metada when a zone is deleted.
 *
 * @since 1.8.0
 * @return array
 */
function wcpbc_get_meta_keys_to_delete() {
	$meta_keys = wcpbc_get_overwrite_meta_keys();
	array_push( $meta_keys, '_price_method', '_sale_price_dates' );
	return array_unique( apply_filters( 'wc_price_based_country_meta_keys_to_delete', $meta_keys ) );
}

/**
 * Returns variable product types
 *
 * @since 1.6.0
 * @return array
 */
function wcpbc_get_parent_product_types() {
	return array_unique( apply_filters( 'wc_price_based_country_parent_product_types', array( 'variable', 'grouped' ) ) );
}
/**
 * Get a max or min price in a postmeta row of children products
 *
 * @param string $zone_price_meta_key The price meta key.
 * @param int    $parent_id           Product parent ID.
 * @param string $min_or_max          min|max.
 * @return object
 */
function wcpbc_get_children_price( $zone_price_meta_key, $parent_id, $min_or_max = 'min' ) {
	global $wpdb;

	$query = array(
		'select'   => 'SELECT _zone_price.post_id, _zone_price.meta_value as value',
		'from'     => "FROM {$wpdb->posts} posts INNER JOIN {$wpdb->postmeta} _zone_price ON posts.ID = _zone_price.post_id AND _zone_price.meta_key = %s",
		'where'    => "WHERE _zone_price.meta_value <> '' AND posts.post_status = 'publish' AND posts.post_parent = %d",
		'order by' => 'ORDER BY _zone_price.meta_value +0 ' . ( 'max' === $min_or_max ? 'desc' : 'asc' ) . ' LIMIT 1',
	);

	$query_params = array( $zone_price_meta_key, $parent_id );

	$query = implode( ' ', $query );

	return $wpdb->get_row( $wpdb->prepare( $query, $query_params ) ); // WPCS: db call ok, unprepared SQL ok.
}

/**
 * Sync product variation prices with parent for a pricing zone
 *
 * @since 1.6.0
 * @version 1.8
 *
 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
 * @param int                $product_id Product ID.
 */
function wcpbc_zone_variable_product_sync( $zone, $product_id ) {

	foreach ( wcpbc_get_price_meta_keys() as $price_metakey ) {
		$zone->delete_postmeta( $product_id, $price_metakey );

		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			$variation_price = '';
			$variation_id    = '';
			$row             = wcpbc_get_children_price( $zone->get_postmetakey( $price_metakey ), $product_id, $min_or_max );
			if ( $row ) {
				$variation_price = $row->value;
				$variation_id    = $row->post_id;
			}
			$zone->set_postmeta( $product_id, "_{$min_or_max}_variation{$price_metakey}", $variation_price );
			$zone->set_postmeta( $product_id, "_{$min_or_max}{$price_metakey}_variation_id", $variation_id );

			if ( '_price' === $price_metakey ) {
				$zone->add_postmeta( $product_id, '_price', $variation_price );
			}
		}
	}

	$zone->set_postmeta( $product_id, '_price_method', 'nothing' );
}

/**
 * Sync grouped products with the children lowest price for a pricing zone
 *
 * @since 1.6.0
 * @version 1.8
 *
 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
 * @param int                $product_id Then product ID.
 */
function wcpbc_zone_grouped_product_sync( $zone, $product_id ) {
	$zone->delete_postmeta( $product_id, '_price' );
	foreach ( array( 'min', 'max' ) as $min_or_max ) {
		$row   = wcpbc_get_children_price( $zone->get_postmetakey( '_price' ), $product_id, $min_or_max );
		$price = empty( $row ) ? '' : $row->value;
		$zone->add_postmeta( $product_id, '_price', $price );
	}
	$zone->set_postmeta( $product_id, '_price_method', 'nothing' );
}

/**
 * Sync product variation prices with parent
 *
 * @since 1.6.0
 * @version 1.8
 *
 * @param  WC_Product|int $product Product instance or product ID.
 */
function wcpbc_variable_product_sync( $product ) {
	$product_id = is_numeric( $product ) ? $product : $product->get_id();
	foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
		wcpbc_zone_variable_product_sync( $zone, $product_id );
	}
}
if ( version_compare( get_option( 'woocommerce_version', null ), '3.0', '<' ) ) {
	add_action( 'woocommerce_variable_product_sync', 'wcpbc_variable_product_sync' );
} else {
	add_action( 'woocommerce_variable_product_sync_data', 'wcpbc_variable_product_sync' );
}

/**
 * Sync products prices by exchange rate for a pricing zone
 *
 * @since 1.6.0
 * @version 1.8.0
 *
 * @param WCPBC_Pricing_Zone $zone Pricing zone instance.
 */
function wcpbc_sync_exchange_rate_prices( $zone ) {
	global $wpdb;

	if ( ! $zone->get_exchange_rate() ) {
		return;
	}

	$exchange_rate         = $zone->get_exchange_rate();
	$price_method_meta_key = $zone->get_postmetakey( '_price_method' );

	// Variable products must haven't a price method.
	$parent_product_types = wcpbc_get_parent_product_types();

	$parent_product_ids = $wpdb->get_col( $wpdb->prepare( "
		SELECT t_r.object_id
		FROM {$wpdb->term_relationships} t_r
		INNER JOIN {$wpdb->posts} p ON p.ID = t_r.object_id and p.post_type = 'product' and p.post_status = 'publish'
		INNER JOIN {$wpdb->term_taxonomy} t_t ON t_r.term_taxonomy_id = t_t.term_taxonomy_id AND t_t.taxonomy = 'product_type'
		INNER JOIN {$wpdb->terms} t ON t.term_id = t_t.term_id
		LEFT JOIN {$wpdb->postmeta} _price_method ON _price_method.post_id = t_r.object_id AND _price_method.meta_key = %s
		WHERE t.slug IN (" . implode( ', ', array_fill( 0, count( $parent_product_types ), '%s' ) ) . ") and ifnull(_price_method.meta_value, '')<>'nothing'
	", array_merge( array( $price_method_meta_key ), $parent_product_types ) ) );

	if ( $parent_product_ids ) {
		foreach ( $parent_product_ids as $parent_product_id ) {
			update_post_meta( $parent_product_id, $price_method_meta_key, 'nothing' );
		}
	}

	// Sync products prices.
	foreach ( wcpbc_get_price_meta_keys() as $price_meta_key ) {

		$zone_price_meta_key = $zone->get_postmetakey( $price_meta_key );

		// Add region price meta key if not exists.
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
			SELECT post.ID, %s, '0'
			FROM {$wpdb->posts} post
			LEFT JOIN {$wpdb->postmeta} _zone_price_meta_key ON _zone_price_meta_key.post_id = post.ID AND _zone_price_meta_key.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} _price_method on post.ID = _price_method.post_id AND _price_method.meta_key = %s
			WHERE post.post_type IN ( 'product', 'product_variation' ) AND post.post_status = 'publish'
			AND ifnull(_price_method.meta_value, 'exchange_rate') = 'exchange_rate' AND _zone_price_meta_key.meta_id is null
		", $zone_price_meta_key, $zone_price_meta_key, $price_method_meta_key ) );

		// Update region price meta key by exchange_rate.
		$wpdb->query( $wpdb->prepare( "
			UPDATE {$wpdb->postmeta} _zone_price_meta_key
			INNER JOIN {$wpdb->posts} posts on posts.ID = _zone_price_meta_key.post_id
			INNER JOIN {$wpdb->postmeta} _price_meta_key on posts.ID = _price_meta_key.post_id AND _price_meta_key.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id AND _price_method.meta_key = %s
			SET _zone_price_meta_key.meta_value = CASE ifnull(_price_meta_key.meta_value, '') when '' THEN '' ELSE (_price_meta_key.meta_value + 0) * %f END
			WHERE posts.post_type IN ( 'product', 'product_variation' ) AND posts.post_status = 'publish'
			AND _zone_price_meta_key.meta_key = %s AND ifnull(_price_method.meta_value, 'exchange_rate') = 'exchange_rate'
			AND _zone_price_meta_key.meta_value <> CASE ifnull(_price_meta_key.meta_value, '') when '' THEN '' ELSE (_price_meta_key.meta_value + 0) * %f END
		", $price_meta_key, $price_method_meta_key, floatval( $exchange_rate ), $zone_price_meta_key, floatval( $exchange_rate ) ) );
	}

	// Sync parents product prices.
	$parent_products = $wpdb->get_results( $wpdb->prepare( "
		SELECT DISTINCT posts.post_parent AS id, posts.post_type as child_post_type
		FROM {$wpdb->posts} posts
		INNER JOIN {$wpdb->term_relationships} t_r ON t_r.object_id = posts.post_parent
		INNER JOIN {$wpdb->term_taxonomy} t_t ON t_r.term_taxonomy_id = t_t.term_taxonomy_id AND t_t.taxonomy = 'product_type'
		INNER JOIN {$wpdb->terms} t ON t.term_id = t_t.term_id
		LEFT JOIN {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id AND _price_method.meta_key = %s
		WHERE t.slug IN (" . implode( ', ', array_fill( 0, count( $parent_product_types ), '%s' ) ) . ")
		AND posts.post_type in ('product_variation', 'product') and posts.post_status = 'publish'
		AND ifnull(_price_method.meta_value, 'exchange_rate') = 'exchange_rate';
	", array_merge( array( $price_method_meta_key ), $parent_product_types ) ) );

	if ( $parent_products ) {
		foreach ( $parent_products as $parent_product ) {

			if ( 'product_variation' === $parent_product->child_post_type ) {
				// Clear prices transient for variable products.
				delete_transient( 'wc_var_prices_' . $parent_product->id );

				// Sync variable product price.
				wcpbc_zone_variable_product_sync( $zone, $parent_product->id );

			} else {
				// Sync grouped product price.
				wcpbc_zone_grouped_product_sync( $zone, $parent_product->id );
			}
		}
	}

	// Clear all transients cache for product data.
	wc_delete_product_transients();
}

/**
 * Function which handles the start and end of scheduled sales via cron.
 *
 * @since 1.6.0
 * @version 1.8.0
 */
function wcpbc_scheduled_sales() {
	global $wpdb;

	foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

		$key_sale_price_dates_from = $zone->get_postmetakey( '_sale_price_dates_from' );
		$key_sale_price_dates_to   = $zone->get_postmetakey( '_sale_price_dates_to' );
		$key_price_method          = $zone->get_postmetakey( '_price_method' );
		$key_regular_price         = $zone->get_postmetakey( '_regular_price' );
		$key_sale_price            = $zone->get_postmetakey( '_sale_price' );
		$key_price                 = $zone->get_postmetakey( '_price' );

		$parents = array();

		// Sales which are due to start.
		$products = $wpdb->get_results( $wpdb->prepare( "
			SELECT posts.ID as id, posts.post_parent, posts.post_type, _sale_price.meta_value as sale_price, _price.meta_value as price
			FROM {$wpdb->posts} posts
			INNER JOIN {$wpdb->postmeta} _sale_price_from on posts.ID = _sale_price_from.post_id and _sale_price_from.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id and _price_method.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _sale_price on posts.ID = _sale_price.post_id and _sale_price.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price on posts.ID = _price.post_id and _price.meta_key = %s
			WHERE _price_method.meta_value = 'manual' AND _sale_price.meta_value != _price.meta_value
				AND _sale_price_from.meta_value > 0 and _sale_price_from.meta_value < %s
		", $key_sale_price_dates_from, $key_price_method, $key_sale_price, $key_price, current_time( 'timestamp' ) ) );

		if ( $products ) {
			foreach ( $products as $product ) {
				if ( $product->sale_price ) {
					update_post_meta( $product->id, $key_price, $product->sale_price );
				} else {
					// No sale price!
					update_post_meta( $product->id, $key_sale_price_dates_from, '' );
					update_post_meta( $product->id, $key_sale_price_dates_to, '' );
				}

				// Store parent for sync.
				if ( $product->post_parent ) {
					if ( ! isset( $parents[ $product->post_type ] ) ) {
						$parents[ $product->post_type ] = array();
					}
					$parents[ $product->post_type ][] = $product->post_parent;
				}
			}
		}

		// Sales which are due to end.
		$products = $wpdb->get_results( $wpdb->prepare( "
			SELECT posts.ID as id, posts.post_parent, posts.post_type, _regular_price.meta_value as regular_price, _price.meta_value as price
			FROM {$wpdb->posts} posts
			INNER JOIN {$wpdb->postmeta} _sale_price_to on posts.ID = _sale_price_to.post_id and _sale_price_to.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price_method on posts.ID = _price_method.post_id and _price_method.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _regular_price on posts.ID = _regular_price.post_id and _regular_price.meta_key = %s
			LEFT JOIN  {$wpdb->postmeta} _price on posts.ID = _price.post_id and _price.meta_key = %s
			WHERE _price_method.meta_value = 'manual' AND _regular_price.meta_value != _price.meta_value
				AND _sale_price_to.meta_value > 0 and _sale_price_to.meta_value < %s
		", $key_sale_price_dates_to, $key_price_method, $key_regular_price, $key_price, current_time( 'timestamp' ) ) );

		if ( $products ) {
			foreach ( $products as $product ) {
				update_post_meta( $product->id, $key_price, $product->regular_price );
				update_post_meta( $product->id, $key_sale_price, '' );
				update_post_meta( $product->id, $key_sale_price_dates_from, '' );
				update_post_meta( $product->id, $key_sale_price_dates_to, '' );

				// Store parent for sync.
				if ( $product->post_parent ) {
					if ( ! isset( $parents[ $product->post_type ] ) ) {
						$parents[ $product->post_type ] = array();
					}
					$parents[ $product->post_type ][] = $product->post_parent;
				}
			}
		}

		// Sync parents.
		foreach ( $parents as $post_type => $parent_ids ) {
			if ( 'product' === $post_type ) {

				foreach ( array_unique( $parent_ids ) as $parent_id ) {
					// Sync grouped product price.
					wcpbc_zone_grouped_product_sync( $zone, $parent_id );
				}
			} elseif ( 'product_variation' === $post_type ) {

				foreach ( array_unique( $parent_ids ) as $parent_id ) {
					// Clear prices transient for variable products.
					delete_transient( 'wc_var_prices_' . $parent_id );

					// Sync variable product price.
					wcpbc_zone_variable_product_sync( $zone, $parent_id );
				}
			}
		}

		// Sync exchange rate prices.
		wcpbc_sync_exchange_rate_prices( $zone );
	}

}
add_action( 'woocommerce_scheduled_sales', 'wcpbc_scheduled_sales', 20 );

/**
 * Clear all WCPBC transients cache for product data.
 *
 * @param int $post_id (default: 0) The product ID.
 */
function wcpbc_delete_product_transients( $post_id = 0 ) {

	$transients_to_clear = array(
		'wcpbc_products_onsale_',
	);

	foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
		foreach ( $transients_to_clear as $transient ) {
			delete_transient( $transient . $zone->get_zone_id() );
		}
	}
}
add_action( 'woocommerce_delete_product_transients', 'wcpbc_delete_product_transients' );
