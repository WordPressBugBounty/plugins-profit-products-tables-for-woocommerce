<?php
/**
 * Template for generating of button HTML "Add to Cart"
 * 
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (is_object($product)) {
    switch ($product_type) {
        case 'grouped':
            $is = $product->is_purchasable() && $product->is_in_stock();
            if ($product->managing_stock()) {
                $is = boolval($product->get_stock_quantity());
            }
            if ($is) {
                ?><span class="woot-button-buy-pack" data-about="grouped,<?php echo intval($product->get_id()) ?>,<?php echo esc_attr($product->get_title()) ?>,<?php echo count($product->get_children()) ?>"></span><?php
            }
            break;

        case 'variable':
            $is = $product->is_purchasable() && $product->is_in_stock();
            if ($product->managing_stock()) {
                $is = boolval($product->get_stock_quantity());
            }
            if ($is) {
                ?><span class="woot-button-buy-pack" data-sold-individually="<?php echo intval($product->is_sold_individually()) ?>" data-about="variable,<?php echo intval($product->get_id()) ?>,<?php echo esc_attr($product->get_title()) ?>,<?php echo count($product->get_children()) ?>"></span><?php
            }

            break;

        case 'simple':
            $is = $product->is_purchasable() && $product->is_in_stock();
            if ($product->managing_stock()) {
                $is = boolval($product->get_stock_quantity());
            }
            if ($is) {
                ?><span class="woot-button-buy-pack" data-instant-redirect="<?php echo esc_attr($intstant_redirect) ?>" data-sold-individually="<?php echo intval($product->is_sold_individually()) ?>" data-about="simple,<?php echo intval($product->get_id()) ?>,<?php echo esc_attr($product->get_stock_quantity()) ?>,<?php echo esc_attr($product->get_sku()) ?>"></span><?php
                }

                break;

            case 'external':
                ?><span class="woot-button-buy-pack" data-about="external,<?php echo esc_url($product->add_to_cart_url()) ?>,<?php echo esc_attr($product->add_to_cart_text()) ?>"></span><?php
            break;


        default:
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- third-party filters return ready-made add-to-cart markup by design
            echo apply_filters('woot_woocommerce_add_to_cart', $product);
            break;
    }
}
