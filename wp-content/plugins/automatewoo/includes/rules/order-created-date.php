<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Order date rule.
 *
 * @class Order_Created_Date
 */
class Order_Created_Date extends Abstract_Date {
	/**
	 * Data item type.
	 *
	 * @var string
	 */
	public $data_item = 'order';

	/**
	 * Order_Created_Date constructor.
	 */
	public function __construct() {
		$this->has_is_past_comparision = true;

		parent::__construct();
	}

	/**
	 * Init
	 */
	public function init() {
		$this->title = __( 'Order - Created Date', 'automatewoo' );
	}


	/**
	 * Validates rule.
	 *
	 * @param \WC_Order  $order   Order we're validating against.
	 * @param string     $compare What variables we're using to compare.
	 * @param array|null $value   The values we have to compare. Null is only allowed when $compare is is_not_set.
	 *
	 * @return bool
	 */
	public function validate( $order, $compare, $value = null ) {
		return $this->validate_date( $compare, $value, aw_normalize_date( $order->get_date_created() ) );
	}

}
