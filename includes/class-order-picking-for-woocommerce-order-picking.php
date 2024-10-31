<?php

/**
 * The file that defines the order picking class
 *
 * @link       https://powerfulwp.com
 * @since      1.0.0
 *
 * @package    Order_Picking_For_Woocommerce
 * @subpackage Order_Picking_For_Woocommerce/includes
 */
if ( !class_exists( 'Order_Picking_For_Woocommerce_Order_Picking' ) ) {
    /**
     * The order picking class
     *
     * @since      1.0.0
     * @package    Order_Picking_For_Woocommerce
     * @subpackage Order_Picking_For_Woocommerce/includes
     * @author     powerfulwp <apowerfulwp@gmail.com>
     */
    class Order_Picking_For_Woocommerce_Order_Picking {
        /**
         * The statuses of this plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      array    $statuses    The string used to uniquely identify the plugin statuses.
         */
        public $statuses;

        /**
         * Define the core functionality of the plugin.
         *
         * @since 1.0.0
         */
        public function __construct() {
            $this->statuses = array(
                'unfulfillment'        => esc_js( __( 'Unfulfillment', 'order-picking-for-woocommerce' ) ),
                'partially_fulfilled'  => esc_js( __( 'Partially Fulfilled', 'order-picking-for-woocommerce' ) ),
                'fulfilled'            => esc_js( __( 'Fulfilled', 'order-picking-for-woocommerce' ) ),
                'awaiting_fulfillment' => esc_js( __( 'Awaiting fulfillment', 'order-picking-for-woocommerce' ) ),
            );
        }

        /**
         * Get picking status by key.
         *
         * @param string $key status key.
         * @return string
         */
        public function get_picking_status( $key ) {
            if ( '' !== $key && false !== $key ) {
                return $this->statuses[$key];
            }
            return '';
        }

        /**
         * Cancel order picking.
         *
         * @param int $order_id order number.
         * @return array
         */
        public function cancel_order_picking( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( !$order ) {
                return json_encode( array(
                    'order_id' => $order_id,
                    'status'   => 'failed',
                ) );
            }
            // Delete order picking status.
            $order->delete_meta_data( '_opfw_picking_status' );
            $order->save();
            // Delete products picking status.
            $this->delete_item_picking_status( $order_id );
            return json_encode( array(
                'order_id' => $order_id,
                'status'   => 'success',
            ) );
        }

        /**
         * Delete item picking status.
         *
         * @param int $order_id order number.
         * @return void
         */
        public function delete_item_picking_status( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $items = $order->get_items();
                if ( !empty( $items ) ) {
                    // Delete products picking status.
                    foreach ( $items as $item_id => $item_data ) {
                        wc_delete_order_item_meta( $item_id, '_opfw_picking' );
                        wc_delete_order_item_meta( $item_id, '_opfw_collected' );
                    }
                }
            }
        }

        /**
         * Set order picking function.
         *
         * @param int    $order_id order number.
         * @param string $status status key.
         * @return array
         */
        public function set_order_picking( $order_id, $status ) {
            $order = wc_get_order( $order_id );
            $order->update_meta_data( '_opfw_picking_status', $status );
            $order->save();
            // On awaiting fulfillment delete products picking status.
            if ( 'awaiting_fulfillment' === $status ) {
                $this->delete_item_picking_status( $order_id );
            }
            return json_encode( array(
                'order_id' => $order_id,
                'status'   => $status,
            ) );
        }

        /**
         * Set picking function.
         *
         * @param int    $product_id product number.
         * @param string $status status key.
         * @param int    $quantity picking quantity.
         * @param string $note picking note.
         * @return array
         */
        public function set_picking(
            $product_id,
            $status,
            $quantity,
            $note
        ) {
            $current_user = wp_get_current_user();
            $picking_array = array(
                'status'   => $status,
                'quantity' => $quantity,
                'note'     => $note,
                'user'     => $current_user->display_name,
                'date'     => date( get_option( 'date_format' ) ) . ' ' . date( get_option( 'time_format' ) ),
            );
            wc_update_order_item_meta( $product_id, '_opfw_picking', $picking_array );
            wc_update_order_item_meta( $product_id, '_opfw_collected', $quantity );
            return json_encode( $picking_array );
        }

        /**
         * Get order picking button function
         *
         * @param string $type section.
         * @return html
         */
        public function get_order_picking_button( $type ) {
            $loading_div = '<div id="opfw_loading_div" style="display:none;"><div class="opfw_loading_content"><h2>' . esc_attr( __( 'Order Picking', 'order-picking-for-woocommerce' ) ) . '</h2><p><span class="opfw_loader_icon"></span> ' . esc_attr( __( 'Loading orders, please wait.', 'order-picking-for-woocommerce' ) ) . '</p></div></div>';
            $data_order = ( opfw_is_hpos_enabled() ? 'id[]' : 'post[]' );
            if ( 'admin_orders_list' === $type ) {
                return '<div class="alignright actions custom">
			<button 
			type="button" 
			name="opfw_picking_orders" 
			data-alert="' . esc_attr( __( 'Please choose orders.', 'order-picking-for-woocommerce' ) ) . '" 
			data-order="' . esc_attr( $data_order ) . '"  
			id="opfw_picking_orders" 
			style="height:32px;" 
			class="button button-secondary" 
			value="">
			' . esc_html( __( 'Order Picking', 'order-picking-for-woocommerce' ) ) . '
			</button>
			</div>' . $loading_div;
            }
        }

        /**
         * Get order phone number.
         *
         * @param object $order order.
         * @return html
         */
        public function get_order_phone_number( $order ) {
            $billing_phone = $order->get_billing_phone();
            return '<div class="opfw_billing_phone">
					<span class="opfw_label">' . esc_html( __( 'Phone', 'order-picking-for-woocommerce' ) ) . ': 
						<a class="opfw_phone_number"  href="tel:' . esc_attr( $billing_phone ) . '"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="phone" class="svg-inline--fa fa-phone fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M493.4 24.6l-104-24c-11.3-2.6-22.9 3.3-27.5 13.9l-48 112c-4.2 9.8-1.4 21.3 6.9 28l60.6 49.6c-36 76.7-98.9 140.5-177.2 177.2l-49.6-60.6c-6.8-8.3-18.2-11.1-28-6.9l-112 48C3.9 366.5-2 378.1.6 389.4l24 104C27.1 504.2 36.7 512 48 512c256.1 0 464-207.5 464-464 0-11.2-7.7-20.9-18.6-23.4z"></path></svg> ' . esc_html( $billing_phone ) . '</a>
					</span>
				</div>';
        }

        /**
         * Get order address.
         *
         * @param object $order order object.
         * @param string $type address type.
         * @return string
         */
        public function get_order_address( $order, $type ) {
            if ( !$order ) {
                return '';
            }
            $billing_address_1 = $order->get_billing_address_1();
            $billing_address_2 = $order->get_billing_address_2();
            $billing_city = $order->get_billing_city();
            $billing_postcode = $order->get_billing_postcode();
            $billing_first_name = $order->get_billing_first_name();
            $billing_last_name = $order->get_billing_last_name();
            $billing_company = $order->get_billing_company();
            $billing_country = $order->get_billing_country();
            $billing_state = $order->get_billing_state();
            if ( '' !== $billing_country ) {
                $billing_country = WC()->countries->countries[$billing_country];
            }
            if ( 'shipping' === $type ) {
                $shipping_first_name = $order->get_shipping_first_name();
                $shipping_last_name = $order->get_shipping_last_name();
                $shipping_address_1 = $order->get_shipping_address_1();
                $shipping_address_2 = $order->get_shipping_address_2();
                $shipping_city = $order->get_shipping_city();
                $shipping_postcode = $order->get_shipping_postcode();
                $shipping_company = $order->get_shipping_company();
                $shipping_country = $order->get_shipping_country();
                $shipping_state = $order->get_shipping_state();
                if ( '' !== $shipping_country ) {
                    $shipping_country = WC()->countries->countries[$shipping_country];
                }
            }
            if ( 'shipping' === $type ) {
                /**
                 * If shipping info is missing if show the billing info.
                 */
                if ( '' === $shipping_first_name && '' === $shipping_address_1 ) {
                    $shipping_first_name = $billing_first_name;
                    $shipping_last_name = $billing_last_name;
                    $shipping_address_1 = $billing_address_1;
                    $shipping_address_2 = $billing_address_2;
                    $shipping_city = $billing_city;
                    $shipping_state = $billing_state;
                    $shipping_postcode = $billing_postcode;
                    $shipping_country = $billing_country;
                    $shipping_company = $billing_company;
                }
                $array = array(
                    'first_name' => $shipping_first_name,
                    'last_name'  => $shipping_last_name,
                    'company'    => $shipping_company,
                    'street_1'   => $shipping_address_1,
                    'street_2'   => $shipping_address_2,
                    'city'       => $shipping_city,
                    'zip'        => $shipping_postcode,
                    'country'    => $shipping_country,
                    'state'      => $shipping_state,
                );
            }
            if ( 'billing' === $type ) {
                $array = array(
                    'first_name' => $billing_first_name,
                    'last_name'  => $billing_last_name,
                    'company'    => $billing_company,
                    'street_1'   => $billing_address_1,
                    'street_2'   => $billing_address_2,
                    'city'       => $billing_city,
                    'zip'        => $billing_postcode,
                    'country'    => $billing_country,
                    'state'      => $billing_state,
                );
            }
            return $this->format_address( 'address', $array );
        }

        /**
         * Format address.
         *
         * @since 1.0.0
         * @param string $format address format.
         * @param array  $array address array.
         * @return string
         */
        public function format_address( $format, $array ) {
            $address_1 = $array['street_1'];
            $address_2 = $array['street_2'];
            $city = $array['city'];
            $postcode = $array['zip'];
            $country = $array['country'];
            $state = $array['state'];
            if ( 'array' === $format ) {
                return $array;
            }
            if ( 'address_line' === $format ) {
                // Show state only for USA.
                if ( 'US' !== $array['country'] && 'United States (US)' !== $array['country'] ) {
                    $state = '';
                }
                $address = $address_1 . ', ';
                $address .= $city;
                if ( !empty( $state ) || !empty( $postcode ) ) {
                    $address .= ', ';
                }
                if ( !empty( $state ) ) {
                    $address .= $state . ' ';
                }
                if ( !empty( $postcode ) ) {
                    $address .= $postcode . ' ';
                }
                if ( !empty( $country ) ) {
                    $address .= ' ' . $country;
                }
                $address = str_replace( '  ', ' ', trim( $address ) );
                return $address;
            }
            if ( 'address' === $format ) {
                // Format address.
                // Show state only for USA.
                if ( 'US' !== $array['country'] && 'United States (US)' !== $array['country'] ) {
                    $state = '';
                }
                $address = '';
                if ( !empty( $array['first_name'] ) ) {
                    $first_name = $array['first_name'];
                    $last_name = $array['last_name'];
                    $address = $first_name . ' ' . $last_name . '<br>';
                }
                if ( !empty( $array['company'] ) ) {
                    $address .= $array['company'] . '<br>';
                }
                $address .= $address_1;
                if ( !empty( $address_2 ) ) {
                    $address .= ', ' . $address_2 . ' ';
                }
                $address .= '<br>' . $city;
                if ( !empty( $state ) || !empty( $postcode ) ) {
                    $address .= ', ';
                }
                if ( !empty( $state ) ) {
                    $address .= $state . ' ';
                }
                if ( !empty( $postcode ) ) {
                    $address .= $postcode . ' ';
                }
                if ( !empty( $country ) ) {
                    $address .= '<br>' . $country;
                }
                return $address;
            }
        }

        /**
         * Get order badge.
         *
         * @param int    $order_id order number.
         * @param string $type badge type.
         * @return html
         */
        public function get_order_badge( $order_id, $type ) {
            $order = wc_get_order( $order_id );
            $badge_type = ( '' === $type ? '' : 'opfw_' . $type );
            $order_picking_status_key = $order->get_meta( '_opfw_picking_status' );
            $picking_status = $this->get_picking_status( $order_picking_status_key );
            $style = '';
            if ( '' === $picking_status ) {
                $style = 'style="display:none;"';
            }
            return '<span ' . $style . ' data-status="' . $order_picking_status_key . '" class="opfw_badge ' . $badge_type . ' opfw_badge_order_' . esc_attr( $order_id ) . '  opfw_badge_' . esc_attr( $order_picking_status_key ) . '" title="' . esc_attr( $picking_status ) . '"><span>' . esc_html( $picking_status ) . '</span></span>';
        }

        /**
         * Get order picking permission function
         *
         * @param object $order order.
         * @return boolean
         */
        public function get_order_picking_permission( $order ) {
            $opfw_picking_statuses = get_option( 'opfw_picking_statuses', '' );
            $order_status = 'wc-' . $order->get_status();
            if ( '' === $opfw_picking_statuses || in_array( $order_status, $opfw_picking_statuses, true ) ) {
                return true;
            }
            return false;
        }

        /**
         * Get awaiting fulfillment orders function.
         *
         * @return html
         */
        public function get_awaiting_fulfillment_orders() {
            $opfw_picking_statuses = get_option( 'opfw_picking_statuses', '' );
            $args = array(
                'status'         => $opfw_picking_statuses,
                'meta_key'       => '_opfw_picking_status',
                'meta_value'     => 'awaiting_fulfillment',
                'meta_compare'   => '=',
                'return'         => 'ids',
                'posts_per_page' => -1,
            );
            $orders = wc_get_orders( $args );
            if ( empty( $orders ) ) {
                echo '<p>' . esc_html__( 'No results found.', 'order-picking-for-woocommerce' ) . '</p>';
                return false;
            }
            echo $this->get_orders( $orders );
        }

        /**
         * Get orders function.
         *
         * @param array $orders orders list.
         * @return html
         */
        public function get_orders( $orders ) {
            $result = '';
            $counter = 0;
            foreach ( $orders as $order_id ) {
                $order_id = trim( $order_id );
                if ( '' !== $order_id ) {
                    $order = wc_get_order( $order_id );
                    if ( $order ) {
                        $order_number = $order->get_order_number();
                        if ( $this->get_order_picking_permission( $order ) ) {
                            $result .= '<div class="opfw_order" id="opfw_order_' . esc_attr( $order_id ) . '" data-order-id="' . esc_attr( $order_id ) . '">';
                            $result .= '<div class="opfw_order_sidebar">';
                            /* translators: %s: order number term */
                            $result .= '<h2 class="opfw_title">' . esc_html( sprintf( __( 'Order #%s', 'order-picking-for-woocommerce' ), $order_number ) ) . '</h2>';
                            $result .= $this->get_order_badge( $order_id, 'thin' );
                            $result .= '<div class="opfw_order_status">' . esc_html__( 'Status:', 'order-picking-for-woocommerce' ) . ' ' . $order->get_status() . '</div>';
                            $payment_method = $order->get_payment_method_title();
                            if ( !empty( $payment_method ) ) {
                                $result .= '<div class="opfw_order_payment">' . esc_html__( 'Payment via ', 'order-picking-for-woocommerce' ) . ' ' . $order->get_payment_method_title() . '</div>';
                            }
                            $result .= '<div class="opfw_product_quantity">' . esc_html( __( 'Quantity:', 'order-picking-for-woocommerce' ) ) . ' <span></span></div>';
                            $result .= '<div class="opfw_product_collected">' . esc_html( __( 'Collected:', 'order-picking-for-woocommerce' ) ) . ' <span></span></h2></div>';
                            $result .= '<div class="opfw_order_address"><b>' . esc_html( __( 'Billing Address:', 'order-picking-for-woocommerce' ) ) . '</b><br> <span>' . $this->get_order_address( $order, 'billing' ) . '</span></div>';
                            $result .= $this->get_order_phone_number( $order );
                            $result .= '<div class="opfw_order_address"><b>' . esc_html( __( 'Shipping Address:', 'order-picking-for-woocommerce' ) ) . '</b><br> <span>' . $this->get_order_address( $order, 'shipping' ) . '</span></div>';
                            /* translators: %s: status term */
                            $result .= '<div class="opfw_order_button"><button class="button button-primary btn btn-primary opfw_order_set_fulfillment" data-status="">' . sprintf( esc_html( __( 'Mark as %s', 'order-picking-for-woocommerce' ) ), '<span class="opfw_label">' . $this->statuses['awaiting_fulfillment'] ) . '</span></button></div>';
                            $result .= '<div class="opfw_order_button"><a href="#" class="opfw_order_cancel_fulfillment btn button btn-secondary" data-status="">' . esc_html( __( 'Cancel fulfillment', 'order-picking-for-woocommerce' ) ) . '</a></div>';
                            $result .= '</div>';
                            $result .= $this->get_order_items( $order );
                            $result .= '</div>';
                        } else {
                            $counter++;
                        }
                    }
                }
            }
            if ( 0 < $counter ) {
                /* translators: %s: order count */
                $result = '<div class="opfw_picking_notes">' . esc_html( sprintf( __( 'Note: %s orders cannot be picked as they are not included in the fulfillment statuses or are not marked as "Awaiting fulfillment"', 'order-picking-for-woocommerce' ), $counter ) ) . '</div>' . $result;
            }
            return $result;
        }

        /**
         * Retrieves the URL of the category image for a given category ID.
         *
         * @param int $category_id The ID of the category.
         * @return string The URL of the category image.
         */
        public function get_category_image_url( $category_id ) {
            $thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );
            if ( $thumbnail_id ) {
                $image = wp_get_attachment_thumb_url( $thumbnail_id );
            } else {
                $image = wc_placeholder_img_src();
            }
            return $image;
        }

        /**
         * Order items.
         *
         * @since 1.0.0
         * @param object $order order data.
         * @return html
         */
        private function get_order_items( $order ) {
            if ( !$order ) {
                return '';
            }
            $items = $order->get_items();
            $weight = 0;
            $hidden_order_itemmeta = array();
            $products_html = '<div class="opfw_products">';
            if ( !empty( $items ) ) {
                $allowed_html = opfw_allowed_html();
                $hidden_order_itemmeta = apply_filters( 'woocommerce_hidden_order_itemmeta', array(
                    '_qty',
                    '_tax_class',
                    '_product_id',
                    '_variation_id',
                    '_line_subtotal',
                    '_line_subtotal_tax',
                    '_line_total',
                    '_line_tax',
                    'method_id',
                    'cost',
                    '_reduced_stock',
                    '_wc_cog_item_cost',
                    '_wc_cog_item_total_cost',
                    '_wcfmmp_order_item_processed',
                    'method_slug',
                    'vendor_id'
                ) );
                foreach ( $items as $item_id => $item_data ) {
                    $product_id = $item_data['product_id'];
                    $variation_id = $item_data['variation_id'];
                    $product_description = '';
                    $product_sort_description = '';
                    $product = false;
                    $product_image = '';
                    $product_sku = '';
                    if ( null !== $product_id && 0 !== $product_id ) {
                        if ( 0 !== $variation_id ) {
                            $product = wc_get_product( $variation_id );
                            if ( false !== $product ) {
                                $product_description = $product->get_description();
                                $product_image = $product->get_image();
                                $product_sku = $product->get_sku();
                                $product_sort_description = $product->get_short_description();
                                $product_id = $variation_id;
                            }
                        } else {
                            $product = wc_get_product( $product_id );
                            if ( false !== $product ) {
                                $product_description = $product->get_description();
                                $product_sort_description = $product->get_short_description();
                                $product_image = $product->get_image();
                                $product_sku = $product->get_sku();
                            }
                        }
                    }
                    $item_name = $item_data['name'];
                    $item_quantity = wc_get_order_item_meta( $item_id, '_qty', true );
                    $opfw_picking_status = '';
                    $opfw_picking = wc_get_order_item_meta( $item_id, '_opfw_picking', true );
                    $opfw_note = sprintf(
                        /* translators: 1: number of products 2: user name 3:date 4:note */
                        esc_html( __( '%1$s product/s picked by %2$s on %3$s %4$s', 'order-picking-for-woocommerce' ) ),
                        '<span class="opfw_quantity"></span>',
                        '<span class="opfw_user"></span>',
                        '<span class="opfw_date"></span>',
                        '<span class="opfw_note_text"></span>'
                    );
                    $item_collected = 0;
                    if ( is_array( $opfw_picking ) ) {
                        $opfw_picking_status = $opfw_picking['status'];
                        $item_collected = ( !empty( $opfw_picking['quantity'] ) ? str_replace( ' ', '', $opfw_picking['quantity'] ) : 0 );
                        $opfw_note_text = ( !empty( $opfw_picking['note'] ) ? '<span>' . $opfw_picking['note'] . '</span>' : '' );
                        $opfw_note = sprintf(
                            /* translators: 1: number of products 2: user name 3:date 4:note */
                            esc_html( __( '%1$s product/s picked by %2$s on %3$s %4$s', 'order-picking-for-woocommerce' ) ),
                            '<span class="opfw_quantity">' . $opfw_picking['quantity'] . '</span>',
                            '<span class="opfw_user">' . $opfw_picking['user'] . '</span>',
                            '<span class="opfw_date">' . $opfw_picking['date'] . '</span>',
                            '<span class="opfw_note_text">' . $opfw_note_text . '</span>'
                        );
                    }
                    // Product weight.
                    if ( false !== $product ) {
                        if ( !$product->is_virtual() ) {
                            if ( is_numeric( $product->get_weight() ) && is_numeric( $item_quantity ) ) {
                                $weight += $product->get_weight() * $item_quantity;
                            }
                        }
                    }
                    // Product image.
                    $product_image = '<div class="opfw_item_image">' . $product_image . '</div>';
                    $product_image_with_info = '<div class="opfw_item_image">' . $product_image . ' 
					<a href="#" class="opfw_undo_icon" style="display:none" data-status="' . $opfw_picking_status . '">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M48.5 224H40c-13.3 0-24-10.7-24-24V72c0-9.7 5.8-18.5 14.8-22.2s19.3-1.7 26.2 5.2L98.6 96.6c87.6-86.5 228.7-86.2 315.8 1c87.5 87.5 87.5 229.3 0 316.8s-229.3 87.5-316.8 0c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0c62.5 62.5 163.8 62.5 226.3 0s62.5-163.8 0-226.3c-62.2-62.2-162.7-62.5-225.3-1L185 183c6.9 6.9 8.9 17.2 5.2 26.2s-12.5 14.8-22.2 14.8H48.5z"/></svg>
					</a>
				</div>
				';
                    // Product name.
                    $product_name = '<div class="opfw_item_name">' . $item_name . '</div>';
                    // Product SKU.
                    if ( '' !== $product_sku ) {
                        $product_sku = '<div class="opfw_item_sku">' . esc_html( __( 'SKU:', 'order-picking-for-woocommerce' ) ) . ' ' . $product_sku . '</div>';
                    }
                    // Product quantity.
                    $product_quantity = '<div class="opfw_item_quantity">' . esc_html( __( 'Quantity:', 'order-picking-for-woocommerce' ) ) . ' ' . $item_quantity . '</div>';
                    // Add Meta data to product.
                    $product_variation = '';
                    $meta_data = $item_data->get_formatted_meta_data( '' );
                    if ( !empty( $meta_data ) ) {
                        $product_variation .= '<div class="opfw_item_variation">';
                        foreach ( $meta_data as $meta_id => $meta ) {
                            if ( in_array( $meta->key, $hidden_order_itemmeta, true ) || '' !== $meta->display_key && '_' === $meta->display_key[0] ) {
                                continue;
                            }
                            $product_variation .= wp_kses_post( $meta->display_key ) . ': ' . wp_kses_post( force_balance_tags( $meta->display_value ) ) . '<br>';
                        }
                        $product_variation .= '</div>';
                    }
                    $product_form = '<form style="display:none" name="opfw_product_form" class="opfw_product_form">
				<label>' . esc_html( __( 'How many items did you collect?', 'order-picking-for-woocommerce' ) ) . '</label>	
				<input type="text" name="opfw_quantity" class="opfw_quantity" value="0">
				<label>' . esc_html( __( 'Note', 'order-picking-for-woocommerce' ) ) . '</label>	
				<textarea name="opfw_note" class="opfw_note"></textarea>
					<button type="button" class="btn btn-primary button button-primary send" data-status="unfulfillment">' . esc_html( __( 'Send', 'order-picking-for-woocommerce' ) ) . '</button>
					<button type="button" class="btn btn-secondary button button-secondary cancel">' . esc_html( __( 'Cancel', 'order-picking-for-woocommerce' ) ) . '</button>
				</form>';
                    $product_icons = '<div class="opfw_product_icons ">
								<div class="opfw_product_icon opfw_picked">
									<a href="#" class="opfw_picked_icon" data-status="fulfilled">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M243.8 339.8C232.9 350.7 215.1 350.7 204.2 339.8L140.2 275.8C129.3 264.9 129.3 247.1 140.2 236.2C151.1 225.3 168.9 225.3 179.8 236.2L224 280.4L332.2 172.2C343.1 161.3 360.9 161.3 371.8 172.2C382.7 183.1 382.7 200.9 371.8 211.8L243.8 339.8zM512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256zM256 48C141.1 48 48 141.1 48 256C48 370.9 141.1 464 256 464C370.9 464 464 370.9 464 256C464 141.1 370.9 48 256 48z"/></svg>
									</a>
								</div>
								<div class="opfw_product_icon opfw_unpicked">
									<a href="#" class="opfw_unpicked_icon" data-status="unfulfillment">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M175 175C184.4 165.7 199.6 165.7 208.1 175L255.1 222.1L303 175C312.4 165.7 327.6 165.7 336.1 175C346.3 184.4 346.3 199.6 336.1 208.1L289.9 255.1L336.1 303C346.3 312.4 346.3 327.6 336.1 336.1C327.6 346.3 312.4 346.3 303 336.1L255.1 289.9L208.1 336.1C199.6 346.3 184.4 346.3 175 336.1C165.7 327.6 165.7 312.4 175 303L222.1 255.1L175 208.1C165.7 199.6 165.7 184.4 175 175V175zM512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256zM256 48C141.1 48 48 141.1 48 256C48 370.9 141.1 464 256 464C370.9 464 464 370.9 464 256C464 141.1 370.9 48 256 48z"/></svg>
									</a>
								</div>
								
								</div>';
                    $product_lighbox = '<div class="opfw_lightbox opfw_product_lightbox" style="display:none">
											 		
											<div class="opfw_lightbox_wrap">
											<a href="#" class="opfw_lightbox_close">×</a>		
											
											<div class="opfw_product_image">' . $product_image . '</div>
											 <h2 class="opfw_product_title">' . $item_name . '</h2>
											 <p class="opfw_product_sku">' . wp_strip_all_tags( $product_sku ) . '</p>
											 ' . $product_variation . '
											 <div class="opfw_product_short_description">' . wc_format_content( wp_kses( force_balance_tags( $product_sort_description ), $allowed_html ) ) . '</div>
											 <div class="opfw_product_description">' . wc_format_content( wp_kses( force_balance_tags( $product_description ), $allowed_html ) ) . '</div>
											 </div>
										 
								</div>
							';
                    $opfw_picking_class = ( '' !== strval( $opfw_picking_status ) ? 'picking_done' : '' );
                    $products_html .= '
							<div class="opfw_product_box ' . $opfw_picking_class . ' opfw_picking_status_' . $opfw_picking_status . '" data-product-id = "' . $item_id . '" data-quantity = "' . $item_quantity . '"  data-collected = "' . $item_collected . '">
							<div class="opfw_product_box_wrap">
								' . $product_image_with_info . '
								' . $product_name . '
								' . $product_sku . '
								' . $product_quantity . '
								' . $product_variation . '
								' . $product_icons . '
								' . $product_form . '
							</div>
							<div class="opfw_product_overlay" style="display:none">
								<a href="#" class="opfw_fulfilled" style="display:none" ><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M243.8 339.8C232.9 350.7 215.1 350.7 204.2 339.8L140.2 275.8C129.3 264.9 129.3 247.1 140.2 236.2C151.1 225.3 168.9 225.3 179.8 236.2L224 280.4L332.2 172.2C343.1 161.3 360.9 161.3 371.8 172.2C382.7 183.1 382.7 200.9 371.8 211.8L243.8 339.8zM512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256zM256 48C141.1 48 48 141.1 48 256C48 370.9 141.1 464 256 464C370.9 464 464 370.9 464 256C464 141.1 370.9 48 256 48z"></path></svg></a>
								<a href="#" class="opfw_unfulfillment" style="display:none" ><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M175 175C184.4 165.7 199.6 165.7 208.1 175L255.1 222.1L303 175C312.4 165.7 327.6 165.7 336.1 175C346.3 184.4 346.3 199.6 336.1 208.1L289.9 255.1L336.1 303C346.3 312.4 346.3 327.6 336.1 336.1C327.6 346.3 312.4 346.3 303 336.1L255.1 289.9L208.1 336.1C199.6 346.3 184.4 346.3 175 336.1C165.7 327.6 165.7 312.4 175 303L222.1 255.1L175 208.1C165.7 199.6 165.7 184.4 175 175V175zM512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256zM256 48C141.1 48 48 141.1 48 256C48 370.9 141.1 464 256 464C370.9 464 464 370.9 464 256C464 141.1 370.9 48 256 48z"></path></svg></a>
								<a href="#" class="opfw_partially_fulfilled" style="display:none" ><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M96 64c0-17.7-14.3-32-32-32S32 46.3 32 64V320c0 17.7 14.3 32 32 32s32-14.3 32-32V64zM64 480c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40z"/></svg></a>
							</div>
							<div class="opfw_picking_note">' . $opfw_note . '</div>
							<a href="#" class="opfw_info_icon" >
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-144c-17.7 0-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32z"/></svg>
						</a>
						' . $product_lighbox . '
							</div>
						';
                }
                $products_html .= '</div>';
                return $products_html;
            }
        }

    }

}