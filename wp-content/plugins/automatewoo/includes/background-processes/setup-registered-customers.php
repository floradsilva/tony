<?php
// phpcs:ignoreFile

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Customer_Factory;
use AutomateWoo\Events;

if ( ! defined( 'ABSPATH' ) ) exit;

class Setup_Registered_Customers extends Base {

	/** @var string  */
	public $action = 'setup_registered_customers';

	/**
	 * Do background task.
	 *
	 * @param int $user_id
	 * @return mixed
	 */
	protected function task( $user_id ) {
		$user_id = absint( $user_id );

		// get/create a customer record
		$customer = Customer_Factory::get_by_user_id( $user_id );

		if ( ! $customer ) {
			// error getting/creating the customer
			return false;
		}

		// ensure the user meta is correct
		update_user_meta( $user_id, '_automatewoo_customer_id', $customer->get_id() );

		// set the last purchase date
		$orders = wc_get_orders([
			'type'     => 'shop_order',
			'status'   => wc_get_is_paid_statuses(),
			'limit'    => 1,
			'customer' => $user_id,
			'orderby'  => 'date',
			'order'    => 'DESC'
		]);

		if ( $orders ) {
			$customer->set_date_last_purchased( $orders[0]->get_date_created() );
			$customer->save();
		}

		return false;
	}


	/**
	 * Batch completed, start a new one in 30 seconds
	 */
	protected function complete() {
		parent::complete();

		// there maybe more customers to process so wait and then do another batch
		Events::schedule_event( time() + 30, 'automatewoo_setup_registered_customers' );
	}

}

return new Setup_Registered_Customers();
