<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Installer
 */
class Installer {

	/** @var array */
	static $db_updates = [
		'2.6.0',
		'2.6.1',
		'2.7.0',
		'2.9.7',
		'3.0.0',
		'3.5.0',
		'3.6.0',
		'4.0.0',
	];

	/** @var int  */
	static $db_update_items_processed = 0;


	static function init() {
		add_action( 'admin_init', [ __CLASS__, 'admin_init' ], 5 );
		add_filter( 'plugin_action_links_' . AW()->plugin_basename, [ __CLASS__, 'plugin_action_links' ] );
	}


	/**
	 * Admin init
	 */
	static function admin_init() {

		if ( defined( 'IFRAME_REQUEST' ) || is_ajax() ) {
			return;
		}

		self::check_if_plugin_files_updated();

		if ( Options::database_version() != AW()->version ) {
			self::install();

			// check for required database update
			if ( self::is_database_upgrade_required() ) {
				add_action( 'admin_notices', [ __CLASS__, 'data_upgrade_prompt' ] );
			}
			else {
				self::update_database_version( AW()->version );
				self::do_plugin_updated_actions();
			}
		}

		foreach( Addons::get_all() as $addon ) {
			$addon->check_version();
		}

		if ( did_action( 'automatewoo_updated' ) || did_action( 'automatewoo_addon_updated' ) ) {
			// do API check-in after an update
			Licenses::schedule_reset_status_check_timer();
		}
	}


	/**
	 * Checks and logs if plugin files have been updated.
	 *
	 * @since 4.3.0
	 */
	static function check_if_plugin_files_updated() {
		$file_version = Options::file_version();

		if ( $file_version !== AW()->version ) {
			if ( $file_version ) {
				Logger::info( 'updates', sprintf( "AutomateWoo - Plugin updated from %s to %s", $file_version, AW()->version ) );
			}

			update_option( 'automatewoo_file_version', AW()->version, true );
		}
	}



	/**
	 * Install
	 */
	static function install() {
		Database_Tables::install_tables();
		self::create_pages();

		do_action( 'automatewoo_installed' );
	}


	/**
	 * @return bool
	 */
	static function is_database_upgrade_required() {
		if ( Options::database_version() == AW()->version ) {
			return false;
		}

		return Options::database_version() && version_compare( Options::database_version(), end( self::$db_updates ), '<' );
	}


	/**
	 * @return array
	 */
	static function get_required_database_updates() {

		$required_updates = [];

		foreach ( self::$db_updates as $version ) {
			if ( version_compare( Options::database_version(), $version, '<' ) ) {
				$required_updates[] = $version;
			}
		}

		return $required_updates;
	}


	/**
	 * Handle updates, may be called multiple times to batch complete
	 * Returns false if updates are still required
	 * @return bool
	 */
	static function run_database_updates() {

		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$required_updates = self::get_required_database_updates();
		self::$db_update_items_processed = 0; // reset counter

		// update one version at a time
		$update = current( $required_updates );
		$complete = self::run_database_update( $update );

		if ( count( $required_updates ) > 1 ) {
			$complete = false; // not complete if there is more than one update
		}

		if ( $complete ) {
			self::do_plugin_updated_actions();
			Licenses::schedule_reset_status_check_timer();
		}

		return $complete;
	}


	/**
	 * Return true if update is complete, return false if another pass is required
	 * @param $version
	 * @return bool
	 */
	static function run_database_update( $version ) {

		$update_file = AW()->path( "/includes/updates/$version.php" );
		$update = include $update_file; // recent updates will return a class

		if ( is_a( $update, 'AutomateWoo\Database_Update' ) ) {
			/** @var $update Database_Update */
			$update->dispatch_process();
			self::$db_update_items_processed += $update->get_items_processed_count();

			$complete = $update->is_complete();
		}
		else {
			// don't check completion on legacy updates
			$complete = true;
		}

		if ( $complete ) {
			self::update_database_version( $version );
		}

		return $complete;
	}


	/**
	 * Returns the item to process count for all currently required updates.
	 *
	 * @return int
	 */
	static function get_database_update_items_to_process_count() {
		$required_updates = self::get_required_database_updates();
		$count = 0;

		foreach ( $required_updates as $version ) {
			if ( version_compare( $version, '2.7.0', '<=' ) ) {
				continue; // old updates don't extend Database_Update class
			}

			$update_file = AW()->path( "/includes/updates/$version.php" );
			$update = include $update_file; /** @var $update Database_Update */
			$count += $update->get_items_to_process_count();
		}

		return $count;
	}


	/**
	 * Update version to current
	 * @param $version string
	 */
	private static function update_database_version( $version ) {
		update_option( 'automatewoo_version', $version, true );
	}


	/**
	 * Renders prompt notice for user to update
	 */
	static function data_upgrade_prompt() {
		Admin::get_view( 'data-upgrade-prompt', [
			'plugin_name' => __( 'AutomateWoo', 'automatewoo' ),
			'plugin_slug' => AW()->plugin_slug
		]);
	}


	/**
	 * @return bool
	 */
	static function is_data_update_screen() {
		$screen = get_current_screen();
		return $screen->id === 'automatewoo_page_automatewoo-data-upgrade';
	}


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	static function plugin_action_links( $links ) {
		$action_links = [
			'settings' => '<a href="' . esc_url( Admin::page_url( 'settings' ) ) . '" title="' . esc_attr( __( 'View AutomateWoo Settings', 'automatewoo' ) ) . '">' . esc_html__( 'Settings', 'automatewoo' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}


	static function do_plugin_updated_actions() {
		do_action( 'automatewoo_updated' );
		Events::schedule_async_event( 'automatewoo_updated_async' );
	}


	/**
	 * Creates required pages, run on every update, so it can be repeated without creating duplicates
	 */
	static function create_pages() {

		$created_pages = get_option( '_automatewoo_created_pages', [] );

		$pages = apply_filters( 'automatewoo_create_pages', [
			'communication_preferences' => [
				'name' => _x( 'communication-preferences', 'Page slug', 'automatewoo' ),
				'title' => _x( 'Communication preferences', 'Page title', 'automatewoo' ),
				'content' => '[automatewoo_communication_preferences]',
				'option' => 'automatewoo_communication_preferences_page_id'
			],
		]);

		foreach ( $pages as $key => $page ) {
			if ( in_array( $key, $created_pages ) ) {
				continue;
			}

			Admin::create_page( esc_sql( $page['name'] ), $page['title'], $page['content'], $page['option'] );
			$created_pages[] = $key;
		}

		update_option( '_automatewoo_created_pages', $created_pages, false );
	}


}
