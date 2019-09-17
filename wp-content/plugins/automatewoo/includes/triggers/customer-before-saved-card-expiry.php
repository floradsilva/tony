<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Trigger_Customer_Before_Saved_Card_Expiry
 * @since 3.7
 */
class Trigger_Customer_Before_Saved_Card_Expiry extends Trigger_Background_Processed_Abstract {

	public $supplied_data_items = [ 'customer', 'card' ];


	function load_admin_details() {
		$this->title = __( 'Customer Before Saved Card Expiry', 'automatewoo' );
		$this->description = __( "This trigger runs a set number of days before a customer's saved card expires. Cards expire on the last calendar day of their expiry month.", 'automatewoo' );
		$this->group = __( 'Customers', 'automatewoo' );
	}


	function load_fields() {
		$days_before = ( new Fields\Number() )
			->set_name( 'days_before_expiry' )
			->set_title( __( 'Days before expiry', 'automatewoo' ) )
			->set_required();

		$this->add_field($days_before);
		$this->add_field( $this->get_field_time_of_day() );
	}


	/**
	 * Get credit cards based on the specified days before expiry field.
	 *
	 * @param Workflow $workflow
	 * @param int      $limit
	 * @param int      $offset
	 *
	 * @return array
	 */
	function get_cards_by_expiry( $workflow, $limit, $offset ) {
		global $wpdb;

		$days_before = absint( $workflow->get_trigger_option( 'days_before_expiry' ) );

		if ( ! $days_before ) {
			return [];
		}

		$date = new DateTime();
		Time_Helper::convert_from_gmt( $date ); // get cards based on the sites timezone
		$date->modify("+$days_before days");

		$day_to_run = (int) $date->format('j');
		$days_in_month = (int) $date->format('t');

		if ( $days_in_month !== $day_to_run ) {
			return [];
		}

		$sql = "SELECT token_id FROM {$wpdb->prefix}woocommerce_payment_tokens as tokens
			LEFT JOIN {$wpdb->payment_tokenmeta} AS m1 ON tokens.token_id = m1.payment_token_id
			LEFT JOIN {$wpdb->payment_tokenmeta} AS m2 ON tokens.token_id = m2.payment_token_id
			WHERE type = 'CC'
			AND m1.meta_key = 'expiry_year'
			AND m1.meta_value = %s
			AND m2.meta_key = 'expiry_month'
			AND m2.meta_value = %s
			LIMIT %d OFFSET %d 
		";

		$sql = $wpdb->prepare( $sql, [
			$date->format( 'Y' ),
			$date->format( 'm' ),
			$limit,
			$offset,
		] );

		return array_keys( $wpdb->get_results( $sql, OBJECT_K ) );
	}


	/**
	 * @param Workflow $workflow
	 * @param int      $limit
	 * @param int      $offset
	 *
	 * @return array
	 */
	function get_background_tasks( $workflow, $limit, $offset = 0 ) {
		$tasks = [];

		foreach ( $this->get_cards_by_expiry( $workflow, $limit, $offset ) as $token_id ) {
			$tasks[] = [
				'workflow_id' => $workflow->get_id(),
				'workflow_data' => [
					'token_id' => $token_id
				]
			];
		}

		return $tasks;
	}


	/**
	 * @param Workflow $workflow
	 * @param array $data
	 */
	function handle_background_task( $workflow, $data ) {
		$token = isset( $data['token_id'] ) ? \WC_Payment_Tokens::get( absint( $data['token_id'] ) ) : false;

		if ( ! $token ) {
			return;
		}

		$customer = Customer_Factory::get_by_user_id( $token->get_user_id() );

		$workflow->maybe_run([
			'customer' => $customer,
			'card' => $token
		]);
	}


	/**
	 * @param $workflow Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		// workflow should only run once for each card
		if ( $workflow->has_run_for_data_item( 'card' ) ) {
			return false;
		}

		return true;
	}



}