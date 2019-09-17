<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Wishlist
 * @since 2.9.9
 */
class Wishlist {

	public $id;
	public $owner_id;
	public $items;


	/**
	 * @return int
	 */
	function get_id() {
		return absint( $this->id );
	}


	/**
	 * @return int
	 */
	function get_user_id() {
		return absint( $this->owner_id );
	}


	/**
	 * @return Customer|bool
	 */
	function get_customer() {
		return Customer_Factory::get_by_user_id( $this->get_user_id() );
	}


	/**
	 * @return string
	 */
	function get_integration() {
		return Wishlists::get_integration();
	}


	/**
	 * @return array
	 */
	function get_items() {

		if ( isset( $this->items ) ) {
			return $this->items;
		}

		$this->items = [];

		if ( $this->get_integration() === 'yith' ) {

			$products = YITH_WCWL()->get_products([
				'wishlist_id' => $this->get_id(),
				'user_id' => $this->get_user_id()
			]);

			if ( ! empty( $products ) ) {
				foreach( $products as $product ) {
					$this->items[] = $product['prod_id'];
				}
			}
		}
		elseif ( $this->get_integration() === 'woothemes' ) {

			$products = get_post_meta( $this->get_id(), '_wishlist_items', true );

			if ( $products ) {
				foreach ( $products as $product ) {
					$this->items[] = $product['product_id'];
				}
			}
		}

		$this->items = array_unique( $this->items );

		return $this->items;
	}


	/**
	 * @return string
	 */
	function get_link() {
		if ( $this->get_integration() === 'yith' ) {
			return YITH_WCWL()->get_wishlist_url();
		}
		elseif ( $this->get_integration() === 'woothemes' ) {
			if ( class_exists( 'WC_Wishlists_Pages' ) ) {
				return add_query_arg( [ 'wlid' => $this->get_id() ], \WC_Wishlists_Pages::get_url_for('view-a-list') );
			}
		}
		return '';
	}


	/**
	 * @return string
	 */
	protected function get_date_created_option_name() {
		return '_automatewoo_wishlist_date_created_' . $this->get_id();
	}


	/**
	 * @return DateTime|false UTC
	 */
	function get_date_created() {
		$val = get_option( $this->get_date_created_option_name() );
		if ( ! $val ) {
			return false;
		}

		return new DateTime( $val );
	}


	/**
	 * @param DateTime $date UTC
	 */
	function set_date_created( $date ) {
		if ( ! is_a( $date, 'DateTime' ) ) {
			return;
		}
		update_option( $this->get_date_created_option_name(), $date->to_mysql_string(), false );
	}

}
