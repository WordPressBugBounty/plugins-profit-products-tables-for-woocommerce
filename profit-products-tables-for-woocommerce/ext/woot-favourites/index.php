<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('WOOT_FAVOURITES_PATH', plugin_dir_path(__FILE__));
define('WOOT_FAVOURITES_LINK', plugin_dir_url(__FILE__));
define('WOOT_FAVOURITES_VERSION', '1.0.3');

include_once 'lib/storage.php';

final class WOOT_Favourites {

	private $storage = null;
	private $storage_key = 'woot_favourites';
	public $action = 'woot_favourites';
	public $setting_key = 'favourites_columns';
	public $default_fields = 'id,title,price,sku,add_to_cart,actions';

	public function __construct() {
		//lets plug in columns of the current profile
		global $woot_extend_ext_profiles;
		if (!isset($woot_extend_ext_profiles['woot_woocommerce_tables'])) {
			$woot_extend_ext_profiles['woot_woocommerce_tables'] = [];
		}
		$woot_extend_ext_profiles['woot_woocommerce_tables'][] = "{$this->action}_profile_extend";

		//***

		$this->storage = new WOOT_STORAGE();
		add_action('init', array($this, 'init'), 999);
		add_action('wp_ajax_woot_get_table_favourites', array($this, 'get_table'));
		add_action('wp_ajax_nopriv_woot_get_table_favourites', array($this, 'get_table'));

		add_action('wp_ajax_woot_add_to_favourites', array($this, 'add'));
		add_action('wp_ajax_nopriv_woot_add_to_favourites', array($this, 'add'));

		add_action('wp_ajax_woot_delete_from_favourites', array($this, 'delete'));
		add_action('wp_ajax_nopriv_woot_delete_from_favourites', array($this, 'delete'));

		//***

		add_action('woot_helper_lang', function ($words) {

			$words['my_favourites'] = WOOT_Vocabulary::get(esc_html__('My Favourites', 'profit-products-tables-for-woocommerce'));

			return $words;
		});

		//***

		add_action('woot_profile_extend', function ($profile, $action_name) {

			$profile['favourites'] = [
				'title' => esc_html__('Favourites', 'profit-products-tables-for-woocommerce'),
				'options' => ['css-background', 'css-media-hide'],
				'order' => FALSE,
				'action' => function ($post_id) {

					$selected_ids = $this->get_user_data();

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is generated and escaped inside render_html()
					return WOOT_HELPER::render_html(WOOT_FAVOURITES_PATH . 'views/heart.php', [
						'post_id' => $post_id,
						'selected' => in_array($post_id, $selected_ids)
						], false);
				}
			];

			return $profile;
		}, 10, 2);

		//***

		add_action('woot_extend_settings', function ($rows) {

			$selected = explode(',', WOOT_Settings::get($this->setting_key));
			//$options = ['actions' => esc_html__('Favourites actions', 'profit-products-tables-for-woocommerce')];

			foreach (apply_filters('woot_woocommerce_tables', 0, []) as $key => $value) {
				if (isset($value['title'])) {

					if ('favourites' === $key) {
						continue;
					}

					$options[$key] = $value['title'];
				}
			}

			//+++

			$rows[] = [
				'id' => 0,
				'title' => esc_html__('Favourites columns', 'profit-products-tables-for-woocommerce'),
				'value' => [
					'value' => WOOT_HELPER::draw_select([
						'class' => 'woot-multiple-select',
						'multiple' => '',
						'data-action' => 'woot_save_settings_field',
						'data-values' => WOOT_Settings::get($this->setting_key)
						], $options, $selected),
					'custom_field_key' => $this->setting_key
				],
				'notes' => esc_html__('Columns used in extension Favourites. Press and wait to reorder.', 'profit-products-tables-for-woocommerce')
			];

			return $rows;
		}, 10, 1);

		add_action('woot_extend_settings_default', function ($defaults) {
			$defaults[$this->setting_key] = $this->default_fields;
			return $defaults;
		}, 10, 1);

		//***
		//synhronize favourites with user data
		add_action('wp_login', function ($user_login, $user) {
			$res1 = (array) get_user_meta($user->ID, $this->storage_key, true);

			$res2 = $this->storage->get_val($this->storage_key);

			if (!empty($res2)) {
				$res2 = json_decode($res2, true);
			}

			if (!$res2) {
				$res2 = [];
			}

			//***

			$table_data = array_unique(array_merge($res1, $res2));

			update_user_meta($user->ID, $this->storage_key, $table_data);
			$this->storage->set_val($this->storage_key, json_encode($table_data));
		}, 10, 2);

		//***

		add_action('woot_include_assets', function () {
			// The handle must stay unique: WordPress silently ignores an enqueue whose
			// handle is already registered, so sharing the bare plugin slug across the
			// extensions dropped these assets and the heart icon never got its CSS.
			wp_enqueue_script('profit-products-tables-for-woocommerce-favourites', WOOT_FAVOURITES_LINK . 'assets/js/favourites.js', [], WOOT_FAVOURITES_VERSION);
			wp_enqueue_style('profit-products-tables-for-woocommerce-favourites', WOOT_FAVOURITES_LINK . 'assets/css/favourites.css', [], WOOT_FAVOURITES_VERSION);
		}, 10);

		//***

		$this->add_shortcode();
	}

	/**
	 * Hook init
	 *
	 * @since 1.0.0
	 * 
	 * @return void
	 */
	public function init() {
		add_action($this->action, function ($args) {

			$selected_columns_keys = explode(',', WOOT_Settings::get($this->setting_key));

			$columns = apply_filters("{$this->action}_profile_extend", [
				0 => [
					'post_type_fixed' => FALSE
				],
				'actions' => [
					'title' => 'X'
				]
				], $selected_columns_keys);

			//***

			$selected_columns = [];
			$woo_profile = apply_filters(WOOT::get_table_action(0), 0);

			if (!empty($columns) AND !empty($selected_columns_keys)) {
				$selected_columns[0] = $columns[0];
				foreach ($selected_columns_keys as $key) {
					if (isset($columns[$key])) {
						$selected_columns[$key] = $columns[$key];
					} else {
						$selected_columns[$key] = $woo_profile[$key];
					}
				}
			}

			$selected_columns['actions'] = ['title' => 'X']; //del button

			return $selected_columns;
		});
	}

	/**
	 * Get user data
	 *
	 * @since 1.0.0
	 * @param int $user_id user ID
	 * @return array
	 */
	public function get_user_data($user_id = 0) {
		if (!$user_id) {
			$user_id = get_current_user_id();
		}

		if (is_user_logged_in() OR $user_id > 0) {
			$res = (array) get_user_meta($user_id, $this->storage_key, true);
		} else {
			$res = $this->storage->get_val($this->storage_key);
			if (!empty($res)) {
				$res = json_decode($res, true);
			}
		}

		if (!$res) {
			$res = [];
		}

		return $res;
	}

	/**
	 * Get table HTML
	 *
	 * @since 1.0.0
	 * @param string $table_html_id table HTML id
	 * @param string $post_type
	 * @param boolean $is_shortcode
	 * @param array $args
	 * @return string table HTML
	 */
	public function get($table_html_id, $post_type = 'product', $skin = '', $is_shortcode = false, $args = []) {
		$table_data = [];
		$posts_ids = $this->get_user_data(isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0);

		//***
		//woot_favourites_profile_extend
		$profile = apply_filters($this->action, $args);
		//at the end insert del btn
		$profile['actions'] = [
			'title' => 'X',
			'action' => function ($post_id) {
				return WOOT_HELPER::draw_html_item('a', [
					'href' => "javascript: woot_favourites.remove({$post_id});void(0);",
					'title' => esc_html__('delete', 'profit-products-tables-for-woocommerce'),
					'class' => 'woot-dash-btn-single'
					], '<i class="woot-icon">&#xe803;</i>');
			}
		];

		if (!empty($posts_ids)) {
			foreach ($posts_ids as $post_id) {
				if ($post_type === get_post_field('post_type', $post_id)) {
					$tmp = [];
					$tmp['pid'] = $post_id;
					foreach ($profile as $field_key => $field) {
						if ($field_key === 0) {
							continue;
						}

						if (isset($field['action'])) {
							$tmp[$field_key] = $field['action']($post_id);
						}
					}

					$table_data[] = $tmp;
				}
			}
		}

		//***

		$table_data = [
			'mode' => 'json',
			'action' => $this->action,
			'orderby' => 'title',
			'order' => 'asc',
			'cells_width' => isset($args['cells_width']) ? $args['cells_width'] : '',
			'per_page_position' => isset($args['per_page_position']) ? $args['per_page_position'] : 't',
			'per_page_sel_position' => 'none',
			'per_page' => isset($args['per_page']) ? $args['per_page'] : 10,
			'compact_view_width' => isset($args['compact_view_width']) ? $args['compact_view_width'] : 600,
			'style' => boolval($skin) ? woot()->skins->get_theme_css($skin, $table_html_id) : '',
			'table_data' => $table_data
		];

		if ($is_shortcode) {
			return woot()->draw_table_data($table_data, $table_html_id);
		}

		return woot()->draw_table_data($table_data, $table_html_id, FALSE);
	}

	/**
	 * Get table HTML by ajax
	 *
	 * @since 1.0.0  
	 * 
	 * @return string table HTML
	 */
	public function get_table() {

		// created by woot_helper.create_id() in favourites.js: prefix + [a-z0-9]
		$table_html_id = isset($_REQUEST['table_html_id']) ? sanitize_key($_REQUEST['table_html_id']) : '';
		$post_type = isset($_REQUEST['post_type']) ? sanitize_key($_REQUEST['post_type']) : 'product';

		// skin is a file name, keep the original case but strip path traversal;
		// get_theme_css() applies the same rule centrally for every caller
		$skin = '';
		if (isset($_REQUEST['skin'])) {
			$skin = preg_replace('/[^A-Za-z0-9_-]/', '', basename(sanitize_text_field(wp_unslash($_REQUEST['skin']))));
		}

		// Argument order here is get($table_html_id, $post_type, $skin) -- this
		// extension's signature differs from the one in woot-compare. Passing a
		// value into the 4th parameter would set $is_shortcode and make get()
		// return the table wrapped in a <div>, while favourites.js reads this
		// response with response.json() and expects the bare JSON payload.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get() returns the JSON payload assembled by draw_table_data()
		die($this->get($table_html_id, $post_type, $skin));
	}

	/**
	 * Add one favorite product by ajax
	 *
	 * @since 1.0.0  
	 * 
	 * @return output string
	 */
	public function add() {
		$table_data = $this->get_user_data();
		$table_data[] = intval($_REQUEST['post_id']);
		$table_data = array_unique($table_data);

		update_user_meta(get_current_user_id(), $this->storage_key, $table_data);
		$this->storage->set_val($this->storage_key, json_encode($table_data));

		die(json_encode($table_data));
	}

	/**
	 * Delete one favorite product by ajax
	 *
	 * @since 1.0.0  
	 * 
	 * @return output string
	 */
	public function delete() {
		$table_data = array_diff($this->get_user_data(), [intval($_REQUEST['post_id'])]);

		update_user_meta(get_current_user_id(), $this->storage_key, $table_data);
		$this->storage->set_val($this->storage_key, json_encode($table_data));

		die(json_encode($table_data));
	}

	/**
	 * Add to system favourites products shortcode
	 * 
	 * @since 1.0.0  
	 * 
	 * @return void
	 */
	private function add_shortcode() {
		add_shortcode('woot_favourites', function ($args) {

			if (!function_exists('woot')) {
				return;
			}

			woot()->include_assets();
			$args = (array) $args;

			if (!isset($args['columns'])) {
				$args['columns'] = WOOT_Settings::get($this->setting_key);
			}

			if (isset($args['table_id'])) {
				$args['id'] = intval($args['table_id']);
			}

			//***

			$table_html_id = uniqid('t');

			add_action('wp_print_footer_scripts', function () use ($table_html_id, $args) {
				$post_type = 'product';
				$_REQUEST['post_type'] = $post_type;
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $this->get() returns prepared table markup built by render_html()/draw_table_data()
				echo $this->get($table_html_id, $post_type, isset($args['skin']) ? $args['skin'] : '', true, $args);
			});

			$share = '';
			if (isset($args['share']) AND !empty($args['share'])) {
				if (is_user_logged_in()) {
					$share = '<strong>' . esc_html__('Share your favorites products', 'profit-products-tables-for-woocommerce') . '</strong>:<br />' . WOOT_HELPER::draw_html_item('input', [
							'type' => 'text',
							'class' => 'woot-share-link-input',
							'readonly' => 'readonly',
							'value' => $args['share'] . '?user_id=' . get_current_user_id()
					]);
				}
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is generated and escaped inside render_html()
			return WOOT_HELPER::render_html('views/table.php', array(
					'table_html_id' => $table_html_id,
					'table_id' => 0,
					'hide_text_search' => true,
					'action' => $this->action,
					'style' => isset($args['skin']) ? woot()->skins->get_theme_css($args['skin'], $table_html_id) : '',
				)) . $share;
		});

//+++

		add_shortcode('woot_favourites_single_btn', function ($args) {

			if (!function_exists('woot')) {
				return;
			}

			woot()->include_assets();
			$args = (array) $args;
			$post_id = 0;

			if (isset($args['id'])) {
				$post_id = intval($args['id']);
			}

			if (!$post_id) {
				global $post;
				if (is_object($post)) {
					$post_id = $post->ID;
				}
			}

			$popup_title = '';
			if (isset($args['popup_title'])) {
				$popup_title = trim($popup_title, '"');
				$popup_title = trim($popup_title, "'");
				unset($args['popup_title']);
			}

			$help_title = '';
			if (isset($args['help_title'])) {
				$help_title = WOOT_Vocabulary::get($args['help_title']);
				$help_title = trim($help_title, '"');
				$help_title = trim($help_title, "'");
				unset($args['help_title']);
			}

			$help_link = '';
			if (isset($args['help_link'])) {
				$help_link = $args['help_link'];
				unset($args['help_link']);
			}

			$skin = '';
			if (isset($args['skin'])) {
				$skin = $args['skin'];
			}

			if ($post_id > 0) {
				$selected = in_array($post_id, $this->get_user_data());
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is generated and escaped inside render_html()
				return WOOT_HELPER::render_html(WOOT_FAVOURITES_PATH . 'views/single.php', [
						'post_id' => $post_id,
						'selected' => $selected,
						'help_link' => $help_link,
						'help_title' => $help_title,
						'popup_title' => $popup_title,
						'skin' => $skin,
						], false);
			}
		});
	}
}

$GLOBALS['WOOT_Favourites'] = new WOOT_Favourites();
