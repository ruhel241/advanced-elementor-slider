<?php
/**
 * Plugin Name: Advanced Slider for Elementor 
 * Description: Advanced slider for elementor wordpress plugin
 * Plugin URI:  https://github.com/ruhel241/advanced-slider
 * Version:     1.0.0
 * Author:      Md.Ruhel Khan
 * Author URI:  https://profiles.wordpress.org/ruhel241/#content-plugins
 * Text Domain: ase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define('ASE_PLUGIN_URL', plugin_dir_url(__FILE__));

define('ASE_DIR_FILE', __FILE__);
define('ADVANCED_SLIDER_LITE', 'advancedSliderLite');
define('ASE_PLUGIN_VERSION', '1.0.0');

/**
 * Main Advanced Slider Lite Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.4.0
 */
final class ASESliderLite
{
	
	/**
	 * Plugin Version
	 *
	 * @since 1.4.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.4.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.4.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.4.0
	 *
	 * @access private
	 * @static
	 *
	 * @var ASESliderLite 
	 * The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 * @static
	 *
	 * @return ASESliderLite 
	 * An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );

		// var_dump(defined(ADVANCEDSLIDERPRO)); die;
	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'ase' );
	}

	/**
	 * On Plugins Loaded
	 *
	 * Checks if Elementor has loaded, and performs some compatibility checks.
	 * If All checks pass, inits the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function on_plugins_loaded() {

		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}
		add_action( 'admin_notices', [$this, 'ase_admin_Notice'] );
		
		if (defined('ASEPRO_DIR_FILE')) {
			if (!class_exists(AdvancedSliderPro\Services\AdvancedSliderWidgetPro::class)) {
				require_once(ABSPATH.'/wp-content/plugins/advanced-slider-for-elementor-pro/Services/slider-widget.php');
			}
		}
	}

	public function ase_admin_Notice() {
		//get the current screen
		$screen = get_current_screen();
		//Checks if settings updated 
		if ( $screen->id == 'dashboard' ||  $screen->id == 'plugins' ) {
			?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php _e('Congratulations! you have installed "Advanced Slider for Elementor" for elementor plugin, Please rating this plugin.', 'ase'); ?>
						<em><a href="https://wordpress.org/support/plugin/advanced-slider-for-elementor/reviews/#new-post" target="_blank">Rating</a></em>
					</p>
				</div>
			<?php
		}
	}
	
	/**
	 * Compatibility Checks
	 *
	 * Checks if the installed version of Elementor meets the plugin's minimum requirement.
	 * Checks if the installed PHP version meets the plugin's minimum requirement.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function is_compatible() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}

		return true;

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function init() {
		
		$this->loadTextDomain();

		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
		
		add_action('elementor/frontend/after_enqueue_styles', function() {
			wp_enqueue_style( 'ase-swiper-css', plugin_dir_url( __FILE__ ). 'assets/css/ase-slider.css', array(), ASE_PLUGIN_VERSION);
		});

		add_action('elementor/editor/after_enqueue_styles', function() {
			wp_enqueue_style( 'ase-editor-css', plugin_dir_url( __FILE__ ). 'assets/css/ase-editor.css', array(), ASE_PLUGIN_VERSION);
		});

		// after_enqueue_scripts
		add_action('elementor/frontend/after_enqueue_scripts', function() {
			wp_enqueue_script( 'ase-swiper-js', plugin_dir_url( __FILE__ ). 'assets/js/ase-slider.js', array('jquery'), ASE_PLUGIN_VERSION, false);
			
			wp_localize_script('ase-swiper-js', 'aseSwiperVar', array(
                'has_pro' => defined('ADVANCED_SLIDER_PRO')
            ));
		});
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		require_once( __DIR__ . '/widgets/slider-widget.php' );

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register( new AdvancedSliderLite\Widgets\AdvancedSliderLiteWidget() );

	}

	public function loadTextDomain()
    {
        load_plugin_textdomain('ase', false, basename(dirname(__FILE__)) . '/languages');
	}
	
	
	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'ase' ),
			'<strong>' . esc_html__( 'Advanced Slider Carousel', 'ase' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'ase' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ase' ),
			'<strong>' . esc_html__( 'Advanced Slider Carousel', 'ase' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'ase' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.4.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ase' ),
			'<strong>' . esc_html__( 'Advanced Slider Carousel', 'ase' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'ase' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

}
ASESliderLite::instance();