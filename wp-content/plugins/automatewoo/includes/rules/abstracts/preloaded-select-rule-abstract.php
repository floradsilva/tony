<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Class Preloaded_Select_Rule_Abstract
 *
 * @since 4.6
 * @package AutomateWoo\Rules
 */
abstract class Preloaded_Select_Rule_Abstract extends Select_Rule_Abstract {

	/**
	 * Cached select options. Leave public for JSON.
	 *
	 * @var array
	 */
	public $select_choices;

	/**
	 * Load select choices for rule.
	 *
	 * @return array
	 */
	public function load_select_choices() {
		return [];
	}

	/**
	 * Get the select choices for the rule.
	 *
	 * Choices are cached in memory.
	 *
	 * @return array
	 */
	public function get_select_choices() {
		if ( ! isset( $this->select_choices ) ) {
			$this->select_choices = $this->load_select_choices();
		}

		return $this->select_choices;
	}

}
