<?php

use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * Check for free version
 *
 * @since 1.1.2
 * @return boolean
 */
function opfw_is_free() {
    if ( opfw_fs()->is__premium_only() && opfw_fs()->can_use_premium_code() ) {
        return false;
    } else {
        return true;
    }
}

/**
 * Premium feature.
 *
 * @param string $value text.
 * @return html
 */
function opfw_admin_premium_feature(  $value  ) {
    $result = $value;
    if ( opfw_is_free() ) {
        $result = '<div class="opfw_premium_feature">
						<a class="opfw_star_button" href="#"><svg style="color:#ffc106" width=20 aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" class=" opfw_premium_iconsvg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"> <title>' . esc_attr__( 'Premium Feature', 'order-picking-for-woocommerce' ) . '</title><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></a>
					  	<div class="opfw_premium_feature_note" style="display:none">
						  <a href="#" class="opfw_premium_close">
						  <svg aria-hidden="true"  width=10 focusable="false" data-prefix="fas" data-icon="times" class="svg-inline--fa fa-times fa-w-11" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><path fill="currentColor" d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg></a>
						  <h2>' . esc_html( __( 'Premium Feature', 'order-picking-for-woocommerce' ) ) . '</h2>
						  <p>' . esc_html( __( 'You Discovered a Premium Feature!', 'order-picking-for-woocommerce' ) ) . '</p>
						  <p>' . esc_html( __( 'Upgrading to Premium will unlock it.', 'order-picking-for-woocommerce' ) ) . '</p>
						  <a target="_blank" href="https://powerfulwp.com/order-picking-for-woocommerce#pricing" class="opfw_premium_buynow">' . esc_html( __( 'UNLOCK PREMIUM', 'order-picking-for-woocommerce' ) ) . '</a>
						  </div>
					  </div>';
    }
    return $result;
}

function opfw_get_order_total_weight(  $order  ) {
    $weight = 0;
    if ( sizeof( $order->get_items() ) > 0 ) {
        foreach ( $order->get_items() as $item ) {
            if ( $item['product_id'] > 0 ) {
                $_product = $item->get_product();
                if ( !$_product->is_virtual() ) {
                    $weight += ( is_numeric( $_product->get_weight() ) ? $_product->get_weight() * $item['qty'] : 0 );
                }
            }
        }
    }
    return $weight;
}

/**
 * Allowed html.
 *
 * @return array
 */
function opfw_allowed_html() {
    $allowed_tags = array(
        'a'          => array(
            'href'   => array(),
            'target' => array(),
        ),
        'abbr'       => array(),
        'b'          => array(),
        'blockquote' => array(),
        'cite'       => array(),
        'code'       => array(),
        'del'        => array(),
        'dd'         => array(),
        'div'        => array(),
        'dl'         => array(),
        'dt'         => array(),
        'em'         => array(),
        'h1'         => array(),
        'h2'         => array(),
        'h3'         => array(),
        'h4'         => array(),
        'h5'         => array(),
        'h6'         => array(),
        'i'          => array(),
        'img'        => array(
            'alt'    => array(),
            'class'  => array(),
            'height' => array(),
            'src'    => array(),
            'width'  => array(),
        ),
        'li'         => array(),
        'ol'         => array(),
        'p'          => array(),
        'q'          => array(),
        'span'       => array(),
        'strike'     => array(),
        'strong'     => array(),
        'ul'         => array(),
    );
    return $allowed_tags;
}

/**
 * Determines whether HPOS is enabled.
 *
 * @return bool
 */
function opfw_is_hpos_enabled() : bool {
    if ( version_compare( get_option( 'woocommerce_version' ), '7.1.0' ) < 0 ) {
        return false;
    }
    if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
        return true;
    }
    return false;
}
