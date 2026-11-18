<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice_Pro
 * @subpackage Woo_Invoice_Pro/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Invoice_Pro
 * @subpackage Woo_Invoice_Pro/includes
 * @author     Md Ohidul Islam <wahid@webappick.com>
 */
class Woo_Invoice_Pro {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Woo_Invoice_Pro_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = CHALLAN_PRO_VERSION;
		$this->plugin_name = 'woo-invoice-pro';
		$this->installer();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 *
	 */
	private function installer() {
	    //
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Invoice_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Invoice_Pro_i18n. Defines internationalization functionality.
	 * - Woo_Invoice_Pro_Admin. Defines all hooks for the admin area.
	 * - Woo_Invoice_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-i18n.php';

		/**
		 * The class responsible for defining PDF functionality
		 * of the plugin.
		 */
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-engine.php';

		/**
		 * The class responsible for loading PDF plugin hooks
		 * of the plugin.
		 */
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-hooks.php';

		/**
		 * The class responsible for initializing MPDF
		 * of the plugin.
		 */
		include_once CHALLAN_PRO_PATH . 'includes/vendor/autoload.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		include_once CHALLAN_PRO_PATH . 'admin/class-woo-invoice-pro-admin.php';

		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-template.php';
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-helper.php';
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-orders.php';
		include_once CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro-pdf.php';
		include_once CHALLAN_PRO_PATH . 'includes/hooks.php';
		include_once CHALLAN_PRO_PATH . 'includes/helper.php';
		include_once CHALLAN_PRO_PATH . 'includes/widget.php';

		$this->loader = new Woo_Invoice_Pro_Loader();

        // check if uploads folder is writable.
        if ( ! woo_invoice_pro_is_uploads_folder_writable() ) {
            if ( isset( $_GET['action'] )
                && ( $_GET['action'] === 'wpifw_generate_invoice'
                    || $_GET['action'] === 'wpifw_generate_invoice_packing_slip'
                    || $_GET['action'] === 'wpifw_generate_delivery_address' ) ) {
                $message = __( 'Your uploads folder is not writable. Please make <strong>wp-content/uploads</strong> folder writable to generate and save invoices.', 'webappick-pdf-invoice-for-woocommerce' );
                die( $message );
            }
        }

        // if mpdf library is missing then downlaod mpdf.
        if ( woo_invoice_pro_is_uploads_folder_writable() && ! CHALLAN_PRO_MPDF_LIB_DIR . "vendor/autoload.php" ) {
            Challan_Pro_MpdfLibDownloader::downloadLib();
        }

        // default fonts are missing then download the defaults fonts.
        if ( woo_invoice_pro_is_uploads_folder_writable() && ! file_exists(CHALLAN_PRO_FONT_DIR . "currencies.ttf") ) {
            if ( ! class_exists("Challan_Pro_PluginDefaultFontDownloader") ) {
                require_once CHALLAN_PRO_PATH . "includes/classes/class-plugin-default-font-downloader.php";
            }

            Challan_Pro_PluginDefaultFontDownloader::downloadDefaultFonts();
            flush_rewrite_rules();
            if ( isset( $_GET['action'] )
                && ( $_GET['action'] === 'wpifw_generate_invoice'
                    || $_GET['action'] === 'wpifw_generate_invoice_packing_slip'
                    || $_GET['action'] === 'wpifw_generate_delivery_address' ) ) {
                $message = __( '<strong>Mpdf</strong> and <strong>fonts</strong> were missing. Now it is downloaded. <strong>Please reload again</strong> ', 'webappick-pdf-invoice-for-woocommerce' );
                die( $message );
            }
        }
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Invoice_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Invoice_Pro_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woo_Invoice_Pro_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_base_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_common_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'load_admin_menu' );
		$this->loader->add_action( 'admin_page_access_denied', $plugin_admin, 'handle_old_menu_slugs' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {

		// $plugin_public = new Woo_Invoice_Pro_Public( $this->get_plugin_name(), $this->get_version() );
		//
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string    The name of the plugin.
	 * @since  1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Woo_Invoice_Pro_Loader    Orchestrates the hooks of the plugin.
	 * @since  1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string    The version number of the plugin.
	 * @since  1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
