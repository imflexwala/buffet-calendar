<?php
/**
 * Plugin Name: Buffet Calendar and Meal Schedule - Opening Hours with Pricing
 * Plugin URI: https://www.linkedin.com/in/imflexwala/
 * Description: Publish daily meal-service hours, buffet times, and prices on a color-coded monthly calendar. Built for hotels, restaurants, cafés, and resorts with variable daily schedules.
 * Version: 1.0.0
 * Author: Mustafa Flexwala
 * Author URI: https://www.linkedin.com/in/imflexwala/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: buffet-calendar
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

! defined( 'BUFFET_CALENDAR_URI' ) && define( 'BUFFET_CALENDAR_URI', plugin_dir_url( __FILE__ ) );

require_once 'vendor/autoload.php';

if ( ! class_exists( 'Buffet_Calendar_Frontend' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-buffet-calendar-frontend.php';
}
if ( ! class_exists( 'Buffet_Calendar_Backend' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-buffet-calendar-backend.php';
}

function buffet_calendar_load_textdomain() {
	load_plugin_textdomain( 'buffet-calendar', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'buffet_calendar_load_textdomain' );

$buffetCalendarFrontend = Buffet_Calendar_Frontend::instance();
$buffetCalendarBackend  = Buffet_Calendar_Backend::instance();
