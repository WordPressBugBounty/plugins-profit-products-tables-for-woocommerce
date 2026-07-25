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

<a href="javascript: woot_attachments.call_media_lib();void(0);" class="button woot-button"><span class="dashicons-before dashicons-plus"></span>&nbsp;<?php echo esc_html__('Add Files', 'woot-attachments') ?></a><br />
<br />
<div id="woot-attachments-content" data-post-id="<?php echo $post_id ?>"></div>

<div class='woot-notice'><?php echo esc_html__('For ordering files positions operation it is necessary condition display all them on one page! Select 100 items per page by the top selector.', 'woot-attachments') ?></div>

<em><?php printf(esc_html__('Use function %s to get attachments of the product or shortcode %s', 'woot-attachments'), '<code>woot_get_product_attachments($product_id)</code>', "[woot_attachments id={$post_id}]") ?></em>

