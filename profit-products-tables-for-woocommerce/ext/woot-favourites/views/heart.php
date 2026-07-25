<?php
/**
 * Template for generating Heart
 * 
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="woot-favourites-heart-wrapper woot-favourites-heart-wrapper-<?php echo $post_id ?> <?php if ($selected): ?>woot-favourites-active<?php endif; ?>" onclick="return woot_favourites.toggle(this, <?php echo $post_id ?>)">
    <div class="woot-favourites-heart">
        <div class="woot-favourites-tl"></div>
        <div class="woot-favourites-tr"></div>
        <div class="woot-favourites-bl"></div>
        <div class="woot-favourites-br"></div>
    </div>
    <div class="woot-favourites-ring"></div>
    <div class="woot-favourites-circles"></div>
</div>

<a href="javascript: void(0);" data-key="favourites" class="woot-favourites-btn"><?php echo WOOT_Vocabulary::get(esc_html__('My Favourites', 'woot-favourites')) ?></a><br />