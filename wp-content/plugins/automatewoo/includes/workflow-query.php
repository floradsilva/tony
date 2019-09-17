<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Workflow_Query
 */
class Workflow_Query {

	/** @var string|array */
	public $trigger;

	/** @var int */
	public $limit = -1;

	/** @var array */
	public $args;

	/** @var string */
	public $return = 'objects';


	function __construct() {
		$this->args = [
			'post_type' => 'aw_workflow',
			'post_status' => 'publish',
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'posts_per_page' => $this->limit,
			'meta_query' => [],
			'suppress_filters' => true,
			'no_found_rows' => true
		];
	}


	/**
	 * Set trigger name or array of names to query.
	 *
	 * @param string|array $trigger
	 */
	function set_trigger( $trigger ) {
		if ( $trigger instanceof Trigger ) {
			$this->trigger = $trigger->get_name();
		}
		else {
			$this->trigger = $trigger;
		}
	}


	/**
	 * @param $i
	 */
	function set_limit( $i ) {
		$this->limit = $i;
	}


	/**
	 * Default status is active
	 * @param string $status - any|active|disabled
	 */
	function set_status( $status ) {
		$status_map = [
			'any' => 'any',
			'active' => 'publish',
			'disabled' => 'aw-disabled'
		];
		$this->args['post_status'] = $status_map[ $status ];
	}


	/**
	 * @param $return - objects|ids
	 */
	function set_return( $return ) {
		$this->return = $return;
	}


	/**
	 * @return Workflow[]
	 */
	function get_results() {

		if ( $this->trigger ) {
			$this->args['meta_query'][] = [
				'key' => 'trigger_name',
				'value' => $this->trigger,
			];
		}

		if ( $this->return == 'ids' ) {
			$this->args['fields'] = 'ids';
		}

		$query = new \WP_Query( $this->args );
		$posts = $query->posts;

		if ( ! $posts ) {
			return [];
		}

		$workflows = [];

		foreach ( $posts as $post ) {

			if ( $this->return == 'ids' ) {
				$workflows[] = $post;
			}
			else {
				$workflow = new Workflow($post);
				$workflows[] = $workflow;
			}

		}

		return $workflows;
	}



	/**
	 * Alias of self::set_trigger()
	 *
	 * @param string|array $trigger
	 */
	function set_triggers( $trigger ) {
		$this->set_trigger( $trigger );
	}

}
