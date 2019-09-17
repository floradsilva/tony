<?php
// phpcs:ignoreFile

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Customer_Factory;
use AutomateWoo\Compat;
use AutomateWoo\Events;

if ( ! defined( 'ABSPATH' ) ) exit;

class Setup_Guest_Customers extends Base {

	/** @var string  */
	public $action = 'setup_guest_customers';


	/**
	 * @param int $order_id
	 * @return mixed
	 */
	protected function task( $order_id ) {

		if ( ! $order = wc_get_order( $order_id ) ) {
			return false;
		}

		if ( ! $customer = Customer_Factory::get_by_order( $order ) ) {
			return false;
		}

		if ( ! $customer->get_date_last_purchased() ) {

			// set the last purchase date
			$orders = wc_get_orders([
				'type' => 'shop_order',
				'status' => wc_get_is_paid_statuses(),
				'limit' => 1,
				'customer' => $customer->get_email(),
				'orderby' => 'date',
				'order' => 'DESC',
			]);

			if ( $orders ) {
				$customer->set_date_last_purchased( $orders[0]->get_date_created() );
				$customer->save();
			}
		}

		return false;
	}


	/**
	 * Batch completed, start a new one in 30 seconds
	 */
	protected function complete() {
		parent::complete();

		// check if there are more orders to process
		Events::schedule_event( time() + 30,'automatewoo_setup_guest_customers' );
	}

}

return new Setup_Guest_Customers();
