<?php

namespace AutomateWoo\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Searchable coupon field class.
 *
 * @since 4.6.0
 * @package AutomateWoo\Fields
 */
class Coupon extends Searchable_Select_Abstract {

	/**
	 * The default name for this field.
	 *
	 * @var string
	 */
	protected $name = 'coupon';

	/**
	 * Get the ajax action to use for the search.
	 *
	 * @return string
	 */
	protected function get_search_ajax_action() {
		return 'aw_json_search_coupons';
	}

	/**
	 * Get the displayed value of a selected option.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function get_select_option_display_value( $value ) {
		return wc_format_coupon_code( $value );
	}

}
