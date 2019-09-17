<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Conversions
 * @since 2.1
 */
class Conversions {


	/**
	 * Max number of days that a purchase to be considered a conversion
	 * @return int
	 */
	static function get_conversion_window() {
		return absint( apply_filters( 'automatewoo_conversion_window', AW()->options()->conversion_window ) );
	}


	/**
	 * @param int $order_id
	 */
	static function check_order_for_conversion( $order_id ) {

		if ( ! $order = wc_get_order( Clean::id( $order_id ) ) ) {
			return;
		}

		if ( ! $customer = Customer_Factory::get_by_order( $order ) ) {
			return;
		}

		$conversion_window_end = aw_normalize_date( $order->get_date_created() ); // convert to UTC

		if ( ! $conversion_window_end ) {
			return;
		}

		$conversion_window_start = clone $conversion_window_end;
		$conversion_window_start->modify( '-' . self::get_conversion_window() . ' days' );

		if ( ! $logs = self::get_logs_by_customer( $customer, $conversion_window_start, $conversion_window_end ) ) {
			return;
		}

		// check that at least one log shows that it has been opened i.e. has tracking data
		foreach ( $logs as $log ) {

			if ( ! $log->get_meta( 'tracking_data' ) ) {
				continue;
			}

			// has tracking data so mark the order as a conversion
			$order->update_meta_data( '_aw_conversion', $log->get_workflow_id() );
			$order->update_meta_data( '_aw_conversion_log', $log->get_id() );
			$order->save();

			break; // break loop so we only mark one log as converted
		}
	}


	/**
	 * @param Customer $customer
	 * @param DateTime $conversion_window_start
	 * @param DateTime $conversion_window_end
	 * @return Log[]
	 */
	static function get_logs_by_customer( $customer, $conversion_window_start, $conversion_window_end ) {
		$query = new Log_Query();
		$query->where( 'conversion_tracking_enabled', true );
		$query->where_customer_or_legacy_user( $customer, true );
		$query->where_date_between( $conversion_window_start, $conversion_window_end );
		$query->set_ordering('date', 'DESC');

		return $query->get_results();
	}

}
