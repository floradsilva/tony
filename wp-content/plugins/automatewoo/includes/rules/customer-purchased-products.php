<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Class Customer_Purchased_Products
 *
 * @package AutomateWoo\Rules
 */
class Customer_Purchased_Products extends Product_Select_Rule_Abstract {

	/**
	 * The rule's primary data item.
	 *
	 * @var string
	 */
	public $data_item = 'customer';

	/**
	 * Init the rule.
	 */
	public function init() {
		$this->title = __( 'Customer - Purchased Products - All Time', 'automatewoo' );
		parent::init();
	}

	/**
	 * Validate the rule for a given customer.
	 *
	 * @param \AutomateWoo\Customer $customer
	 * @param string                $compare
	 * @param string|int            $expected_value
	 *
	 * @return bool
	 */
	public function validate( $customer, $compare, $expected_value ) {
		$product_id = absint( $expected_value );
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return false;
		}

		// phpcs:disable WordPress.PHP.StrictInArray.MissingTrueStrict
		// Using strict here cause tests to incorrectly fail
		$includes = in_array( $product_id, $customer->get_purchased_products() );
		// phpcs:enable

		switch ( $compare ) {
			case 'includes':
				return $includes;
			case 'not_includes':
				return ! $includes;
		}
	}
}
