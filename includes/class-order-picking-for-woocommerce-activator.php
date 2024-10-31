<?php

/**
 * Fired during plugin activation
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/includes
 */
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/includes
 * @author     powerfulwp <apowerfulwp@gmail.com>
 */
class Order_Picking_For_Woocommerce_Activator {
    /**
     * Set plugin option.
     *
     * @since 1.0.0
     */
    public function opfw_set_options() {
        // Add default order picking status.
        add_option( 'opfw_picking_statuses', array('wc-processing') );
    }

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public function activate( $network_wide ) {
        if ( is_multisite() && $network_wide ) {
            // Run the code for all sites in a Multisite network.
            foreach ( get_sites( array(
                'fields' => 'ids',
            ) ) as $blog_id ) {
                switch_to_blog( $blog_id );
                $this->opfw_set_options();
            }
            restore_current_blog();
        } else {
            $this->opfw_set_options();
        }
    }

}
