<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use benhall14\phpCalendar\Calendar as Calendar;
use Carbon\Carbon;

class Buffet_Calendar_Backend {

	/**
	 * @var Buffet_Calendar_Backend
	 */
	private static $instance;

	public static $event_classes = [
		"1" => 'event-yellow',
		"2" => 'event-green',
		"3" => 'event-orange',
		"4" => 'event-blue',
		"5" => 'event-beige',
		"6" => 'event-red',
	];

	const NONCE_ACTION = 'buffet_calendar_admin_submit';
	const NONCE_NAME   = 'buffet_calendar_admin_nonce';

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_buffet_calendar_save_data', array( $this, 'save_calendar_data' ) );
		add_action( 'admin_post_buffet_calendar_save_settings', array( $this, 'save_settings_data' ) );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function admin_menu() {
		add_menu_page(
			__( 'Calendar Timings', 'buffet-calendar' ),
			__( 'Calendar', 'buffet-calendar' ),
			'manage_options',
			'buffet-calendar-page',
			array( $this, 'calendar_page_callback' ),
			'dashicons-images-alt2',
			4
		);

		add_submenu_page(
			'buffet-calendar-page',
			__( 'Calendar Settings', 'buffet-calendar' ),
			__( 'Calendar Settings', 'buffet-calendar' ),
			'manage_options',
			'buffet-calendar-settings-page',
			array( $this, 'settings_page_callback' )
		);
	}

	public function calendar_page_callback() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style( 'buffet_calendar_backend-bootstrap-grid' );
		wp_enqueue_style( 'buffet_calendar_backend-calendar' );
		wp_enqueue_script( 'buffet_calendar_backend-calendar' );

		$calendar = new Calendar;
		$calendar->stylesheet();
		$calendar->setLocale( get_locale() );
		$calendar->useMondayStartingDate();
		$months = $this->getMonthsArray();

		$calendar_data = json_decode( get_option( 'buffet_calendar_data' ), true );
		if ( ! is_array( $calendar_data ) ) {
			$calendar_data = [];
		}

		$calendar->addEvents( self::getEvents( $months, $calendar_data ) );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form id="buffet_calendar_form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
				<input class="button button-primary" type="submit" name="submit" value="Save">
				<div class="mt-5 row">
					<?php foreach ( $months as $month ) : ?>
						<div class="col-md-12">
							<div class="mb-5 cldr">
								<?php $calendar->render( [ 'startDate' => $month, 'color' => 'light-grey' ] ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<input class="button button-primary" type="submit" name="submit" value="Save">
				<input type="hidden" name="action" value="buffet_calendar_save_data">
			</form>
		</div>
		<?php
	}

	public static function getEvents( $months, $calendar_data, $select = true ): array {

		$events        = [];
		$settings_data = json_decode( get_option( 'buffet_calendar_settings_data' ), true );
		if ( ! is_array( $settings_data ) ) {
			$settings_data = [];
		}

		foreach ( $months as $month ) {
			$date = Carbon::createFromFormat( 'Y-m-d', $month );
			if ( ! isset( $calendar_data[ $month ] ) ) {
				$calendar_data[ $month ] = [];
			}

			$dd = $date->format( 'Y-m-d' );
			if ( ! isset( $calendar_data[ $month ][ $dd ] ) ) {
				$calendar_data[ $month ][ $dd ] = "6";
			}
			$event = array(
				'start'             => $dd,
				'end'               => $dd,
				'summary'           => '',
				'mask'              => false,
				'classes'           => [ self::$event_classes[ $calendar_data[ $month ][ $dd ] ] ?? '', 'event-' . $month . '-' . $dd ],
				'event_box_classes' => [ 'event-box-1' ],
				'title'             => '',
			);

			if ( $select ) {
				$event['summary'] = self::getSelectInput( $month, $dd, $calendar_data[ $month ][ $dd ], $settings_data );
			}

			$events[] = $event;

			for ( $i = 1; $i < $date->daysInMonth; $i++ ) {

				$dd = $date->addDay()->format( 'Y-m-d' );
				if ( ! isset( $calendar_data[ $month ][ $dd ] ) ) {
					$calendar_data[ $month ][ $dd ] = "6";
				}

				$event = array(
					'start'             => $dd,
					'end'               => $dd,
					'summary'           => '',
					'mask'              => false,
					'classes'           => [ self::$event_classes[ $calendar_data[ $month ][ $dd ] ] ?? '', 'event-' . $month . '-' . $dd ],
					'event_box_classes' => [ 'event-box-1' ],
					'title'             => '',
				);

				if ( $select ) {
					$event['summary'] = self::getSelectInput( $month, $dd, $calendar_data[ $month ][ $dd ], $settings_data );
				}
				$events[] = $event;
			}
		}

		return $events;
	}

	public static function getSelectInput( $month, $day, $value, $settings_data ) {
		ob_start();
		?>
		<label>
			<select data-id="<?php echo esc_attr( 'event-' . $month . '-' . $day ); ?>"
			        name="buffet_calendar_data[<?php echo esc_attr( $month ); ?>][<?php echo esc_attr( $day ); ?>]"
			        class="buffet-calendar-select">
				<option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Select an option', 'buffet-calendar' ); ?></option>
				<?php foreach ( $settings_data as $key => $text ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
						<?php echo esc_html( $text ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php

		return ob_get_clean();
	}

	public function getMonthsArray( $count = 14 ) {
		$date   = Carbon::now()->subMonth();
		$months = [ $date->format( 'Y-m-01' ) ];

		for ( $i = 1; $i < $count; $i++ ) {
			$months[] = $date->addMonth()->format( 'Y-m-01' );
		}

		return $months;
	}

	/**
	 * Recursively sanitize the calendar-data array. Expected shape:
	 * [ 'YYYY-MM-DD' => [ 'YYYY-MM-DD' => '1'..'6' or '' ] ]
	 */
	private static function sanitize_calendar_data( $data ) {
		if ( ! is_array( $data ) ) {
			return [];
		}
		$allowed_values = [ '', '1', '2', '3', '4', '5', '6' ];
		$sanitized      = [];
		foreach ( $data as $month_key => $days ) {
			if ( ! is_string( $month_key ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $month_key ) || ! is_array( $days ) ) {
				continue;
			}
			$sanitized[ $month_key ] = [];
			foreach ( $days as $day_key => $value ) {
				if ( ! is_string( $day_key ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $day_key ) ) {
					continue;
				}
				$value = is_scalar( $value ) ? (string) $value : '';
				if ( in_array( $value, $allowed_values, true ) ) {
					$sanitized[ $month_key ][ $day_key ] = $value;
				}
			}
		}
		return $sanitized;
	}

	/**
	 * Sanitize the settings labels array. Expected keys: '1' through '6'.
	 */
	private static function sanitize_settings_data( $data ) {
		if ( ! is_array( $data ) ) {
			return [];
		}
		$sanitized = [];
		foreach ( [ '1', '2', '3', '4', '5', '6' ] as $key ) {
			if ( isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) ) {
				$sanitized[ $key ] = sanitize_textarea_field( (string) $data[ $key ] );
			}
		}
		return $sanitized;
	}

	public function save_calendar_data() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'buffet-calendar' ) );
		}

		$nonce = isset( $_POST[ self::NONCE_NAME ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Security check failed.', 'buffet-calendar' ) );
		}

		if ( isset( $_POST['submit'] ) && isset( $_POST['buffet_calendar_data'] ) ) {
			$raw       = wp_unslash( $_POST['buffet_calendar_data'] );
			$sanitized = self::sanitize_calendar_data( $raw );
			update_option( 'buffet_calendar_data', wp_json_encode( $sanitized ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=buffet-calendar-page' ) );
		exit;
	}

	public function save_settings_data() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'buffet-calendar' ) );
		}

		$nonce = isset( $_POST[ self::NONCE_NAME ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Security check failed.', 'buffet-calendar' ) );
		}

		if ( isset( $_POST['submit'] ) && isset( $_POST['buffet_calendar_setting'] ) ) {
			$raw       = wp_unslash( $_POST['buffet_calendar_setting'] );
			$sanitized = self::sanitize_settings_data( $raw );
			update_option( 'buffet_calendar_settings_data', wp_json_encode( $sanitized ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=buffet-calendar-settings-page' ) );
		exit;
	}

	public function settings_page_callback() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings_data = json_decode( get_option( 'buffet_calendar_settings_data' ), true );

		if ( empty( $settings_data ) || ! is_array( $settings_data ) ) {
			$settings_data = [
				"1" => __( 'Breakfast 7:30 am - 10 am, Dinner 4 pm - 9:30 pm', 'buffet-calendar' ),
				"2" => __( 'Breakfast 7:30 am - 10 am, Lunch 12 pm - 2 pm, Dinner 4 pm - 11 pm', 'buffet-calendar' ),
				"3" => __( 'Lunch 12 pm - 4 pm', 'buffet-calendar' ),
				"4" => __( 'Breakfast 7:30 am - 10 am', 'buffet-calendar' ),
				"5" => __( 'Coffee & Cake 4 pm - 9 pm', 'buffet-calendar' ),
				"6" => __( 'Closed', 'buffet-calendar' ),
			];
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
				<table class="form-table">
					<tbody>
					<?php foreach ( $settings_data as $key => $value ) : ?>
						<tr valign="top">
							<th scope="row">
								<label for="label_<?php echo esc_attr( $key ); ?>">
									<?php
									/* translators: %s is a numeric label index, e.g. "1". */
									echo esc_html( sprintf( __( 'Label %s', 'buffet-calendar' ), $key ) );
									?>
								</label>
							</th>
							<td class="forminp forminp-text">
								<textarea name="buffet_calendar_setting[<?php echo esc_attr( $key ); ?>]"
								          id="label_<?php echo esc_attr( $key ); ?>"
								          class="buffet-calendar-settings-textarea"><?php echo esc_textarea( $value ); ?></textarea>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<input class="button button-primary" type="submit" name="submit" value="Save">
				<input type="hidden" name="action" value="buffet_calendar_save_settings">
			</form>
		</div>
		<?php
	}

	public function register_scripts() {
		wp_register_style( 'buffet_calendar_backend-bootstrap-grid', BUFFET_CALENDAR_URI . 'assets/css/bootstrap-grid.min.css', [], '1.1' );
		wp_register_style( 'buffet_calendar_backend-calendar', BUFFET_CALENDAR_URI . 'assets/css/calendar.css', [], '1.15' );
		wp_register_script( 'buffet_calendar_backend-calendar', BUFFET_CALENDAR_URI . 'assets/js/calendar-timings-admin.js', [ 'jquery' ], '1.1', true );
	}

}
