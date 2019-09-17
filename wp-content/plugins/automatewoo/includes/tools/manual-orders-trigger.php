<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Tool_Manual_Orders_Trigger
 */
class Tool_Manual_Orders_Trigger extends Tool_Background_Processed_Abstract {

	public $id = 'manual_orders_trigger';


	function __construct() {
		parent::__construct();
		$this->title = __( 'Manual Orders Trigger', 'automatewoo' );
		$this->description = __( 'Manually trigger a workflow for existing orders that match a date range. For example, if you create a workflow using the <b>Order Completed</b> trigger and want to have that workflow run for existing completed orders.', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_form_fields() {
		$fields = [];

		$fields[] = ( new Fields\Workflow() )
			->set_name_base('args')
			->set_required()
			->add_query_arg( 'post_status', 'publish' )
			->add_query_arg( 'meta_query', [[
				'key' => 'trigger_name',
				'value' => [
					'order_placed',
					'order_placed_each_line_item',
					'order_payment_received',
					'order_payment_received_each_line_item',
					'order_status_changes',
					'order_status_changes_each_line_item',

					'order_cancelled',
					'order_completed',
					'order_on_hold',
					'order_pending',
					'order_processing',
					'order_refunded',

					'users_order_count_reaches',
					'users_total_spend',
					'user_purchases_from_taxonomy_term',
				]
				]]
			);

		$fields[] = ( new Fields\Date() )
			->set_name( 'date_from' )
			->set_title(__( 'Order Created Date - Range From', 'automatewoo' ))
			->set_name_base('args')
			->set_required();

		$fields[] = ( new Fields\Date() )
			->set_name( 'date_to' )
			->set_title( __( 'Order Created Date - Range To', 'automatewoo' ) )
			->set_name_base( 'args' )
			->set_required();

		return $fields;
	}


	/**
	 * @param array $args sanitized
	 * @return bool|\WP_Error
	 */
	function validate_process( $args ) {

		if ( empty( $args['workflow'] ) || empty( $args['date_from'] ) || empty( $args['date_to'] ) ) {
			return new \WP_Error( 1, __('Missing a required field.', 'automatewoo') );
		}

		$workflow = Workflow_Factory::get( $args['workflow'] );

		if ( ! $workflow || ! $workflow->is_active() ) {
			return new \WP_Error( 2, __( 'The selected workflow is not currently active.', 'automatewoo') );
		}

		$orders = $this->get_orders( $args['date_from'], $args['date_to'], $workflow );

		if ( empty( $orders ) ) {
			return new \WP_Error( 3, __( 'No orders were found matching the date range or required status.', 'automatewoo') );
		}

		return true;
	}


	/**
	 * @param $date_from
	 * @param $date_to
	 * @param $workflow Workflow
	 * @return array
	 */
	function get_orders( $date_from, $date_to, $workflow ) {
		$trigger = $workflow->get_trigger();

		if ( ! $trigger ) {
			return [];
		}

		$query_args = [
			'post_type' => 'shop_order',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'post_status' => array_keys( wc_get_order_statuses() ),
			'date_query' => [
				[
					'after' => $date_from,
					'before' => $date_to,
					'inclusive' => true
				]
			]
		];

		// filter query by order status
		if ( $trigger instanceof Trigger_Order_Status_Changes ) {
			if ( $statuses = Clean::recursive( $workflow->get_trigger_option( 'order_status_to' ) ) ) {
				$query_args[ 'post_status' ] = $statuses;
			}
		}
		elseif ( $trigger instanceof Trigger_Abstract_Order_Status_Base ) {
			$query_args[ 'post_status' ] = 'wc-' . $trigger->_target_status;
		}

		$query = new \WP_Query( $query_args );

		return $query->posts;
	}


	/**
	 * @param $args
	 * @return bool|\WP_Error
	 */
	function process( $args ) {
		$args = $this->sanitize_args( $args );

		$workflow = Workflow_Factory::get( $args['workflow'] );
		$orders = $this->get_orders( $args['date_from'], $args['date_to'], $workflow );

		$tasks = [];

		foreach ( $orders as $order_id ) {
			$tasks[] = [
				'tool_id' => $this->get_id(),
				'workflow_id' => $workflow->get_id(),
				'order_id' => $order_id,
			];
		}

		return Tools::init_background_process( $tasks );
	}


	/**
	 * Do validation in the validate_process() method not here
	 *
	 * @param $args
	 */
	function display_confirmation_screen( $args ) {

		$args = $this->sanitize_args( $args );

		$workflow = Workflow_Factory::get( $args[ 'workflow' ] );
		$orders = $this->get_orders( $args['date_from'], $args['date_to'], $workflow );

		$number_to_preview = 25;

		echo '<p>' . sprintf(
				__('Are you sure you want to manually trigger the <strong>%s</strong> workflow for '
					.'<strong>%s</strong> orders? This can not be undone.', 'automatewoo'),
				$workflow->title, count($orders) ) . '</p>';

		echo '<p>' . __( '<strong>Please note:</strong> This list only indicates the orders that match your selected date period. '
				. "These orders have yet to be validated against the selected workflow.", 'automatewoo' ) . '</p>';

		echo '<p>';

		foreach ( $orders as $i => $order_id ) {

			if ( $i == $number_to_preview )
				break;

			$order = wc_get_order( $order_id );

			printf( _x( '%1$s for %2$s', '1: order ID, 2: customer name', 'automatewoo' ),
				'<a href="'. get_edit_post_link( $order_id ).'">#' . $order_id . '</a>',
				$order->get_formatted_billing_full_name()
			);
			echo '<br>';
		}

		if ( count( $orders ) > $number_to_preview ) {
			printf( __( '+%d more orders...', 'automatewoo' ), count( $orders ) - $number_to_preview );
		}

		echo '</p>';
	}


	/**
	 * @param array $task
	 */
	function handle_background_task( $task ) {
		$workflow = isset( $task['workflow_id'] ) ? Workflow_Factory::get( $task['workflow_id'] ) : false;
		$order = isset( $task['order_id'] ) ? wc_get_order( Clean::id( $task['order_id'] ) ) : false;

		if ( ! $workflow || ! $order || ! $trigger = $workflow->get_trigger() ) {
			return;
		}

		$trigger->limit_trigger_to_specific_workflows( $workflow->get_id() );

		if ( $trigger instanceof Trigger_Abstract_Order_Status_Base ) {
			$trigger->status_changed( $order->get_id(), '', $order->get_status() );
		}
		elseif ( $trigger instanceof Trigger_Abstract_Order_Base ) {
			if ( $trigger->is_run_for_each_line_item ) {
				$trigger->trigger_for_each_order_item( $order );
			}
			else {
				$trigger->trigger_for_order( $order );
			}
		}

		$trigger->remove_limit_trigger_to_specific_workflows();
	}


	/**
	 * @param array $args
	 * @return array
	 */
	function sanitize_args( $args ) {
		$args = parent::sanitize_args( $args );

		if ( isset( $args['order_ids'] ) ) {
			$args['order_ids'] = Clean::ids( $args['order_ids'] );
		}

		return $args;
	}

}

return new Tool_Manual_Orders_Trigger();
