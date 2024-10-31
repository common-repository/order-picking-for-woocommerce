<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/admin
 * @author     powerfulwp <apowerfulwp@gmail.com>
 */
class Order_Picking_For_Woocommerce_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name       The name of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Order_Picking_For_Woocommerce_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Order_Picking_For_Woocommerce_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        global $pagenow;
        $screen = get_current_screen();
        $screen_id = ( $screen ? $screen->id : '' );
        $page = ( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' );
        if ( 'shop_order' === $screen_id || 'woocommerce_page_wc-orders' === $screen_id || 'edit-shop_order' === $screen_id || 'admin.php' === $pagenow && ('opfw-report' === $page || 'opfw-settings' === $page || 'opfw-awaiting-fulfillment' === $page || 'opfw-picklist' === $page) ) {
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/order-picking-for-woocommerce-admin.css',
                array(),
                $this->version,
                'all'
            );
        }
        if ( 'opfw-settings' === $page ) {
            wp_enqueue_style(
                'woocommerce_admin_styles',
                WC()->plugin_url() . '/assets/css/admin.css',
                array(),
                WC_VERSION
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Order_Picking_For_Woocommerce_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Order_Picking_For_Woocommerce_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        global $pagenow;
        $screen = get_current_screen();
        $screen_id = ( $screen ? $screen->id : '' );
        $page = ( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' );
        if ( 'shop_order' === $screen_id || 'woocommerce_page_wc-orders' === $screen_id || 'edit-shop_order' === $screen_id || 'admin.php' === $pagenow && ('opfw-report' === $page || 'opfw-settings' === $page || 'opfw-awaiting-fulfillment' === $page || 'opfw-picklist' === $page) ) {
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/order-picking-for-woocommerce-admin.js',
                array('jquery', 'wc-enhanced-select', 'selectWoo'),
                $this->version,
                false
            );
            wp_localize_script( $this->plugin_name, 'opfw_ajax', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            ) );
            wp_localize_script( $this->plugin_name, 'opfw_nonce', array(
                'nonce' => esc_js( wp_create_nonce( 'opfw-nonce' ) ),
            ) );
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            $statuses = $picking->statuses;
            wp_localize_script( $this->plugin_name, 'opfw_statuses', $statuses );
        }
    }

    /**
     * Plugin submenu.
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_menu() {
        // Add menu to main menu.
        add_menu_page(
            esc_html( __( 'Order Picking', 'order-picking-for-woocommerce' ) ),
            esc_html( __( 'Order Picking', 'order-picking-for-woocommerce' ) ),
            'edit_pages',
            'opfw-settings',
            array(&$this, 'settings'),
            'dashicons-clipboard',
            56
        );
        add_submenu_page(
            'opfw-settings',
            esc_html( __( 'Settings', 'order-picking-for-woocommerce' ) ),
            esc_html( __( 'Settings', 'order-picking-for-woocommerce' ) ),
            'edit_pages',
            'opfw-settings',
            array(&$this, 'settings')
        );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function settings() {
        // Default variables.
        $settings_title = esc_html( __( 'General Settings', 'order-picking-for-woocommerce' ) );
        // Get the current tab from the $_GET param.
        $current_tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '' );
        // Tabs array.
        $tabs = array(array(
            'slug'  => '',
            'label' => esc_html( __( 'General settings', 'order-picking-for-woocommerce' ) ),
            'title' => esc_html( __( 'General settings', 'order-picking-for-woocommerce' ) ),
            'url'   => '?page=opfw-settings',
        ));
        foreach ( $tabs as $tab ) {
            if ( $current_tab === $tab['slug'] ) {
                $settings_title = $tab['title'];
                break;
            }
        }
        echo $this->opfw_admin_plugin_bar();
        ?>
		<div class="wrap">
		<form action='options.php' method='post'>
			<h1 class="wp-heading-inline"><?php 
        echo esc_html( $settings_title );
        ?></h1>
			<?php 
        if ( 1 < count( $tabs ) ) {
            ?>
							<nav class="nav-tab-wrapper">
						<?php 
            foreach ( $tabs as $tab ) {
                $url = ( '' !== $tab['slug'] ? 'admin.php?page=opfw-settings&tab=' . esc_attr( $tab['slug'] ) : 'admin.php?page=opfw-settings' );
                echo '<a href="' . esc_html( admin_url( $url ) ) . '" class="nav-tab ' . (( $current_tab === $tab['slug'] ? 'nav-tab-active' : '' )) . '">' . esc_html( $tab['label'] ) . '</a>';
            }
            ?>
							</nav>
						<?php 
        }
        echo '<hr class="wp-header-end">';
        foreach ( $tabs as $tab ) {
            if ( '' === $current_tab ) {
                settings_fields( 'order-picking-for-woocommerce' );
                do_settings_sections( 'order-picking-for-woocommerce' );
                break;
            } elseif ( $current_tab === $tab['slug'] ) {
                settings_fields( $tab['slug'] );
                do_settings_sections( $tab['slug'] );
                break;
            }
        }
        submit_button();
        ?>
		</form>
	</div>
			<?php 
    }

    /**
     * Plugin register settings.
     *
     * @since 1.0.0
     * @return void
     */
    public function settings_init() {
        // Get settings tab.
        $tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '' );
        register_setting( 'order-picking-for-woocommerce', 'opfw_picking_statuses' );
        if ( '' === $tab ) {
            // General Settings.
            add_settings_section(
                'opfw_setting_section',
                '',
                '',
                'order-picking-for-woocommerce'
            );
            add_settings_field(
                'opfw_picking_statuses',
                __( 'Order Statuses for Picking', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_picking_statuses'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_fulfillment_page',
                __( 'Staff Frontend Fulfillment Panel', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_fulfillment_page'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_awaiting_fulfillment_on_status_change',
                __( 'Automatically set the order to "awaiting fulfillment" when the order status changes', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_awaiting_fulfillment_on_status_change'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_picking_awaiting_fulfillment_status',
                __( 'Automatically update the order status on the action of waiting for fulfillment', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_picking_awaiting_fulfillment_status'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_picking_fulfilled_status',
                __( 'Automatically update the order status on the action of fulfilling', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_picking_fulfilled_status'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_picking_partially_fulfilled_status',
                __( 'Automatically update the order status on the action of partially fulfilling', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_picking_partially_fulfilled_status'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_picking_unfulfillment_status',
                __( 'Automatically update the order status on the action of not fulfilling', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_picking_unfulfillment_status'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            add_settings_field(
                'opfw_picking_cancel_status',
                __( 'Automatically update the order status on the action of canceling fulfillment', 'order-picking-for-woocommerce' ),
                array($this, 'opfw_picking_cancel_status'),
                'order-picking-for-woocommerce',
                'opfw_setting_section'
            );
            if ( opfw_is_free() ) {
                add_settings_field(
                    'opfw_picking_reports',
                    __( 'Reports', 'order-picking-for-woocommerce' ),
                    array($this, 'opfw_picking_reports'),
                    'order-picking-for-woocommerce',
                    'opfw_setting_section'
                );
                add_settings_field(
                    'opfw_picklist',
                    __( 'Picklist', 'order-picking-for-woocommerce' ),
                    array($this, 'opfw_Picklist'),
                    'order-picking-for-woocommerce',
                    'opfw_setting_section'
                );
            }
        }
        do_action( 'opfw_settings' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_picking_unfulfillment_status() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_awaiting_fulfillment_on_status_change() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_picking_awaiting_fulfillment_status() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_picking_fulfilled_status() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_picking_partially_fulfilled_status() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_Picklist() {
        $picklists = new Order_Picking_For_Woocommerce_Order_Picklist();
        $links = $picklists->picklist_links();
        foreach ( $links as $picklist => $title ) {
            echo '<p>' . opfw_admin_premium_feature( '' ) . ' ' . $title . '</p>';
        }
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_picking_reports() {
        $order_picking = new Order_Picking_For_Woocommerce_Order_Reports();
        $links = $order_picking->report_links();
        foreach ( $links as $report => $title ) {
            echo '<p>' . opfw_admin_premium_feature( '' ) . ' ' . $title . '</p>';
        }
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_picking_cancel_status() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     */
    public function opfw_fulfillment_page() {
        echo opfw_admin_premium_feature( '' );
    }

    /**
     * Picking statuses selectbox for settings.
     *
     * @return void
     */
    public function opfw_picking_statuses() {
        $result = '';
        if ( function_exists( 'wc_get_order_statuses' ) ) {
            $result = wc_get_order_statuses();
        }
        $opfw_picking_statuses = get_option( 'opfw_picking_statuses', '' );
        ?>
		<select multiple="multiple" style="width:350px" class="opfw_picking_statuses_select wc-enhanced-select" data-placeholder="<?php 
        esc_attr_e( 'Select order picking statuses', 'order-picking-for-woocommerce' );
        ?>"  id="opfw_picking_statuses_select" name='opfw_picking_statuses[]'>
			<?php 
        if ( !empty( $result ) ) {
            foreach ( $result as $key => $status ) {
                $selected_status = '';
                if ( !empty( $opfw_picking_statuses ) ) {
                    foreach ( $opfw_picking_statuses as $picking_status ) {
                        if ( $key === $picking_status ) {
                            $selected_status = $picking_status;
                            break;
                        }
                    }
                }
                ?>
					<option value="<?php 
                echo esc_attr( $key );
                ?>" <?php 
                selected( esc_attr( $selected_status ), $key );
                ?>><?php 
                echo esc_html( $status );
                ?></option>
					<?php 
            }
        }
        ?>
		</select>
		<p class="opfw_description" id="opfw_picking_statuses-description">
		<?php 
        echo esc_html( __( 'Choose the order statuses that allow for picking.', 'order-picking-for-woocommerce' ) );
        ?>
		<?php 
        if ( opfw_is_free() ) {
            echo opfw_admin_premium_feature( '' ) . ' ' . esc_html( __( 'New WooCommerce status for fulfillment.' ) );
        }
        ?>
		</p>
		<?php 
    }

    /**
     * The function that handles ajax requests.
     *
     * @since 1.0.0
     * @return void
     */
    public function opfw_ajax() {
        $opfw_service = ( isset( $_POST['opfw_service'] ) ? sanitize_text_field( wp_unslash( $_POST['opfw_service'] ) ) : '' );
        $opfw_order_id = ( isset( $_POST['opfw_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['opfw_order_id'] ) ) : '' );
        $opfw_product_id = ( isset( $_POST['opfw_product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['opfw_product_id'] ) ) : '' );
        $opfw_status = ( isset( $_POST['opfw_status'] ) ? sanitize_text_field( wp_unslash( $_POST['opfw_status'] ) ) : '' );
        /**
         * Security check.
         */
        if ( isset( $_POST['opfw_wpnonce'] ) ) {
            $nonce = sanitize_text_field( wp_unslash( $_POST['opfw_wpnonce'] ) );
            if ( !wp_verify_nonce( $nonce, 'opfw-nonce' ) ) {
                exit;
            }
        }
        // Get awaiting fulfillment orders.
        if ( 'get_awaiting_fulfillment_orders' === $opfw_service ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            echo $picking->get_awaiting_fulfillment_orders();
        }
        // Get selected orders.
        if ( 'get_orders' === $opfw_service ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            $array = explode( ',', $opfw_order_id );
            echo $picking->get_orders( $array );
        }
        // Set product picking status.
        if ( 'set_picking' === $opfw_service ) {
            $note = ( isset( $_POST['opfw_note'] ) ? sanitize_text_field( wp_unslash( $_POST['opfw_note'] ) ) : '' );
            $quantity = ( isset( $_POST['opfw_quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['opfw_quantity'] ) ) : '' );
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            echo $picking->set_picking(
                $opfw_product_id,
                $opfw_status,
                $quantity,
                $note
            );
        }
        // Set order picking status.
        if ( 'set_order_picking' === $opfw_service ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            echo $picking->set_order_picking( $opfw_order_id, $opfw_status );
        }
        // Set order picking status.
        if ( 'cancel_order_picking' === $opfw_service ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            echo $picking->cancel_order_picking( $opfw_order_id );
        }
        exit;
    }

    /**
     * Admin plugin bar.
     *
     * @since 1.1.0
     * @return html
     */
    public function opfw_admin_plugin_bar() {
        return '<div class="opfw_admin_bar">' . esc_html( __( 'Developed by', 'order-picking-for-woocommerce' ) ) . ' <a href="https://powerfulwp.com/" target="_blank">PowerfulWP</a> | <a href="https://powerfulwp.com/order-picking-for-woocommerce/" target="_blank" >' . esc_html( __( 'Premium', 'order-picking-for-woocommerce' ) ) . '</a> | <a href="https://powerfulwp.com/docs/order-picking-for-woocommerce-premium/" target="_blank" >' . esc_html( __( 'Documents', 'order-picking-for-woocommerce' ) ) . '</a></div>';
    }

    /**
     * Custom action button.
     *
     * @param array  $actions action array.
     * @param object $order order.
     * @return array
     */
    public function add_custom_order_actions_button( $actions, $order ) {
        // Display the button for all orders that have a 'picking' permission.
        $picking = new Order_Picking_For_Woocommerce_Order_Picking();
        if ( $picking->get_order_picking_permission( $order ) ) {
            // Get Order ID.
            $order_id = $order->get_id();
            // Set the action button.
            $actions['opfw_picking_order'] = array(
                'url'    => wp_nonce_url( admin_url( 'post.php?post=' . $order_id . '&amp;action=edit' ) ),
                'name'   => __( 'Picking Order', 'woocommerce' ),
                'action' => 'view opfw_order_picking_action',
            );
        }
        return $actions;
    }

    /**
     * Order picking button.
     *
     * @param string $which section.
     * @return void
     */
    public function admin_order_list_top_bar_button( $which ) {
        global $typenow;
        if ( 'shop_order' === $typenow && 'top' === $which ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            echo $picking->get_order_picking_button( 'admin_orders_list' );
        }
    }

    /**
     * Order picking button.
     *
     * @param string $which section.
     * @return void
     */
    public function admin_order_list_top_bar_button_hpos( $order_type, $which ) {
        if ( 'shop_order' === $order_type && 'top' === $which ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            echo $picking->get_order_picking_button( 'admin_orders_list' );
        }
    }

    /**
     * Columns order
     *
     * @param array $columns columns array.
     * @since 1.0.0
     * @return array
     */
    public function orders_list_columns_order( $columns ) {
        $reordered_columns = array();
        // Inserting columns to a specific location.
        foreach ( $columns as $key => $column ) {
            $reordered_columns[$key] = $column;
            if ( 'order_status' === $key ) {
                // Inserting after "Status" column.
                $reordered_columns['fulfillment'] = __( 'Fulfillment', 'order-picking-for-woocommerce' );
            }
        }
        return $reordered_columns;
    }

    /**
     * Print fulfillment in column
     *
     * @param string $column column name.
     * @param int    $post_id post number.
     * @since 1.0.0
     */
    public function orders_list_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'fulfillment':
                $picking = new Order_Picking_For_Woocommerce_Order_Picking();
                $order = wc_get_order( $post_id );
                echo $picking->get_order_badge( $order->get_id(), '' );
                break;
        }
    }

    /**
     * Sortable columns
     *
     * @param array $columns columns array.
     * @since 1.0.0
     * @return array
     */
    public function orders_list_sortable_columns( $columns ) {
        $columns['fulfillment'] = 'fulfillment';
        return $columns;
    }

    /**
     * Admin order filters.
     *
     * @since 1.1.0
     */
    public function orders_filter() {
        global $pagenow, $post_type;
        if ( 'shop_order' === $post_type && 'edit.php' === $pagenow ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            $statuses = $picking->statuses;
            $current = ( isset( $_GET['opfw_orders_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['opfw_orders_filter'] ) ) : '' );
            echo '<select name="opfw_orders_filter">
			<option value="">' . esc_html( __( 'Filter By', 'order-picking-for-woocommerce' ) ) . '</option>';
            foreach ( $statuses as $key => $value ) {
                $selected = ( strval( $key ) === $current ? 'selected' : '' );
                echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
            }
            echo '</select>';
        }
    }

    /**
     * Admin order filters.
     *
     * @since 1.1.0
     */
    public function orders_filter_hpos( $order_type, $which ) {
        if ( 'shop_order' === $order_type && 'top' === $which ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            $statuses = $picking->statuses;
            $current = ( isset( $_GET['opfw_orders_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['opfw_orders_filter'] ) ) : '' );
            echo '<select name="opfw_orders_filter">
				<option value="">' . esc_html( __( 'Filter By', 'order-picking-for-woocommerce' ) ) . '</option>';
            foreach ( $statuses as $key => $value ) {
                $selected = ( strval( $key ) === $current ? 'selected' : '' );
                echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
            }
            echo '</select>';
        }
    }

    /**
     * Admin order filters process.
     *
     * @param object $query query.
     * @since 1.1.0
     */
    public function orders_filter_hpos_process( $query ) {
        if ( isset( $_GET['opfw_orders_filter'] ) && '' !== $_GET['opfw_orders_filter'] && 'shop_order' === $query['type'] ) {
            $nonce_key = 'opfw_nonce';
            if ( isset( $_REQUEST[$nonce_key] ) ) {
                $retrieved_nonce = sanitize_text_field( wp_unslash( $_REQUEST[$nonce_key] ) );
                if ( !wp_verify_nonce( $retrieved_nonce, basename( __FILE__ ) ) ) {
                    die( 'Failed security check' );
                }
            }
            $opfw_orders_filter = ( isset( $_GET['opfw_orders_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['opfw_orders_filter'] ) ) : '' );
            // Filter picking statuses.
            if ( '' !== $opfw_orders_filter ) {
                $query['meta_query'][] = array(array(
                    'key'     => '_opfw_picking_status',
                    'value'   => $opfw_orders_filter,
                    'compare' => '=',
                ));
            }
        }
        return $query;
    }

    /**
     * Admin order filters process.
     *
     * @param object $query query.
     * @since 1.1.0
     */
    public function orders_filter_process( $query ) {
        global $pagenow;
        if ( $query->is_admin && 'edit.php' === $pagenow && isset( $_GET['opfw_orders_filter'] ) && '' !== $_GET['opfw_orders_filter'] && isset( $_GET['post_type'] ) && 'shop_order' === $_GET['post_type'] ) {
            $nonce_key = 'opfw_nonce';
            if ( isset( $_REQUEST[$nonce_key] ) ) {
                $retrieved_nonce = sanitize_text_field( wp_unslash( $_REQUEST[$nonce_key] ) );
                if ( !wp_verify_nonce( $retrieved_nonce, basename( __FILE__ ) ) ) {
                    die( 'Failed security check' );
                }
            }
            $opfw_orders_filter = ( isset( $_GET['opfw_orders_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['opfw_orders_filter'] ) ) : '' );
            // Filter picking statuses.
            if ( '' !== $opfw_orders_filter ) {
                $query->query_vars['meta_query'][] = array(array(
                    'key'     => '_opfw_picking_status',
                    'value'   => $opfw_orders_filter,
                    'compare' => '=',
                ));
            }
        }
    }

    /**
     * Order picking link and badge in order page.
     *
     * @param object $order order.
     * @return void
     */
    public function woocommerce_admin_order_data_after_order_details( $order ) {
        $picking = new Order_Picking_For_Woocommerce_Order_Picking();
        $order_id = $order->get_id();
        echo '<div id="opfw_admin_order_page_badge" style="display:none;">' . $picking->get_order_badge( $order_id, 'thin' );
        if ( $picking->get_order_picking_permission( $order ) ) {
            echo ' <a href="#" data-order="' . esc_attr( $order_id ) . '" id="opfw_picking_order">' . esc_html( __( 'Order picking', 'order-picking-for-woocommerce' ) ) . '</a>
			<div id="opfw_loading_div" style="display:none;"><div class="opfw_loading_content"><h2>' . esc_attr( __( 'Order Picking', 'order-picking-for-woocommerce' ) ) . '</h2><p><span class="opfw_loader_icon"></span> ' . esc_attr( __( 'Loading orders, please wait.', 'order-picking-for-woocommerce' ) ) . '</p></div></div>
			';
        }
        echo '</div> ';
    }

    /**
     * Adds custom actions to the order actions dropdown.
     *
     * @param array    $actions The existing order actions.
     * @param WC_Order $order   The order object.
     * @return array Updated order actions.
     */
    public function add_custom_order_action( $actions, $order ) {
        // Add custom action to the select dropdown
        $actions['mark_awaiting_fulfillment'] = __( 'Mark as awaiting fulfillment', 'order-picking-for-woocommerce' );
        $actions['mark_cancel_fulfillment'] = __( 'Cancel fulfillment', 'order-picking-for-woocommerce' );
        return $actions;
    }

    /**
     * Handles the "mark_awaiting_fulfillment" order action.
     *
     * @param WC_Order $order The order object.
     */
    public function order_action_mark_awaiting_fulfillment( $order ) {
        $order_id = $order->get_id();
        $picking = new Order_Picking_For_Woocommerce_Order_Picking();
        $picking->set_order_picking( $order_id, 'awaiting_fulfillment' );
    }

    /**
     * Handles the "mark_cancel_fulfillment" order action.
     *
     * @param WC_Order $order The order object.
     */
    public function order_action_mark_cancel_fulfillment( $order ) {
        $order_id = $order->get_id();
        $picking = new Order_Picking_For_Woocommerce_Order_Picking();
        $picking->cancel_order_picking( $order_id );
    }

    /**
     * Bulk actions.
     *
     * @param string $redirect_to redirect to page.
     * @param string $action action type.
     * @param array  $post_ids orders.
     * @return string
     */
    public function handle_bulk_actions_edit_shop_order( $redirect_to, $action, $post_ids ) {
        if ( 'mark_awaiting_fulfillment' === $action ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            foreach ( $post_ids as $post_id ) {
                $picking->set_order_picking( $post_id, 'awaiting_fulfillment' );
            }
        }
        if ( 'mark_cancel_fulfillment' === $action ) {
            $picking = new Order_Picking_For_Woocommerce_Order_Picking();
            foreach ( $post_ids as $post_id ) {
                $picking->cancel_order_picking( $post_id );
            }
        }
        return $redirect_to;
    }

    /**
     * Order action options.
     *
     * @param array $actions actions options.
     * @return array
     */
    public function bulk_actions_edit_shop_order( $actions ) {
        $actions['mark_awaiting_fulfillment'] = __( 'Mark as awaiting fulfillment', 'order-picking-for-woocommerce' );
        $actions['mark_cancel_fulfillment'] = __( 'Cancel fulfillment', 'order-picking-for-woocommerce' );
        return $actions;
    }

}
