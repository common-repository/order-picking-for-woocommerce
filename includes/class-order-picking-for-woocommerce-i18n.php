<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/includes
 */
if ( ! class_exists( 'Order_Picking_For_Woocommerce_I18n' ) ) {
	/**
	 * Define the internationalization functionality.
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @since      1.0.0
	 * @package    Order_Picking_For_Woocommerce
	 * @subpackage Order_Picking_For_Woocommerce/includes
	 * @author     powerfulwp <apowerfulwp@gmail.com>
	 */
	class Order_Picking_For_Woocommerce_I18n {


		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain(
				'order-picking-for-woocommerce',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);

		}



	}
}
