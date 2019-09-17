<?php
/**
 * Update WCPBC to 1.8.2
 *
 * @package WCPBC
 * @version 1.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $wpdb;

$query = "SELECT posts.ID FROM {$wpdb->posts} posts INNER JOIN {$wpdb->postmeta} postmeta ON postmeta.post_id = posts.ID
		  WHERE posts.post_type = 'product' AND posts.post_status = 'publish'
		  AND postmeta.meta_key = %s AND postmeta.meta_value = ''
		  ";

foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {

	$post_ids = $wpdb->get_col( $wpdb->prepare( $query, $zone->get_postmetakey( '_min_price_variation_id' ) ) );

	foreach ( $post_ids as $post_id ) {
		wcpbc_zone_variable_product_sync( $zone, $post_id );
	}
}
