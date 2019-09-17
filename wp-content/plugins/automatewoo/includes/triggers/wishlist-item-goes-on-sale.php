<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Wishlist_Item_Goes_On_Sale
 */
class Trigger_Wishlist_Item_Goes_On_Sale extends Trigger {

	public $supplied_data_items = [ 'customer', 'product', 'wishlist' ];

	const SUPPORTS_QUEUING = false;


	function load_admin_details() {
		$this->title = sprintf( __( 'Wishlist Item On Sale (%s)', 'automatewoo' ), Wishlists::get_integration_title() );
		$this->group = __( 'Wishlists', 'automatewoo' );
		$this->description = __(
			"This trigger doesn't fire instantly when a product goes on sale. Instead, it performs a check for new on-sale products every 30 minutes. "
			. "Please note this doesn't work for guests because their wishlist data only exists in their session data.",
			'automatewoo' );
	}


	function register_hooks() {
		$integration = Wishlists::get_integration();

		if ( ! $integration )
			return;

		add_action( 'automatewoo/products/gone_on_sale', [ $this, 'handle_products_on_sale' ] );
	}


	/**
	 * @param array $products
	 */
	function handle_products_on_sale( $products ) {
		if ( ! $this->has_workflows() ) {
			return;
		}
		$this->start_background_process( Wishlists::get_all_wishlist_ids(), $products );
	}


	/**
	 * @param array $wishlists
	 * @param array $products
	 */
	function start_background_process( $wishlists, $products ) {

		/** @var Background_Processes\Wishlist_Item_On_Sale $process */
		$process = Background_Processes::get('wishlist_item_on_sale');

		foreach( $wishlists as $wishlist_id ) {
			$process->push_to_queue([
				'wishlist_id' => $wishlist_id,
				'product_ids' => $products
			]);
		}

		$process->start();
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		// Only trigger once per user, per product, per workflow, check logs
		if ( $workflow->has_run_for_data_item( [ 'product', 'user' ] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @param Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		$product = $workflow->data_layer()->get_product();

		if ( ! $product->is_on_sale() ) {
			return false; // check product is still on sale
		}

		return true;
	}

}

