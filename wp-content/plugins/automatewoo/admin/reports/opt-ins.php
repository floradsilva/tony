<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Report_Optins
 */
class Report_Optins extends Admin_List_Table {

	public $name = 'opt-ins';

	public $enable_search = true;

	
	function __construct() {
		parent::__construct([
			'singular' => Options::optin_enabled() ? __( 'Opt-in', 'automatewoo' ) : __( 'Opt-out', 'automatewoo' ),
			'plural' => Options::optin_enabled() ? __( 'Opt-ins', 'automatewoo' ) : __( 'Opt-outs', 'automatewoo' ),
			'ajax' => false
		]);
		$this->search_button_text = __( 'Search by email', 'automatewoo' );
	}


	/**
	 * @param Customer $customer
	 * @return string
	 */
	function column_cb( $customer ) {
		return '<input type="checkbox" name="customer_ids[]" value="' . absint( $customer->get_id() ) . '" />';
	}


	/**
	 * @param Customer $customer
	 * @param mixed $column_name
	 * @return string
	 */
	function column_default( $customer, $column_name ) {

		switch( $column_name ) {
			case 'email':
				return Format::customer( $customer );
				break;

			case 'time':
				return $this->format_date( Options::optin_enabled() ? $customer->get_date_subscribed() : $customer->get_date_unsubscribed() );
				break;
		}
	}


	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'email' => __( 'Customer', 'automatewoo' ),
			'time' => __( 'Date', 'automatewoo' ),
		];

		return $columns;
	}


	/**
	 * Retrieve the bulk actions
	 */
	function get_bulk_actions() {
		$actions = [];

		if ( Options::optin_enabled() ) {
			$actions['bulk_optout'] = __( 'Set as opted-out', 'automatewoo' );
		}
		else {
			$actions['bulk_optin'] = __( 'Set as opted-in', 'automatewoo' );
		}

		return $actions;
	}


	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page = absint( $this->get_pagenum() );
		$per_page = $this->get_items_per_page( 'automatewoo_unsubscribes_per_page' );

		$this->get_items( $current_page, $per_page );

		$this->set_pagination_args([
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}


	/**
	 * @param $current_page
	 * @param $per_page
	 */
	function get_items( $current_page, $per_page ) {
		$query = new Customer_Query();

		if ( Options::optin_enabled() ) {
			$query->where( 'subscribed', true );
			$query->set_ordering( 'subscribed_date' );
		}
		else {
			$query->where( 'unsubscribed', true );
			$query->set_ordering( 'unsubscribed_date' );
		}

		$has_no_valid_search_matches = false;

		if ( ! empty( $_GET['s'] ) ) {
			$search = trim( strtolower( Clean::string($_GET['s'] ) ) );
			$search_wheres = [];

			$guest_query = new Guest_Query();
			$guest_query->where( 'email', "%$search%", 'LIKE' );
			$guest_query->set_return( 'ids' );
			$guest_ids = $guest_query->get_results();

			if ( $guest_ids ) {
				$search_wheres[] = [
					'column' => 'guest_id',
					'value' => $guest_ids,
					'compare' => 'IN'
				];
			}

			$user_query = new \WP_User_Query([
				'search' => '*' . esc_attr( $search ) . '*',
				'search_columns' => [ 'user_email' ],
				'fields' => 'ID',
			]);

			$user_ids = $user_query->get_results();

			if ( $user_ids ) {
				$search_wheres[] = [
					'column' => 'user_id',
					'value' => $user_ids,
					'compare' => 'IN'
				];
			}

			if ( $search_wheres ) {
				$query->where[] = $search_wheres;
			}
			else {
				$has_no_valid_search_matches = true;
			}
		}

		$query->set_calc_found_rows( true );
		$query->set_limit( $per_page );
		$query->set_page( $current_page );

		// if there are no valid search matches there are no matching customers
		if ( $has_no_valid_search_matches === false ) {
			$results         = $query->get_results();
			$this->items     = $results;
			$this->max_items = $query->found_rows;
		}
	}

}
