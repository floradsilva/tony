<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 *
 * @since 4.0
 */
class Autoloader {

	/**
	 * Register autoloader
	 */
	public static function init() {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	/**
	 * Autoload a class by name.
	 *
	 * @param string $class
	 */
	public static function autoload( $class ) {
		$path = self::get_autoload_path( $class );

		if ( $path && file_exists( $path ) ) {
			require_once $path;
		}
	}

	/**
	 * Get the file path for a class.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	public static function get_autoload_path( $class ) {
		if ( substr( $class, 0, 3 ) !== 'AW_' && substr( $class, 0, 12 ) !== 'AutomateWoo\\' ) {
			return false;
		}

		if ( strpos( $class, 'AutomateWoo\Referrals\\' ) === 0 ) {
			return false;
		}

		$file = str_replace( [ 'AW_', 'AutomateWoo\\' ], '/', $class );
		$file = str_replace( '_', '-', $file );
		$file = strtolower( $file );
		$file = str_replace( '\\', '/', $file );

		$abstracts = [
			'/action',
			'/trigger',
			'/model',
			'/query-custom-table',
			'/integration',
			'/variable',
			'/options-api',
			'/tool',
			'/data-type',
			'/database-table',
		];

		if ( in_array( $file, $abstracts, true ) ) {
			return AW()->path() . '/includes/abstracts' . $file . '.php';
		}

		if ( $file === '/admin' ) {
			return AW()->path() . '/admin/admin.php';
		} elseif ( strstr( $file, '/admin-' ) || strstr( $file, '/admin/' ) ) {
			$file = str_replace( '/admin-', '/admin/', $file );
			$file = str_replace( '/controller-', '/controllers/', $file );

			return AW()->path() . $file . '.php';
		} else {
			$file = str_replace( '/trigger-', '/triggers/', $file );
			$file = str_replace( '/action-', '/actions/', $file );
			$file = str_replace( '/variable-', '/variables/', $file );
			$file = str_replace( '/integration-', '/integrations/', $file );
			$file = self::str_replace_start( $file, '/rule-', '/rules/' );

			// Handle rules
			if ( strpos( $file, '/rules/' ) === 0 ) {
				if ( strstr( $file, 'abstract' ) || $file === '/rules/rule' ) {
					$file = str_replace( '/rules/', '/rules/abstracts/', $file );
				}
			}

			if ( strpos( $file, '/actions/' ) === 0 && strstr( $file, 'abstract' ) ) {
				$file = str_replace( '/actions/', '/actions/abstracts/', $file );
			}

			if ( strpos( $file, '/triggers/' ) === 0 && strstr( $file, 'abstract' ) ) {
				$file = str_replace( '/triggers/', '/triggers/abstracts/', $file );
			}

			if ( strpos( $file, '/variables/' ) === 0 && strstr( $file, 'abstract' ) ) {
				$file = str_replace( '/variables/', '/variables/abstracts/', $file );
			}

			if ( strpos( $file, '/fields/' ) === 0 && strstr( $file, 'abstract' ) ) {
				$file = str_replace( '/fields/', '/fields/abstracts/', $file );
			}

			return AW()->path() . '/includes' . $file . '.php';
		}
	}


	/**
	 * Do a string replace based only on the start of a string.
	 *
	 * @param string $subject
	 * @param string $find
	 * @param string $replace
	 *
	 * @return string
	 */
	protected static function str_replace_start( $subject, $find, $replace ) {
		if ( strpos( $subject, $find ) === 0 ) {
			return substr_replace( $subject, $replace, 0, strlen( $find ) );
		}

		return $subject;
	}

}

Autoloader::init();
