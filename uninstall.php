<?php
/**
 * Fired when the plugin is uninstalled. Removes plugin options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'buffet_calendar_data' );
delete_option( 'buffet_calendar_settings_data' );
