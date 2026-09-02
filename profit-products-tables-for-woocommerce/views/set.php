<?php
/**
 * Template for generating HTML structure of some tables in the tabs
 * 
 * @see https://products-tables.com/codex/
 * @version 1.0.0
 */
if (!defined('ABSPATH'))
	die('No direct access allowed');

$ids = [];
?>

<?php if (!empty($shortcodes)): ?>
	<div class="woot-tables-set">
		<!------------------------ tabs -------------------------------->
		<?php foreach ($shortcodes as $c => $sh): $ids[$c] = uniqid('t') ?>
			<a onclick="woot_show_tab(event, '<?php echo esc_js($ids[$c]) ?>')" data-tab-id="<?php echo esc_attr($ids[$c]) ?>" class="woot-btn woot-tab-link <?php if ($c === 0): ?>woot-tab-link-current<?php endif; ?>" href="javascript: void(0);"><?php echo esc_html($sh['title']) ?></a>
		<?php endforeach; ?>

		<!------------------------ content ----------------------------->
		<?php foreach ($shortcodes as $c => $sh): ?>
			<?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $sh['content'] is rendered table markup produced by do_shortcode() ?>
			<div id="<?php echo esc_attr($ids[$c]) ?>" class="woot-tab-content <?php if ($c === 0): ?>woot-tab-content-current<?php else: ?>woot-tab-content-hidden<?php endif; ?>"><?php echo $sh['content'] ?></div>
			<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endforeach; ?>

	</div>
<?php endif; ?>