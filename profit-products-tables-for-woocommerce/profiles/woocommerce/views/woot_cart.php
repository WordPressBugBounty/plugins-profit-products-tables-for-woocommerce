<?php
/**
 * Template for generating HTML of shortcode [woot_cart]
 * 
 * @see https://products-tables.com/shortcode/woot_cart/
 * @version 1.0.0
 */
if (!defined('ABSPATH'))
    die('No direct access allowed');

$id = uniqid('woot-cart-c-');
?>

<div <?php if (isset($skin)): ?>id="<?php echo esc_attr($id) ?>" class="data-table-23 woot-data-table"<?php endif; ?>>
    <div class='woot-woocommerce-cart'>
        <a href='<?php echo esc_url(wc_get_checkout_url()) ?>' class='woot_cart_info woot-btn'>&nbsp;</a>
    </div>
    <div class="woot-clearfix"></div>
    <div class='woot-cart-shortcode'><div class="table23-place-loader"><?php echo esc_html(WOOT_Vocabulary::get(esc_html__('Cart loading', 'profit-products-tables-for-woocommerce'))) ?> ...</div></div>
</div>

<?php if (isset($skin)): ?>

    <style>
    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_theme_css() returns raw CSS; escaping it would corrupt the stylesheet
    echo woot()->skins->get_theme_css($skin, $id) ?>
    </style>

<?php endif; ?>
