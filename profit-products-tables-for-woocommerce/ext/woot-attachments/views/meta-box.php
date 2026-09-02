<?php
/**
 * Template for product attachments admin meta-box
 * 
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<a href="javascript: woot_attachments.call_media_lib();void(0);" class="button woot-button"><span class="dashicons-before dashicons-plus"></span>&nbsp;<?php echo esc_html__('Add Files', 'profit-products-tables-for-woocommerce') ?></a><br />
<br />
<div id="woot-attachments-content" data-post-id="<?php echo intval($post_id) ?>"></div>

<div class='woot-notice'><?php echo esc_html__('For ordering files positions operation it is necessary condition display all them on one page! Select 100 items per page by the top selector.', 'profit-products-tables-for-woocommerce') ?></div>

<em><?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- the first argument is a fixed <code> literal, the second is built from the integer post ID
/* translators: 1: PHP function name, 2: shortcode to use */
printf(esc_html__('Use function %1$s to get attachments of the product or shortcode %2$s', 'profit-products-tables-for-woocommerce'), '<code>woot_get_product_attachments($product_id)</code>', "[woot_attachments id=" . intval($post_id) . "]");
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
?></em>

