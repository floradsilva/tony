<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Registry
 * @since 3.2.4
 */
abstract class Registry {

	/** @var array - must be declared in child class */
	static $includes;

	/** @var array - must be declared in child class */
	static $loaded = [];


	/**
	 * Implement this method in sub classes
	 * @return array
	 */
	static function load_includes() {
		return [];
	}


	/**
	 * Optional method to implement
	 * @param string $name
	 * @param mixed $object
	 */
	static function after_loaded( $name, $object ) {}


	/**
	 * @return array
	 */
	static function get_includes() {
		if ( ! isset( static::$includes ) ) {
			static::$includes = static::load_includes();
		}
		return static::$includes;
	}


	/**
	 * @return mixed
	 */
	static function get_all() {
		foreach ( static::get_includes() as $name => $path ) {
			static::load( $name );
		}
		return static::$loaded;
	}


	/**
	 * @param $name
	 * @return bool|object
	 */
	static function get( $name ) {
		if ( static::load( $name ) ) {
			return static::$loaded[ $name ];
		}
		return false;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	static function is_loaded( $name ) {
		return isset( static::$loaded[ $name ] );
	}


	/**
	 * Load an object by name.
	 *
	 * Returns true if the object has been loaded.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	static function load( $name ) {
		if ( self::is_loaded( $name ) ) {
			return true;
		}

		$includes = static::get_includes();
		$object   = false;

		if ( empty( $includes[ $name ] ) ) {
			return false;
		}

		$include = $includes[ $name ];

		// Check if include is a file path or a class name
		// NOTE: the file include method should NOT be used! It is kept for compatibility.
		if ( strstr( $include, '.php' ) ) {
			if ( file_exists( $include ) ) {
				$object = include_once $include;
			}
		} else {
			// If include is not a file path, assume it's a class name
			if ( class_exists( $include ) ) {
				$object = new $include();
			}
		}

		if ( ! is_object( $object ) ) {
			return false;
		}

		static::after_loaded( $name, $object );
		static::$loaded[ $name ] = $object;

		return true;
	}


	/**
	 * Clear all registry cached data.
	 *
	 * @since 4.4.0
	 */
	static function reset() {
		static::$includes = null;
		static::$loaded = [];
	}

}
