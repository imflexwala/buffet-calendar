<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use benhall14\phpCalendar\Calendar as Calendar;
use Carbon\Carbon;

class Hapnics_Calendar_Backend {

	/**
	 * The plugin instance.
	 *
	 * @var Hapnics_Calendar_Backend
	 * @since 0.8.4
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

	public function __construct( ) {
		add_action('admin_enqueue_scripts',array($this,'register_scripts'));
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_action('admin_post_hcft_save_calendar_data', array($this,'hcft_save_calendar_data'));
		add_action('admin_post_hcft_save_calendar_settings_data', array($this,'hcft_save_calendar_settings_data'));
	}

	/**
	 * Get the single plugin instance.
	 *
	 * @return Hapnics_Calendar_Backend The plugin instance.
	 * @since  0.8.4
	 */
	public static function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    public function admin_menu() {
	    add_menu_page(
		    'Calendar Timings', // page <title>Title</title>
		    'Calendar', // link text
		    'manage_options', // user capabilities
		    'hcft-calendar-page', // page slug
		    array($this,'hcft_calendar_page_callback'), // this function prints the page content
		    'dashicons-images-alt2', // icon (from Dashicons for example)
		    4 // menu position
	    );

	    add_submenu_page( 'hcft-calendar-page',
            'Calendar Settings',
            'Calendar Settings',
            'manage_options',
            'hcft-calendar-setting-page',
            array( $this, 'hcft_calendar_setting_page'),
        );
    }

    public function hcft_calendar_page_callback() {

	    wp_enqueue_style('hapnics_backend-bootstrap-grid');
	    wp_enqueue_style('hapnics_backend-calendar');
	    wp_enqueue_script('hapnics_backend-calendar');

	    $calendar = new Calendar;
	    $calendar->stylesheet();
	    $calendar->setLocale('de_DE');
	    $calendar->useMondayStartingDate();
	    $months = $this->getMonthsArray();
        $hcft_calendar_data = json_decode(get_option('hcft_calendar_data'),true);
        if(!is_array($hcft_calendar_data)) $hcft_calendar_data = [];
        //echo json_encode($hcft_calendar_data);
	    //echo json_encode($months);
	    $calendar->addEvents(self::getEvents($months,$hcft_calendar_data));

	    ?>
            <div class="wrap">
                <h1><?php echo get_admin_page_title() ?></h1>
                <form id="hcft_timing_calender" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
	                <?php wp_nonce_field('hcft_calendar_admin_page_submit', 'hcft_calendar_admin_page_nonce'); ?>
                    <input class="button button-primary" type="submit" name="submit" value="Save">
                    <div class="mt-5 row">
                        <?php foreach($months as $month): ?>
                            <div class="col-md-12">
                                <div class="mb-5 cldr">
                                    <?php $calendar->render(['startDate'=>$month,'color'=>'light-grey']);  ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input class="button button-primary" type="submit" name="submit" value="Save">
                    <input type="hidden" name="action" value="hcft_save_calendar_data">
                </form>
            </div>
	    <?php
    }

    public static function getEvents($months,$hcft_calendar_data,$select = true): array {

	    $events = [];
	    $hcft_calendar_settings_data = json_decode(get_option('hcft_calendar_settings_data'),true);

	    foreach ($months as $month){
		    $date = Carbon::createFromFormat('Y-m-d',$month);
		    if(!isset($hcft_calendar_data[$month])) $hcft_calendar_data[$month] = [];

		    $dd = $date->format('Y-m-d');
		    if(!isset($hcft_calendar_data[$month][$dd])) $hcft_calendar_data[$month][$dd] = "6";
            $event = array(
	            'start' => $dd,
	            'end' => $dd,
	            'summary' => '',
	            'mask' => false,
	            'classes' => [ self::$event_classes[$hcft_calendar_data[$month][$dd]] ?? '' , 'event-'.$month.'-'.$dd ],
	            'event_box_classes' => ['event-box-1'],
	            'title' => '',
            );

		    if ($select) $event['summary'] = self::getSelectInput($month,$dd,$hcft_calendar_data[$month][$dd],$hcft_calendar_settings_data);

		    $events[] = $event;
		    if(!isset($hcft_calendar_data[$month][$dd]))
			    $hcft_calendar_data[$month][$dd] = "6";

		    for($i = 1; $i < $date->daysInMonth; $i++){

			    $dd =  $date->addDay()->format('Y-m-d');
			    if(!isset($hcft_calendar_data[$month][$dd]))
				    $hcft_calendar_data[$month][$dd] = "6";

                $event = array(
	                'start' => $dd,
	                'end' => $dd,
	                'summary' => '',
	                'mask' => false,
	                'classes' => [ self::$event_classes[$hcft_calendar_data[$month][$dd]] ?? '', 'event-'.$month.'-'.$dd ],
	                'event_box_classes' => ['event-box-1'],
	                'title' => '',
                );

                if ($select) $event['summary'] = self::getSelectInput($month,$dd,$hcft_calendar_data[$month][$dd],$hcft_calendar_settings_data);
			    $events[] = $event;
		    }
	    }

        return $events;
    }

    public static function getSelectInput($month,$day,$value,$hcft_calendar_settings_data) {
        ob_start();
	    ?>
        <label>
            <select data-id="<?php echo 'event-'.$month.'-'.$day; ?>" name="hcft_calendar_data[<?php echo $month; ?>][<?php echo $day; ?>]" class="hcft-select">
                <option value="" <?php echo $value == '' ? 'selected' : ''; ?> >Select an option</option>
	            <?php foreach ($hcft_calendar_settings_data as $key => $text): ?>
                    <option value="<?php echo $key;?>" <?php echo $value == $key ? 'selected' : ''; ?>> <?php echo $text;?>
                    </option>
	            <?php endforeach; ?>
            </select>
        </label>
	    <?php

        return ob_get_clean();
    }

	public function getMonthsArray($count = 14) {
		$date = Carbon::now()->subMonth();
		$months = [$date->format('Y-m-01')];

		for($i = 1 ; $i < $count; $i++){
			$months[] = $date->addMonth()->format('Y-m-01');
		}

		return $months;
	}

	// Save the input value to wp_options table
	function hcft_save_calendar_data() {

		if (isset($_POST['submit']) && isset($_POST['hcft_calendar_admin_page_nonce']) && wp_verify_nonce($_POST['hcft_calendar_admin_page_nonce'], 'hcft_calendar_admin_page_submit')) {
			$input_value = sanitize_post($_POST['hcft_calendar_data']);
            unset($input_value['ID']);
            unset($input_value['filter']);
			update_option('hcft_calendar_data', json_encode($input_value));
		}

		wp_redirect(admin_url('admin.php?page=hcft-calendar-page'));
		exit;
	}

	// Save the input value to wp_options table
	function hcft_save_calendar_settings_data() {

		if (isset($_POST['submit']) && isset($_POST['hcft_calendar_admin_page_nonce']) && wp_verify_nonce($_POST['hcft_calendar_admin_page_nonce'], 'hcft_calendar_admin_page_submit')) {
			$input_value = sanitize_post($_POST['hcft_calendar_setting']);
            unset($input_value['ID']);
            unset($input_value['filter']);
			update_option('hcft_calendar_settings_data', json_encode($input_value));
		}

		wp_redirect(admin_url('admin.php?page=hcft-calendar-setting-page'));
		exit;
	}

    public function hcft_calendar_setting_page() {
	    $hcft_calendar_settings_data = json_decode(get_option('hcft_calendar_settings_data'),true);

        if (empty($hcft_calendar_settings_data))
	        $hcft_calendar_settings_data = [
		        "1" => '7.30 am - 10 am and 4 pm - 9.30 pm',
		        "2" => '7.30-10 am; 12-14 pm lunch buffet and 16-23 pm ',
		        "3" => '12pm - 4pm',
		        "4" => '7.30-10 a.m.',
		        "5" => '16-21 h',
		        "6" => 'geschlossen'
	        ]

        ?>
        <div class="wrap">
            <h1><?php echo get_admin_page_title() ?></h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			    <?php wp_nonce_field('hcft_calendar_admin_page_submit', 'hcft_calendar_admin_page_nonce'); ?>
                <table class="form-table">
                    <tbody>
                        <?php foreach ($hcft_calendar_settings_data as $key => $value): ?>
                            <tr valign="top">
                                <th scope="row" class="">
                                    <label for="label_<?php echo $key; ?>">
                                        Label <?php echo $key; ?>
                                    </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <textarea name="hcft_calendar_setting[<?php echo $key; ?>]" id="label_<?php echo $key; ?>" type="text"
                                           style="width:100%;"  class="" placeholder=""><?php echo $hcft_calendar_settings_data[$key] ?></textarea>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input class="button button-primary" type="submit" name="submit" value="Save">
                <input type="hidden" name="action" value="hcft_save_calendar_settings_data">
            </form>
        </div>

        <?php
    }

	public function register_scripts() {
		wp_register_style('hapnics_backend-bootstrap-grid',HCFT_URI.'/assets/css/bootstrap-grid.min.css','',1.1);
		wp_register_style('hapnics_backend-calendar',HCFT_URI.'/assets/css/calendar.css','',1.15);
		wp_register_script('hapnics_backend-calendar',HCFT_URI.'/assets/js/calendar-timings-admin.js','',1.1);
	}

}