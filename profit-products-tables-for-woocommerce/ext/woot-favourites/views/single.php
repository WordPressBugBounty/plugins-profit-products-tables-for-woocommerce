<?php
/**
 * Template for generating Heart [woot_favourites_single_btn]
 * 
 * @see https://products-tables.com/shortcode/woot_favourites_single_btn/
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="woot-my-facourites-single-container">
    <div class="woot-favourites-heart-wrapper woot-favourites-heart-wrapper-<?php echo intval($post_id) ?> <?php if ($selected): ?>woot-favourites-active<?php endif; ?>" onclick="return woot_favourites.toggle(this, <?php echo intval($post_id) ?>)">
        <div class="woot-favourites-heart">
            <div class="woot-favourites-tl"></div>
            <div class="woot-favourites-tr"></div>
            <div class="woot-favourites-bl"></div>
            <div class="woot-favourites-br"></div>
        </div>
        <div class="woot-favourites-ring"></div>
        <div class="woot-favourites-circles"></div>
    </div>
    <br />
    <a href="javascript: woot_favourites.click_action('<?php echo esc_js($popup_title) ?>', '<?php echo esc_js($help_title) ?>', '<?php echo esc_url($help_link) ?>', '<?php echo esc_js($skin) ?>'); void(0);" class="woot-my-facourites-single-btn"><?php echo esc_html(WOOT_Vocabulary::get(esc_html__('My Favourites', 'profit-products-tables-for-woocommerce'))) ?></a>

</div>
