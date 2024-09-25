<?php
/**
 * Template for wrapping shortcode [woot] into <div> to simulate drop-down list
 * 
 * @see https://products-tables.com/shortcode/woot_drop_down/
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$table_html_id = uniqid('t-');
?>

<?php if (!woot()->show_notes) : ?>

    <div class="woot-woocommerce-text-search-container" style="width: <?php echo $width ?>;" data-table-id="<?php echo $table_html_id ?>">
        <input type="search" value="" autocomplete="off" placeholder="<?php echo $placeholder ?>" /><br />
        <div class="woot-woocommerce-text-search-wrapper" style="display: none; max-height: <?php echo $height ?>px; overflow: auto; overflow-x: hidden;"><?php echo do_shortcode("[woot id={$table_id} skin='{$skin}' table_html_id='{$table_html_id}' not_load_on_init=1]") ?></div>
    </div>

<?php else: ?>

    <div class="woot-notice">
        <?php echo esc_html__('WOOT: Premium feature', 'profit-products-tables-for-woocommerce') ?>, <a href="https://products-tables.com/downloads" target="_blank"><?php echo esc_html__('see details', 'profit-products-tables-for-woocommerce') ?></a>
    </div>

<?php endif; ?>

