<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use benhall14\phpCalendar\Calendar as Calendar;
use Carbon\Carbon;

class Hapnics_Calendar_Frontend {

	/**
	 * The plugin instance.
	 *
	 * @var   \Hapnics_Calendar_Frontend
	 * @since 0.8.4
	 */
	private static $instance;

	public function __construct( ) {
		add_shortcode( 'hapnics_calendar_frontend', array( $this, 'display_shortcode' ) );
		add_action('wp_enqueue_scripts',array($this,'register_scripts'));
	}

	/**
	 * Get the single plugin instance.
	 *
	 * @return \MultipleDomain The plugin instance.
	 * @since  0.8.4
	 */
	public static function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function display_shortcode() {

		wp_enqueue_style('hapnics_frontend-bootstrap-grid');
		wp_enqueue_style('hapnics_frontend-calendar');
		wp_enqueue_script('hapnics_frontend-calendar');

		ob_start();

		$calendar = new Calendar;
		$calendar->stylesheet();
		$calendar->setLocale('de_DE');
		$calendar->useMondayStartingDate();
		$months = $this->getMonthsArray();
		//echo json_encode($months);

		$hcft_calendar_data = json_decode(get_option('hcft_calendar_data'),true);
		if(!is_array($hcft_calendar_data)) $hcft_calendar_data = [];

		$hcft_calendar_settings_data = json_decode(get_option('hcft_calendar_settings_data'),true);

		$calendar->addEvents(Hapnics_Calendar_Backend::getEvents($months,$hcft_calendar_data,false));

		?>
        <div class="calendar-frontend">
            <div class="mt-3">
                <div class="time-grid">
                    <div class="time-grid-item">
                        <span class="hcft-color color-yellow"></span>
			            <?php echo nl2br($hcft_calendar_settings_data["1"]); ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="hcft-color color-green"></span>
			            <?php echo nl2br($hcft_calendar_settings_data["2"]); ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="hcft-color color-orange"></span>
			            <?php echo nl2br($hcft_calendar_settings_data["3"]); ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="hcft-color color-blue"></span>
			            <?php echo nl2br($hcft_calendar_settings_data["4"]); ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="hcft-color color-beige"></span>
			            <?php echo nl2br($hcft_calendar_settings_data["5"]); ?>
                    </div>
                    <div class="time-grid-item">
                        <span class="hcft-color color-red"></span>
			            <?php echo nl2br($hcft_calendar_settings_data["6"]) ?>
                    </div>
                </div>
	            <?php foreach($months as $key => $month): ?>
		            <?php if ($key == 3): ?>
                        <div class="hcft-text-center">
                            <div class="hcft-btn show-row-3">Mehr Anzeigen</div>
                        </div>
		            <?php endif; ?>
		            <?php if ($key == 0 || $key == 3): ?>
                        <div class="row row-<?php echo $key;?>" style="<?php echo $key == 3 ? 'display:none;' : ''; ?>" >
		            <?php endif; ?>
                    <div class="col-lg-4 col-md-6 col-12 c-<?php echo $key;?>">
                        <div class="mb-3 cldr">
				            <?php $calendar->render(['startDate'=>$month,'color'=>'light-grey']);  ?>
                        </div>
                    </div>
		            <?php if ($key == 2 || $key == sizeof($months)-1): ?>
                        </div>
		            <?php endif; ?>
	            <?php endforeach; ?>
            </div>
            <!--<div class="mt-4">
                <div class="">
                    <div class="time-list">
                        <div class="time-item">
                            <span class="hcft-color color-yellow"></span>
                            <?php /*echo nl2br($hcft_calendar_settings_data["1"]); */?>
                        </div>
                        <div class="time-item">
                            <span class="hcft-color color-green"></span>
	                        <?php /*echo nl2br($hcft_calendar_settings_data["2"]); */?>
                        </div>
                        <div class="time-item">
                            <span class="hcft-color color-orange"></span>
	                        <?php /*echo nl2br($hcft_calendar_settings_data["3"]); */?>
                        </div>
                        <div class="time-item">
                            <span class="hcft-color color-blue"></span>
	                        <?php /*echo nl2br($hcft_calendar_settings_data["4"]); */?>
                        </div>
                        <div class="time-item">
                            <span class="hcft-color color-beige"></span>
	                        <?php /*echo nl2br($hcft_calendar_settings_data["5"]); */?>
                        </div>
                        <div class="time-item">
                            <span class="hcft-color color-red"></span>
	                        <?php /*echo nl2br($hcft_calendar_settings_data["6"]) */?>
                        </div>
                    </div>
                </div>
            </div>-->
        </div>
		<?php

		return ob_get_clean();
	}

	public function getMonthsArray($count = 12) {
		$date = Carbon::now();
		$months = [$date->format('Y-m-01')];

		for($i = 1 ; $i < $count; $i++){
			$months[] = $date->addMonth()->format('Y-m-01');
		}

		return $months;
	}

	public function register_scripts() {
		wp_register_style('hapnics_frontend-bootstrap-grid',HCFT_URI.'/assets/css/bootstrap-grid.min.css','',1.1);
		wp_register_style('hapnics_frontend-calendar',HCFT_URI.'/assets/css/calendar.css','',1.18);
		wp_register_script('hapnics_frontend-calendar',HCFT_URI.'/assets/js/calendar-timings.js','',1.1);
	}

}