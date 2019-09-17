<?php
// phpcs:ignoreFile

namespace AutomateWoo\Compat;

use AutomateWoo\DateTime;

/**
 * @class Coupon
 * @since 2.9
 *
 * @deprecated
 */
class Coupon {

	/**
	 * @param \WC_Coupon $coupon
	 * @return mixed
	 */
	static function get_code( $coupon ) {
		return $coupon->get_code();
	}

	/**
	 * @param \WC_Coupon $coupon
	 * @param $key
	 * @return mixed
	 */
	static function get_meta( $coupon, $key ) {
		return $coupon->get_meta( $key );
	}

	/**
	 * @param \WC_Coupon $coupon
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	static function update_meta( $coupon, $key, $value ) {
		$coupon->update_meta_data( $key, $value );
		$coupon->save();
	}

	/**
	 * @param \WC_Coupon $coupon
	 * @param DateTime $date
	 */
	static function set_date_expires( $coupon, $date ) {
		$coupon->set_date_expires( $date->getTimestamp() );
		$coupon->save();
	}

	/**
	 * @param \WC_Coupon $coupon
	 * @param int $limit
	 */
	static function set_usage_limit( $coupon, $limit ) {
		$coupon->set_usage_limit( $limit );
		$coupon->save();
	}

	/**
	 * @param \WC_Coupon $coupon
	 * @param array $emails
	 */
	static function set_email_restriction( $coupon, $emails ) {
		$coupon->set_email_restrictions( $emails );
		$coupon->save();
	}

	/**
	 * @param int $coupon_id
	 * @return int
	 */
	static function get_date_expires_by_id( $coupon_id ) {
		$coupon = new \WC_Coupon( $coupon_id );
		$expiry = $coupon->get_date_expires();

		if ( $expiry ) {
			return $expiry->getTimestamp();
		}

		return 0;
	}

	/**
	 * Get coupon ID by code.
	 *
	 * @param string $code
	 * @return int
	 */
	static function get_coupon_id_by_code( $code ) {
		return wc_get_coupon_id_by_code( $code );
	}

}
