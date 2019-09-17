<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Wishlists
 */
class Wishlists {

	/**
	 * @var array
	 */
	static $integration_options = [
		'yith' => 'YITH Wishlists',
		'woothemes' => 'WooCommerce Wishlists'
	];


	/**
	 * @return string|false
	 */
	static function get_integration() {
		if ( class_exists( 'WC_Wishlists_Plugin') ) {
			return 'woothemes';
		}
		elseif ( class_exists( 'YITH_WCWL') ) {
			return 'yith';
		}
		else {
			return false;
		}
	}


	/**
	 * @return string|false
	 */
	static function get_integration_title() {
		$integration = self::get_integration();

		if ( ! $integration )
			return false;

		return self::$integration_options[$integration];
	}


	/**
	 * Get wishlist by ID
	 *
	 * @param int $id
	 * @return bool|Wishlist
	 */
	static function get_wishlist( $id ) {

		$integration = self::get_integration();

		if ( ! $id || ! $integration )
			return false;

		if ( $integration == 'yith' ) {
			$wishlist = YITH_WCWL()->get_wishlist_detail( $id );
		}
		elseif ( $integration == 'woothemes' ) {
			$wishlist = get_post( $id );
		}
		else {
			return false;
		}

		return self::get_normalized_wishlist( $wishlist );
	}



	/**
	 * Convert wishlist objects from both integrations into the same format
	 * Returns false if wishlist is empty
	 *
	 * @param $wishlist \WP_Post|array
	 * @return Wishlist|false
	 */
	static function get_normalized_wishlist( $wishlist ) {

		$integration = self::get_integration();

		if ( ! $wishlist || ! $integration ) {
			return false;
		}

		$normalized_wishlist = new Wishlist();


		if ( $integration == 'yith' ) {

			if ( ! is_array( $wishlist ) ) {
				return false;
			}

			$normalized_wishlist->id = $wishlist['ID'];
			$normalized_wishlist->owner_id = $wishlist['user_id'];
		}
		elseif ( $integration == 'woothemes' ) {

			if ( ! $wishlist instanceof \WP_Post ) {
				return false;
			}

			$normalized_wishlist->id = $wishlist->ID;
			$normalized_wishlist->owner_id = get_post_meta( $wishlist->ID, '_wishlist_owner', true );
		}

		return $normalized_wishlist;
	}


	/**
	 * Get an array with the IDs of all wishlists.
	 *
	 * @since 4.3.2
	 *
	 * @return array
	 */
	static function get_all_wishlist_ids() {
		return self::get_wishlist_ids();
	}

	/**
	 * Get wishlist IDs.
	 *
	 * @since 4.5
	 *
	 * @param int|bool $limit
	 * @param int      $offset
	 *
	 * @return array
	 */
	static function get_wishlist_ids( $limit = false, $offset = 0 ) {
		$integration = Wishlists::get_integration();

		if ( $integration === 'woothemes' ) {
			$query = new \WP_Query([
				'post_type'      => 'wishlist',
				'posts_per_page' => $limit === false ? -1 : $limit,
				'offset'         => $offset,
				'fields'         => 'ids',
			]);
			$ids = $query->posts;
		}
		elseif ( $integration === 'yith' ) {
			$wishlists = YITH_WCWL()->get_wishlists( [
				'user_id'    => false,
				'show_empty' => false,
				'limit'      => $limit === false ? false : $limit,
				'offset'     => $offset,
			] );

			$ids = wp_list_pluck( $wishlists, 'ID' );
		}
		else {
			return [];
		}

		$ids = array_map( 'absint', $ids );
		return $ids;
	}

}
