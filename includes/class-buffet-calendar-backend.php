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

	const NONCE_ACTION = 'buffet_calendar_admin_submit';
	const NONCE_NAME   = 'buffet_calendar_admin_nonce';

	/**
	 * Default cell-color alpha applied to user-picked hex colors.
	 * Kept at ~0.72 to match the original muted look of the calendar cells.
	 */
	const COLOR_ALPHA = 0.72;

	const FALLBACK_COLOR = '#cccccc';

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

	/**
	 * Default settings for a fresh install. The 6 default colors match the original named-color palette.
	 */
	private static function default_settings() {
		return [
			'1' => [ 'label' => __( 'Breakfast 7:30 am - 10 am, Dinner 4 pm - 9:30 pm', 'buffet-calendar' ),                  'color' => '#e6cf04', 'enabled' => true ],
			'2' => [ 'label' => __( 'Breakfast 7:30 am - 10 am, Lunch 12 pm - 2 pm, Dinner 4 pm - 11 pm', 'buffet-calendar' ), 'color' => '#22a71c', 'enabled' => true ],
			'3' => [ 'label' => __( 'Lunch 12 pm - 4 pm', 'buffet-calendar' ),                                                'color' => '#ffa500', 'enabled' => true ],
			'4' => [ 'label' => __( 'Breakfast 7:30 am - 10 am', 'buffet-calendar' ),                                         'color' => '#0a8cee', 'enabled' => true ],
			'5' => [ 'label' => __( 'Coffee & Cake 4 pm - 9 pm', 'buffet-calendar' ),                                         'color' => '#f5f5dc', 'enabled' => true ],
			'6' => [ 'label' => __( 'Closed', 'buffet-calendar' ),                                                            'color' => '#f11111', 'enabled' => true ],
		];
	}

	/**
	 * Read settings, normalizing the legacy flat shape `[id => "label string"]` into the
	 * new shape `[id => ['label' => ..., 'color' => ..., 'enabled' => ...]]`.
	 */
	public static function get_settings() {
		$raw = json_decode( get_option( 'buffet_calendar_settings_data' ), true );
		if ( ! is_array( $raw ) || empty( $raw ) ) {
			return self::default_settings();
		}

		$first = reset( $raw );
		if ( ! is_array( $first ) ) {
			$defaults = self::default_settings();
			$migrated = [];
			foreach ( $raw as $key => $label ) {
				$key                = (string) $key;
				$migrated[ $key ] = [
					'label'   => is_string( $label ) ? $label : '',
					'color'   => isset( $defaults[ $key ]['color'] ) ? $defaults[ $key ]['color'] : self::FALLBACK_COLOR,
					'enabled' => true,
				];
			}
			return $migrated;
		}

		$normalized = [];
		foreach ( $raw as $key => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$key                = (string) $key;
			$color              = isset( $row['color'] ) && is_string( $row['color'] ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $row['color'] )
				? strtolower( $row['color'] )
				: self::FALLBACK_COLOR;
			$normalized[ $key ] = [
				'label'   => isset( $row['label'] ) ? (string) $row['label'] : '',
				'color'   => $color,
				'enabled' => ! empty( $row['enabled'] ),
			];
		}
		return $normalized;
	}

	/**
	 * Convert "#RRGGBB" to "rgba(R, G, B, A)".
	 */
	private static function hex_to_rgba( $hex, $alpha = self::COLOR_ALPHA ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( strlen( $hex ) !== 6 ) {
			return self::FALLBACK_COLOR;
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		return sprintf( 'rgba(%d, %d, %d, %s)', $r, $g, $b, $alpha );
	}

	/**
	 * Build a `<style>` block that maps each label id to its calendar-cell and legend-swatch color.
	 */
	public static function render_dynamic_styles( $settings ) {
		if ( empty( $settings ) ) {
			return '';
		}
		$rules = '';
		foreach ( $settings as $id => $row ) {
			$id = (int) $id;
			if ( $id <= 0 ) {
				continue;
			}
			$color = isset( $row['color'] ) ? $row['color'] : self::FALLBACK_COLOR;
			$rgba  = self::hex_to_rgba( $color );
			$rules .= sprintf( '.row .calendar tbody tr td.event-%1$d{background:%2$s;}.buffet-calendar-color.color-%1$d{background:%2$s;}', $id, $rgba );
		}
		if ( '' === $rules ) {
			return '';
		}
		return '<style id="buffet-calendar-dynamic-styles">' . $rules . '</style>';
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
		// Backend admin UI is always English regardless of site locale.
		$calendar->setLocale( 'en_US' );
		$calendar->useMondayStartingDate();
		$months = $this->getMonthsArray();

		$calendar_data = json_decode( get_option( 'buffet_calendar_data' ), true );
		if ( ! is_array( $calendar_data ) ) {
			$calendar_data = [];
		}

		$settings = self::get_settings();
		$calendar->addEvents( self::getEvents( $months, $calendar_data, true, $settings ) );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			// Dynamic per-label color rules. The CSS we emit is built from a sanitized hex value.
			echo self::render_dynamic_styles( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			<form id="buffet_calendar_form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
				<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'buffet-calendar' ); ?>">
				<div class="mt-5 row">
					<?php foreach ( $months as $month ) : ?>
						<div class="col-md-12">
							<div class="mb-5 cldr">
								<?php $calendar->render( [ 'startDate' => $month, 'color' => 'light-grey' ] ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'buffet-calendar' ); ?>">
				<input type="hidden" name="action" value="buffet_calendar_save_data">
			</form>
		</div>
		<?php
	}

	public static function getEvents( $months, $calendar_data, $select = true, $settings = null ): array {

		$events   = [];
		$settings = is_array( $settings ) ? $settings : self::get_settings();

		// Pick a reasonable fallback id for unset days: prefer "6" (legacy "Closed"), else last id.
		$fallback_id = '';
		if ( isset( $settings['6'] ) ) {
			$fallback_id = '6';
		} elseif ( ! empty( $settings ) ) {
			$keys        = array_keys( $settings );
			$fallback_id = (string) end( $keys );
		}

		foreach ( $months as $month ) {
			$date = Carbon::createFromFormat( 'Y-m-d', $month );
			if ( ! isset( $calendar_data[ $month ] ) ) {
				$calendar_data[ $month ] = [];
			}

			$dd       = $date->format( 'Y-m-d' );
			$value    = isset( $calendar_data[ $month ][ $dd ] ) ? (string) $calendar_data[ $month ][ $dd ] : $fallback_id;
			$events[] = self::build_event( $month, $dd, $value, $settings, $select );

			for ( $i = 1; $i < $date->daysInMonth; $i++ ) {
				$dd       = $date->addDay()->format( 'Y-m-d' );
				$value    = isset( $calendar_data[ $month ][ $dd ] ) ? (string) $calendar_data[ $month ][ $dd ] : $fallback_id;
				$events[] = self::build_event( $month, $dd, $value, $settings, $select );
			}
		}

		return $events;
	}

	private static function build_event( $month, $dd, $value, $settings, $select ) {
		$event_class = ( '' !== $value && isset( $settings[ $value ] ) ) ? 'event-' . (int) $value : '';
		return [
			'start'             => $dd,
			'end'               => $dd,
			'summary'           => $select ? self::getSelectInput( $month, $dd, $value, $settings ) : '',
			'mask'              => false,
			'classes'           => [ $event_class, 'event-' . $month . '-' . $dd ],
			'event_box_classes' => [ 'event-box-1' ],
			'title'             => '',
		];
	}

	public static function getSelectInput( $month, $day, $value, $settings ) {
		ob_start();
		?>
		<label>
			<select data-id="<?php echo esc_attr( 'event-' . $month . '-' . $day ); ?>"
			        name="buffet_calendar_data[<?php echo esc_attr( $month ); ?>][<?php echo esc_attr( $day ); ?>]"
			        class="buffet-calendar-select">
				<option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Select an option', 'buffet-calendar' ); ?></option>
				<?php foreach ( $settings as $key => $row ) : ?>
					<?php if ( empty( $row['enabled'] ) && (string) $key !== (string) $value ) {
						continue;
					} ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( (string) $value, (string) $key ); ?>>
						<?php echo esc_html( $row['label'] ); ?>
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
	 * [ 'YYYY-MM-DD' => [ 'YYYY-MM-DD' => '<positive int>' or '' ] ]
	 * Numeric values are validated against current settings ids.
	 */
	private static function sanitize_calendar_data( $data ) {
		if ( ! is_array( $data ) ) {
			return [];
		}
		$settings  = self::get_settings();
		$valid_ids = array_map( 'strval', array_keys( $settings ) );
		$sanitized = [];
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
				if ( '' === $value || in_array( $value, $valid_ids, true ) ) {
					$sanitized[ $month_key ][ $day_key ] = $value;
				}
			}
		}
		return $sanitized;
	}

	/**
	 * Sanitize the new settings shape: [ id => [ label, color, enabled ] ].
	 * Variable number of rows allowed; ids are positive integers.
	 */
	private static function sanitize_settings_data( $data ) {
		if ( ! is_array( $data ) ) {
			return [];
		}
		$sanitized = [];
		foreach ( $data as $key => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$id = (int) $key;
			if ( $id <= 0 ) {
				continue;
			}
			$label = isset( $row['label'] ) && is_scalar( $row['label'] )
				? sanitize_textarea_field( (string) $row['label'] )
				: '';
			$color = isset( $row['color'] ) && is_string( $row['color'] ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $row['color'] )
				? strtolower( $row['color'] )
				: self::FALLBACK_COLOR;
			$enabled = ! empty( $row['enabled'] );
			$sanitized[ (string) $id ] = [
				'label'   => $label,
				'color'   => $color,
				'enabled' => $enabled,
			];
		}
		ksort( $sanitized, SORT_NUMERIC );
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
			// Array is sanitized inside sanitize_calendar_data() (each leaf validated against an allow-list).
			$raw       = wp_unslash( $_POST['buffet_calendar_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
			// Array is sanitized inside sanitize_settings_data() (label via sanitize_textarea_field, color via regex, enabled coerced to bool).
			$raw       = wp_unslash( $_POST['buffet_calendar_setting'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'buffet_calendar_backend-calendar' );
		wp_enqueue_script( 'buffet_calendar_backend-settings' );

		$settings = self::get_settings();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
				<table class="widefat buffet-calendar-settings-table">
					<thead>
						<tr>
							<th scope="col" class="buffet-calendar-col-label"><?php esc_html_e( 'Label', 'buffet-calendar' ); ?></th>
							<th scope="col" class="buffet-calendar-col-color"><?php esc_html_e( 'Color', 'buffet-calendar' ); ?></th>
							<th scope="col" class="buffet-calendar-col-enabled"><?php esc_html_e( 'Enabled', 'buffet-calendar' ); ?></th>
							<th scope="col" class="buffet-calendar-col-actions"></th>
						</tr>
					</thead>
					<tbody id="buffet-calendar-settings-rows">
					<?php foreach ( $settings as $id => $row ) : ?>
						<?php self::render_settings_row( $id, $row ); ?>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p>
					<button type="button" class="button" id="buffet-calendar-add-row">
						<?php esc_html_e( 'Add Label', 'buffet-calendar' ); ?>
					</button>
				</p>
				<input type="hidden" name="action" value="buffet_calendar_save_settings">
				<p class="submit">
					<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'buffet-calendar' ); ?>">
				</p>
			</form>

			<script type="text/html" id="buffet-calendar-row-template">
				<?php self::render_settings_row( '__ID__', [ 'label' => '', 'color' => self::FALLBACK_COLOR, 'enabled' => true ] ); ?>
			</script>
		</div>
		<?php
	}

	private static function render_settings_row( $id, $row ) {
		$id      = (string) $id;
		$label   = isset( $row['label'] ) ? (string) $row['label'] : '';
		$color   = isset( $row['color'] ) ? (string) $row['color'] : self::FALLBACK_COLOR;
		$enabled = ! empty( $row['enabled'] );
		?>
		<tr class="buffet-calendar-settings-row" data-row-id="<?php echo esc_attr( $id ); ?>">
			<td>
				<textarea name="buffet_calendar_setting[<?php echo esc_attr( $id ); ?>][label]"
				          rows="2"
				          class="buffet-calendar-settings-textarea"><?php echo esc_textarea( $label ); ?></textarea>
			</td>
			<td>
				<input type="text"
				       name="buffet_calendar_setting[<?php echo esc_attr( $id ); ?>][color]"
				       value="<?php echo esc_attr( $color ); ?>"
				       class="buffet-calendar-color-picker"
				       data-default-color="<?php echo esc_attr( $color ); ?>">
			</td>
			<td>
				<label>
					<input type="checkbox"
					       name="buffet_calendar_setting[<?php echo esc_attr( $id ); ?>][enabled]"
					       value="1"
					       <?php checked( $enabled ); ?>>
				</label>
			</td>
			<td>
				<button type="button" class="button-link button-link-delete buffet-calendar-remove-row">
					<?php esc_html_e( 'Remove', 'buffet-calendar' ); ?>
				</button>
			</td>
		</tr>
		<?php
	}

	public function register_scripts() {
		wp_register_style( 'buffet_calendar_backend-bootstrap-grid', BUFFET_CALENDAR_URI . 'assets/css/bootstrap-grid.min.css', [], '1.1' );
		wp_register_style( 'buffet_calendar_backend-calendar', BUFFET_CALENDAR_URI . 'assets/css/calendar.css', [], '1.16' );
		wp_register_script( 'buffet_calendar_backend-calendar', BUFFET_CALENDAR_URI . 'assets/js/calendar-timings-admin.js', [ 'jquery' ], '1.1', true );
		wp_register_script( 'buffet_calendar_backend-settings', BUFFET_CALENDAR_URI . 'assets/js/calendar-settings-admin.js', [ 'jquery', 'wp-color-picker' ], '1.0', true );
	}

}
