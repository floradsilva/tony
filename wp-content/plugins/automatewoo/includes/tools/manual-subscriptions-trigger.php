<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Tool_Manual_Subscriptions_Trigger
 */
class Tool_Manual_Subscriptions_Trigger extends Tool_Background_Processed_Abstract {

	public $id = 'manual_subscriptions_trigger';


	function __construct() {
		parent::__construct();

		$this->title = __( 'Manual Subscriptions Trigger', 'automatewoo' );
		$this->description = __( 'Manually trigger a workflow for existing subscriptions based on the date they were created.', 'automatewoo' );

	}


	function get_form_fields() {
		$fields = [];

		$fields[] = ( new Fields\Workflow() )
			->set_name_base('args')
			->add_query_arg( 'post_status', 'publish' )
			->set_required()
			->add_query_arg( 'meta_query', [[
				'key' => 'trigger_name',
				'value' => [
					'subscription_status_changed',
					'subscription_status_changed_each_line_item',
				]
				]]);

		$fields[] = ( new Fields\Date() )
			->set_name('date_from')
			->set_title(__( 'Subscription Start Date - Range From','automatewoo' ))
			->set_name_base('args')
			->set_required();

		$fields[] = ( new Fields\Date() )
			->set_name('date_to')
			->set_title(__( 'Subscription Start Date - Range To','automatewoo' ))
			->set_name_base('args')
			->set_required();

		return $fields;
	}


	/**
	 * @param $args
	 * @return bool|\WP_Error
	 */
	function validate_process( $args ) {

		if ( empty( $args['workflow'] ) || empty( $args['date_from'] ) || empty( $args['date_to'] ) ) {
			return new \WP_Error( 1, __('Missing a required field.', 'automatewoo') );
		}

		$workflow = Workflow_Factory::get( $args['workflow'] );

		if ( ! $workflow || ! $workflow->is_active() ) {
			return new \WP_Error( 2, __('The selected workflow is not currently active.', 'automatewoo') );
		}

		$subscriptions = $this->get_subscriptions( $args['date_from'], $args['date_to'] );

		if ( empty( $subscriptions ) ) {
			return new \WP_Error( 3, __( 'No subscriptions match that date range.', 'automatewoo') );
		}

		return true;
	}


	/**
	 * @param $date_from
	 * @param $date_to
	 * @return array
	 */
	function get_subscriptions( $date_from, $date_to ) {
		$query = new \WP_Query([
			'post_type' => 'shop_subscription',
			'post_status' => 'any',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'date_query' => [
				[
					'after' => $date_from,
					'before' => $date_to,
					'inclusive' => true
				]
			]
		]);

		return $query->posts;
	}


	/**
	 * @param $args
	 * @return bool|\WP_Error
	 */
	function process( $args ) {
		$args = $this->sanitize_args( $args );

		$workflow = Workflow_Factory::get( $args['workflow'] );
		$subscriptions = $this->get_subscriptions( $args['date_from'], $args['date_to'] );

		$tasks = [];

		foreach ( $subscriptions as $subscription_id ) {
			$tasks[] = [
				'tool_id' => $this->get_id(),
				'workflow_id' => $workflow->get_id(),
				'subscription_id' => $subscription_id,
			];
		}

		return Tools::init_background_process( $tasks );
	}


	/**
	 * Do validation in the init_process() method not here
	 *
	 * @param $args
	 */
	function display_confirmation_screen( $args ) {

		$args = $this->sanitize_args( $args );

		$workflow = Workflow_Factory::get( $args['workflow'] );
		$subscriptions = $this->get_subscriptions( $args['date_from'], $args['date_to'] );
		$subscription_count = count( $subscriptions );

		$number_to_preview = 25;

		echo '<p>' . sprintf(
				__('Are you sure you want to manually trigger the <strong>%s</strong> workflow for '
					.'<strong>%s</strong> subscriptions? This can not be undone.', 'automatewoo'),
				$workflow->title, count($subscriptions) ) . '</p>';

		echo '<p>' . __( '<strong>Please note:</strong> This list only indicates the subscriptions that match your selected date period. '
				. "These subscriptions have yet to be validated against the selected workflow.", 'automatewoo' ) . '</p>';

		echo '<p>';

		foreach ( $subscriptions as $i => $subscription_id ) {

			if ( $i == $number_to_preview )
				break;

			$subscription = wcs_get_subscription( $subscription_id );
			$subscription_id = $subscription->get_id();

			printf( _x( '%1$s for %2$s', '1: subscription ID, 2: customer name', 'automatewoo' ),
				'<a href="'. get_edit_post_link( $subscription_id ).'">#' . $subscription_id . '</a>',
				$subscription->get_formatted_billing_full_name()
			);
			echo '<br>';
		}

		if ( $subscription_count > $number_to_preview ) {
			printf( __( '+%d more subscriptions...', 'automatewoo' ), $subscription_count - $number_to_preview );
		}

		echo '</p>';


	}


	/**
	 * @param array $task
	 */
	function handle_background_task( $task ) {
		$workflow = isset( $task['workflow_id'] ) ? Workflow_Factory::get( $task['workflow_id'] ) : false;
		$subscription = isset( $task['subscription_id'] ) ? wcs_get_subscription( Clean::id( $task['subscription_id'] ) ) : false;

		$trigger = $workflow->get_trigger();

		if ( ! $workflow || ! $subscription || ! $trigger ) {
			return;
		}

		$trigger->limit_trigger_to_specific_workflows( $workflow->get_id() );

		if ( $trigger instanceof Trigger_Subscription_Status_Changed ) {
			$trigger->handle_status_changed( $subscription->get_id(), $subscription->get_status(), '' );
		}

		$trigger->remove_limit_trigger_to_specific_workflows();
	}


	/**
	 * @param array $args
	 * @return array
	 */
	function sanitize_args( $args ) {
		$args = parent::sanitize_args( $args );

		if ( isset( $args['subscription_ids'] ) ) {
			$args['subscription_ids'] = Clean::ids( $args['subscription_ids'] );
		}

		return $args;
	}

}

return new Tool_Manual_Subscriptions_Trigger();