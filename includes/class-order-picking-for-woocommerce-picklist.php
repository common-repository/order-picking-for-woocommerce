<?php

/**
 * Plugin picklist.
 *
 * All the picklist functions.
 *
 * @package    OPFW
 * @subpackage OPFW/includes
 * @author     powerfulwp <cs@powerfulwp.com>
 */
if ( !class_exists( 'Order_Picking_For_Woocommerce_Order_Picklist' ) ) {
    /**
     * Plugin Picklist.
     *
     * All the screens functions.
     *
     * @package    OPFW
     * @subpackage OPFW/includes
     * @author     powerfulwp <cs@powerfulwp.com>
     */
    class Order_Picking_For_Woocommerce_Order_Picklist {
        /**
         * Report links.
         *
         * @return void
         */
        public function picklist_links() {
            return array(
                'by_order'    => esc_html__( 'Picklist by Order', 'order-picking-for-woocommerce' ),
                'by_product'  => esc_html__( 'Picklist by Product', 'order-picking-for-woocommerce' ),
                'by_category' => esc_html__( 'Picklist by Category', 'order-picking-for-woocommerce' ),
            );
        }

    }

}