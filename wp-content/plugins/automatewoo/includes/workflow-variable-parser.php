<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Class to parse a variable string into separate usable parts
 *
 * @class Workflow_Variable_Parser
 * @since 3.6
 */
class Workflow_Variable_Parser {

	/** @var string */
	public $name;

	/** @var string */
	public $type;

	/** @var string */
	public $field;

	/** @var array */
	public $parameters;

	/** @var string */
	public $parameter_string;


	/**
	 * Returns true on successful parsing
	 *
	 * @param $variable_string
	 * @return bool
	 */
	function parse( $variable_string ) {

		$matches = [];
		$parameters = [];

		// extract the variable name (first part) of the variable string, e.g. 'customer.email'
		preg_match('/([a-z._0-9])+/', $variable_string, $matches, PREG_OFFSET_CAPTURE );

		if ( ! $matches ) {
			return false;
		}

		$name = $matches[0][0];

		// the name must contain a period
		if ( ! strstr( $name, '.' ) ) {
			return false;
		}

		list( $type, $field ) = explode( '.', $name, 2 );

		$parameter_string = trim( substr( $variable_string, $matches[1][1] + 1 ) );
		$parameter_string = trim( aw_str_replace_first_match( $parameter_string, '|' ) ); // remove pipe

		$parameters_split = preg_split('/(,)(?=(?:[^\']|\'[^\']*\')*$)/', $parameter_string );

		foreach ( $parameters_split as $parameter ) {
			if ( ! strstr( $parameter, ':' ) ) {
				continue;
			}

			list( $key, $value ) = explode( ':', $parameter, 2 );

			$key = Clean::string( $key );
			$value = Clean::string( $this->unquote( $value ) );

			$parameters[ $key ] = $value;
		}

		$this->name = $name;
		$this->type = $type;
		$this->field = $field;
		$this->parameters = $parameters;
		$this->parameter_string = $parameter_string;

		return true;

	}


	/**
	 * Remove single quotes from start and end of a string
	 * @param $string
	 * @return string
	 */
	private function unquote( $string ) {
		return trim( trim( $string ), "'" );
	}

}
