<?php
/*
 * Plugin Name: Meal Schedule & Buffet Calendar — Opening Hours with Pricing
   Author URI: https://www.linkedin.com/in/imflexwala/
   Description: Meal Schedule & Buffet Calendar is the simplest way to publish daily opening hours, meal service windows, and pricing on a WordPress site. Built originally for a German hotel that needed to communicate breakfast, lunch, dinner buffet times, and "coffee & cake" hours at a glance, the plugin works equally well for any restaurant, café, resort, spa, or seasonal venue with varying daily schedules.
   Author: Mustafa Flexwala
   Version: 1.0.0
   Text Domain: buffet-calendar
   License: GPL v2 or later
   License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

! defined( 'HCFT_URI' ) && define( 'HCFT_URI', plugin_dir_url( __FILE__ ) );

require_once 'vendor/autoload.php';

if ( ! class_exists( 'Hapnics_Calendar_Frontend' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hapnics-calendar-frontend.php';
}
if ( ! class_exists( 'Hapnics_Calendar_Backend' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-hapnics-calendar-backend.php';
}

$hapnicsCalendarFrontend = Hapnics_Calendar_Frontend::instance();
$hapnicsCalendarBackend = Hapnics_Calendar_Backend::instance();
