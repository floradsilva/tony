<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Order_Related_Products
 */
class Variable_Order_Related_Products extends Variable_Abstract_Product_Display {


	public $support_limit_field = true;


	function load_admin_details() {
		parent::load_admin_details();
		$this->description = __( "Displays a listing of products related to the items in an order.", 'automatewoo');
	}


	/**
	 * @param $order \WC_Order
	 * @param $parameters array
	 * @param $workflow
	 * @return mixed
	 */
	function get_value( $order, $parameters, $workflow ) {

		$related = [];
		$in_order = [];
		$template = isset( $parameters['template'] ) ? $parameters['template'] : false;
		$limit = isset( $parameters['limit'] ) ? absint( $parameters['limit'] ) : 8;

		/** @var \WC_Order_Item_Product[] $items */
		$items = $order->get_items();

		foreach ( $items as $item ) {
			// Product variations are not considered when getting related products.
			$in_order[] = $item->get_product_id();
			$related = array_merge( wc_get_related_products( $item->get_product_id(), $limit ), $related );
		}

		$related = array_diff( $related, $in_order );

		if ( empty( $related ) ) {
			return false;
		}

		$products = $this->prepare_products( $related, 'date', 'DESC', $limit );

		$args = array_merge( $this->get_default_product_template_args( $workflow, $parameters ), [
			'products' => $products,
		]);

		return $this->get_product_display_html( $template, $args );
	}
}

return new Variable_Order_Related_Products();
