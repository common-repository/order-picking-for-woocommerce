<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://powerfulwp.com
 * @since             1.0.0
 * @package           Order_Picking_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Order Picking For WooCommerce
 * Plugin URI:        https://powerfulwp.com/order-picking-for-woocommerce
 * Description:       Order Picking For WooCommerce lets you fulfill your orders quickly and easily.
 * Version:           1.0.5
 * Author:            powerfulwp
 * Author URI:        https://powerfulwp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       order-picking-for-woocommerce
 * Domain Path:       /languages
 *
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
// Declare extension compatible with HPOS.
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
if ( !function_exists( 'opfw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function opfw_fs() {
        global $opfw_fs;
        if ( !isset( $opfw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $opfw_fs = fs_dynamic_init( array(
                'id'             => '11646',
                'slug'           => 'order-picking-for-woocommerce',
                'type'           => 'plugin',
                'public_key'     => 'pk_4371e0196c4590fb6c0e948d10ac6',
                'is_premium'     => false,
                'premium_suffix' => 'premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'    => 'opfw-settings',
                    'support' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $opfw_fs;
    }

    // Init Freemius.
    opfw_fs();
    // Signal that SDK was initiated.
    do_action( 'opfw_fs_loaded' );
}
$opfw_plugin_basename = plugin_basename( __FILE__ );
$opfw_plugin_basename_array = explode( '/', $opfw_plugin_basename );
$opfw_plugin_folder = $opfw_plugin_basename_array[0];
if ( !function_exists( 'activate_order_picking_for_woocommerce' ) ) {
    /**
     * Define plugin folder name.
     */
    define( 'OPFW_FOLDER', $opfw_plugin_folder );
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'ORDER_PICKING_FOR_WOOCOMMERCE_VERSION', '1.0.5' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-order-picking-for-woocommerce-activator.php
     */
    function activate_order_picking_for_woocommerce(  $network_wide  ) {
        include_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-order-picking-for-woocommerce-activator.php';
        $activator = new Order_Picking_For_Woocommerce_Activator();
        $activator->activate( $network_wide );
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-order-picking-for-woocommerce-deactivator.php
     */
    function deactivate_order_picking_for_woocommerce() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-order-picking-for-woocommerce-deactivator.php';
        Order_Picking_For_Woocommerce_Deactivator::deactivate();
    }

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_order_picking_for_woocommerce() {
        $plugin = new Order_Picking_For_Woocommerce();
        $plugin->run();
    }

    /**
     * Admin notices function.
     *
     * @since 1.0.0
     */
    function opfw_admin_notices() {
        if ( !class_exists( 'WooCommerce' ) ) {
            echo '<div class="notice notice-error is-dismissible">
				<p>' . esc_html( __( 'Order Picking For WooCommerce is a WooCommerce add-on, you must activate a WooCommerce on your site.', 'order-picking-for-woocommerce' ) ) . '</p>
				</div>';
        }
    }

    /**
     * Initializes the plugin.
     * This function checks if WooCommerce is active before running the plugin.
     * If WooCommerce is not active, it displays an admin notice.
     */
    function initialize_opfw_run() {
        // Check if WooCommerce is active.
        if ( !class_exists( 'WooCommerce' ) ) {
            // Adding action to admin_notices to display a notice if WooCommerce is not active.
            add_action( 'admin_notices', 'opfw_admin_notices' );
            return;
            // Stop the initialization as WooCommerce is not active.
        }
        // WooCommerce is active, so initialize the plugin.
        run_order_picking_for_woocommerce();
    }

}
// Include the internationalization class to handle text domain loading.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-order-picking-for-woocommerce-i18n.php';
/**
 * Initializes internationalization (i18n) support for the plugin.
 */
if ( !function_exists( 'opfw_initialize_i18n' ) ) {
    function opfw_initialize_i18n() {
        // Create an instance of the OPFW_I18n class.
        $plugin_i18n = new Order_Picking_For_Woocommerce_I18n();
        // Hook the 'load_plugin_textdomain' method of the OPFW_I18n class to the 'plugins_loaded' action.
        // This ensures that the plugin's text domain is loaded as soon as all plugins are loaded by WordPress,
        // making translations available.
        add_action( 'plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain') );
    }

}
// Call the function to initialize internationalization support.
opfw_initialize_i18n();
register_activation_hook( __FILE__, 'activate_order_picking_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_order_picking_for_woocommerce' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-order-picking-for-woocommerce.php';
// Hook into 'plugins_loaded' with a priority of 20 to initialize the plugin after all plugins have loaded.
// This is particularly useful for ensuring the plugin loads after WooCommerce, if WooCommerce is a dependency.
add_action( 'plugins_loaded', 'initialize_opfw_run', 20 );