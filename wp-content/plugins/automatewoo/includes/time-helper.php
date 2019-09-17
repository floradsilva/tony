<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Time_Helper
 * @since 2.9
 */
class Time_Helper {


	/**
	 * @param string|DateTime $time - string must be in format 00:00
	 * @return int
	 */
	static function calculate_seconds_from_day_start( $time ) {

		if ( is_a( $time, 'DateTime' ) ) {
			$time = $time->format( 'G:i' );
		}

		$parts = explode( ':', $time );

		if ( count( $parts ) !== 2 ) {
			return 0;
		}

		return ( absint( $parts[0] ) * HOUR_IN_SECONDS + absint( $parts[1] ) * MINUTE_IN_SECONDS );
	}


	/**
	 * @param \DateTime|DateTime $datetime
	 */
	static function convert_to_gmt( $datetime ) {
		$datetime->modify( '-' . self::get_timezone_offset() * HOUR_IN_SECONDS .' seconds' );
	}


	/**
	 * @param \DateTime|DateTime $datetime
	 */
	static function convert_from_gmt( $datetime ) {
		$datetime->modify( '+' . self::get_timezone_offset() * HOUR_IN_SECONDS .' seconds' );
	}


	/**
	 * @since 3.9
	 * @return float|int
	 */
	static function get_timezone_offset() {
		if ( $timezone = get_option( 'timezone_string' ) ) {
			$timezone_object = new \DateTimeZone( $timezone );
			return $timezone_object->getOffset( new \DateTime( 'now', new \DateTimeZone( 'UTC' ) ) ) / HOUR_IN_SECONDS;
		} else {
			return floatval( get_option( 'gmt_offset', 0 ) );
		}
	}


}
