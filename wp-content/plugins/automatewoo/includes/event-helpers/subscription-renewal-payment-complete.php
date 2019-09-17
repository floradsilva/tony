<?php
// phpcs:ignoreFile

namespace AutomateWoo\Event_Helpers;

use AutomateWoo\Compat;
use AutomateWoo\Events;

/**
 * @class Subscription_Renewal_Payment_Complete
 */
class Subscription_Renewal_Payment_Complete {


	static function init() {
		add_action( 'woocommerce_subscription_renewal_payment_complete', [ __CLASS__, 'dispatch' ], 20, 2 );
	}


	/**
	 * @param \WC_Subscription $subscription
	 * @param \WC_Order $order
	 */
	static function dispatch( $subscription, $order ) {
		Events::schedule_async_event( 'automatewoo/subscription/renewal_payment_complete_async', [
			$subscription->get_id(),
			$order->get_id()
		] );
	}

}
