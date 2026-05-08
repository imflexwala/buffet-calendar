<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use benhall14\phpCalendar\Calendar as Calendar;
use Carbon\Carbon;

class Buffet_Calendar_Frontend {

	/**
	 * @var \Buffet_Calendar_Frontend
	 */
	private static $instance;

	public function __construct() {
		add_shortcode( 'buffet_calendar_frontend', array( $this, 'display_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function display_shortcode() {

		wp_enqueue_style( 'buffet_calendar_frontend-bootstrap-grid' );
		wp_enqueue_style( 'buffet_calendar_frontend-calendar' );
		wp_enqueue_script( 'buffet_calendar_frontend-calendar' );

		ob_start();

		$calendar = new Calendar;
		$calendar->stylesheet();
		$calendar->setLocale( get_locale() );
		$calendar->useMondayStartingDate();
		$months = $this->getMonthsArray();

		$calendar_data = json_decode( get_option( 'buffet_calendar_data' ), true );
		if ( ! is_array( $calendar_data ) ) {
			$calendar_data = [];
		}

		$settings_data = json_decode( get_option( 'buffet_calendar_settings_data' ), true );
		if ( ! is_array( $settings_data ) ) {
			$settings_data = [];
		}

		$calendar->addEvents( Buffet_Calendar_Backend::getEvents( $months, $calendar_data, false ) );

		?>
        <div class="calendar-frontend">
            <div class="mt-3">
                <div class="time-grid">
                    <div class="time-grid-item">
                        <span class="buffet-calendar-color color-yellow"></span>
			            <?php echo isset( $settings_data['1'] ) ? nl2br( esc_html( $settings_data['1'] ) ) : ''; ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="buffet-calendar-color color-green"></span>
			            <?php echo isset( $settings_data['2'] ) ? nl2br( esc_html( $settings_data['2'] ) ) : ''; ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="buffet-calendar-color color-orange"></span>
			            <?php echo isset( $settings_data['3'] ) ? nl2br( esc_html( $settings_data['3'] ) ) : ''; ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="buffet-calendar-color color-blue"></span>
			            <?php echo isset( $settings_data['4'] ) ? nl2br( esc_html( $settings_data['4'] ) ) : ''; ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="buffet-calendar-color color-beige"></span>
			            <?php echo isset( $settings_data['5'] ) ? nl2br( esc_html( $settings_data['5'] ) ) : ''; ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="buffet-calendar-color color-red"></span>
			            <?php echo isset( $settings_data['6'] ) ? nl2br( esc_html( $settings_data['6'] ) ) : ''; ?>
                    </div>
                </div>
		        <?php foreach ( $months as $key => $month ) : ?>
			        <?php if ( $key == 3 ) : ?>
                        <div class="buffet-calendar-text-center">
                            <div class="buffet-calendar-btn show-row-3"><?php esc_html_e( 'Show more', 'buffet-calendar' ); ?></div>
                        </div>
			        <?php endif; ?>
			        <?php if ( $key == 0 || $key == 3 ) : ?>
                        <div class="row row-<?php echo esc_attr( $key ); ?> <?php echo $key == 3 ? 'is-hidden' : ''; ?>">
			        <?php endif; ?>
                    <div class="col-lg-4 col-md-6 col-12 c-<?php echo esc_attr( $key ); ?>">
                        <div class="mb-3 cldr">
				            <?php $calendar->render( [ 'startDate' => $month, 'color' => 'light-grey' ] ); ?>
                        </div>
                    </div>
			        <?php if ( $key == 2 || $key == sizeof( $months ) - 1 ) : ?>
                        </div>
			        <?php endif; ?>
		        <?php endforeach; ?>
            </div>
        </div>
		<?php

		return ob_get_clean();
	}

	public function getMonthsArray( $count = 12 ) {
		$date   = Carbon::now();
		$months = [ $date->format( 'Y-m-01' ) ];

		for ( $i = 1; $i < $count; $i++ ) {
			$months[] = $date->addMonth()->format( 'Y-m-01' );
		}

		return $months;
	}

	public function register_scripts() {
		wp_register_style( 'buffet_calendar_frontend-bootstrap-grid', BUFFET_CALENDAR_URI . 'assets/css/bootstrap-grid.min.css', [], '1.1' );
		wp_register_style( 'buffet_calendar_frontend-calendar', BUFFET_CALENDAR_URI . 'assets/css/calendar.css', [], '1.18' );
		wp_register_script( 'buffet_calendar_frontend-calendar', BUFFET_CALENDAR_URI . 'assets/js/calendar-timings.js', [], '1.1', true );
	}

}
