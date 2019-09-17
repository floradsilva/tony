<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Variable_Abstract_Datetime
 *
 * @package AutomateWoo
 */
class Variable_Abstract_Datetime extends Variable {

	/**
	 * Shared description prop for datetime variables.
	 *
	 * @var string
	 */
	public $_desc_format_tip;

	/**
	 * Load admin details.
	 */
	public function load_admin_details() {
		$this->_desc_format_tip = sprintf(
			__( 'To set a custom date or time format please refer to the %1$sWordPress documentation%2$s.', 'automatewoo' ),
			'<a href="https://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">',
			'</a>'
		);

		$date_format_options = $this->get_date_format_options();

		foreach ( $date_format_options as $format_name => &$format_value ) {
			$format_value = $this->get_date_format_option_displayed_value( $format_name, $format_value );
		}

		$this->add_parameter_select_field(
			'format',
			__( 'Choose the format that the date will be displayed in. The default is MySQL datetime format.', 'automatewoo' ),
			$date_format_options,
			true
		);

		$this->add_parameter_text_field(
			'custom-format',
			__( "Set a format according to the documentation link in the variable's description.", 'automatewoo' ),
			true,
			'',
			[ 'show' => 'format=custom' ]
		);

		$this->add_parameter_text_field(
			'modify',
			__( 'Optional parameter to modify the value of the datetime. Uses the PHP strtotime() function.', 'automatewoo' ),
			false,
			__( 'e.g. +2 months, -1 day, +6 hours', 'automatewoo' )
		);
	}

	/**
	 * Get the PHP date format from the variable's format parameter.
	 *
	 * @since 4.5
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	protected function get_date_format_from_format_param( $format ) {
		switch ( $format ) {
			case 'mysql':
				return Format::MYSQL;
			case 'site':
				return wc_date_format();
			case 'custom':
				return '';
		}

		// Allow all other date formats through for backwards compatibility.
		return $format;
	}

	/**
	 * Get options for the date format select parameter.
	 *
	 * @since 4.5
	 *
	 * @return array
	 */
	protected function get_date_format_options() {
		$options = apply_filters(
			'automatewoo/variables/date_format_options',
			[
				'mysql'  => __( 'MySQL datetime - %2$s', 'automatewoo' ),
				'site'   => __( 'Site setting - %2$s', 'automatewoo' ),
				'Y-m-d'  => false,
				'm/d/Y'  => false,
				'd/m/Y'  => false,
				'U'      => __( 'Unix timestamp - %2$s', 'automatewoo' ),
				'custom' => _x( 'Custom', 'custom date format option', 'automatewoo' ),
			],
			$this
		);

		return $options;
	}

	/**
	 * Get the date format value for display in the admin area.
	 *
	 * @param string $format_name
	 * @param string $format_value
	 *
	 * @return string
	 */
	protected function get_date_format_option_displayed_value( $format_name, $format_value ) {
		$now          = aw_normalize_date( 'now' );
		$example_date = $now->format( $this->get_date_format_from_format_param( $format_name ) );

		// Set default format
		if ( $format_value === false ) {
			$format_value = _x( '%1$s - %2$s', 'date format option', 'automatewoo' );
		}

		return sprintf( $format_value, $format_name, $example_date );
	}

	/**
	 * Formats a datetime variable.
	 *
	 * Dates should be passed in the site's timezone.
	 * WC_DateTime objects will maintain their specified timezone.
	 *
	 * @param \WC_DateTime|DateTime|string $input
	 * @param array                        $parameters [modify, format]
	 * @param bool                         $is_gmt
	 *
	 * @return string|false
	 */
	public function format_datetime( $input, $parameters, $is_gmt = false ) {
		if ( ! $input ) {
			return false;
		}

		// \WC_DateTime objects will be converted to GMT by aw_normalize_date()
		if ( $input instanceof \WC_DateTime ) {
			$is_gmt = true;
		}

		$date = aw_normalize_date( $input );

		if ( ! $date ) {
			return false;
		}

		if ( $is_gmt ) {
			$date->convert_to_site_time();
		}

		if ( empty( $parameters['format'] ) ) {
			// Blank value meant MYSQL format pre version 4.5
			$format = Format::MYSQL;
		} else {
			if ( $parameters['format'] === 'custom' ) {
				$format = $parameters['custom-format'];
			} else {
				$format = $this->get_date_format_from_format_param( $parameters['format'] );
			}
		}

		if ( ! empty( $parameters['modify'] ) ) {
			$date->modify( $parameters['modify'] );
		}

		return $date->format( $format );
	}
}
