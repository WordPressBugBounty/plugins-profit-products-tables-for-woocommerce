<?php

/*
  Plugin Name: WOOT - Compare
  Plugin URI: https://products-tables.com/extension/compare/
  Description: WOOT extension. Allows to create table with products to compare them.
  Requires at least: WP 4.9
  Tested up to: WP 6.0
  Author: realmag777
  Author URI: https://pluginus.net/
  Version: 1.0.2
  Requires PHP: 7.0
  Tags: compare, products tables, woocommerce, products
  Text Domain: woot-compare
  Domain Path: /languages
  WC requires at least: 3.6
  WC tested up to: 6.6
  Forum URI: https://pluginus.net/support/forum/woot-woocommerce-active-products-tables/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WOOT_COMPARE_LINK', plugin_dir_url(__FILE__));
define('WOOT_COMPARE_VERSION', '1.0.2');

include_once 'lib/storage.php';

final class WOOT_Compare {

    private $storage = null;
    private $storage_key = 'woot_compare';
    public $action = 'woot_compare';
    public $setting_key = 'compare_columns';
    public $default_fields = 'id,title,price,sku,add_to_cart,actions';
    public $ids_from_shortcod = []; //buffer

    public function __construct() {
        $this->storage = new WOOT_STORAGE();
        add_action('init', array($this, 'init'), 999);
        add_action('wp_ajax_woot_get_table_compare', array($this, 'get_table'));
        add_action('wp_ajax_nopriv_woot_get_table_compare', array($this, 'get_table'));

        add_action('wp_ajax_woot_add_to_compare', array($this, 'add'));
        add_action('wp_ajax_nopriv_woot_add_to_compare', array($this, 'add'));

        add_action('wp_ajax_woot_delete_from_compare', array($this, 'delete'));
        add_action('wp_ajax_nopriv_woot_delete_from_compare', array($this, 'delete'));

        //***

        add_action('woot_helper_lang', function ($words) {

            $words['compare_products'] = WOOT_Vocabulary::get(esc_html__('Compare', 'woot-compare'));

            return $words;
        });

        //***

        add_action('woot_profile_extend', function ($profile, $action_name) {

            $profile['compare'] = [
                'title' => esc_html__('Compare', 'woot-compare'),
                'options' => ['css-background', 'css-media-hide'],
                'order' => FALSE,
                'action' => function ($post_id) {

                    $selected_ids = $this->get_user_data();
                    $is = in_array($post_id, $selected_ids);

                    $table_id = 0;
                    if (isset($_REQUEST['table_id'])) {
                        $table_id = intval($_REQUEST['table_id']);
                    }

                    return WOOT_HELPER::draw_switcher('compare', $is, $post_id, 'woot-compare-product', ['table_id' => $table_id]) .
                    WOOT_HELPER::draw_html_item('a', [
                        'href' => 'javascript: void(0);',
                        'data-key' => 'compare',
                        'class' => 'woot-compare-btn'
                            ], WOOT_Vocabulary::get(esc_html__('to Compare', 'woot-compare')));
                }
            ];

            return $profile;
        }, 10, 2);

        //***

        add_action('woot_extend_settings', function ($rows) {
            $selected = explode(',', WOOT_Settings::get($this->setting_key));

            foreach (apply_filters('woot_woocommerce_tables', 0, []) as $key => $value) {
                if (isset($value['title']) AND $key !== 'compare') {
                    $options[$key] = $value['title'];
                }
            }

            //+++

            $rows[] = [
                'id' => 0,
                'title' => esc_html__('Compare columns', 'woot-compare'),
                'value' => [
                    'value' => WOOT_HELPER::draw_select([
                        'class' => 'woot-multiple-select',
                        'multiple' => '',
                        'data-action' => 'woot_save_settings_field',
                        'data-values' => WOOT_Settings::get($this->setting_key)
                            ], $options, $selected),
                    'custom_field_key' => $this->setting_key
                ],
                'notes' => esc_html__('Columns used in extension Compare. Press and wait to reorder.', 'woot-compare')
            ];

            return $rows;
        }, 10, 1);

        add_action('woot_extend_settings_default', function ($defaults) {
            $defaults[$this->setting_key] = $this->default_fields;
            return $defaults;
        }, 10, 1);

        //***
        //synhronize compare with user data
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
            wp_enqueue_script('woot-compare', WOOT_COMPARE_LINK . 'assets/js/compare.js', [], WOOT_COMPARE_VERSION);
            wp_enqueue_style('woot-compare', WOOT_COMPARE_LINK . 'assets/css/compare.css', [], WOOT_COMPARE_VERSION);
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


        add_action($this->action, function () {

            $is_predefined = false;
            if (!empty($this->ids_from_shortcode)) {
                $posts_ids = $this->ids_from_shortcode;
                $this->ids_from_shortcode = []; //clean buffer
                $is_predefined = true;
            } else {
                $posts_ids = $this->get_user_data();
            }

            $columns = [
                'compare_fields' => [
                    'title' => esc_html__('Fields', 'woot-compare')
                ]
            ];

            if (!empty($posts_ids)) {
                foreach ($posts_ids as $post_id) {
                    if ($post_id > 0) {
                        $del_btn = '';
                        if (!$is_predefined) {
                            $del_btn = WOOT_HELPER::draw_html_item('a', [
                                        'href' => "javascript: woot_compare.remove({$post_id}); void(0);",
                                        'class' => 'woot_compare_remove_btn woot-do-not-print'
                                            ], '<i class="woot-icon">&#xe803;</i>');
                        }

                        $columns['product_compare_' . $post_id] = [
                            'title' => WOOT_WooCommerce::get_product($post_id)->get_title() . $del_btn
                        ];
                    }
                }
            }

            return $columns;
        });
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
    public function get($table_html_id, $post_type = 'product', $is_shortcode = false, $args = [], $skin = '') {

        if (isset($args['ids']) AND!empty($args['ids'])) {
            $this->ids_from_shortcode = $posts_ids = explode(',', $args['ids']);
        } else {
            $posts_ids = $this->get_user_data();
        }

        $selected_columns_keys = explode(',', WOOT_Settings::get($this->setting_key));
        $profile = apply_filters(WOOT_WooCommerce::$action, NULL);

        $table_data = [];

        if (!empty($posts_ids)) {
            foreach ($posts_ids as $key => $value) {
                if (!$value) {
                    unset($posts_ids[$key]);
                }
            }
        }

        if (!empty($selected_columns_keys) AND!empty($posts_ids)) {

            foreach ($selected_columns_keys as $field_key) {

                if (!isset($profile[$field_key]) OR $field_key === 'compare') {
                    continue;
                }

                $tmp = [];
                $tmp['pid'] = $field_key;
                $tmp['compare_fields'] = WOOT_Vocabulary::get($profile[$field_key]['title']);

                if ($field_key === 'add_to_cart') {
                    $tmp['compare_fields'] = '<span data-key="add_to_cart">' . $profile[$field_key]['title'] . '</span>';
                }

                if ($field_key === 'favourites') {
                    $tmp['compare_fields'] = '<span data-key="favourites">' . $profile[$field_key]['title'] . '</span>';
                }

                foreach ($posts_ids as $post_id) {
                    $tmp['product_compare_' . $post_id] = $profile[$field_key]['action']($post_id);
                }

                $table_data[] = $tmp;
            }
        }

        //***
        $stop_notice = '';
        if (!is_array($table_data) OR count($table_data) <= 1) {
            $stop_notice = '<div class="woot-notice">' . WOOT_Vocabulary::get(esc_html__('No products added to compare!', 'woot-compare')) . '</div>';
        }

        $table_data = [
            'mode' => 'json',
            'action' => $this->action,
            'orderby' => 'none',
            'order' => 'none',
            'per_page_position' => 'none',
            'per_page_sel_position' => 'none',
            'per_page' => -1,
            'stop_notice' => $stop_notice,
            'use_flow_header' => 0,
            'skin' => $skin,
            'style' => !empty($skin) ? woot()->skins->get_theme_css($skin, $table_html_id) : '',
            'table_data' => $table_data
        ];

        //+++

        if ($is_shortcode) {
            if (isset($args['columns'])) {
                unset($args['columns']);
            }

            return woot()->draw_table_data(array_merge($table_data, $args), $table_html_id);
        }

        //+++

        return woot()->draw_table_data($table_data, $table_html_id, FALSE);
    }

    /**
     * Get user data
     *
     * @since 1.0.0
     * @param int $user_id user ID
     * @return array
     */
    public function get_user_data() {
        if (is_user_logged_in()) {
            $res = (array) get_user_meta(get_current_user_id(), $this->storage_key, true);
        } else {
            $res = $this->storage->get_val($this->storage_key);
            if (!empty($res)) {
                $res = json_decode($res, true);
            }
        }

        if (!$res) {
            $res = [1, 92];
        }

        return $res;
    }

    /**
     * Get table HTML by ajax
     *
     * @since 1.0.0  
     * 
     * @return string table HTML
     */
    public function get_table() {
        $ids = '';
        if (isset($_REQUEST['ids']) AND!empty($_REQUEST['ids'])) {
            $ids = $_REQUEST['ids'];
        }
        die($this->get($_REQUEST['table_id'], $_REQUEST['post_type'], false, ['ids' => $ids], $_REQUEST['skin']));
    }

    /**
     * Add one product for compare by ajax
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
     * Delete one compared product by ajax
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
     * Add to system compared products shortcode
     * 
     * @since 1.0.0  
     * 
     * @return void
     */
    private function add_shortcode() {

        add_shortcode('woot_compare', function ($args) {

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
                echo $this->get($table_html_id, $post_type, true, $args);
            });

            $skin = '';
            if (isset($args['skin'])) {
                $skin = $args['skin'];
            }

            return WOOT_HELPER::render_html('views/table.php', array(
                'table_html_id' => $table_html_id,
                'table_id' => 0,
                'style' => boolval($skin) ? woot()->skins->get_theme_css($skin, $table_html_id) : '',
                'hide_text_search' => true,
                'action' => $this->action
            ));
        });

        //+++

        add_shortcode('woot_compare_btn', function ($args) {

            wp_enqueue_script('woot-compare', WOOT_COMPARE_LINK . 'assets/js/compare.js', [], WOOT_COMPARE_VERSION);
            wp_enqueue_style('woot-compare', WOOT_COMPARE_LINK . 'assets/css/compare.css', [], WOOT_COMPARE_VERSION);

            $args = (array) $args;
            $unique_id = uniqid('gp');

            $ids = '';
            if (isset($args['ids']) AND!empty($args['ids'])) {
                $ids = strval($args['ids']);
            }

            $title = esc_html__('to Compare', 'woot-compare');
            if (isset($args['title']) AND!empty($args['title'])) {
                $title = strval($args['title']);
            }

            $popup_title = esc_html__('Compare Products', 'woot-compare');
            if (isset($args['popup_title']) AND!empty($args['popup_title'])) {
                $popup_title = strval($args['popup_title']);
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

            return WOOT_HELPER::draw_html_item('a', [
                'href' => "javascript: woot_compare.call_popup(\"{$ids}\",\"{$popup_title}\",\"{$help_title}\",\"{$help_link}\",\"{$skin}\"); void(0);",
                'class' => isset($args['css_class']) ? $args['css_class'] : ''
                    ], $title);
        });
    }

}

$GLOBALS['WOOT_Compare'] = new WOOT_Compare();

