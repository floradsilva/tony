<?php
/**
 * Update WCPBC to 1.7.4
 *
 * @author 		OscarGare
 * @category 	Admin
 * @version     1.7.4
 */

if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Admin_Notices', false ) ) {

	include_once( WCPBC()->plugin_path() . 'includes/admin/class-wcpbc-admin-notices.php' );
}

WCPBC_Admin_Notices::add_notice( 'maxmind_geoip_database' );

