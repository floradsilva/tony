<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_Order_Is_Customers_First
 */
class AW_Rule_Order_Is_Customers_First extends AutomateWoo\Rules\Abstract_Bool {

	public $data_item = 'order';


	function init() {
		$this->title = __( "Order - Is Customer's First", 'automatewoo' );
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		$customer = [ $order->get_billing_email() ];

		if ( $order->get_user_id() ) {
			$customer[] = $order->get_user_id();
		}

		$orders = wc_get_orders(
			[
				'type'         => 'shop_order',
				'customer'     => $customer,
				'limit'        => 1,
				'return'       => 'ids',
				'exclude'      => [ $order->get_id() ],
				'date_created' => '<' . $order->get_date_created()->getTimestamp(),
			]
		);

		$is_first = empty( $orders );

		switch ( $value ) {
			case 'yes':
				return $is_first;

			case 'no':
				return ! $is_first;
		}
	}

}
