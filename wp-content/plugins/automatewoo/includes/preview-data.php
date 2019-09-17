<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Preview_Data
 * @since 2.4.6
 */
class Preview_Data {

	/**
	 * The current preview workflow.
	 *
	 * @since 4.6.0
	 *
	 * @var Workflow
	 */
	private static $preview_workflow;

	/**
	 * @param array $data_items list of specific data items to get for preview
	 * @return array
	 */
	static function get_preview_data_layer( $data_items = [] ) {

		$data_layer = [];


		if ( in_array( 'user', $data_items ) ) {
			$data_layer['user'] = wp_get_current_user();
		}


		if ( in_array( 'customer', $data_items ) ) {
			$data_layer['customer'] = Customer_Factory::get_by_user_id( get_current_user_id() );
		}


		/**
		 * Order and order item
		 */
		if ( in_array( 'order', $data_items ) || in_array( 'order_item', $data_items ) ) {

			$order = self::get_preview_order();

			if ( $order ) {
				$data_layer['order'] = $order;

				if ( $data_layer['order'] ) {
					$data_layer['order_item'] = current( $data_layer['order']->get_items() );
					$data_layer['order_item']['id'] = current( array_keys( $data_layer['order']->get_items() ) );
				}
			}
		}


		/**
		 * Product
		 */
		if ( in_array( 'product', $data_items ) ) {
			$product_query = new \WP_Query([
				'post_type' => 'product',
				'posts_per_page' => 4,
				//'orderby' => 'rand',
				'fields' => 'ids'
			]);
			$data_layer['product'] = wc_get_product( $product_query->posts[0] );
		}


		/**
		 * Category
		 */
		if ( in_array( 'category', $data_items ) ) {
			$cats = get_terms( [
				'taxonomy' => 'product_cat',
				'order' => 'count',
				'number' => 1
			] );

			$data_layer['category'] = current( $cats );
		}


		/**
		 * Cart
		 */
		if ( in_array( 'cart', $data_items ) ) {

			$product_query = new \WP_Query([
				'post_type' => 'product',
				'posts_per_page' => 4,
				'fields' => 'ids'
			]);

			$cart = new Cart();
			$cart->set_id( 1 );
			$cart->set_total( 100 );
			$cart->set_user_id( get_current_user_id() );
			$cart->set_token();
			$cart->set_date_last_modified( new DateTime() );

			$items = [];

			foreach ( $product_query->posts as $product_id ) {

				$product = wc_get_product( $product_id );
				$variation_id = 0;
				$variation = [];

				if ( $product->is_type('variable') ) {
					/** @var \WC_Product_Variable $product */
					$variations = $product->get_available_variations();
					if ( $variations ) {
						$variation_id = $variations[0]['variation_id'];
						$variation = $variations[0]['attributes'];
					}
				}

				$items[] = [
					'product_id' => $product_id,
					'variation_id' => $variation_id,
					'variation' => $variation,
					'quantity' => 1,
//				'line_total' => $product->get_price(),
					'line_subtotal' => $product->get_price(),
					'line_subtotal_tax' => wc_get_price_including_tax( $product ) - $product->get_price(),
				];

			}

			$cart->set_items( $items );

			$cart->set_coupons([
				'10off' => [
					'discount_incl_tax' => '10',
					'discount_excl_tax' => '9',
					'discount_tax' => '1'
				]
			]);

			$data_layer['cart'] = $cart;
		}


		/**
		 * Wishlist
		 */
		if ( in_array( 'wishlist', $data_items ) ) {
			$wishlist = new Wishlist();

			$wishlist->items = $product_query->posts;

			$data_layer['wishlist'] = $wishlist;
		}


		/**
		 * Guest
		 */
		if ( in_array( 'guest', $data_items ) ) {
			$guest = new Guest();
			$guest->set_email( 'guest@example.com' );
			$data_layer['guest'] = $guest;
		}


		/**
		 * Subscription
		 */
		if ( Integrations::is_subscriptions_active() && in_array( 'subscription', $data_items ) ) {
			$subscriptions = wcs_get_subscriptions([
				'subscriptions_per_page' => 1
			]);

			$data_layer['subscription'] = current($subscriptions);
		}


		/**
		 * Membership
		 */
		if ( Integrations::is_memberships_enabled() && in_array( 'membership', $data_items ) ) {
			$memberships = get_posts( [
				'post_type' => 'wc_user_membership',
				'post_status' => 'any',
				'posts_per_page' => 1,
			]);

			$data_layer['membership'] = wc_memberships_get_user_membership( current($memberships) );
		}


		/**
		 * Card
		 */
		if ( in_array( 'card', $data_items ) ) {
			$token = new \WC_Payment_Token_CC();
			$token->set_user_id( 0 );
			$token->set_card_type('visa');
			$token->set_expiry_month( 04 );
			$token->set_expiry_year( 2020 );
			$token->set_last4( 1234 );

			$data_layer['card'] = $token;
		}


		return apply_filters( 'automatewoo/preview_data_layer', $data_layer, $data_items );
	}


	/**
	 * @param $workflow_id
	 * @param $action_number
	 * @param string $mode test|preview
	 *
	 * @return Action_Send_Email|Action_Send_Email_Raw|false
	 */
	static function generate_preview_action( $workflow_id, $action_number, $mode = 'preview' ) {
		$preview_data = wp_unslash( get_option( 'aw_wf_preview_data_' . $workflow_id ) );
		$workflow = Workflow_Factory::get( $workflow_id );

 		if ( ! $workflow || ! $action_number || ! is_array( $preview_data )  ) {
			return false;
		}

 		$trigger = Triggers::get( Clean::string( $preview_data['trigger_name'] ) );
 		$action_fields = $workflow->sanitize_action_fields( $preview_data['action_fields'] );

 		if ( ! $trigger || ! $action_fields ) {
 			return false;
	    }

		// check action exists
		if ( ! Actions::get( $action_fields['action_name'] ) ) {
			return false;
		}

		// create a fake action
		$action = clone Actions::get( $action_fields['action_name'] );

		// add the workflow in preview mode
		$workflow = Workflow_Factory::get( $workflow_id );

		if ( $mode === 'test' ) {
			$workflow->enable_test_mode();
		}
		else {
			$workflow->enable_preview_mode();
		}

		// set the data layer from preview trigger
		$workflow->set_data_layer( Preview_Data::get_preview_data_layer( $trigger->get_supplied_data_items() ), true );

		$action->workflow = $workflow;

		// replace saved options with live preview data
		$action->set_options( $action_fields );

		return $action;
	}


	/**
	 * @param int $offset used to do multiple attempts to get a valid order
	 * @return bool
	 */
	static function get_preview_order( $offset = 0 ) {
		if ( $offset > 8 ) {
			return false; // limit attempts
		}

		$orders = wc_get_orders([
			'type' => 'shop_order',
			'limit' => 1,
			'offset' => $offset,
			'return' => 'ids',
		]);

		$order = wc_get_order( $orders[0] );

		// if the order has a blank email, it will cause issues
		if ( $order && $order->get_billing_email() ) {
			return $order;
		}

		return self::get_preview_order( $offset + 1 );
	}

	/**
	 * Add customer language filter.
	 *
	 * @since 4.6.0
	 *
	 * @param Action $action
	 */
	public static function add_customer_language_filter( $action ) {
		self::$preview_workflow = $action->workflow;
		add_filter( 'automatewoo/customer/get_language', [ __CLASS__, 'filter_customer_language' ], 10, 2 );
	}

	/**
	 * Filter the current customers language when previewing.
	 * Make it the same as the preview workflow language.
	 *
	 * @since 4.6.0
	 *
	 * @param string $lang
	 * @param Customer $customer
	 *
	 * @return string
	 */
	public static function filter_customer_language( $lang, $customer ) {
		if ( ! self::$preview_workflow ) {
			return $lang;
		}

		$workflow_customer = self::$preview_workflow->data_layer()->get_customer();

		// Only filter the language of the customer in the preview workflow.
		if ( $workflow_customer && $customer->get_id() === $workflow_customer->get_id() ) {
			return (string) self::$preview_workflow->get_language();
		}

		return $lang;
	}

	/**
	 * Remove customer language filter.
	 *
	 * @since 4.6.0
	 *
	 * @param Action $action
	 */
	public static function remove_customer_language_filter( $action ) {
		self::$preview_workflow = null;
		remove_filter( 'automatewoo/customer/get_language', [ __CLASS__, 'filter_customer_language' ], 10 );
	}

}
