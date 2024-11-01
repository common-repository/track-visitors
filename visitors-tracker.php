<?php
/**
 * Plugin Name: Visitors Tracker
 * Description: A plugin that will track a visitor how many time vistior visit a website and give a noticfication message.
 * Plugin URI: https://www.github.com/abdulhadicse/visitor-tracker/
 * Author: Abdul Hadi
 * Author URI: https://www.abdulhadi.info
 * Version: 1.0.1
 * License: GPL2 or later
 * Text Domain: visitors-tracker
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class
 */
final class Visitors_Tracker {
	/**
	 * User IP Address
	 */
	public $user_ip_address;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '1.0';

	/**
	 * Class construcotr
	 */
	private function __construct() {
		$this->define_constants();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
	}

	/**
	 * Initializes a singleton instance
	 *
	 * @return \Visitors_Tracker
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'VISITOR_TRACKER_VERSION', self::version );
		define( 'VISITOR_TRACKER_FILE', __FILE__ );
		define( 'VISITOR_TRACKER_PATH', __DIR__ );
		define( 'VISITOR_TRACKER_URL', plugins_url( '', VISITOR_TRACKER_FILE ) );
		define( 'VISITOR_TRACKER_ASSETS', VISITOR_TRACKER_URL . '/assets' );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'visitor-tracker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 99 );
		add_action( 'init', array( $this, 'visitor_ip_address_tracker' ) );
		add_action( 'wp_head', array( $this, 'display_notification_message' ) );
	}
	/**
	 * Enqueue Style
	 *
	 * @return void
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'vt-css', VISITOR_TRACKER_ASSETS . '/css/custom-style.css', null, time() );

		wp_enqueue_script( 'v-cookie-js', VISITOR_TRACKER_ASSETS . '/js/jquery.cookie.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'vt-js', VISITOR_TRACKER_ASSETS . '/js/main.js', array( 'jquery' ), time(), true );

		wp_localize_script(
			'vt-js',
			'vt_obj',
			array(
				'url' => admin_url( 'ajax.php' ),
			)
		);
	}
	/**
	 * Get Individual User IP address
	 * & store data into options table
	 *
	 * @return void
	 */
	public function visitor_ip_address_tracker() {
		// get visitor track data
		$get_visitors_data = get_option( 'wpvt_visitors_tracker' );
		// check empty
		if ( empty( $get_visitors_data ) ) {
			$get_visitors_data = array();
		}
		// set initial count
		$count = 0;
		// set current time & date
		$current_date = date( 'Y-m-d H:i:s' );
		// whether ip is from share internet
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		// whether ip is from proxy
		elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		// whether ip is from remote address
		else {
			$ip_address            = $_SERVER['REMOTE_ADDR'];
			$this->user_ip_address = $ip_address;
		}
		// check cookie is set
		if ( isset( $_COOKIE['visitors-tracker'] ) ) {
			return;
		}
		// set cookie
		setcookie( 'visitors-tracker', $ip_address, time() + 1800, '/' );

		// get all IP Address into an array
		if ( ! empty( $get_visitors_data ) ) {
			foreach ( $get_visitors_data as $key => $value ) {
				$ip_addresses[ $key ] = $value['ip'];
			}
		}
		// check IP address if already has
		if ( ! empty( $ip_addresses ) && in_array( $ip_address, $ip_addresses, true ) ) {
			foreach ( $get_visitors_data as $key => $value ) {
				if ( $ip_address == $value['ip'] ) {
					$count                = ++$value['count'];
					$visitor_data[ $key ] = array(
						'ip'    => $ip_address,
						'count' => $count,
						'time'  => $current_date,
					);
					// update count and time
					update_option( 'wpvt_visitors_tracker', $visitor_data, true );
					return;
				}
			}
		}
		// if already not ip adress set new
		$get_visitors_data[] = array(
			'ip'    => $ip_address,
			'count' => ++$count,
			'time'  => $current_date,
		);
		// update user data
		update_option( 'wpvt_visitors_tracker', $get_visitors_data, true );
	}

	public function display_notification_message() {
		$get_visitors_data = get_option( 'wpvt_visitors_tracker' );
		// get all IP Address into an array
		if ( ! empty( $get_visitors_data ) ) {
			foreach ( $get_visitors_data as $key => $value ) {
				$ip_addresses[ $key ] = $value['ip'];
			}
		}
		// check individual ip address and show meaasge frontend
		if ( ! empty( $ip_addresses ) && in_array( $this->user_ip_address, $ip_addresses, true ) ) {
			foreach ( $get_visitors_data as $key => $value ) {
				if ( $this->user_ip_address == $value['ip'] ) {
					// html message markup here
					include __DIR__ . '/assets/views/display.php';
				}
			}
		}
	}

	/**
	 * Do stuff upon plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		$installed = get_option( 'visitor_tracker_installed' );

		if ( ! $installed ) {
			update_option( 'visitor_tracker_installed', time() );
		}

		update_option( 'visitor_tracker_version', VISITOR_TRACKER_VERSION );
	}
}

/**
 * Initializes the main plugin
 *
 * @return \Visitors_Tracker
 */
function visitor_tracker() {
	return Visitors_Tracker::init();
}

// kick-off the plugin
visitor_tracker();
