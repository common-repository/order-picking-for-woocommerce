<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/includes
 */
if ( !class_exists( 'Order_Picking_For_Woocommerce' ) ) {
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
     * @package    Order_Picking_For_Woocommerce
     * @subpackage Order_Picking_For_Woocommerce/includes
     * @author     powerfulwp <apowerfulwp@gmail.com>
     */
    class Order_Picking_For_Woocommerce {
        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      Order_Picking_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
         */
        protected $loader;

        /**
         * The unique identifier of this plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string    $plugin_name    The string used to uniquely identify this plugin.
         */
        protected $plugin_name;

        /**
         * The current version of the plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string    $version    The current version of the plugin.
         */
        protected $version;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function __construct() {
            if ( defined( 'ORDER_PICKING_FOR_WOOCOMMERCE_VERSION' ) ) {
                $this->version = ORDER_PICKING_FOR_WOOCOMMERCE_VERSION;
            } else {
                $this->version = '1.0.0';
            }
            $this->plugin_name = 'order-picking-for-woocommerce';
            $this->load_dependencies();
            $this->set_locale();
            $this->define_admin_hooks();
            $this->define_public_hooks();
        }

        /**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * - Order_Picking_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
         * - Order_Picking_For_Woocommerce_I18n. Defines internationalization functionality.
         * - Order_Picking_For_Woocommerce_Admin. Defines all hooks for the admin area.
         * - Order_Picking_For_Woocommerce_Public. Defines all hooks for the public side of the site.
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    1.0.0
         * @access   private
         */
        private function load_dependencies() {
            /**
             * The function file.
             * core plugin.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';
            /**
             * The class responsible for orchestrating the actions and filters of the
             * core plugin.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-order-picking-for-woocommerce-loader.php';
            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-order-picking-for-woocommerce-admin.php';
            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-order-picking-for-woocommerce-public.php';
            /**
             * The class responsible for the order picking
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-order-picking-for-woocommerce-order-picking.php';
            /**
             * The file responsible for the reports
             */
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-order-picking-for-woocommerce-reports.php';
            /**
             * The file responsible for the picklist
             */
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-order-picking-for-woocommerce-picklist.php';
            $this->loader = new Order_Picking_For_Woocommerce_Loader();
        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the Order_Picking_For_Woocommerce_I18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    1.0.0
         * @access   private
         */
        private function set_locale() {
            // $plugin_i18n = new Order_Picking_For_Woocommerce_I18n();
            // $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
        }

        /**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_admin_hooks() {
            $plugin_admin = new Order_Picking_For_Woocommerce_Admin($this->get_plugin_name(), $this->get_version());
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
            /**
             * Add menu
             */
            $this->loader->add_action(
                'admin_menu',
                $plugin_admin,
                'admin_menu',
                99
            );
            /**
             * Settings
             */
            $this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );
            /**
             * Ajax calls
             */
            $this->loader->add_action( 'wp_ajax_opfw_ajax', $plugin_admin, 'opfw_ajax' );
            $this->loader->add_action( 'wp_ajax_nopriv_opfw_ajax', $plugin_admin, 'opfw_ajax' );
            if ( opfw_is_hpos_enabled() ) {
                /**
                 * Order columns
                 */
                $this->loader->add_action(
                    'woocommerce_shop_order_list_table_custom_column',
                    $plugin_admin,
                    'orders_list_columns',
                    20,
                    2
                );
                $this->loader->add_filter(
                    'woocommerce_shop_order_list_table_columns',
                    $plugin_admin,
                    'orders_list_columns_order',
                    20
                );
                $this->loader->add_filter( 'manage_woocommerce_page_wc-orders_sortable_columns', $plugin_admin, 'orders_list_sortable_columns' );
                /**
                 * Orders filters
                 */
                $this->loader->add_action(
                    'woocommerce_order_list_table_restrict_manage_orders',
                    $plugin_admin,
                    'orders_filter_hpos',
                    20,
                    2
                );
                $this->loader->add_action( 'woocommerce_order_list_table_prepare_items_query_args', $plugin_admin, 'orders_filter_hpos_process' );
                /**
                 * Bulk orders update
                 */
                $this->loader->add_filter(
                    'handle_bulk_actions-woocommerce_page_wc-orders',
                    $plugin_admin,
                    'handle_bulk_actions_edit_shop_order',
                    10,
                    3
                );
                $this->loader->add_filter(
                    'bulk_actions-woocommerce_page_wc-orders',
                    $plugin_admin,
                    'bulk_actions_edit_shop_order',
                    20,
                    1
                );
                /**
                 * Order list
                 */
                $this->loader->add_action(
                    'woocommerce_order_list_table_restrict_manage_orders',
                    $plugin_admin,
                    'admin_order_list_top_bar_button_hpos',
                    20,
                    2
                );
            } else {
                /**
                 * Order columns
                 */
                $this->loader->add_action(
                    'manage_shop_order_posts_custom_column',
                    $plugin_admin,
                    'orders_list_columns',
                    20,
                    2
                );
                $this->loader->add_filter(
                    'manage_edit-shop_order_columns',
                    $plugin_admin,
                    'orders_list_columns_order',
                    20
                );
                $this->loader->add_filter( 'manage_edit-shop_order_sortable_columns', $plugin_admin, 'orders_list_sortable_columns' );
                /**
                 * Bulk orders update
                 */
                $this->loader->add_filter(
                    'handle_bulk_actions-edit-shop_order',
                    $plugin_admin,
                    'handle_bulk_actions_edit_shop_order',
                    10,
                    3
                );
                $this->loader->add_filter(
                    'bulk_actions-edit-shop_order',
                    $plugin_admin,
                    'bulk_actions_edit_shop_order',
                    20,
                    1
                );
                /**
                 * Orders filters
                 */
                $this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'orders_filter' );
                $this->loader->add_action( 'parse_query', $plugin_admin, 'orders_filter_process' );
                /**
                 * Order list
                 */
                $this->loader->add_action(
                    'manage_posts_extra_tablenav',
                    $plugin_admin,
                    'admin_order_list_top_bar_button',
                    20,
                    1
                );
            }
            /**
             * Order list actions
             */
            $this->loader->add_filter(
                'woocommerce_admin_order_actions',
                $plugin_admin,
                'add_custom_order_actions_button',
                100,
                2
            );
            /**
             * Order page
             */
            $this->loader->add_action(
                'woocommerce_admin_order_data_after_order_details',
                $plugin_admin,
                'woocommerce_admin_order_data_after_order_details',
                20,
                1
            );
            $this->loader->add_action(
                'woocommerce_order_action_mark_awaiting_fulfillment',
                $plugin_admin,
                'order_action_mark_awaiting_fulfillment',
                20,
                1
            );
            $this->loader->add_action(
                'woocommerce_order_action_mark_cancel_fulfillment',
                $plugin_admin,
                'order_action_mark_cancel_fulfillment',
                20,
                1
            );
            $this->loader->add_filter(
                'woocommerce_order_actions',
                $plugin_admin,
                'add_custom_order_action',
                20,
                2
            );
        }

        /**
         * Register all of the hooks related to the public-facing functionality
         * of the plugin.
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_public_hooks() {
        }

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->loader->run();
        }

        /**
         * The name of the plugin used to uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @since     1.0.0
         * @return    string    The name of the plugin.
         */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @since     1.0.0
         * @return    Order_Picking_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
         */
        public function get_loader() {
            return $this->loader;
        }

        /**
         * Retrieve the version number of the plugin.
         *
         * @since     1.0.0
         * @return    string    The version number of the plugin.
         */
        public function get_version() {
            return $this->version;
        }

    }

}