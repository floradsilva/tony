<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Process variables into values. Is used on workflows and action options.
 *
 * @class Variable_Processor
 * @since 2.0.2
 */
class Variables_Processor {

	/** @var Workflow */
	public $workflow;


	/**
	 * @param $workflow
	 */
	function __construct( $workflow ) {
		$this->workflow = $workflow;
	}


	/**
	 * @param $text string
	 * @param bool $allow_html
	 * @return string
	 */
	function process_field( $text, $allow_html = false ) {

		$replacer = new Replace_Helper( $text, [ $this, '_callback_process_field' ], 'variables' );
		$value = $replacer->process();

		if ( ! $allow_html ) {
			$value = html_entity_decode( wp_strip_all_tags( $value ) );
		}

		return $value;
	}


	/**
	 * Callback function to process a variable string.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	function _callback_process_field( $string ) {
		$string = $this->sanitize( $string );

		if ( self::is_excluded( $string ) ) {
			return "{{ $string }}";
		}

		$variable = self::parse_variable( $string );

		if ( ! $variable ) {
			return '';
		}

		$parameters = $variable->parameters;
		$value      = $this->get_variable_value( $variable->type, $variable->field, $parameters );
		$value      = (string) apply_filters( 'automatewoo/variables/after_get_value', $value, $variable->type, $variable->field, $parameters, $this->workflow );

		if ( $value === '' ) {
			// backwards compatibility
			if ( isset( $parameters['default'] ) ) {
				$parameters['fallback'] = $parameters['default'];
			}

			// show default if set and no real value
			if ( isset( $parameters['fallback'] ) ) {
				$value = $parameters['fallback'];
			}
		}

		return $value;
	}


	/**
	 * @param $string
	 * @return Workflow_Variable_Parser|bool
	 */
	static function parse_variable( $string ) {
		$variable = new Workflow_Variable_Parser();
		if ( $variable->parse( $string ) ) {
			return $variable;
		}
		return false;
	}


	/**
	 * Get the value of a variable.
	 *
	 * @param string $data_type
	 * @param string $data_field
	 * @param array $parameters
	 *
	 * @return string
	 */
	function get_variable_value( $data_type, $data_field, $parameters = [] ) {

		// Short circuit filter for the variable value
		$short_circuit = (string) apply_filters( 'automatewoo_text_variable_value', false, $data_type, $data_field );

		if ( $short_circuit ) {
			return $short_circuit;
		}

		$this->convert_legacy_variables( $data_type, $data_field, $parameters );

		$variable_name = "$data_type.$data_field";
		$variable      = Variables::get_variable( $variable_name );

		$value = '';

		if ( method_exists( $variable, 'get_value' ) ) {

			if ( in_array( $data_type, Data_Types::get_non_stored_data_types(), true ) ) {
				$value = $variable->get_value( $parameters, $this->workflow );
			} else {
				$data_item = $this->workflow->get_data_item( $variable->get_data_type() );

				if ( $data_item ) {
					$value = $variable->get_value( $data_item, $parameters, $this->workflow );
				}
			}
		}

		return (string) apply_filters( 'automatewoo/variables/get_variable_value', (string) $value, $this, $variable );
	}


	/**
	 * Based on sanitize_title()
	 *
	 * @param $string
	 * @return mixed|string
	 */
	static function sanitize( $string ) {

		// remove style and script tags
		$string = wp_strip_all_tags( $string, true );
		$string = remove_accents( $string );

		// remove unicode white spaces
		$string = preg_replace( "#\x{00a0}#siu", ' ', $string );

		$string = trim($string);

		return $string;
	}


	/**
	 * Certain variables can be excluded from processing.
	 * Currently only {{ unsubscribe_url }}
	 *
	 * @param string $variable
	 * @return bool
	 */
	static function is_excluded( $variable ) {
		$excluded = apply_filters('automatewoo/variables_processor/excluded', [
			'unsubscribe_url'
		]);

		return in_array( $variable, $excluded );
	}


	/**
	 * Handle legacy variable compatibility.
	 *
	 * @param string $data_type
	 * @param string $data_field
	 * @param array $parameters
	 */
	private function convert_legacy_variables( &$data_type, &$data_field, &$parameters ) {
		if ( $data_type === 'site' ) {
			$data_type = 'shop';
		}

		if ( $data_type === 'shop' ) {
			if ( $data_field === 'products_on_sale' ) {
				$data_field = 'products';
				$parameters['type'] = 'sale';
			}

			if ( $data_field === 'products_recent' ) {
				$data_field = 'products';
				$parameters['type'] = 'recent';
			}

			if ( $data_field === 'products_featured' ) {
				$data_field = 'products';
				$parameters['type'] = 'featured';
			}
		}
	}

}

