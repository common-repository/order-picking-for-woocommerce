<?php

/**
 * Plugin reports.
 *
 * All the reports functions.
 *
 * @package    OPFW
 * @subpackage OPFW/includes
 * @author     powerfulwp <cs@powerfulwp.com>
 */
if ( !class_exists( 'Order_Picking_For_Woocommerce_Order_Reports' ) ) {
    /**
     * Plugin Reports.
     *
     * All the screens functions.
     *
     * @package    OPFW
     * @subpackage OPFW/includes
     * @author     powerfulwp <cs@powerfulwp.com>
     */
    class Order_Picking_For_Woocommerce_Order_Reports {
        /**
         * Report links.
         *
         * @return array
         */
        public function report_links() {
            return array(
                'by_order'                 => esc_html__( 'Order Fulfillment Report', 'order-picking-for-woocommerce' ),
                'by_product'               => esc_html__( 'Product Fulfillment Report', 'order-picking-for-woocommerce' ),
                'by_order_product'         => esc_html__( 'Order and Product Fulfillment Report', 'order-picking-for-woocommerce' ),
                'by_missing_order_product' => esc_html__( 'Missing Order and Product Report', 'order-picking-for-woocommerce' ),
                'by_missing_order'         => esc_html__( 'Missing Order Items Report', 'order-picking-for-woocommerce' ),
                'by_missing_product'       => esc_html__( 'Missing Items Report', 'order-picking-for-woocommerce' ),
            );
        }

    }

}