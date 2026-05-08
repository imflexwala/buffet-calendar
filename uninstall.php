<?php
/**
 * Fired when the plugin is uninstalled. Removes plugin options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'buffet_calendar_data' );
delete_option( 'buffet_calendar_settings_data' );
delete_option( 'buffet_calendar_migrated' );
// Legacy options (pre-1.0 internal naming).
delete_option( 'hcft_calendar_data' );
delete_option( 'hcft_calendar_settings_data' );
