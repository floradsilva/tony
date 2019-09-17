<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Registry class for background processes
 */
class Background_Processes extends Registry {

	/**
	 * Static cache of includes.
	 *
	 * @var array
	 */
	public static $includes;

	/**
	 * Static cache of loaded objects.
	 *
	 * @var array
	 */
	public static $loaded = [];

	/**
	 * Load includes.
	 *
	 * @return array
	 */
	public static function load_includes() {

		$path = AW()->path( '/includes/background-processes/' );

		$includes = [
			'events'                     => $path . 'events.php',
			'queue'                      => $path . 'queue.php',
			'abandoned_carts'            => $path . 'abandoned-carts.php',
			'setup_registered_customers' => $path . 'setup-registered-customers.php',
			'setup_guest_customers'      => $path . 'setup-guest-customers.php',
			'wishlist_item_on_sale'      => $path . 'wishlist-item-on-sale.php',
			'delete_expired_coupons'     => $path . 'delete-expired-coupons.php',
			'workflows'                  => $path . 'workflows.php',
			'tools'                      => $path . 'tools.php',
		];

		return apply_filters( 'automatewoo/background_processes/includes', $includes );
	}

	/**
	 * Get all background processes.
	 *
	 * @return Background_Processes\Base[]
	 */
	public static function get_all() {
		return parent::get_all();
	}

	/**
	 * Get a background.
	 *
	 * @param string $name
	 *
	 * @return Background_Processes\Base|false
	 */
	public static function get( $name ) {
		return parent::get( $name );
	}

}
